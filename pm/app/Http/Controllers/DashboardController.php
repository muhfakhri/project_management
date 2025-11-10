<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Card;
use App\Models\Board;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is system admin OR project admin
        $isSystemAdmin = $user->role === 'admin';
        $isProjectAdmin = ProjectMember::where('user_id', $user->user_id)
            ->where('role', 'Project Admin')
            ->exists();
        
        if ($isSystemAdmin || $isProjectAdmin) {
            return $this->adminDashboard($isSystemAdmin);
        }
        
        // Regular user dashboard (existing)
        return view('dashboard', [
            'user' => $user
        ]);
    }
    
    private function adminDashboard($isSystemAdmin = false)
    {
        $user = Auth::user();
        
        // If Project Admin (not system admin), only show their projects
        if (!$isSystemAdmin) {
            $userProjects = ProjectMember::where('user_id', $user->user_id)
                ->where('role', 'Project Admin')
                ->pluck('project_id');
            
            // Project Statistics (filtered)
            $totalProjects = Project::whereIn('project_id', $userProjects)->count();
            $activeProjects = Project::whereIn('project_id', $userProjects)->where('status', 'in_progress')->count();
            $completedProjects = Project::whereIn('project_id', $userProjects)->where('status', 'done')->count();
            $onHoldProjects = Project::whereIn('project_id', $userProjects)->where('status', 'on_hold')->count();
            $planningProjects = Project::whereIn('project_id', $userProjects)->where('status', 'planning')->count();
        } else {
            // System Admin sees all projects
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', 'in_progress')->count();
            $completedProjects = Project::where('status', 'done')->count();
            $onHoldProjects = Project::where('status', 'on_hold')->count();
            $planningProjects = Project::where('status', 'planning')->count();
        }
        
        // Project by Status for Chart
        $projectsByStatus = [
            'planning' => $planningProjects,
            'in_progress' => $activeProjects,
            'done' => $completedProjects,
            'on_hold' => $onHoldProjects,
        ];
        
        // Task Statistics
        if (!$isSystemAdmin) {
            // Project Admin: only tasks from their projects
            $boardIds = Board::whereIn('project_id', $userProjects)->pluck('board_id');
            $totalTasks = Card::whereIn('board_id', $boardIds)->count();
            $todoTasks = Card::whereIn('board_id', $boardIds)->where('status', 'todo')->count();
            $inProgressTasks = Card::whereIn('board_id', $boardIds)->where('status', 'in_progress')->count();
            $reviewTasks = Card::whereIn('board_id', $boardIds)->where('status', 'review')->count();
            $completedTasks = Card::whereIn('board_id', $boardIds)->where('status', 'done')->count();
        } else {
            // System Admin: all tasks
            $totalTasks = Card::count();
            $todoTasks = Card::where('status', 'todo')->count();
            $inProgressTasks = Card::where('status', 'in_progress')->count();
            $reviewTasks = Card::where('status', 'review')->count();
            $completedTasks = Card::where('status', 'done')->count();
        }
        
        // User Statistics (only for system admin)
        if ($isSystemAdmin) {
            $totalUsers = User::count();
            $adminUsers = User::where('role', 'admin')->count();
            $regularUsers = User::where('role', 'user')->count();
        } else {
            // Project Admin: users in their projects
            $totalUsers = ProjectMember::whereIn('project_id', $userProjects)->distinct('user_id')->count('user_id');
            $adminUsers = 0; // Not relevant for project admin
            $regularUsers = $totalUsers;
        }
        
        // Recent Projects (last 5)
        if (!$isSystemAdmin) {
            $recentProjects = Project::with('creator')
                ->whereIn('project_id', $userProjects)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } else {
            $recentProjects = Project::with('creator')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        
        // Projects with Deadline Info
        if (!$isSystemAdmin) {
            $overdueProjects = Project::whereIn('project_id', $userProjects)
                ->where('status', '!=', 'done')
                ->whereNotNull('deadline')
                ->where('deadline', '<', Carbon::now())
                ->count();
            
            $dueSoonProjects = Project::whereIn('project_id', $userProjects)
                ->where('status', '!=', 'done')
                ->whereNotNull('deadline')
                ->where('deadline', '>=', Carbon::now())
                ->where('deadline', '<=', Carbon::now()->addDays(7))
                ->count();
        } else {
            $overdueProjects = Project::where('status', '!=', 'done')
                ->whereNotNull('deadline')
                ->where('deadline', '<', Carbon::now())
                ->count();
            
            $dueSoonProjects = Project::where('status', '!=', 'done')
                ->whereNotNull('deadline')
                ->where('deadline', '>=', Carbon::now())
                ->where('deadline', '<=', Carbon::now()->addDays(7))
                ->count();
        }
        
        // Top Projects by Progress
        if (!$isSystemAdmin) {
            $topProjects = Project::with('creator')
                ->whereIn('project_id', $userProjects)
                ->where('status', 'in_progress')
                ->get()
                ->map(function($project) {
                    return [
                        'project' => $project,
                        'progress' => $project->progress
                    ];
                })
                ->sortByDesc('progress')
                ->take(5);
        } else {
            $topProjects = Project::with('creator')
                ->where('status', 'in_progress')
                ->get()
                ->map(function($project) {
                    return [
                        'project' => $project,
                        'progress' => $project->progress
                    ];
                })
                ->sortByDesc('progress')
                ->take(5);
        }
        
        // Projects by Month (Last 6 months)
        if (!$isSystemAdmin) {
            $projectsByMonth = Project::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereIn('project_id', $userProjects)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            $projectsByMonth = Project::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }
        
        // Team Members Statistics
        if (!$isSystemAdmin) {
            $totalTeamMembers = ProjectMember::whereIn('project_id', $userProjects)->distinct('user_id')->count('user_id');
            $projectAdmins = ProjectMember::whereIn('project_id', $userProjects)->where('role', 'Project Admin')->distinct('user_id')->count('user_id');
            $teamLeads = ProjectMember::whereIn('project_id', $userProjects)->where('role', 'Team Lead')->distinct('user_id')->count('user_id');
            $members = ProjectMember::whereIn('project_id', $userProjects)->where('role', 'Member')->distinct('user_id')->count('user_id');
        } else {
            $totalTeamMembers = ProjectMember::distinct('user_id')->count('user_id');
            $projectAdmins = ProjectMember::where('role', 'Project Admin')->distinct('user_id')->count('user_id');
            $teamLeads = ProjectMember::where('role', 'Team Lead')->distinct('user_id')->count('user_id');
            $members = ProjectMember::where('role', 'Member')->distinct('user_id')->count('user_id');
        }
        
        return view('admin.dashboard', compact(
            'isSystemAdmin',
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'onHoldProjects',
            'planningProjects',
            'projectsByStatus',
            'totalTasks',
            'todoTasks',
            'inProgressTasks',
            'reviewTasks',
            'completedTasks',
            'totalUsers',
            'adminUsers',
            'regularUsers',
            'recentProjects',
            'overdueProjects',
            'dueSoonProjects',
            'topProjects',
            'projectsByMonth',
            'totalTeamMembers',
            'projectAdmins',
            'teamLeads',
            'members'
        ));
    }
}