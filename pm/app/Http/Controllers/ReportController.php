<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Card;
use App\Models\User;
use App\Models\Subtask;
use App\Models\TimeLog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('project-admin');
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        $projects = Project::withCount(['boards', 'members'])
            ->with(['creator'])
            ->get();

        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalTasks = Card::count();
        $totalTimeLogged = TimeLog::sum('duration_minutes') / 60;

        return view('reports.index', compact(
            'projects',
            'totalUsers',
            'totalProjects',
            'totalTasks',
            'totalTimeLogged'
        ));
    }

    /**
     * Export Projects Report
     */
    public function exportProjects(Request $request)
    {
        $query = Project::with(['creator', 'boards', 'members.user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $projects = $query->get();

        $filename = 'projects-report-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Project Name',
                'Description',
                'Status',
                'Priority',
                'Progress (%)',
                'Created By',
                'Start Date',
                'End Date',
                'Total Members',
                'Total Boards',
                'Is Archived',
                'Created At'
            ]);
            
            // Data
            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->project_name,
                    $project->description ?? '',
                    ucfirst($project->status),
                    ucfirst($project->priority ?? 'medium'),
                    $project->progress ?? 0,
                    $project->creator ? ($project->creator->full_name ?? $project->creator->username) : 'N/A',
                    $project->start_date ? $project->start_date->format('Y-m-d') : '',
                    $project->end_date ? $project->end_date->format('Y-m-d') : '',
                    $project->members->count(),
                    $project->boards->count(),
                    $project->is_archived ? 'Yes' : 'No',
                    $project->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Tasks Report
     */
    public function exportTasks(Request $request)
    {
        $query = Card::with(['board.project', 'creator', 'assignments.user']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->whereHas('board.project', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $filename = 'tasks-report-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Task Title',
                'Project',
                'Board',
                'Status',
                'Priority',
                'Assigned To',
                'Created By',
                'Due Date',
                'Estimated Hours',
                'Actual Hours',
                'Started At',
                'Completed At',
                'Needs Approval',
                'Is Approved',
                'Created At'
            ]);
            
            // Data
            foreach ($tasks as $task) {
                $assignedUsers = $task->assignments->pluck('user.username')->join(', ');
                
                fputcsv($file, [
                    $task->card_title,
                    $task->board && $task->board->project ? $task->board->project->project_name : 'N/A',
                    $task->board ? $task->board->board_name : 'N/A',
                    ucfirst($task->status),
                    ucfirst($task->priority),
                    $assignedUsers ?: 'Unassigned',
                    $task->creator ? ($task->creator->full_name ?? $task->creator->username) : 'N/A',
                    $task->due_date ? $task->due_date->format('Y-m-d') : '',
                    $task->estimated_hours ?? '',
                    $task->actual_hours ? round($task->actual_hours, 2) : '',
                    $task->started_at ? $task->started_at->format('Y-m-d H:i:s') : '',
                    $task->completed_at ? $task->completed_at->format('Y-m-d H:i:s') : '',
                    $task->needs_approval ? 'Yes' : 'No',
                    $task->is_approved ? 'Yes' : 'No',
                    $task->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Users Report
     */
    public function exportUsers()
    {
        $users = User::with(['projectMembers.project'])->get();

        $filename = 'users-report-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Username',
                'Full Name',
                'Email',
                'Role',
                'Total Projects',
                'Total Tasks Assigned',
                'Total Tasks Created',
                'Total Comments',
                'Total Time Logged (hours)',
                'Created At',
                'Last Login'
            ]);
            
            // Data
            foreach ($users as $user) {
                $totalProjects = $user->projectMembers->count();
                $totalTasksAssigned = DB::table('card_assignments')->where('user_id', $user->user_id)->count();
                $totalTasksCreated = Card::where('created_by', $user->user_id)->count();
                $totalComments = Comment::where('user_id', $user->user_id)->count();
                $totalTimeLogged = TimeLog::where('user_id', $user->user_id)->sum('duration_minutes') / 60;
                
                fputcsv($file, [
                    $user->username,
                    $user->full_name ?? '',
                    $user->email,
                    $user->role,
                    $totalProjects,
                    $totalTasksAssigned,
                    $totalTasksCreated,
                    $totalComments,
                    round($totalTimeLogged, 2),
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Time Logs Report
     */
    public function exportTimeLogs(Request $request)
    {
        $query = TimeLog::with(['card.board.project', 'user', 'subtask']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->whereHas('card.board.project', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        $timeLogs = $query->orderBy('start_time', 'desc')->get();

        $filename = 'time-logs-report-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($timeLogs) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Date',
                'User',
                'Project',
                'Task',
                'Subtask',
                'Start Time',
                'End Time',
                'Duration (hours)',
                'Description'
            ]);
            
            // Data
            foreach ($timeLogs as $log) {
                fputcsv($file, [
                    $log->start_time->format('Y-m-d'),
                    $log->user ? ($log->user->full_name ?? $log->user->username) : 'N/A',
                    $log->card && $log->card->board && $log->card->board->project ? $log->card->board->project->project_name : 'N/A',
                    $log->card ? $log->card->card_title : 'N/A',
                    $log->subtask ? $log->subtask->subtask_title : '',
                    $log->start_time->format('Y-m-d H:i:s'),
                    $log->end_time ? $log->end_time->format('Y-m-d H:i:s') : '',
                    round($log->duration_minutes / 60, 2),
                    $log->description ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Project Performance Report
     */
    public function exportProjectPerformance(Request $request)
    {
        $projectId = $request->get('project_id');
        
        if ($projectId) {
            $projects = Project::where('project_id', $projectId)->get();
        } else {
            $projects = Project::all();
        }

        $filename = 'project-performance-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Project Name',
                'Status',
                'Total Tasks',
                'Completed Tasks',
                'In Progress Tasks',
                'Todo Tasks',
                'On Hold Tasks',
                'Completion Rate (%)',
                'Total Members',
                'Total Time Logged (hours)',
                'Avg Time Per Task (hours)',
                'Total Comments',
                'Start Date',
                'End Date',
                'Days Elapsed',
                'Days Remaining'
            ]);
            
            // Data
            foreach ($projects as $project) {
                $boardIds = $project->boards->pluck('board_id');
                
                $totalTasks = Card::whereIn('board_id', $boardIds)->count();
                $completedTasks = Card::whereIn('board_id', $boardIds)->where('status', 'done')->count();
                $inProgressTasks = Card::whereIn('board_id', $boardIds)->where('status', 'in_progress')->count();
                $todoTasks = Card::whereIn('board_id', $boardIds)->where('status', 'todo')->count();
                $onHoldTasks = Card::whereIn('board_id', $boardIds)->where('status', 'on_hold')->count();
                
                $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
                
                $cardIds = Card::whereIn('board_id', $boardIds)->pluck('card_id');
                $totalTimeLogged = TimeLog::whereIn('card_id', $cardIds)->sum('duration_minutes') / 60;
                $avgTimePerTask = $totalTasks > 0 ? round($totalTimeLogged / $totalTasks, 2) : 0;
                
                $totalComments = Comment::whereIn('card_id', $cardIds)->count();
                
                $daysElapsed = $project->start_date ? now()->diffInDays($project->start_date) : 0;
                $daysRemaining = $project->end_date ? now()->diffInDays($project->end_date, false) : 0;
                
                fputcsv($file, [
                    $project->project_name,
                    ucfirst($project->status),
                    $totalTasks,
                    $completedTasks,
                    $inProgressTasks,
                    $todoTasks,
                    $onHoldTasks,
                    $completionRate,
                    $project->members->count(),
                    round($totalTimeLogged, 2),
                    $avgTimePerTask,
                    $totalComments,
                    $project->start_date ? $project->start_date->format('Y-m-d') : '',
                    $project->end_date ? $project->end_date->format('Y-m-d') : '',
                    $daysElapsed,
                    $daysRemaining
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export User Performance Report
     */
    public function exportUserPerformance()
    {
        $users = User::all();

        $filename = 'user-performance-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'User',
                'Role',
                'Total Projects',
                'Tasks Assigned',
                'Tasks Completed',
                'Tasks In Progress',
                'Completion Rate (%)',
                'Total Time Logged (hours)',
                'Avg Time Per Task (hours)',
                'Subtasks Completed',
                'Comments Posted',
                'Approval Rate (%)',
                'Last Active'
            ]);
            
            // Data
            foreach ($users as $user) {
                $totalProjects = DB::table('project_members')->where('user_id', $user->user_id)->count();
                
                $assignedTaskIds = DB::table('card_assignments')->where('user_id', $user->user_id)->pluck('card_id');
                $tasksAssigned = $assignedTaskIds->count();
                $tasksCompleted = Card::whereIn('card_id', $assignedTaskIds)->where('status', 'done')->count();
                $tasksInProgress = Card::whereIn('card_id', $assignedTaskIds)->where('status', 'in_progress')->count();
                
                $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;
                
                $totalTimeLogged = TimeLog::where('user_id', $user->user_id)->sum('duration_minutes') / 60;
                $avgTimePerTask = $tasksCompleted > 0 ? round($totalTimeLogged / $tasksCompleted, 2) : 0;
                
                $subtasksCompleted = Subtask::whereHas('card.assignments', function($q) use ($user) {
                    $q->where('user_id', $user->user_id);
                })->where('status', 'done')->count();
                
                $commentsPosted = Comment::where('user_id', $user->user_id)->count();
                
                // Approval rate for Team Leads
                $approvedCount = Subtask::where('approved_by', $user->user_id)->where('is_approved', true)->count();
                $rejectedCount = Subtask::where('approved_by', $user->user_id)->whereNotNull('rejection_reason')->count();
                $totalApprovals = $approvedCount + $rejectedCount;
                $approvalRate = $totalApprovals > 0 ? round(($approvedCount / $totalApprovals) * 100, 2) : 0;
                
                fputcsv($file, [
                    $user->full_name ?? $user->username,
                    $user->role,
                    $totalProjects,
                    $tasksAssigned,
                    $tasksCompleted,
                    $tasksInProgress,
                    $completionRate,
                    round($totalTimeLogged, 2),
                    $avgTimePerTask,
                    $subtasksCompleted,
                    $commentsPosted,
                    $approvalRate,
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
