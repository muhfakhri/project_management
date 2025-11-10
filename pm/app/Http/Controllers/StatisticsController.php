<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Board;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Check if user is system admin OR project admin
        $isSystemAdmin = $user->role === 'admin';
        $isProjectAdmin = ProjectMember::where('user_id', $user->user_id)
            ->where('role', 'Project Admin')
            ->exists();
        
        if ($isSystemAdmin || $isProjectAdmin) {
            return $this->adminStatistics($isSystemAdmin);
        }
        
        // Regular user statistics
        return $this->userStatistics();
    }
    
    private function userStatistics()
    {
        $user = auth()->user();

        // User's overall statistics
        $userStats = [
            'projects_count' => Project::whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->count(),
            'tasks_assigned' => Card::whereHas('assignments', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->count(),
            'tasks_completed' => Card::whereHas('assignments', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->where('status', 'done')->count(),
            'total_time_logged' => round(TimeLog::where('user_id', $user->user_id)->sum('duration_minutes') / 60, 2),
            'comments_made' => Comment::where('user_id', $user->user_id)->count()
        ];

        // Recent activity
        $recentActivity = collect();

        // Recent tasks
        $recentTasks = Card::whereHas('assignments', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with('board.project')->latest()->take(5)->get()
        ->map(function ($task) {
            return [
                'type' => 'task',
                'title' => $task->title,
                'subtitle' => $task->board->project->project_name,
                'date' => $task->updated_at,
                'status' => $task->status
            ];
        });

        // Recent comments
        $recentComments = Comment::where('user_id', $user->user_id)
            ->with('card.board.project')->latest()->take(5)->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'title' => 'Commented on: ' . $comment->card->title,
                    'subtitle' => $comment->card->board->project->project_name,
                    'date' => $comment->created_at,
                    'content' => substr($comment->content, 0, 100)
                ];
            });

        $recentActivity = $recentTasks->concat($recentComments)
            ->sortByDesc('date')->take(10);

        // Performance metrics
        $performanceMetrics = [
            'task_completion_rate' => $userStats['tasks_assigned'] > 0 
                ? round(($userStats['tasks_completed'] / $userStats['tasks_assigned']) * 100, 1) 
                : 0,
            'avg_time_per_task' => $userStats['tasks_completed'] > 0 
                ? round($userStats['total_time_logged'] / $userStats['tasks_completed'], 1) 
                : 0,
            'productivity_score' => $this->calculateProductivityScore($userStats)
        ];

        return view('statistics.index', compact('userStats', 'recentActivity', 'performanceMetrics'));
    }

    public function project(Project $project)
    {
        // Check if user has access to this project
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $project->load(['members.user', 'boards.cards']);

        // Project statistics
        $projectStats = [
            'total_tasks' => $project->boards->sum(function ($board) {
                return $board->cards->count();
            }),
            'completed_tasks' => $project->boards->sum(function ($board) {
                return $board->cards->where('status', 'done')->count();
            }),
            'in_progress_tasks' => $project->boards->sum(function ($board) {
                return $board->cards->where('status', 'in_progress')->count();
            }),
            'total_members' => $project->members->count(),
            'total_time' => TimeLog::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->sum('duration_minutes') / 60,
            'total_comments' => Comment::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->count()
        ];

        // Task distribution by status
        $tasksByStatus = [
            'todo' => $project->boards->sum(function ($board) {
                return $board->cards->where('status', 'todo')->count();
            }),
            'in_progress' => $projectStats['in_progress_tasks'],
            'completed' => $projectStats['completed_tasks']
        ];

        // Member performance
        $memberStats = $project->members->map(function ($member) use ($project) {
            $tasksAssigned = Card::whereHas('board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->whereHas('assignments', function ($query) use ($member) {
                $query->where('user_id', $member->user_id);
            })->count();

            $tasksCompleted = Card::whereHas('board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->whereHas('assignments', function ($query) use ($member) {
                $query->where('user_id', $member->user_id);
            })->where('status', 'done')->count();

            $timeLogged = TimeLog::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->where('user_id', $member->user_id)->sum('duration_minutes') / 60;

            return [
                'user' => $member->user,
                'role' => $member->role,
                'tasks_assigned' => $tasksAssigned,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 1) : 0,
                'time_logged' => $timeLogged
            ];
        })->sortByDesc('tasks_completed');

        // Progress over time (last 30 days)
        $progressData = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('boards.project_id', $project->project_id)
            ->where('cards.status', 'done')
            ->where('cards.updated_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(cards.updated_at) as date'), DB::raw('COUNT(*) as completed'))
            ->groupBy(DB::raw('DATE(cards.updated_at)'))
            ->orderBy('date')
            ->get();

        return view('statistics.project', compact('project', 'projectStats', 'tasksByStatus', 'memberStats', 'progressData'));
    }

    public function team()
    {
        // Only accessible to Project Admins
        if (auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        // Overall team statistics
        $teamStats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_tasks' => Card::count(),
            'total_time' => TimeLog::sum('hours_spent'),
            'active_projects' => Project::whereIn('status', ['planning', 'in_progress'])->count(),
            'completed_projects' => Project::where('status', 'done')->count()
        ];

        // User activity (top performers)
        $topPerformers = User::withCount(['timeLogsCreated', 'commentsCreated'])
            ->with(['assignments' => function ($query) {
                $query->with('card');
            }])
            ->get()
            ->map(function ($user) {
                $tasksCompleted = $user->assignments->where('card.status', 'completed')->count();
                $totalTasks = $user->assignments->count();
                
                return [
                    'user' => $user,
                    'tasks_completed' => $tasksCompleted,
                    'total_tasks' => $totalTasks,
                    'completion_rate' => $totalTasks > 0 ? round(($tasksCompleted / $totalTasks) * 100, 1) : 0,
                    'time_logged' => $user->time_logs_created_count ?? 0,
                    'comments_made' => $user->comments_created_count ?? 0
                ];
            })
            ->sortByDesc('completion_rate')
            ->take(10);

        // Project status distribution
        $projectsByStatus = Project::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Monthly activity
        $monthlyData = [
            'projects_created' => Project::whereMonth('created_at', now()->month)->count(),
            'tasks_completed' => Card::where('status', 'done')
                ->whereMonth('updated_at', now()->month)->count(),
            'time_logged' => TimeLog::whereMonth('start_time', now()->month)->sum('duration_minutes') / 60,
            'new_users' => User::whereMonth('created_at', now()->month)->count()
        ];

        return view('statistics.team', compact('teamStats', 'topPerformers', 'projectsByStatus', 'monthlyData'));
    }

    public function reports()
    {
        $user = auth()->user();

        // Time reports
        $timeData = [
            'daily' => TimeLog::where('user_id', $user->user_id)
                ->whereDate('start_time', now())
                ->sum('duration_minutes') / 60,
            'weekly' => TimeLog::where('user_id', $user->user_id)
                ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes') / 60,
            'monthly' => TimeLog::where('user_id', $user->user_id)
                ->whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year)
                ->sum('duration_minutes') / 60
        ];

        // Task completion trends
        $completionTrends = Card::whereHas('assignments', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })
        ->where('status', 'done')
        ->where('updated_at', '>=', now()->subDays(30))
        ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as completed'))
        ->groupBy(DB::raw('DATE(updated_at)'))
        ->orderBy('date')
        ->get();

        // Project breakdown
        $projectBreakdown = TimeLog::where('user_id', $user->user_id)
            ->join('cards', 'time_logs.card_id', '=', 'cards.card_id')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->join('projects', 'boards.project_id', '=', 'projects.project_id')
            ->select('projects.project_name', DB::raw('SUM(time_logs.duration_minutes) / 60 as total_hours'))
            ->groupBy('projects.project_id', 'projects.project_name')
            ->orderByDesc('total_hours')
            ->get();

        return view('statistics.reports', compact('timeData', 'completionTrends', 'projectBreakdown'));
    }

    private function calculateProductivityScore($stats)
    {
        $score = 0;

        // Task completion contribution (40%)
        if ($stats['tasks_assigned'] > 0) {
            $completionRate = ($stats['tasks_completed'] / $stats['tasks_assigned']) * 100;
            $score += ($completionRate * 0.4);
        }

        // Time logging contribution (30%)
        if ($stats['total_time_logged'] > 0) {
            $timeScore = min(100, ($stats['total_time_logged'] / 40) * 100); // 40 hours = 100%
            $score += ($timeScore * 0.3);
        }

        // Collaboration contribution (30%)
        if ($stats['comments_made'] > 0) {
            $collaborationScore = min(100, ($stats['comments_made'] / 20) * 100); // 20 comments = 100%
            $score += ($collaborationScore * 0.3);
        }

        return round($score, 1);
    }
    
    private function adminStatistics($isSystemAdmin = false)
    {
        $user = Auth::user();
        
        // Determine project scope
        if (!$isSystemAdmin) {
            $userProjects = ProjectMember::where('user_id', $user->user_id)
                ->where('role', 'Project Admin')
                ->pluck('project_id');
        }
        
        // ========== PROJECT ANALYTICS ==========
        
        // Project performance metrics
        if (!$isSystemAdmin) {
            $projectPerformance = Project::whereIn('project_id', $userProjects)
                ->with('creator')
                ->get()
                ->map(function($project) {
                    $totalTasks = Card::whereHas('board', function($q) use ($project) {
                        $q->where('project_id', $project->project_id);
                    })->count();
                    
                    $completedTasks = Card::whereHas('board', function($q) use ($project) {
                        $q->where('project_id', $project->project_id);
                    })->where('status', 'done')->count();
                    
                    return [
                        'project' => $project,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'progress' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                        'overdue' => $project->deadline && $project->deadline->isPast() && $project->status != 'done'
                    ];
                });
        } else {
            $projectPerformance = Project::with('creator')
                ->get()
                ->map(function($project) {
                    $totalTasks = Card::whereHas('board', function($q) use ($project) {
                        $q->where('project_id', $project->project_id);
                    })->count();
                    
                    $completedTasks = Card::whereHas('board', function($q) use ($project) {
                        $q->where('project_id', $project->project_id);
                    })->where('status', 'done')->count();
                    
                    return [
                        'project' => $project,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'progress' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                        'overdue' => $project->deadline && $project->deadline->isPast() && $project->status != 'done'
                    ];
                });
        }
        
        // ========== TASK ANALYTICS ==========
        
        // Task completion rate over time (last 30 days)
        if (!$isSystemAdmin) {
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $taskCompletionTrend = Card::whereIn('board_id', $boardIds)
                ->where('status', 'done')
                ->where('updated_at', '>=', Carbon::now()->subDays(30))
                ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            $taskCompletionTrend = Card::where('status', 'done')
                ->where('updated_at', '>=', Carbon::now()->subDays(30))
                ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }
        
        // Task status distribution
        if (!$isSystemAdmin) {
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $taskStatusDistribution = Card::whereIn('board_id', $boardIds)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        } else {
            $taskStatusDistribution = Card::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        }
        
        // ========== TIME TRACKING ANALYTICS ==========
        
        // Total time logged
        if (!$isSystemAdmin) {
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $totalTimeLogged = TimeLog::whereHas('card', function($q) use ($boardIds) {
                $q->whereIn('board_id', $boardIds);
            })->sum('duration_minutes');
            
            // Time by user
            $timeByUser = TimeLog::whereHas('card', function($q) use ($boardIds) {
                $q->whereIn('board_id', $boardIds);
            })
            ->join('users', 'time_logs.user_id', '=', 'users.user_id')
            ->select('users.full_name', 'users.username', 'users.profile_picture', DB::raw('SUM(time_logs.duration_minutes) / 60 as total_hours'))
            ->groupBy('users.user_id', 'users.full_name', 'users.username', 'users.profile_picture')
            ->orderByDesc('total_hours')
            ->take(10)
            ->get();
        } else {
            $totalTimeLogged = TimeLog::sum('duration_minutes');
            
            $timeByUser = TimeLog::join('users', 'time_logs.user_id', '=', 'users.user_id')
                ->select('users.full_name', 'users.username', 'users.profile_picture', DB::raw('SUM(time_logs.duration_minutes) / 60 as total_hours'))
                ->groupBy('users.user_id', 'users.full_name', 'users.username', 'users.profile_picture')
                ->orderByDesc('total_hours')
                ->take(10)
                ->get();
        }
        
        // ========== TEAM PERFORMANCE ==========
        
        // Most active users
        if (!$isSystemAdmin) {
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $activeUsers = User::whereHas('assignedTasks', function($q) use ($boardIds) {
                $q->whereIn('board_id', $boardIds);
            })
            ->withCount(['assignedTasks as completed_tasks' => function($q) use ($boardIds) {
                $q->whereIn('board_id', $boardIds)->where('status', 'done');
            }])
            ->withCount(['assignedTasks as total_tasks' => function($q) use ($boardIds) {
                $q->whereIn('board_id', $boardIds);
            }])
            ->having('total_tasks', '>', 0)
            ->orderByDesc('completed_tasks')
            ->take(10)
            ->get()
            ->map(function($user) {
                return [
                    'user' => $user,
                    'completion_rate' => $user->total_tasks > 0 ? round(($user->completed_tasks / $user->total_tasks) * 100, 1) : 0
                ];
            });
        } else {
            $activeUsers = User::withCount(['assignedTasks as completed_tasks' => function($q) {
                $q->where('status', 'done');
            }])
            ->withCount(['assignedTasks as total_tasks'])
            ->having('total_tasks', '>', 0)
            ->orderByDesc('completed_tasks')
            ->take(10)
            ->get()
            ->map(function($user) {
                return [
                    'user' => $user,
                    'completion_rate' => $user->total_tasks > 0 ? round(($user->completed_tasks / $user->total_tasks) * 100, 1) : 0
                ];
            });
        }
        
        // ========== SUBTASK ANALYTICS ==========
        
        // Subtask approval statistics
        if (!$isSystemAdmin) {
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $subtaskStats = [
                'total' => Subtask::whereHas('card', function($q) use ($boardIds) {
                    $q->whereIn('board_id', $boardIds);
                })->count(),
                'pending' => Subtask::whereHas('card', function($q) use ($boardIds) {
                    $q->whereIn('board_id', $boardIds);
                })->where('needs_approval', true)->where('is_approved', false)->count(),
                'approved' => Subtask::whereHas('card', function($q) use ($boardIds) {
                    $q->whereIn('board_id', $boardIds);
                })->where('is_approved', true)->count(),
            ];
        } else {
            $subtaskStats = [
                'total' => Subtask::count(),
                'pending' => Subtask::where('needs_approval', true)->where('is_approved', false)->count(),
                'approved' => Subtask::where('is_approved', true)->count(),
            ];
        }
        
        // ========== PROJECT TRENDS ==========
        
        // Projects created over time (last 6 months)
        if (!$isSystemAdmin) {
            $projectTrends = Project::whereIn('project_id', $userProjects)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            $projectTrends = Project::where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }
        
        return view('statistics.admin', compact(
            'isSystemAdmin',
            'projectPerformance',
            'taskCompletionTrend',
            'taskStatusDistribution',
            'totalTimeLogged',
            'timeByUser',
            'activeUsers',
            'subtaskStats',
            'projectTrends'
        ));
    }
}


