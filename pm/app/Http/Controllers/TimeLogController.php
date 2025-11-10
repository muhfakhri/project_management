<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Project;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
    /**
     * Display time logs with filtering options
     */
    public function index(Request $request)
    {
        $query = TimeLog::with(['card.board.project', 'user', 'subtask'])
            ->orderBy('start_time', 'desc');

        // Filter by project
        if ($request->filled('project_id')) {
            $query->whereHas('card.board.project', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        // Only show logs from projects user has access to
        if (auth()->user()->role !== 'Project Admin') {
            $query->whereHas('card.board.project', function ($q) {
                $q->where('created_by', auth()->id())
                    ->orWhereHas('members', function ($memberQuery) {
                        $memberQuery->where('user_id', auth()->id());
                    });
            });
        }

        $timeLogs = $query->paginate(20);

        // Get projects for filter dropdown
        $projects = Project::where(function ($q) {
            $q->where('created_by', auth()->id())
                ->orWhereHas('members', function ($memberQuery) {
                    $memberQuery->where('user_id', auth()->id());
                });
        })->get();

        // Calculate statistics
        $stats = [
            'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            'total_logs' => $timeLogs->total(),
            'avg_duration' => $timeLogs->avg('duration_minutes') ? round($timeLogs->avg('duration_minutes') / 60, 2) : 0,
        ];

        return view('time-logs.index', compact('timeLogs', 'projects', 'stats'));
    }

    /**
     * Display time logs for a specific task
     */
    public function task(Card $card)
    {
        $timeLogs = TimeLog::with(['user'])
            ->where('card_id', $card->card_id)
            ->orderBy('start_time', 'desc')
            ->get();

        return view('time-logs.task', compact('timeLogs', 'card'));
    }

    /**
     * Display my time logs
     */
    public function myLogs(Request $request)
    {
        $query = TimeLog::with(['card.board.project', 'subtask'])
            ->where('user_id', auth()->id())
            ->orderBy('start_time', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        $timeLogs = $query->paginate(20);

        // Calculate my statistics
        $stats = [
            'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            'total_logs' => $timeLogs->total(),
            'avg_duration' => $timeLogs->avg('duration_minutes') ? round($timeLogs->avg('duration_minutes') / 60, 2) : 0,
        ];

        return view('time-logs.my-logs', compact('timeLogs', 'stats'));
    }

    /**
     * Export time logs to CSV
     */
    public function export(Request $request)
    {
        $query = TimeLog::with(['card.board.project', 'user', 'subtask'])
            ->orderBy('start_time', 'desc');

        // Apply same filters as index
        if ($request->filled('project_id')) {
            $query->whereHas('card.board.project', function ($q) use ($request) {
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

        // Only show logs from projects user has access to
        if (auth()->user()->role !== 'Project Admin') {
            $query->whereHas('card.board.project', function ($q) {
                $q->where('created_by', auth()->id())
                    ->orWhereHas('members', function ($memberQuery) {
                        $memberQuery->where('user_id', auth()->id());
                    });
            });
        }

        $timeLogs = $query->get();

        $filename = 'time-logs-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($timeLogs) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Date', 'User', 'Project', 'Task', 'Start Time', 'End Time', 'Duration (hours)', 'Description']);
            
            // Add data
            foreach ($timeLogs as $log) {
                fputcsv($file, [
                    $log->start_time->format('Y-m-d'),
                    $log->user ? $log->user->full_name ?? $log->user->username : 'N/A',
                    $log->card && $log->card->board && $log->card->board->project ? $log->card->board->project->project_name : 'N/A',
                    $log->card ? $log->card->card_title : ($log->subtask ? $log->subtask->subtask_title : 'N/A'),
                    $log->start_time->format('Y-m-d H:i:s'),
                    $log->end_time ? $log->end_time->format('Y-m-d H:i:s') : 'N/A',
                    round($log->duration_minutes / 60, 2),
                    $log->description ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
