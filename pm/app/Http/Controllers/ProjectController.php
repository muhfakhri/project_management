<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get active (non-archived) projects where user is a member
        $projects = Project::where('is_archived', false)
            ->whereHas('members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['creator', 'members.user'])
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Display all types of approvals (Project Admin & Team Lead)
     */
    public function approvals(\Illuminate\Http\Request $request)
    {
        // Check if user is Project Admin or Team Lead in any project
        $isProjectAdmin = ProjectMember::where('user_id', auth()->id())
            ->where('role', 'Project Admin')
            ->exists();
        
        $isTeamLead = ProjectMember::where('user_id', auth()->id())
            ->where('role', 'Team Lead')
            ->exists();

        // Get all user's projects where they have admin/lead role
        $userProjectIds = ProjectMember::where('user_id', auth()->id())
            ->whereIn('role', ['Project Admin', 'Team Lead'])
            ->pluck('project_id');

        // Get filter parameters
        $filter = $request->get('filter', 'all'); // all, subtasks, tasks
        $projectFilter = $request->get('project', 'all');
        $statusFilter = $request->get('status', 'pending'); // pending, approved, rejected

        // Initialize all collections to empty
        $pendingProjects = collect();
        $approvedProjects = collect();
        $rejectedProjects = collect();
        $pendingTasks = collect();
        $recentlyApprovedTasks = collect();
        $pendingSubtasks = collect();
        $recentlyApprovedSubtasks = collect();
        
        if ($isProjectAdmin) {
            $pendingProjects = Project::where('created_by', auth()->id())
                ->where('completion_status', 'pending_approval')
                ->with(['requester', 'members.user', 'boards.cards'])
                ->orderBy('requested_at', 'desc')
                ->get();

            $approvedProjects = Project::where('created_by', auth()->id())
                ->where('completion_status', 'completed')
                ->with(['requester', 'approver', 'members.user'])
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get();

            $rejectedProjects = Project::where('created_by', auth()->id())
                ->where('completion_status', 'rejected')
                ->with(['requester', 'approver', 'members.user'])
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get();
        }

        // TASK APPROVALS (For Team Lead only)
        if ($isTeamLead) {
            // Build task query with filtering
            $tasksQuery = \App\Models\Card::whereHas('board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true); // Must need approval

            // Apply status filter for tasks
            if ($statusFilter === 'pending') {
                // Pending tasks: not approved and not rejected
                $tasksQuery->where('is_approved', false)
                          ->whereNull('rejection_reason');
            } elseif ($statusFilter === 'approved') {
                $tasksQuery->where('is_approved', true);
            } elseif ($statusFilter === 'rejected') {
                // Handle rejected tasks if applicable
                $tasksQuery->whereNotNull('rejection_reason');
            }

            // Apply project filter
            if ($projectFilter !== 'all') {
                $tasksQuery->whereHas('board', function($q) use ($projectFilter) {
                    $q->where('project_id', $projectFilter);
                });
            }

            $pendingTasks = ($filter === 'all' || $filter === 'tasks') 
                ? $tasksQuery->with(['board.project', 'assignments.user', 'creator'])
                    ->orderBy('updated_at', 'desc')
                    ->get()
                : collect();

            // Recently approved tasks
            $recentlyApprovedTasks = \App\Models\Card::whereHas('board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('is_approved', true)
                ->whereNotNull('approved_at')
                ->with(['board.project', 'approver'])
                ->orderBy('approved_at', 'desc')
                ->limit(10)
                ->get();
        }

        // SUBTASK APPROVALS (For Team Lead only)
        if ($isTeamLead) {
            // Build subtask query with filtering
            $subtasksQuery = \App\Models\SubTask::whereHas('card.board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true); // Must need approval

            // Apply status filter for subtasks
            if ($statusFilter === 'pending') {
                $subtasksQuery->where('status', 'done')
                             ->where('is_approved', false)
                             ->whereNull('rejection_reason');
            } elseif ($statusFilter === 'approved') {
                $subtasksQuery->where('is_approved', true);
            } elseif ($statusFilter === 'rejected') {
                $subtasksQuery->whereNotNull('rejection_reason');
            }

            // Apply project filter
            if ($projectFilter !== 'all') {
                $subtasksQuery->whereHas('card.board', function($q) use ($projectFilter) {
                    $q->where('project_id', $projectFilter);
                });
            }

            $pendingSubtasks = ($filter === 'all' || $filter === 'subtasks')
                ? $subtasksQuery->with(['card.board.project', 'card.assignments.user'])
                    ->whereNotNull('completed_at')
                    ->orderBy('completed_at', 'desc')
                    ->get()
                : collect();

            // Recently approved subtasks
            $recentlyApprovedSubtasks = \App\Models\SubTask::whereHas('card.board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('is_approved', true)
                ->whereNotNull('approved_at')
                ->with(['card.board.project', 'approver'])
                ->orderBy('approved_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get projects for filter dropdown
        $projects = Project::whereIn('project_id', $userProjectIds)
            ->orderBy('project_name')
            ->get();

        // Count statistics
        $stats = [
            'pending_subtasks' => \App\Models\SubTask::whereHas('card.board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('status', 'done')
                ->where('is_approved', false)
                ->whereNull('rejection_reason')
                ->count(),
            
            'pending_tasks' => \App\Models\Card::whereHas('board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('is_approved', false)
                ->whereNull('rejection_reason')
                ->count(),
            
            'approved_today' => \App\Models\SubTask::whereHas('card.board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('is_approved', true)
                ->whereDate('approved_at', today())
                ->count() +
                \App\Models\Card::whereHas('board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->where('is_approved', true)
                ->whereDate('approved_at', today())
                ->count(),
            
            'rejected_count' => \App\Models\SubTask::whereHas('card.board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->whereNotNull('rejection_reason')
                ->count() +
                \App\Models\Card::whereHas('board', function($q) use ($userProjectIds) {
                    $q->whereIn('project_id', $userProjectIds);
                })
                ->where('needs_approval', true)
                ->whereNotNull('rejection_reason')
                ->count(),
        ];

        return view('approvals.index', compact(
            'isProjectAdmin',
            'isTeamLead',
            'pendingProjects',
            'approvedProjects', 
            'rejectedProjects',
            'pendingTasks',
            'recentlyApprovedTasks',
            'pendingSubtasks',
            'recentlyApprovedSubtasks',
            'filter',
            'projectFilter',
            'statusFilter',
            'projects',
            'stats'
        ) + [
            'tasks' => $pendingTasks,
            'subtasks' => $pendingSubtasks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only users who are Project Admin in at least one project can create new projects
        if (!auth()->user()->canCreateProject()) {
            return redirect()->route('projects.index')->with('error', 'Only Project Admins can create new projects. Please contact an administrator.');
        }

        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only users who are Project Admin in at least one project can create new projects
        if (!auth()->user()->canCreateProject()) {
            return redirect()->route('projects.index')->with('error', 'Only Project Admins can create new projects. Please contact an administrator.');
        }

        $request->validate([
            'project_name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'nullable|in:planning,in_progress,done,on_hold',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date'
        ]);

        // Check if user is Project Admin in any project
        $isProjectAdmin = ProjectMember::where('user_id', auth()->id())
            ->where('role', 'Project Admin')
            ->exists();

        // Only non-admins are restricted to one active project
        if (!$isProjectAdmin) {
            // Check if user is already in any active project
            $activeProjectMember = ProjectMember::where('user_id', auth()->id())
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($activeProjectMember) {
                $activeProject = Project::find($activeProjectMember->project_id);
                return redirect()->route('projects.index')->with('error', 'You are already a member of active project: ' . $activeProject->project_name . '. Please complete or archive that project before creating a new one. Only Project Admins can manage multiple projects.');
            }
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'status' => $request->status ?? 'planning',
            'created_by' => auth()->id(),
            'start_date' => $request->start_date,
            'deadline' => $request->deadline
        ]);

        // Add creator as Project Admin
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => auth()->id(),
            'role' => 'Project Admin'
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with(['creator', 'members.user', 'boards.cards'])->findOrFail($id);
        
        // Check if user can view this project
        $canView = false;
        
        // If project is archived, allow viewing if user was ever a member or is admin
        if ($project->is_archived) {
            // Check if user is the project creator
            if ($project->created_by === auth()->id()) {
                $canView = true;
            }
            // Check if user is a member (including archived projects)
            elseif ($project->members->contains('user_id', auth()->id())) {
                $canView = true;
            }
            // System admins can view any archived project
            elseif (auth()->user()->role === 'admin') {
                $canView = true;
            }
        } else {
            // Active projects: only members can view
            if ($project->members->contains('user_id', auth()->id())) {
                $canView = true;
            }
        }
        
        if (!$canView) {
            abort(403, 'Access denied to this project.');
        }

        // Get available users to add (users not already in project)
        $availableUsers = \App\Models\User::whereNotIn('user_id', $project->members->pluck('user_id'))
            ->where('user_id', '!=', $project->created_by)
            ->get();

        return view('projects.show', compact('project', 'availableUsers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::with(['members.user'])->findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can edit this project.');
        }

        // Get available users to add (users not already in project)
        $availableUsers = \App\Models\User::whereNotIn('user_id', $project->members->pluck('user_id'))
            ->where('user_id', '!=', $project->created_by)
            ->get();

        return view('projects.edit', compact('project', 'availableUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can update this project.');
        }

        $request->validate([
            'project_name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'nullable|in:planning,in_progress,done,on_hold',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date'
        ]);

        // Check if status is being changed to 'done'
        $oldStatus = $project->status;
        $newStatus = $request->input('status', $oldStatus);
        
        $project->update($request->only(['project_name', 'description', 'status', 'start_date', 'deadline']));

        // Auto-archive when status changed to 'done'
        if ($oldStatus !== 'done' && $newStatus === 'done') {
            $project->update([
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => auth()->id()
            ]);
            
            // Auto-remove all members from the project when archived
            $removedMembers = ProjectMember::where('project_id', $project->project_id)->get();
            
            foreach ($removedMembers as $member) {
                // Notify each member that they have been removed due to project completion
                \App\Models\Notification::create([
                    'user_id' => $member->user_id,
                    'type' => 'project_archived',
                    'title' => 'Project Completed',
                    'message' => 'You have been removed from project "' . $project->project_name . '" as it has been completed and archived.',
                    'url' => route('projects.show', $project->project_id),
                    'read' => false
                ]);
            }
            
            // Delete all project members
            ProjectMember::where('project_id', $project->project_id)->delete();
            
            return redirect()->route('projects.archived')->with('success', 'Project marked as complete and archived successfully! All members have been removed and can now join new projects.');
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can delete this project.');
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }

    /**
     * Archive a project (mark as completed and archived)
     */
    public function archive(Project $project)
    {
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can archive this project.');
        }

        $project->update([
            'status' => 'done',
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => auth()->id()
        ]);

        // Auto-remove all members from the project when archived
        // This allows them to join new projects
        $removedMembers = ProjectMember::where('project_id', $project->project_id)->get();
        
        foreach ($removedMembers as $member) {
            // Notify each member that they have been removed due to project completion
            \App\Models\Notification::create([
                'user_id' => $member->user_id,
                'type' => 'project_archived',
                'title' => 'Project Completed',
                'message' => 'You have been removed from project "' . $project->project_name . '" as it has been completed and archived.',
                'url' => route('projects.show', $project->project_id),
                'read' => false
            ]);
        }
        
        // Delete all project members
        ProjectMember::where('project_id', $project->project_id)->delete();

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Project archived successfully! All members have been removed and can now join new projects.');
    }

    /**
     * Unarchive a project
     */
    public function unarchive(Project $project)
    {
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can unarchive this project.');
        }

        // Note: When unarchiving, members are NOT automatically re-added
        // because they may now be in other active projects
        // Project Admin will need to manually re-assign members

        $project->update([
            'status' => 'in_progress', // Reset to in_progress when unarchived
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null
        ]);

        $message = 'Project unarchived successfully! Status changed to In Progress. Please note: Members were not automatically re-added. You will need to manually assign team members again.';

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', $message);
    }

    /**
     * Show archived projects
     */
    public function archived()
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            // Admin can see all archived projects
            $projects = Project::where('is_archived', true)
                ->with(['creator', 'boards.cards.assignments.user'])
                ->orderBy('archived_at', 'desc')
                ->paginate(12);
        } else {
            // Get all project IDs where user is member (active or archived)
            $activeProjectIds = ProjectMember::where('user_id', $user->user_id)
                ->pluck('project_id');
            
            // Get all project IDs where user was a member (archived history)
            $archivedProjectIds = \App\Models\ProjectMemberArchive::where('user_id', $user->user_id)
                ->pluck('project_id');
            
            // Combine both lists
            $allProjectIds = $activeProjectIds->merge($archivedProjectIds)->unique();

            // Get archived projects where user is member, was member, or created
            $projects = Project::where('is_archived', true)
                ->where(function($query) use ($user, $allProjectIds) {
                    $query->where('created_by', $user->user_id) // User created it
                        ->orWhereIn('project_id', $allProjectIds); // User is/was a member
                })
                ->with(['creator', 'boards.cards.assignments.user'])
                ->orderBy('archived_at', 'desc')
                ->paginate(12);
        }

        return view('projects.archived', compact('projects'));
    }

    public function addMember(Request $request, Project $project)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot add members to an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can add members.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:Team Lead,Developer,Designer'
        ]);

        // Check if user is already a member of THIS project
        $existingMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return back()->withErrors(['user_id' => 'User is already a member of this project.']);
        }

        // Check if the role being assigned is Project Admin
        $isAddingAsProjectAdmin = ($request->role === 'Project Admin');
        
        // Check if user is already a Project Admin in any project
        $isExistingProjectAdmin = ProjectMember::where('user_id', $request->user_id)
            ->where('role', 'Project Admin')
            ->exists();

        // Only non-admins are restricted to one active project
        if (!$isAddingAsProjectAdmin && !$isExistingProjectAdmin) {
            // Check if user is already in ANY active (non-archived) project
            $activeProjectMember = ProjectMember::where('user_id', $request->user_id)
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($activeProjectMember) {
                $activeProject = Project::find($activeProjectMember->project_id);
                return back()->withErrors(['user_id' => 'User is already a member of active project: ' . $activeProject->project_name . '. Users can only be assigned to one active project at a time. Only Project Admins can be in multiple projects.']);
            }
        }

        // Check if trying to assign as Team Lead
        if ($request->role === 'Team Lead') {
            // Check if THIS project already has a Team Lead
            $projectHasTeamLead = ProjectMember::where('project_id', $project->project_id)
                ->where('role', 'Team Lead')
                ->exists();

            if ($projectHasTeamLead) {
                return back()->withErrors([
                    'role' => 'This project already has a Team Lead. Only one Team Lead is allowed per project.'
                ]);
            }

            // Check if user is already a Team Lead in another ACTIVE (non-archived) project
            $existingTeamLead = ProjectMember::where('user_id', $request->user_id)
                ->where('role', 'Team Lead')
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($existingTeamLead) {
                $existingProject = Project::find($existingTeamLead->project_id);
                return back()->withErrors([
                    'role' => 'This user is already a Team Lead in project: ' . $existingProject->project_name . '. A Team Lead can only be assigned to one active project.'
                ]);
            }
        }

        // Determine role: System admin should become Project Admin
        $userToAssign = User::find($request->user_id);
        $finalRole = $request->role;
        
        // If the user being assigned is a system admin, force role to Project Admin
        if ($userToAssign->role === 'admin') {
            $finalRole = 'Project Admin';
        }

        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $request->user_id,
            'role' => $finalRole
        ]);

        // Send notification to the new member
        Notification::create_notification(
            $request->user_id,
            'project_invitation',
            'Added to Project',
            'You have been added to project: ' . $project->project_name . ' as ' . $request->role,
            [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'role' => $request->role,
                'added_by' => auth()->id()
            ],
            route('projects.show', $project)
        );

        return back()->with('success', 'Team member added successfully.');
    }

    public function removeMember(Project $project, ProjectMember $member)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot remove members from an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can remove members.');
        }

        // Don't allow removing project creator
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['member' => 'Cannot remove project creator.']);
        }

        $member->delete();

        return back()->with('success', 'Team member removed successfully.');
    }

    public function updateMemberRole(Request $request, Project $project, ProjectMember $member)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot update member roles in an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can update member roles.');
        }

        $request->validate([
            'role' => 'required|in:Team Lead,Developer,Designer'
        ]);

        // Don't allow changing role of project creator
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['role' => 'Cannot change role of project creator.']);
        }

        // Check if trying to change to Team Lead
        if ($request->role === 'Team Lead' && $member->role !== 'Team Lead') {
            // Check if THIS project already has a Team Lead
            $projectHasTeamLead = ProjectMember::where('project_id', $project->project_id)
                ->where('role', 'Team Lead')
                ->where('member_id', '!=', $member->member_id)
                ->exists();

            if ($projectHasTeamLead) {
                return back()->withErrors([
                    'role' => 'This project already has a Team Lead. Only one Team Lead is allowed per project.'
                ]);
            }

            // Check if user is already a Team Lead in another ACTIVE (non-archived) project
            $existingTeamLead = ProjectMember::where('user_id', $member->user_id)
                ->where('role', 'Team Lead')
                ->where('member_id', '!=', $member->member_id)
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($existingTeamLead) {
                $existingProject = Project::find($existingTeamLead->project_id);
                return back()->withErrors([
                    'role' => 'This user is already a Team Lead in project: ' . $existingProject->project_name . '. A Team Lead can only be assigned to one active project.'
                ]);
            }
        }

        $member->update(['role' => $request->role]);

        return back()->with('success', 'Member role updated successfully.');
    }

    /**
     * Show Kanban Board view for a project
     */
    public function kanban(Project $project)
    {
        // Authorization check
        if (!$project->isMember(auth()->id())) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'You are not authorized to view this project.');
        }

        // Get all boards with their cards
        $boards = $project->boards()
            ->with([
                'cards' => function($query) {
                    $query->with([
                        'assignments.user',
                        'subtasks',
                        'comments'
                    ])->orderBy('position');
                }
            ])
            ->orderBy('position')
            ->get();

        return view('projects.kanban', compact('project', 'boards'));
    }

    /**
     * Request project completion (Team Lead only)
     */
    public function requestCompletion(Project $project)
    {
        // Only Team Lead can request completion
        if (!$project->isTeamLead(auth()->id())) {
            return back()->with('error', 'Only Team Lead can request project completion.');
        }

        // Check if all tasks are completed
        if (!$project->allTasksCompleted()) {
            return back()->with('error', 'Cannot request completion. Not all tasks are completed and approved.');
        }

        // Check if already requested or completed
        if ($project->completion_status !== 'working') {
            return back()->with('error', 'Project completion has already been requested or completed.');
        }

        // Update project status
        $project->update([
            'completion_status' => 'pending_approval',
            'requested_by' => auth()->id(),
            'requested_at' => now()
        ]);

        // Send notification to Project Admin
        $projectAdmin = User::find($project->created_by);
        if ($projectAdmin) {
            Notification::create_notification(
                $projectAdmin->user_id,
                'project_completion_request',
                'Project Completion Request',
                'Team Lead has requested approval for project completion: ' . $project->project_name,
                null,
                route('projects.show', $project->project_id)
            );
        }

        return back()->with('success', 'Project completion request sent to Project Admin for approval.');
    }

    /**
     * Approve project completion (Project Admin only)
     */
    public function approveCompletion(Request $request, Project $project)
    {
        // Only Project Admin can approve
        if (!$project->isAdmin(auth()->id())) {
            return back()->with('error', 'Only Project Admin can approve project completion.');
        }

        // Check if pending approval
        if ($project->completion_status !== 'pending_approval') {
            return back()->with('error', 'This project is not pending approval.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:500'
        ]);

        // Update project status
        $project->update([
            'completion_status' => 'completed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
            'status' => 'done',
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => auth()->id()
        ]);

        // Get members before archiving
        $members = $project->members()->with('user')->get();

        // Save all members to archive table for historical tracking
        foreach ($members as $member) {
            \App\Models\ProjectMemberArchive::updateOrCreate(
                [
                    'project_id' => $project->project_id,
                    'user_id' => $member->user_id
                ],
                [
                    'role' => $member->role
                ]
            );
        }

        // Send notification to Team Lead who requested first
        if ($project->requested_by) {
            Notification::create_notification(
                $project->requested_by,
                'project_approved',
                'Project Approved',
                'Your project completion request has been approved: ' . $project->project_name,
                null,
                route('projects.show', $project->project_id)
            );
        }

        // Notify all team members about project completion and archiving
        foreach ($members as $member) {
            if ($member->user_id !== auth()->id() && $member->user_id !== $project->requested_by) {
                Notification::create_notification(
                    $member->user_id,
                    'project_completed',
                    'Project Completed',
                    'Project has been completed and archived: ' . $project->project_name . '. You can still view it in archived projects.',
                    null,
                    route('projects.archived')
                );
            }
        }

        // Note: Members are kept in the project for read-only access and historical records
        // They are archived in project_member_archives for tracking
        // When archived, team members can view the project in read-only mode
        // Project admin can unarchive if needed

        return back()->with('success', 'Project completion approved successfully!');
    }

    /**
     * Reject project completion (Project Admin only)
     */
    public function rejectCompletion(Request $request, Project $project)
    {
        // Only Project Admin can reject
        if (!$project->isAdmin(auth()->id())) {
            return back()->with('error', 'Only Project Admin can reject project completion.');
        }

        // Check if pending approval
        if ($project->completion_status !== 'pending_approval') {
            return back()->with('error', 'This project is not pending approval.');
        }

        $request->validate([
            'approval_notes' => 'required|string|max:500'
        ]);

        // Update project status to rejected
        $project->update([
            'completion_status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes
        ]);

        // Send notification to Team Lead who requested
        if ($project->requested_by) {
            Notification::create_notification(
                $project->requested_by,
                'project_rejected',
                'Project Completion Rejected',
                'Your project completion request has been rejected: ' . $project->project_name . '. Reason: ' . $request->approval_notes,
                null,
                route('projects.show', $project->project_id)
            );
        }

        return back()->with('warning', 'Project completion request rejected.');
    }
}
