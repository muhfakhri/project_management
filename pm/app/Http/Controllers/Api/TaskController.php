<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Get tasks assigned to the authenticated user
     */
    public function myTasks(Request $request)
    {
        $user = $request->user();

        // Get tasks where user is assigned (adjust based on your database structure)
        $tasks = DB::table('cards')
            ->leftJoin('projects', 'cards.project_id', '=', 'projects.project_id')
            ->leftJoin('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('cards.assigned_to', $user->id)
            ->select(
                'cards.card_id',
                'cards.card_title',
                'cards.description',
                'cards.status',
                'cards.priority',
                'cards.due_date',
                'cards.started_at',
                'cards.completed_at',
                'cards.estimated_hours',
                'cards.actual_hours',
                'cards.needs_approval',
                'cards.is_approved',
                'cards.rejection_reason',
                'cards.created_by',
                'cards.created_at',
                'cards.updated_at',
                'projects.project_name',
                'boards.board_name'
            )
            ->orderBy('cards.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ], 200);
    }

    /**
     * Get task detail with subtasks
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        // Get task detail
        $task = DB::table('cards')
            ->leftJoin('projects', 'cards.project_id', '=', 'projects.project_id')
            ->leftJoin('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('cards.card_id', $id)
            ->where('cards.assigned_to', $user->id)
            ->select(
                'cards.card_id',
                'cards.card_title',
                'cards.description',
                'cards.status',
                'cards.priority',
                'cards.due_date',
                'cards.started_at',
                'cards.completed_at',
                'cards.estimated_hours',
                'cards.actual_hours',
                'cards.needs_approval',
                'cards.is_approved',
                'cards.rejection_reason',
                'cards.created_by',
                'cards.created_at',
                'cards.updated_at',
                'projects.project_name',
                'boards.board_name'
            )
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Get subtasks
        $subtasks = DB::table('subtasks')
            ->where('card_id', $id)
            ->select(
                'subtask_id',
                'card_id',
                'subtask_title',
                'description',
                'status',
                'needs_approval',
                'is_approved',
                'started_at',
                'completed_at',
                'duration_minutes'
            )
            ->get();

        $task->subtasks = $subtasks;

        // Get active blocker if exists
        $blocker = DB::table('blockers')
            ->where('card_id', $id)
            ->whereIn('status', ['reported', 'assigned', 'in_progress'])
            ->select('id', 'reason', 'priority', 'status', 'created_at')
            ->first();

        $task->has_blocker = $blocker ? true : false;
        $task->blocker = $blocker;

        return response()->json([
            'success' => true,
            'task' => $task
        ], 200);
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        $task = DB::table('cards')
            ->where('card_id', $id)
            ->where('assigned_to', $user->id)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $status = $request->input('status');

        DB::table('cards')
            ->where('card_id', $id)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully'
        ], 200);
    }

    /**
     * Start work on task
     */
    public function startWork(Request $request, $id)
    {
        $user = $request->user();

        // Get task with board and project info
        $task = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('cards.card_id', $id)
            ->where('cards.assigned_to', $user->user_id)
            ->select('cards.*', 'boards.project_id')
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not assigned to you'
            ], 404);
        }

        // Check if user can track time (only Developer/Designer)
        if (!$user->canTrackTimeInProject($task->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Developers and Designers can track time on tasks. Team Leads and Project Admins are supervisors.'
            ], 403);
        }

        DB::table('cards')
            ->where('card_id', $id)
            ->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Work started successfully'
        ], 200);
    }

    /**
     * Pause work on task
     */
    public function pauseWork(Request $request, $id)
    {
        $user = $request->user();

        // Get task with board and project info
        $task = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('cards.card_id', $id)
            ->where('cards.assigned_to', $user->user_id)
            ->select('cards.*', 'boards.project_id')
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not assigned to you'
            ], 404);
        }

        // Check if user can track time
        if (!$user->canTrackTimeInProject($task->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Developers and Designers can track time on tasks.'
            ], 403);
        }

        // Calculate actual hours worked
        if ($task->started_at) {
            $startedAt = \Carbon\Carbon::parse($task->started_at);
            $now = \Carbon\Carbon::now();
            $hoursWorked = $startedAt->diffInHours($now, true);
            
            $currentHours = $task->actual_hours ?? 0;
            $newHours = $currentHours + $hoursWorked;

            DB::table('cards')
                ->where('card_id', $id)
                ->update([
                    'actual_hours' => $newHours,
                    'updated_at' => now()
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work paused successfully'
        ], 200);
    }

    /**
     * Complete work on task
     */
    public function completeWork(Request $request, $id)
    {
        $user = $request->user();

        // Get task with board and project info
        $task = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('cards.card_id', $id)
            ->where('cards.assigned_to', $user->user_id)
            ->select('cards.*', 'boards.project_id')
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not assigned to you'
            ], 404);
        }

        // Check if user can track time
        if (!$user->canTrackTimeInProject($task->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Developers and Designers can track time on tasks.'
            ], 403);
        }

        DB::table('cards')
            ->where('card_id', $id)
            ->update([
                'status' => 'review',
                'completed_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Work completed successfully. Task submitted for review.'
        ], 200);
    }

    /**
     * Get task history (completed tasks)
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $tasks = DB::table('cards')
            ->leftJoin('projects', 'cards.project_id', '=', 'projects.project_id')
            ->where('cards.assigned_to', $user->id)
            ->whereIn('cards.status', ['review', 'done'])
            ->select(
                'cards.card_id',
                'cards.card_title',
                'cards.status',
                'cards.completed_at',
                'projects.project_name'
            )
            ->orderBy('cards.completed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ], 200);
    }
}
