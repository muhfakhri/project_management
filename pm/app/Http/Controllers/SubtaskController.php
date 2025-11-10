<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Subtask;
use App\Models\Notification;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'needs_approval' => 'nullable|boolean'
        ]);

        $card = Card::with('board.project')->findOrFail($request->card_id);
        
        // Prevent creating subtasks on locked (approved & done) tasks
        if ($card->isLocked()) {
            return back()->with('error', 'This task has been approved and is locked. Subtasks cannot be created.');
        }

        // Check if project is archived
        if ($card->board->project->isArchived()) {
            return back()->with('error', 'Cannot add subtasks to tasks in an archived project.');
        }
        
        // Check if user can manage subtasks (includes assigned developers/designers)
        if (!$card->canManageSubtasks(auth()->user())) {
            abort(403, 'You do not have permission to add subtasks to this task.');
        }

        Subtask::create([
            'card_id' => $request->card_id,
            'subtask_title' => $request->title,
            'description' => $request->description,
            'needs_approval' => true, // ALWAYS require approval for all subtasks
            'status' => 'todo',
            'position' => Subtask::where('card_id', $request->card_id)->max('position') + 1
        ]);

        return back()->with('success', 'Subtask added successfully.');
    }

    public function toggle(Subtask $subtask)
    {
        $card = $subtask->card;
        $user = auth()->user();
        
        // Load project relationship
        $card->load('board.project');
        
        // Check if project is archived
        if ($card->board->project->isArchived()) {
            return back()->with('error', 'Cannot modify subtasks in an archived project.');
        }
        
        // Check if user can manage subtasks
        if (!$card->canManageSubtasks($user)) {
            abort(403, 'You do not have permission to modify this subtask.');
        }

        // Handle different status transitions
        if ($subtask->status === 'todo') {
            // Start the subtask
            $subtask->start();
            return back()->with('success', 'Subtask started! Time tracking began.');
        } 
        elseif ($subtask->status === 'in_progress') {
            // Complete the subtask
            $subtask->complete($user);

            // If subtask requires approval, notify approvers (Project Admins & Team Leads)
            if ($subtask->needs_approval) {
                $projectId = $card->board->project->project_id;
                $approverIds = \App\Models\ProjectMember::where('project_id', $projectId)
                    ->whereIn('role', ['Project Admin', 'Team Lead'])
                    ->pluck('user_id');

                foreach ($approverIds as $approverId) {
                    if ((int)$approverId === (int)$user->user_id) continue;
                    Notification::create_notification(
                        $approverId,
                        'subtask_pending_approval',
                        'Subtask Awaiting Approval',
                        ($user->full_name ?? $user->username) . ' completed subtask: ' . $subtask->subtask_title . ' in task "' . $card->card_title . '". Please review and approve.',
                        [
                            'subtask_id' => $subtask->subtask_id,
                            'task_id' => $card->card_id,
                            'task_title' => $card->card_title,
                            'project_id' => $projectId,
                            'completed_by' => $user->user_id
                        ],
                        route('tasks.show', $card)
                    );
                }

                return back()->with('success', 'Subtask completed! Waiting for approval from Team Lead/Admin.');
            } else {
                return back()->with('success', 'Subtask completed and approved!');
            }
        }
        elseif ($subtask->status === 'done') {
            // Reopen subtask - keep needs_approval true for mandatory approval system
            $subtask->update([
                'status' => 'todo',
                'started_at' => null,
                'completed_at' => null,
                'duration_minutes' => null,
                'needs_approval' => true, // Keep true - mandatory approval for all subtasks
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null
            ]);
            return back()->with('success', 'Subtask reopened.');
        }

        return back();
    }

    public function approve(Subtask $subtask)
    {
        $user = auth()->user();
        
        // Load necessary relationships for permission check and notifications
        $subtask->load('card.board.project', 'card.assignees');
        
        // Check if project is archived
        if ($subtask->card->board->project->isArchived()) {
            return back()->with('error', 'Cannot approve subtasks in an archived project.');
        }
        
        // Check if user can approve
        if (!$subtask->canApprove($user)) {
            return back()->with('error', 'You do not have permission to approve this subtask. Only Project Admin or Team Lead in this project can approve.');
        }

        if (!$subtask->needs_approval) {
            return back()->with('error', 'This subtask does not need approval.');
        }

        $subtask->approve($user);

        // Notify all assignees of the parent task (excluding the approver) that the subtask was approved
        $card = $subtask->card;
        foreach ($card->assignees as $assignee) {
            if ($assignee->user_id !== $user->user_id) {
                \App\Models\Notification::create_notification(
                    userId: $assignee->user_id,
                    type: 'subtask_approved',
                    title: 'Subtask Approved',
                    message: "Your subtask \"{$subtask->subtask_title}\" in task \"{$card->card_title}\" was approved by {$user->name}.",
                    data: [
                        'subtask_id' => $subtask->subtask_id,
                        'card_id' => $card->card_id,
                        'approved_by' => $user->user_id
                    ],
                    actionUrl: route('tasks.show', $card->card_id)
                );
            }
        }

        return back()->with('success', 'Subtask approved successfully! The assignees have been notified.');
    }

    public function reject(Subtask $subtask, Request $request)
    {
        $user = auth()->user();
        
        // Load necessary relationships for permission check and notification
        $subtask->load('card.board.project', 'card.assignees');
        
        // Check if project is archived
        if ($subtask->card->board->project->isArchived()) {
            return back()->with('error', 'Cannot reject subtasks in an archived project.');
        }
        
        // Check if user can approve (same permission for reject)
        if (!$subtask->canApprove($user)) {
            return back()->with('error', 'You do not have permission to reject this subtask. Only Project Admin or Team Lead in this project can reject.');
        }

        if (!$subtask->needs_approval) {
            return back()->with('error', 'This subtask does not need approval.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $subtask->reject($user, $request->rejection_reason);

        // Send notification to all assignees of the parent task
        $card = $subtask->card;
        $rejectionReason = $request->rejection_reason ?? 'No specific reason provided';
        
        foreach ($card->assignees as $assignee) {
            // Don't notify the person who rejected it
            if ($assignee->user_id !== $user->user_id) {
                Notification::create_notification(
                    userId: $assignee->user_id,
                    type: 'subtask_rejected',
                    title: 'Subtask Rejected',
                    message: "Your subtask \"{$subtask->subtask_title}\" in task \"{$card->card_title}\" was rejected by {$user->name}. Reason: {$rejectionReason}",
                    data: [
                        'subtask_id' => $subtask->subtask_id,
                        'card_id' => $card->card_id,
                        'rejected_by' => $user->user_id,
                        'rejection_reason' => $rejectionReason
                    ],
                    actionUrl: route('tasks.show', $card->card_id)
                );
            }
        }

        return back()->with('success', 'Subtask rejected and assignees notified. Developer can fix and resubmit.');
    }

    public function destroy(Subtask $subtask)
    {
        $card = $subtask->card;
        
        // Load project relationship
        $card->load('board.project');
        
        // Prevent deleting subtasks on locked (approved & done) tasks
        if ($card->isLocked()) {
            return back()->with('error', 'This task has been approved and is locked. Subtasks cannot be deleted.');
        }

        // Check if project is archived
        if ($card->board->project->isArchived()) {
            return back()->with('error', 'Cannot delete subtasks in an archived project.');
        }
        
        // Check if user can manage subtasks (includes assigned developers/designers)
        if (!$card->canManageSubtasks(auth()->user())) {
            abort(403, 'You do not have permission to delete this subtask.');
        }

        $subtask->delete();

        return back()->with('success', 'Subtask deleted successfully.');
    }
}
