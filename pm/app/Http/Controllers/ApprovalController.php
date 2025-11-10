<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Subtask;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get projects where user is Admin or Team Lead
        $projectIds = ProjectMember::where('user_id', $user->user_id)
            ->whereIn('role', ['Project Admin', 'Team Lead'])
            ->pluck('project_id');

        // Also include if user is global Project Admin
        if ($user->role === 'Project Admin') {
            $allProjectIds = DB::table('projects')->pluck('project_id');
            $projectIds = $projectIds->merge($allProjectIds)->unique();
        }

        if ($projectIds->isEmpty()) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Get filter parameters
        $filter = $request->get('filter', 'all'); // all, subtasks, tasks
        $projectFilter = $request->get('project', 'all');
        $statusFilter = $request->get('status', 'pending'); // pending, approved, rejected

        // Build query for subtasks needing approval
        $subtasksQuery = Subtask::with(['card.board.project', 'card.assignments.user', 'approver'])
            ->whereHas('card.board.project', function($query) use ($projectIds) {
                $query->whereIn('projects.project_id', $projectIds);
            })
            ->where('needs_approval', true);

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

        // Apply project filter for subtasks
        if ($projectFilter !== 'all') {
            $subtasksQuery->whereHas('card.board.project', function($query) use ($projectFilter) {
                $query->where('projects.project_id', $projectFilter);
            });
        }

        $subtasks = ($filter === 'all' || $filter === 'subtasks') 
            ? $subtasksQuery->orderBy('completed_at', 'desc')->get() 
            : collect();

        // Build query for tasks needing approval
        $tasksQuery = Card::with(['board.project', 'assignments.user', 'approver'])
            ->whereHas('board.project', function($query) use ($projectIds) {
                $query->whereIn('projects.project_id', $projectIds);
            })
            ->where('needs_approval', true);

        // Apply status filter for tasks
        if ($statusFilter === 'pending') {
            $tasksQuery->where('status', 'review')  // Changed from 'done' to 'review'
                       ->where('is_approved', false)
                       ->whereNull('rejection_reason');
        } elseif ($statusFilter === 'approved') {
            $tasksQuery->where('is_approved', true);
        } elseif ($statusFilter === 'rejected') {
            $tasksQuery->whereNotNull('rejection_reason');
        }

        // Apply project filter for tasks
        if ($projectFilter !== 'all') {
            $tasksQuery->whereHas('board.project', function($query) use ($projectFilter) {
                $query->where('projects.project_id', $projectFilter);
            });
        }

        $tasks = ($filter === 'all' || $filter === 'tasks') 
            ? $tasksQuery->orderBy('completed_at', 'desc')->get() 
            : collect();

        // Get projects for filter dropdown
        $projects = DB::table('projects')
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_name')
            ->get();

        // Count statistics
        $stats = [
            'pending_subtasks' => Subtask::whereHas('card.board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->where('needs_approval', true)
                ->where('status', 'done')
                ->where('is_approved', false)
                ->whereNull('rejection_reason')
                ->count(),
            
            'pending_tasks' => Card::whereHas('board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->where('needs_approval', true)
                ->where('status', 'review')  // Changed from 'done' to 'review'
                ->where('is_approved', false)
                ->whereNull('rejection_reason')
                ->count(),
            
            'approved_today' => Subtask::whereHas('card.board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->where('is_approved', true)
                ->whereDate('approved_at', today())
                ->count() +
                Card::whereHas('board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->where('is_approved', true)
                ->whereDate('approved_at', today())
                ->count(),
            
            'rejected_count' => Subtask::whereHas('card.board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->whereNotNull('rejection_reason')
                ->count() +
                Card::whereHas('board.project', function($query) use ($projectIds) {
                    $query->whereIn('projects.project_id', $projectIds);
                })
                ->whereNotNull('rejection_reason')
                ->count(),
        ];

        return view('approvals.index', compact('subtasks', 'tasks', 'projects', 'stats', 'filter', 'projectFilter', 'statusFilter'));
    }
}
