<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        // Count completed projects (projects where user has completed all assigned tasks)
        $completedProjects = DB::table('projects')
            ->join('cards', 'projects.project_id', '=', 'cards.project_id')
            ->where('cards.assigned_to', $user->id)
            ->where('cards.status', 'done')
            ->distinct('projects.project_id')
            ->count();

        // Calculate average completion time (in hours)
        $avgCompletionTime = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->where('status', 'done')
            ->whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_time')
            ->value('avg_time') ?? 0;

        // Get task counts by status
        $totalTasks = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->count();

        $completedTasks = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->where('status', 'done')
            ->count();

        $inProgressTasks = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $pendingTasks = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->where('status', 'todo')
            ->count();

        // Calculate working hours for today
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayWorkingHours = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->sum('actual_hours') ?? 0;

        // Calculate working hours for this week
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weekWorkingHours = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->sum('actual_hours') ?? 0;

        // Calculate working hours for this month
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthWorkingHours = DB::table('cards')
            ->where('assigned_to', $user->id)
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->sum('actual_hours') ?? 0;

        // Get blocker statistics
        $activeBlockers = DB::table('blockers')
            ->join('cards', 'blockers.card_id', '=', 'cards.card_id')
            ->where('cards.assigned_to', $user->id)
            ->whereIn('blockers.status', ['reported', 'assigned', 'in_progress'])
            ->count();

        $resolvedBlockersThisWeek = DB::table('blockers')
            ->join('cards', 'blockers.card_id', '=', 'cards.card_id')
            ->where('cards.assigned_to', $user->id)
            ->where('blockers.status', 'resolved')
            ->whereBetween('blockers.resolved_at', [$weekStart, $weekEnd])
            ->count();

        // If user is team lead/admin, get blockers assigned to them
        $blockersAssignedToMe = DB::table('blockers')
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'completed_projects' => $completedProjects,
                'average_completion_time' => round($avgCompletionTime, 2),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'pending_tasks' => $pendingTasks,
                'today_working_hours' => round($todayWorkingHours, 2),
                'week_working_hours' => round($weekWorkingHours, 2),
                'month_working_hours' => round($monthWorkingHours, 2),
                'active_blockers' => $activeBlockers,
                'resolved_blockers_this_week' => $resolvedBlockersThisWeek,
                'blockers_assigned_to_me' => $blockersAssignedToMe,
            ]
        ], 200);
    }
}
