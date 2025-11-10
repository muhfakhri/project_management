<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blocker;
use App\Models\BlockerComment;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockerController extends Controller
{
    /**
     * Get all blockers for projects the user has access to
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Blocker::with(['card', 'reporter', 'assignee'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'resolved') {
            $query->resolved();
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Get user's projects
        $projectIds = DB::table('project_members')
            ->where('user_id', $user->id)
            ->pluck('project_id');

        // Filter blockers by user's projects
        $query->whereHas('card.board', function($q) use ($projectIds) {
            $q->whereIn('project_id', $projectIds);
        });

        $blockers = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $blockers->map(function($blocker) {
                return [
                    'id' => $blocker->id,
                    'card_id' => $blocker->card_id,
                    'card_title' => $blocker->card->card_title,
                    'reason' => $blocker->reason,
                    'priority' => $blocker->priority,
                    'status' => $blocker->status,
                    'reporter' => [
                        'id' => $blocker->reporter->id,
                        'name' => $blocker->reporter->name,
                        'avatar' => $blocker->reporter->avatar_url ?? null,
                    ],
                    'assignee' => $blocker->assignee ? [
                        'id' => $blocker->assignee->id,
                        'name' => $blocker->assignee->name,
                        'avatar' => $blocker->assignee->avatar_url ?? null,
                    ] : null,
                    'resolution_note' => $blocker->resolution_note,
                    'time_blocked_hours' => $blocker->time_blocked,
                    'is_overdue' => $blocker->is_overdue,
                    'created_at' => $blocker->created_at->toIso8601String(),
                    'resolved_at' => $blocker->resolved_at?->toIso8601String(),
                ];
            })
        ]);
    }

    /**
     * Get my blockers (reported by me)
     */
    public function myBlockers(Request $request)
    {
        $user = $request->user();
        
        $blockers = Blocker::with(['card', 'assignee'])
            ->where('reporter_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blockers
        ]);
    }

    /**
     * Get blockers assigned to me
     */
    public function assignedToMe(Request $request)
    {
        $user = $request->user();
        
        $blockers = Blocker::with(['card', 'reporter'])
            ->where('assigned_to', $user->id)
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blockers
        ]);
    }

    /**
     * Get a specific blocker with details
     */
    public function show(Request $request, $id)
    {
        $blocker = Blocker::with(['card.board.project', 'reporter', 'assignee', 'comments.user'])
            ->findOrFail($id);

        // Check access
        $user = $request->user();
        $projectId = $blocker->card->board->project_id;
        $hasAccess = DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $blocker->id,
                'card' => [
                    'id' => $blocker->card->id,
                    'title' => $blocker->card->card_title,
                    'status' => $blocker->card->status,
                ],
                'reason' => $blocker->reason,
                'priority' => $blocker->priority,
                'status' => $blocker->status,
                'reporter' => [
                    'id' => $blocker->reporter->id,
                    'name' => $blocker->reporter->name,
                    'avatar' => $blocker->reporter->avatar_url ?? null,
                ],
                'assignee' => $blocker->assignee ? [
                    'id' => $blocker->assignee->id,
                    'name' => $blocker->assignee->name,
                    'avatar' => $blocker->assignee->avatar_url ?? null,
                ] : null,
                'resolution_note' => $blocker->resolution_note,
                'comments' => $blocker->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                            'avatar' => $comment->user->avatar_url ?? null,
                        ],
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->toIso8601String(),
                    ];
                }),
                'time_blocked_hours' => $blocker->time_blocked,
                'is_overdue' => $blocker->is_overdue,
                'created_at' => $blocker->created_at->toIso8601String(),
                'resolved_at' => $blocker->resolved_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Report a new blocker
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'reason' => 'required|string|min:10|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $user = $request->user();
        $card = Card::with('board.project')->findOrFail($validated['card_id']);
        
        // Check access
        $hasAccess = DB::table('project_members')
            ->where('project_id', $card->board->project_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this task'
            ], 403);
        }

        // Check for existing active blocker
        $existingBlocker = Blocker::where('card_id', $validated['card_id'])
            ->active()
            ->first();

        if ($existingBlocker) {
            return response()->json([
                'success' => false,
                'message' => 'This task already has an active blocker'
            ], 422);
        }

        $blocker = Blocker::create([
            'card_id' => $validated['card_id'],
            'reporter_id' => $user->id,
            'reason' => $validated['reason'],
            'priority' => $validated['priority'],
            'status' => 'reported',
        ]);

        // Notify team leads
        $this->notifyTeamLeads($blocker);

        return response()->json([
            'success' => true,
            'message' => 'Blocker reported successfully. Team leads have been notified.',
            'data' => $blocker->load(['reporter', 'card'])
        ]);
    }

    /**
     * Assign helper to blocker (TeamLead/Admin only)
     */
    public function assign(Request $request, $id)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,user_id',
        ]);

        $blocker = Blocker::with('card.board')->findOrFail($id);
        $user = $request->user();
        $projectId = $blocker->card->board->project_id;

        // Check if user is team lead or admin
        $isAuthorized = DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['team_lead', 'project_admin'])
            ->exists() || $user->role === 'admin';

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Only team leads and project admins can assign helpers'
            ], 403);
        }

        $blocker->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        // Notify assignee
        $this->notifyAssignee($blocker);

        return response()->json([
            'success' => true,
            'message' => 'Helper assigned successfully',
            'data' => $blocker->load(['assignee'])
        ]);
    }

    /**
     * Update blocker status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,resolved',
            'resolution_note' => 'required_if:status,resolved|nullable|string|max:500',
        ]);

        $blocker = Blocker::with('card.board')->findOrFail($id);
        $user = $request->user();
        $projectId = $blocker->card->board->project_id;

        // Check authorization
        $isAuthorized = $blocker->assigned_to === $user->id ||
            DB::table('project_members')
                ->where('project_id', $projectId)
                ->where('user_id', $user->id)
                ->whereIn('role', ['team_lead', 'project_admin'])
                ->exists() ||
            $user->role === 'admin';

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $updateData = ['status' => $validated['status']];
        
        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolution_note'] = $validated['resolution_note'] ?? null;
        }

        $blocker->update($updateData);

        // Notify reporter if resolved
        if ($validated['status'] === 'resolved') {
            $this->notifyReporter($blocker);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $blocker
        ]);
    }

    /**
     * Add comment to blocker
     */
    public function addComment(Request $request, $id)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $blocker = Blocker::with('card.board')->findOrFail($id);
        $user = $request->user();

        // Check access
        $projectId = $blocker->card->board->project_id;
        $hasAccess = DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $comment = BlockerComment::create([
            'blocker_id' => $blocker->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added',
            'data' => $comment->load('user')
        ]);
    }

    /**
     * Helper methods for notifications
     */
    private function notifyTeamLeads(Blocker $blocker)
    {
        $projectId = $blocker->card->board->project_id;
        $teamLeads = DB::table('project_members')
            ->where('project_id', $projectId)
            ->whereIn('role', ['team_lead', 'project_admin'])
            ->where('user_id', '!=', $blocker->reporter_id)
            ->pluck('user_id');

        foreach ($teamLeads as $userId) {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'type' => 'blocker_reported',
                'title' => 'New Blocker Reported',
                'message' => $blocker->reporter->name . ' reported a blocker on task: ' . $blocker->card->card_title,
                'data' => json_encode([
                    'blocker_id' => $blocker->id,
                    'card_id' => $blocker->card_id,
                    'priority' => $blocker->priority,
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function notifyAssignee(Blocker $blocker)
    {
        DB::table('notifications')->insert([
            'user_id' => $blocker->assigned_to,
            'type' => 'blocker_assigned',
            'title' => 'You Have Been Assigned to Help',
            'message' => 'You have been assigned to help with a blocker on task: ' . $blocker->card->card_title,
            'data' => json_encode([
                'blocker_id' => $blocker->id,
                'card_id' => $blocker->card_id,
                'priority' => $blocker->priority,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function notifyReporter(Blocker $blocker)
    {
        DB::table('notifications')->insert([
            'user_id' => $blocker->reporter_id,
            'type' => 'blocker_resolved',
            'title' => 'Blocker Resolved',
            'message' => 'Your blocker on task "' . $blocker->card->card_title . '" has been resolved',
            'data' => json_encode([
                'blocker_id' => $blocker->id,
                'card_id' => $blocker->card_id,
                'resolution_note' => $blocker->resolution_note,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
