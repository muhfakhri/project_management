<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Board;
use App\Models\Project;
use App\Models\CardAssignment;
use App\Models\Notification;
use App\Models\Subtask;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks.
     */
    public function index()
    {
        // Get tasks from projects where user is a member
        $tasks = Card::whereHas('board.project.members', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['board.project', 'creator', 'assignments.user'])->paginate(15);

        // Check if user can manage tasks (Admin or Team Lead in any project)
        $canManageTasks = auth()->user()->projectMembers()
            ->whereIn('role', ['Project Admin', 'Team Lead'])
            ->exists();

        return view('tasks.index', compact('tasks', 'canManageTasks'));
    }

    /**
     * Display user's assigned tasks.
     */
    public function myTasks()
    {
        $tasks = Card::whereHas('assignments', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['board.project', 'creator', 'assignments.user', 'subtasks'])->paginate(15);

        // Compute simple task stats for the current user (used in the my-tasks view)
        $userId = auth()->id();
        $baseQuery = Card::whereHas('assignments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        $taskStats = [
            'total' => (clone $baseQuery)->count(),
            'todo' => (clone $baseQuery)->where('status', 'todo')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'done')->count(),
        ];

        return view('tasks.my-tasks', compact('tasks', 'taskStats'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        // Get ACTIVE (non-archived) projects where user is Admin or Team Lead
        $projects = Project::whereHas('members', function ($query) {
            $query->where('user_id', auth()->id())
                  ->whereIn('role', ['Project Admin', 'Team Lead']);
        })
        ->where('is_archived', false) // Only active projects
        ->with('boards')
        ->get();

        return view('tasks.create', compact('projects'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'card_title' => 'required|max:100',
            'description' => 'nullable',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignee_id' => 'nullable|exists:users,user_id'
        ]);

        // Get project and check access
        $project = Project::with('members', 'boards')->findOrFail($request->project_id);
        
        // Check if project is archived
        if ($project->is_archived) {
            return back()->withErrors(['project_id' => 'Cannot create task in archived project.'])
                        ->withInput();
        }

        // Check if user is member
        if (!$project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this project.');
        }

        // Only Admin or Team Lead can create tasks
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'Only Project Admin and Team Lead can create tasks.');
        }

        // Find "To Do" board (case insensitive)
        $todoBoard = $project->boards()
            ->whereRaw('LOWER(board_name) = ?', ['to do'])
            ->first();

        if (!$todoBoard) {
            return back()->withErrors(['project_id' => 'Project must have a "To Do" board to create tasks.'])
                        ->withInput();
        }

        // Wrap creation and related inserts in a transaction
        DB::beginTransaction();
        try {
            $task = Card::create([
            'board_id' => $todoBoard->board_id,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'estimated_hours' => $request->estimated_hours ?? 0,
            'status' => 'todo',
            'needs_approval' => true, // ALWAYS require approval for all tasks
            'position' => Card::where('board_id', $todoBoard->board_id)->max('position') + 1
        ]);
            // Single assignee only (radio/select named assignee_id)
            $assigneeId = $request->input('assignee_id');
            if ($assigneeId) {
                // Ensure assignee is a project member or project creator
                if (!$project->members->contains('user_id', (int)$assigneeId) && $project->created_by !== (int)$assigneeId) {
                    return back()->withErrors(['assignee_id' => 'User must be a member of the project.'])->withInput();
                }

                // Check if user is Project Admin (they can handle multiple projects/tasks)
                $isProjectAdmin = \App\Models\ProjectMember::where('user_id', $assigneeId)
                    ->where('role', 'Project Admin')
                    ->exists();

                // Enforce: Non-admin assignees cannot have other active tasks (todo or in_progress)
                if (!$isProjectAdmin) {
                    $activeTask = CardAssignment::where('user_id', $assigneeId)
                        ->whereHas('card', function ($q) {
                            $q->whereIn('status', ['todo', 'in_progress']);
                        })
                        ->with('card')
                        ->first();
                        
                    if ($activeTask) {
                        $assigneeName = \App\Models\User::find($assigneeId)->full_name ?? \App\Models\User::find($assigneeId)->username;
                        $activeTaskTitle = $activeTask->card->card_title ?? 'Unknown Task';
                        $errorMsg = "Cannot assign {$assigneeName} - this user already has an active task: \"{$activeTaskTitle}\". No multitasking is allowed. Please wait until the current task is completed or approved.";
                        
                        DB::rollBack();
                        return back()
                            ->withErrors(['assignee_id' => $errorMsg])
                            ->with('error', $errorMsg)
                            ->withInput();
                    }
                }

                // Create the single assignment
                CardAssignment::create([
                    'card_id' => $task->card_id,
                    'user_id' => $assigneeId,
                    'assignment_status' => 'assigned'
                ]);

                // Send notification to assigned user
                Notification::create_notification(
                    $assigneeId,
                    'task_assigned',
                    'New Task Assigned',
                    (auth()->user()->full_name ?? auth()->user()->username) . ' assigned you to task: ' . $task->card_title,
                    [
                        'task_id' => $task->card_id,
                        'task_title' => $task->card_title,
                        'project_id' => $project->project_id,
                        'assigned_by' => auth()->id()
                    ],
                    route('tasks.show', $task)
                );
            }

            // Create subtasks if provided (name: subtasks[])
            $subtasks = $request->input('subtasks', []);
            if (is_array($subtasks) && count(array_filter($subtasks)) > 0) {
                foreach ($subtasks as $subtaskTitle) {
                    $subtaskTitle = trim($subtaskTitle);
                    if (empty($subtaskTitle)) continue;

                    Subtask::create([
                        'card_id' => $task->card_id,
                        'subtask_title' => $subtaskTitle,
                        'status' => 'pending',
                        'created_by' => auth()->id()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('tasks.index')->with('success', 'Task created successfully in "To Do" board!');
        } catch (\Exception $e) {
            DB::rollBack();
            // If creation failed, delete partial task if exists
            if (isset($task) && $task instanceof Card) {
                try { $task->delete(); } catch (\Exception $_) {}
            }

            return back()->withErrors(['error' => 'Failed to create task: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified task.
     */
    public function show(string $id)
    {
        $task = Card::with([
            'board.project', 
            'creator', 
            'assignments.user', 
            'subtasks.card.board.project', 
            'comments.user',
            'timeLogs.user'
        ])->findOrFail($id);

        // Check access
        if (!$task->board->project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this task.');
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(string $id)
    {
        $task = Card::with(['board.project.members.user', 'assignments'])->findOrFail($id);

        // Check access
        if (!$task->board->project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this task.');
        }

        $projects = Project::whereHas('members', function ($query) {
            $query->where('user_id', auth()->id());
        })->with('boards')->get();
        $boards = $projects->flatMap(function ($p) { return $p->boards; })->values();

        // Get available members (project members not already assigned to this task)
        $assignedUserIds = $task->assignments->pluck('user_id')->toArray();
        $availableMembers = $task->board->project->members()
            ->whereNotIn('user_id', $assignedUserIds)
            ->where('role', '!=', 'Team Lead')
            ->where('role', '!=', 'Project Admin')
            ->with('user')
            ->get();

        return view('tasks.edit', compact('task', 'projects', 'boards', 'availableMembers'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, string $id)
    {
        $task = Card::with('board.project')->findOrFail($id);

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot update tasks in archived projects.');
        }

        // Check access
        if (!$task->board->project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this task.');
        }

        $request->validate([
            'card_title' => 'required|max:100',
            'description' => 'nullable',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,in_progress,review,done',
            'estimated_hours' => 'nullable|numeric|min:0'
        ]);

        $oldBoardId = $task->board_id;
        
        $task->update($request->only([
            'card_title', 'description', 'due_date', 'priority', 
            'status', 'estimated_hours'
        ]));

        // Auto-move task to matching board based on status
        $moved = $task->moveToMatchingBoard();
        $task->refresh();

        $message = 'Task updated successfully!';
        if ($moved && $task->board_id != $oldBoardId) {
            $message .= ' Task moved to "' . $task->board->board_name . '" board.';
        }

        return redirect()->route('tasks.show', $task->card_id)->with('success', $message);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(string $id)
    {
        $task = Card::with('board.project')->findOrFail($id);

        // Prevent deleting locked (approved & done) tasks
        if ($task->isLocked()) {
            return back()->with('error', 'This task has been approved and is locked. Deletion is not allowed.');
        }

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot delete tasks in archived projects.');
        }

        // Check access and permission
        if (!$task->board->project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this task.');
        }

        // Only Admin or Team Lead can delete tasks
        if (!$task->board->project->canManageTeam(auth()->id())) {
            abort(403, 'Only Project Admin and Team Lead can delete tasks.');
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    /**
     * Assign a user to a task.
     */
    public function assign(Request $request, Card $task)
    {
        // Load relationships
        $task->load('board.project');

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot assign users to tasks in archived projects.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id'
        ]);

        // Enforce only one assignee per task
        if ($task->assignments()->count() >= 1) {
            $msg = 'This task already has an assignee. Only one user is allowed per task.';
            return back()->withErrors(['user_id' => $msg])->with('error', $msg);
        }

        // Check if user has permission to assign (must be project member)
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to assign members to this task.');
        }

        // Only Admin or Team Lead can assign tasks
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'Only Project Admin and Team Lead can assign tasks.');
        }

        // Check if assignee is a project member
        if (!$project->members->contains('user_id', $request->user_id) && $project->created_by !== $request->user_id) {
            $msg = 'User must be a member of the project.';
            return back()->withErrors(['user_id' => $msg])->with('error', $msg)->withInput();
        }

        // Check if user is Project Admin (they can handle multiple projects/tasks)
        $isProjectAdmin = \App\Models\ProjectMember::where('user_id', $request->user_id)
            ->where('role', 'Project Admin')
            ->exists();

        // Enforce: Non-admin assignees cannot have other active tasks (todo or in_progress)
        if (!$isProjectAdmin) {
            $hasActive = CardAssignment::where('user_id', $request->user_id)
                ->whereHas('card', function ($q) {
                    $q->whereIn('status', ['todo', 'in_progress']);
                })
                ->exists();
            if ($hasActive) {
                $msg = 'This user already has an active task (no multitasking allowed). Only Project Admins can handle multiple tasks.';
                return back()->withErrors(['user_id' => $msg])->with('error', $msg)->withInput();
            }
        }

        // Check if user is already assigned
        if ($task->assignments->contains('user_id', $request->user_id)) {
            $msg = 'User is already assigned to this task.';
            return back()->withErrors(['user_id' => $msg])->with('error', $msg)->withInput();
        }

        // Create assignment
        CardAssignment::create([
            'card_id' => $task->card_id,
            'user_id' => $request->user_id,
            'assignment_status' => 'assigned'
        ]);

        // Send notification to assigned user
        Notification::create_notification(
            $request->user_id,
            'task_assigned',
            'New Task Assigned',
            auth()->user()->full_name . ' assigned you to task: ' . $task->card_title,
            [
                'task_id' => $task->card_id,
                'task_title' => $task->card_title,
                'project_id' => $project->project_id,
                'assigned_by' => auth()->id()
            ],
            route('tasks.show', $task)
        );

        return back()->with('success', 'Team member assigned successfully!');
    }

    /**
     * Remove assignment from a task.
     */
    public function unassign(Card $task, CardAssignment $assignment)
    {
        // Load relationships
        $task->load('board.project');

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot unassign users from tasks in archived projects.');
        }

        // Check if user has permission (must be project member or the assigned user)
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) 
            && $project->created_by !== auth()->id() 
            && $assignment->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to remove this assignment.');
        }

        // Verify assignment belongs to this task
        if ($assignment->card_id !== $task->card_id) {
            abort(404, 'Assignment not found for this task.');
        }

        $assignment->delete();

        return back()->with('success', 'Assignment removed successfully!');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, Card $task)
    {
        // Load relationships
        $task->load('board.project');

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot update task status in archived projects.');
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done'
        ]);

        // Check if user has access to this task
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to update this task.');
        }

        // Simple status update - no approval needed for tasks
        $oldBoardId = $task->board_id;
        $task->update(['status' => $request->status]);

        // Auto-move task to matching board based on status
        $moved = $task->moveToMatchingBoard();
        $task->refresh();

        $message = 'Task status updated successfully!';
        if ($moved && $task->board_id != $oldBoardId) {
            $message .= ' Task moved to "' . $task->board->board_name . '" board.';
        }

        return back()->with('success', $message);
    }

    /**
     * Update task board via AJAX (for Kanban drag & drop)
     */
    public function updateBoardAjax(Request $request, Card $task)
    {
        $request->validate([
            'board_id' => 'required|exists:boards,board_id',
            'position' => 'nullable|integer'
        ]);

        // Check if user can update this task
        $oldBoard = $task->board;
        $project = $oldBoard->project;
        
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this task.'
            ], 403);
        }

        // Check if user has permission to move cards (only Project Admin and Team Lead)
        if (!$project->canManageTeam(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'Only Project Admin and Team Lead can move cards between boards.'
            ], 403);
        }

        // Get the new board and verify it's in the same project
        $newBoard = \App\Models\Board::findOrFail($request->board_id);
        if ($newBoard->project_id !== $project->project_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot move task to a different project.'
            ], 403);
        }

        // Update board, position, and status based on board's status_mapping
        $updateData = [
            'board_id' => $request->board_id,
            'position' => $request->position ?? $task->position
        ];
        
        // If board has status_mapping, update task status accordingly
        if ($newBoard->status_mapping) {
            $updateData['status'] = $newBoard->status_mapping;
        }
        
        $task->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Task moved successfully!',
            'board_name' => $newBoard->board_name,
            'status' => $task->status,
            'task' => $task
        ]);
    }

    /**
     * Start working on a task
     */
    public function startWork(Card $task)
    {
        // Load relationships
        $task->load('board.project');

        // Check if task is locked
        if ($task->isLocked()) {
            return back()->with('error', 'This task is locked. Cannot start work on completed and approved tasks.');
        }

        // Check if user can work on this task
        if (!$task->canWorkOn(auth()->user())) {
            return back()->with('error', 'You are not authorized to work on this task. Only assigned members can track time.');
        }

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot start work on tasks in archived projects.');
        }

        // Check if user can track time (only Developer/Designer, not Team Lead/Project Admin)
        if (!auth()->user()->canTrackTimeInProject($task->board->project_id)) {
            return back()->with('error', 'Only Developers and Designers can track time on tasks. Team Leads and Project Admins are supervisors.');
        }

        // Check if user has access to this task
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to start this task.');
        }

        // Enforce no multitasking: user cannot start this task if they have another task in progress
        $hasInProgress = CardAssignment::where('user_id', auth()->id())
            ->whereHas('card', function ($q) use ($task) {
                $q->where('status', 'in_progress')
                  ->where('card_id', '!=', $task->card_id);
            })
            ->exists();
        if ($hasInProgress) {
            return back()->with('error', 'You already have a task in progress. Finish it before starting another task.');
        }

        // Check if already started
        if ($task->started_at) {
            return back()->with('error', 'Task has already been started.');
        }

        $task->startWork();
        
        // Refresh to get updated board info
        $task->refresh();
        
        $message = 'Task started! Time tracking is now active.';
        if ($task->board->status_mapping === 'in_progress') {
            $message .= ' Task moved to "' . $task->board->board_name . '" board.';
        }

        return back()->with('success', $message);
    }

    /**
     * Pause work on a task
     */
    public function pauseWork(Card $task)
    {
        // Load relationships
        $task->load('board.project');

        // Check if task is locked
        if ($task->isLocked()) {
            return back()->with('error', 'This task is locked. Cannot pause work on completed and approved tasks.');
        }

        // Check if user can work on this task
        if (!$task->canWorkOn(auth()->user())) {
            return back()->with('error', 'You are not authorized to work on this task.');
        }

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot pause tasks in archived projects.');
        }

        // Check if user can track time
        if (!auth()->user()->canTrackTimeInProject($task->board->project_id)) {
            return back()->with('error', 'Only Developers and Designers can track time on tasks.');
        }

        // Check if user has access to this task
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to pause this task.');
        }

        // Check if task is in progress and not already paused
        if (!$task->started_at) {
            return back()->with('error', 'Task has not been started yet.');
        }

        if ($task->paused_at) {
            return back()->with('error', 'Task is already paused.');
        }

        $task->pauseWork();

        return back()->with('success', 'Task paused. Time tracking stopped.');
    }

    /**
     * Resume work on a task
     */
    public function resumeWork(Card $task)
    {
        // Load relationships
        $task->load('board.project');

        // Check if task is locked
        if ($task->isLocked()) {
            return back()->with('error', 'This task is locked. Cannot resume work on completed and approved tasks.');
        }

        // Check if user can work on this task
        if (!$task->canWorkOn(auth()->user())) {
            return back()->with('error', 'You are not authorized to work on this task.');
        }

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot resume tasks in archived projects.');
        }

        // Check if user can track time
        if (!auth()->user()->canTrackTimeInProject($task->board->project_id)) {
            return back()->with('error', 'Only Developers and Designers can track time on tasks.');
        }

        // Check if user has access to this task
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to resume this task.');
        }

        // Check if task is paused
        if (!$task->paused_at) {
            return back()->with('error', 'Task is not paused.');
        }

        $task->resumeWork();

        return back()->with('success', 'Task resumed. Time tracking continues.');
    }

    /**
     * Complete work on a task
     */
    public function completeWork(Card $task)
    {
        // Load relationships
        $task->load('board.project', 'assignees');

        // Check if task is locked
        if ($task->isLocked()) {
            return back()->with('error', 'This task is already completed and approved. It is locked.');
        }

        // Check if user can work on this task
        if (!$task->canWorkOn(auth()->user())) {
            return back()->with('error', 'You are not authorized to complete this task.');
        }

        // Check if project is archived
        if ($task->board->project->isArchived()) {
            return back()->with('error', 'Cannot complete tasks in archived projects.');
        }

        // Check if user can track time
        if (!auth()->user()->canTrackTimeInProject($task->board->project_id)) {
            return back()->with('error', 'Only Developers and Designers can track time on tasks.');
        }

        // Check if user has access to this task
        $project = $task->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to complete this task.');
        }

        // Check if task is in progress
        if (!$task->started_at) {
            return back()->with('error', 'Task has not been started yet.');
        }

        if ($task->completed_at) {
            return back()->with('error', 'Task is already completed.');
        }

        $task->completeWork();
        
        // Refresh to get updated board info
        $task->refresh();
        
        // If task now needs approval, notify approvers (Project Admins & Team Leads)
        if ($task->needs_approval && !$task->is_approved) {
            $projectId = $task->board->project->project_id;
            $approverIds = \App\Models\ProjectMember::where('project_id', $projectId)
                ->whereIn('role', ['Project Admin', 'Team Lead'])
                ->pluck('user_id');

            foreach ($approverIds as $approverId) {
                // Skip notifying the completer if they are also approver
                if ((int)$approverId === (int)auth()->id()) continue;

                Notification::create_notification(
                    $approverId,
                    'task_pending_approval',
                    'Task Awaiting Approval',
                    (auth()->user()->full_name ?? auth()->user()->username) . ' completed task: ' . $task->card_title . '. Please review and approve.',
                    [
                        'task_id' => $task->card_id,
                        'task_title' => $task->card_title,
                        'project_id' => $projectId,
                        'completed_by' => auth()->id()
                    ],
                    route('tasks.show', $task)
                );
            }
        }
        
        $message = 'Task completed! Waiting for Team Lead approval. Total working time: ' . $task->getFormattedWorkingTime();
        if ($task->board->status_mapping === 'review') {
            $message .= ' Task moved to "' . $task->board->board_name . '" board for review.';
        }

        return back()->with('success', $message);
    }

    /**
     * Approve a task (Team Lead or Project Admin only)
     */
    public function approveTask(Card $task)
    {
        // Check if user can approve
        if (!$task->canApprove(auth()->user())) {
            return back()->with('error', 'Only Project Admin or Team Lead can approve tasks.');
        }

        // Check if task needs approval
        if (!$task->needs_approval) {
            return back()->with('error', 'This task does not need approval.');
        }

        if ($task->is_approved) {
            return back()->with('error', 'This task has already been approved.');
        }

        // Approve the task
        $task->approve(auth()->user());
        
        // Refresh to get updated board info
        $task->refresh();
        
        // Notify all assignees that task was approved
        $task->loadMissing('assignees');
        foreach ($task->assignees as $assignee) {
            if ((int)$assignee->user_id === (int)auth()->id()) continue;
            Notification::create_notification(
                $assignee->user_id,
                'task_approved',
                'Task Approved',
                'Your task "' . $task->card_title . '" was approved by ' . (auth()->user()->full_name ?? auth()->user()->username) . '.',
                [
                    'task_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'approved_by' => auth()->id()
                ],
                route('tasks.show', $task)
            );
        }
        
        $message = 'Task approved successfully!';
        if ($task->board->status_mapping === 'done') {
            $message .= ' Task moved to "' . $task->board->board_name . '" board.';
        }

        return back()->with('success', $message);
    }

    /**
     * Reject a task (Team Lead or Project Admin only)
     */
    public function rejectTask(Request $request, Card $task)
    {
        // Check if user can reject
        if (!$task->canApprove(auth()->user())) {
            return back()->with('error', 'Only Project Admin or Team Lead can reject tasks.');
        }

        // Validate rejection reason
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Check if task needs approval
        if (!$task->needs_approval) {
            return back()->with('error', 'This task does not need approval.');
        }

        // Reject the task
        $task->reject(auth()->user(), $request->rejection_reason);
        
        // Refresh to get updated board info
        $task->refresh();
        
        // Notify all assignees about rejection with reason
        $task->loadMissing('assignees');
        foreach ($task->assignees as $assignee) {
            if ((int)$assignee->user_id === (int)auth()->id()) continue;
            Notification::create_notification(
                $assignee->user_id,
                'task_rejected',
                'Task Rejected',
                'Your task "' . $task->card_title . '" was rejected by ' . (auth()->user()->full_name ?? auth()->user()->username) . '. Reason: ' . $request->rejection_reason,
                [
                    'task_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'rejected_by' => auth()->id(),
                    'rejection_reason' => $request->rejection_reason
                ],
                route('tasks.show', $task)
            );
        }
        
        $message = 'Task rejected and moved back to In Progress. Developer can fix and resubmit.';
        if ($task->board->status_mapping === 'in_progress') {
            $message = 'Task rejected and moved to "' . $task->board->board_name . '" board. Developer can fix and resubmit.';
        }

        return back()->with('success', $message);
    }
}

