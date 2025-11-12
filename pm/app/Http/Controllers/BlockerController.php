<?php

namespace App\Http\Controllers;

use App\Models\Blocker;
use App\Models\BlockerComment;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BlockerController extends Controller
{
    /**
     * Display a listing of blockers for team leads and admins
     */
    public function index(Request $request)
    {
        $query = Blocker::with(['card.board.project', 'reporter', 'assignee'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'resolved') {
                $query->resolved();
            }
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by project (if user is not admin)
        $user = Auth::user();
        if ($user->role !== 'admin') {
            // Get projects where user is Team Lead or Project Admin
            $projectIds = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->whereIn('role', ['Project Admin', 'Team Lead'])
                ->pluck('project_id');
            
            // Show blockers that user can access:
            // 1. Blockers in projects where user is Team Lead/Project Admin
            // 2. Blockers reported by the user
            // 3. Blockers assigned to the user
            $query->where(function($q) use ($projectIds, $user) {
                $q->whereHas('card.board', function($boardQuery) use ($projectIds) {
                    $boardQuery->whereIn('project_id', $projectIds);
                })
                ->orWhere('reporter_id', $user->user_id)
                ->orWhere('assigned_to', $user->user_id);
            });
        }

        $blockers = $query->paginate(20);

        return view('blockers.index', compact('blockers'));
    }

    /**
     * Show the form for creating a new blocker
     */
    public function create(Request $request)
    {
        $cardId = $request->get('card_id');
        $card = Card::with('board.project')->findOrFail($cardId);
        
        return view('blockers.create', compact('card'));
    }

    /**
     * Store a newly created blocker
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'reason' => 'required|string|min:10|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $card = Card::with('board.project')->findOrFail($validated['card_id']);
        
        // Check if user can report blocker
        if (!$card->canReportBlocker(Auth::user())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to report blocker for this task. Only assigned members can report blockers on tasks that are not yet completed.'
                ], 403);
            }
            
            return redirect()->route('tasks.show', $card->card_id)
                ->with('error', 'You are not authorized to report blocker for this task. Only assigned members can report blockers on tasks that are not yet completed.');
        }
        
        // Check if user has access to this task
        $hasAccess = DB::table('project_members')
            ->where('project_id', $card->board->project_id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this task'
            ], 403);
        }

        // Check if there's already an active blocker for this task
        $existingBlocker = Blocker::where('card_id', $validated['card_id'])
            ->active()
            ->first();

        if ($existingBlocker) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This task already has an active blocker. Please add a comment to the existing blocker instead.'
                ], 422);
            }
            
            return redirect()->route('tasks.show', $card->card_id)
                ->with('error', 'This task already has an active blocker. Please add a comment to the existing blocker instead.');
        }

        $blocker = Blocker::create([
            'card_id' => $validated['card_id'],
            'reporter_id' => Auth::id(),
            'reason' => $validated['reason'],
            'priority' => $validated['priority'],
            'status' => 'reported',
        ]);

        // Notify team leads and project admins
        $this->notifyTeamLeads($blocker);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blocker reported successfully. Team leads have been notified.',
                'data' => $blocker->load(['reporter', 'card'])
            ]);
        }

        return redirect()->route('tasks.show', $card->card_id)
            ->with('success', 'Blocker reported successfully. Team leads have been notified.');
    }

    /**
     * Display the specified blocker
     */
    public function show(Blocker $blocker)
    {
        $blocker->load(['card.board.project', 'reporter', 'assignee', 'comments.user']);
        
        $user = Auth::user();
        $projectId = $blocker->card->board->project_id;
        
        // Check if user has access to view this blocker
        $canView = false;
        
        // System admin can view all
        if ($user->role === 'admin') {
            $canView = true;
        }
        // Reporter can view their own blocker
        elseif ($blocker->reporter_id === $user->user_id) {
            $canView = true;
        }
        // Assignee can view blocker assigned to them
        elseif ($blocker->assigned_to === $user->user_id) {
            $canView = true;
        }
        // Team Lead or Project Admin of the project can view
        else {
            $canView = DB::table('project_members')
                ->where('project_id', $projectId)
                ->where('user_id', $user->user_id)
                ->whereIn('role', ['Team Lead', 'Project Admin'])
                ->exists();
        }
        
        if (!$canView) {
            abort(403, 'You do not have permission to view this blocker.');
        }
        
        // Get potential helpers (team leads and project admins from the project)
        $helpers = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $projectId)
            ->whereIn('project_members.role', ['Team Lead', 'Project Admin'])
            ->select('users.*')
            ->get();

        return view('blockers.show', compact('blocker', 'helpers'));
    }

    /**
     * Assign a helper to the blocker
     */
    public function assign(Request $request, Blocker $blocker)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,user_id',
        ]);

        // Check if user is team lead or project admin
        $user = Auth::user();
        $projectId = $blocker->card->board->project_id;
        $isAuthorized = DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->whereIn('role', ['Team Lead', 'Project Admin'])
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

        // Notify the assignee
        $this->notifyAssignee($blocker);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Helper assigned successfully',
                'data' => $blocker->load(['assignee'])
            ]);
        }

        return redirect()->back()->with('success', 'Helper assigned successfully');
    }

    /**
     * Update blocker status
     */
    public function updateStatus(Request $request, Blocker $blocker)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,resolved',
            'resolution_note' => 'required_if:status,resolved|nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        // Only assigned user, team lead, or project admin can update status
        $projectId = $blocker->card->board->project_id;
        $isAuthorized = $blocker->assigned_to === $user->user_id ||
            DB::table('project_members')
                ->where('project_id', $projectId)
                ->where('user_id', $user->user_id)
                ->whereIn('role', ['Team Lead', 'Project Admin'])
                ->exists() || 
            $user->role === 'admin';

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this blocker'
            ], 403);
        }

        $updateData = ['status' => $validated['status']];
        
        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolution_note'] = $validated['resolution_note'];
        }

        $blocker->update($updateData);

        // Notify reporter based on status
        if ($validated['status'] === 'resolved') {
            $this->notifyReporter($blocker, 'resolved');
        } elseif ($validated['status'] === 'in_progress') {
            $this->notifyReporter($blocker, 'in_progress');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $blocker
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    /**
     * Add a comment to the blocker
     */
    public function addComment(Request $request, Blocker $blocker)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = BlockerComment::create([
            'blocker_id' => $blocker->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        // Notify reporter and assignee about new comment
        $this->notifyAboutComment($blocker, $comment);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added',
                'data' => $comment->load('user')
            ]);
        }

        return redirect()->back()->with('success', 'Comment added');
    }

    /**
     * Notify team leads and project admins
     */
    private function notifyTeamLeads(Blocker $blocker)
    {
        $projectId = $blocker->card->board->project_id;
        $teamLeads = DB::table('project_members')
            ->where('project_id', $projectId)
            ->whereIn('role', ['Team Lead', 'Project Admin'])
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
                    'url' => route('blockers.show', $blocker->id),
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Notify assignee
     */
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
                'url' => route('blockers.show', $blocker->id),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Notify reporter about blocker status changes
     */
    private function notifyReporter(Blocker $blocker, $status = 'resolved')
    {
        $title = '';
        $message = '';
        $type = '';
        
        switch ($status) {
            case 'in_progress':
                $type = 'blocker_in_progress';
                $title = 'Blocker Being Worked On';
                $message = 'Your blocker on task "' . $blocker->card->card_title . '" is now being worked on by ' . $blocker->assignee->full_name;
                break;
            case 'resolved':
                $type = 'blocker_resolved';
                $title = 'Blocker Resolved';
                $message = 'Your blocker on task "' . $blocker->card->card_title . '" has been resolved';
                break;
        }
        
        DB::table('notifications')->insert([
            'user_id' => $blocker->reporter_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode([
                'blocker_id' => $blocker->id,
                'card_id' => $blocker->card_id,
                'status' => $status,
                'resolution_note' => $blocker->resolution_note ?? null,
                'url' => route('blockers.show', $blocker->id),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Notify reporter and assignee about new comment
     */
    private function notifyAboutComment(Blocker $blocker, $comment)
    {
        $commenterName = $comment->user->full_name ?? $comment->user->username;
        $usersToNotify = [];
        
        // Notify reporter if comment is not from them
        if ($blocker->reporter_id !== $comment->user_id) {
            $usersToNotify[] = $blocker->reporter_id;
        }
        
        // Notify assignee if exists and comment is not from them
        if ($blocker->assigned_to && $blocker->assigned_to !== $comment->user_id) {
            $usersToNotify[] = $blocker->assigned_to;
        }
        
        foreach ($usersToNotify as $userId) {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'type' => 'blocker_comment',
                'title' => 'New Comment on Blocker',
                'message' => $commenterName . ' commented on blocker for task "' . $blocker->card->card_title . '"',
                'data' => json_encode([
                    'blocker_id' => $blocker->id,
                    'card_id' => $blocker->card_id,
                    'comment_id' => $comment->id,
                    'url' => route('blockers.show', $blocker->id),
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
