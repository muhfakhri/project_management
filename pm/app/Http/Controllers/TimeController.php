<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get user's time logs with related data
        $timeLogs = TimeLog::where('user_id', $user->user_id)
            ->with(['card.board.project', 'user'])
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        // Calculate statistics
        $stats = [
            'total_hours' => round(TimeLog::where('user_id', $user->user_id)->sum('duration_minutes') / 60, 2),
            'this_week' => round(TimeLog::where('user_id', $user->user_id)
                ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes') / 60, 2),
            'this_month' => round(TimeLog::where('user_id', $user->user_id)
                ->whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year)
                ->sum('duration_minutes') / 60, 2),
            'entries_count' => TimeLog::where('user_id', $user->user_id)->count()
        ];

        return view('time.index', compact('timeLogs', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Get cards assigned to the user
        $cards = Card::whereHas('assignments', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with('board.project')->get();

        return view('time.create', compact('cards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'nullable|integer|min:0|max:59',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500'
        ]);

        // Calculate total minutes
        $totalMinutes = ($request->hours ?? 0) * 60 + ($request->minutes ?? 0);
        
        if ($totalMinutes < 1) {
            return back()->withErrors(['hours' => 'Please log at least 1 minute.'])->withInput();
        }

        // Check if user is assigned to this card
        $card = Card::findOrFail($request->card_id);
        if (!$card->assignments->contains('user_id', auth()->id())) {
            return back()->withErrors(['card_id' => 'You are not assigned to this task.'])->withInput();
        }

        // Create time log with start_time set to the specified date
        TimeLog::create([
            'card_id' => $request->card_id,
            'user_id' => auth()->id(),
            'duration_minutes' => $totalMinutes,
            'start_time' => $request->date . ' 00:00:00',
            'description' => $request->description
        ]);

        return redirect()->route('time.index')->with('success', 'Time log added successfully.');
    }

    public function show(TimeLog $timeLog)
    {
        // Check if user can view this time log
        if ($timeLog->user_id !== auth()->id() && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $timeLog->load(['card.board.project', 'user']);

        return view('time.show', compact('timeLog'));
    }

    public function edit(TimeLog $timeLog)
    {
        // Only allow editing own time logs
        if ($timeLog->user_id !== auth()->id()) {
            abort(403);
        }

        $user = auth()->user();
        $cards = Card::whereHas('assignments', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with('board.project')->get();

        return view('time.edit', compact('timeLog', 'cards'));
    }

    public function update(Request $request, TimeLog $timeLog)
    {
        // Only allow updating own time logs
        if ($timeLog->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'nullable|integer|min:0|max:59',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500'
        ]);

        // Calculate total minutes
        $totalMinutes = ($request->hours ?? 0) * 60 + ($request->minutes ?? 0);
        
        if ($totalMinutes < 1) {
            return back()->withErrors(['hours' => 'Please log at least 1 minute.'])->withInput();
        }

        // Check if user is assigned to this card
        $card = Card::findOrFail($request->card_id);
        if (!$card->assignments->contains('user_id', auth()->id())) {
            return back()->withErrors(['card_id' => 'You are not assigned to this task.'])->withInput();
        }

        $timeLog->update([
            'card_id' => $request->card_id,
            'duration_minutes' => $totalMinutes,
            'start_time' => $request->date . ' 00:00:00',
            'description' => $request->description
        ]);

        return redirect()->route('time.index')->with('success', 'Time log updated successfully.');
    }

    public function destroy(TimeLog $timeLog)
    {
        // Only allow deleting own time logs
        if ($timeLog->user_id !== auth()->id()) {
            abort(403);
        }

        $timeLog->delete();

        return back()->with('success', 'Time log deleted successfully.');
    }

    public function project(Project $project)
    {
        // Check if user is member of this project
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $project->load(['members.user']);

        // Get time logs for this project
        $timeLogs = TimeLog::whereHas('card.board', function ($query) use ($project) {
            $query->where('project_id', $project->project_id);
        })->with(['card', 'user'])->orderBy('start_time', 'desc')->paginate(15);

        // Calculate project time statistics
        $stats = [
            'total_hours' => round(TimeLog::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->sum('duration_minutes') / 60, 2),
            'this_week' => round(TimeLog::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->sum('duration_minutes') / 60, 2),
            'members_logged' => TimeLog::whereHas('card.board', function ($query) use ($project) {
                $query->where('project_id', $project->project_id);
            })->distinct('user_id')->count()
        ];

        // Get time by member
        $timeByMember = TimeLog::whereHas('card.board', function ($query) use ($project) {
            $query->where('project_id', $project->project_id);
        })
        ->select('user_id', DB::raw('SUM(duration_minutes) / 60 as total_hours'))
        ->with('user')
        ->groupBy('user_id')
        ->orderByDesc('total_hours')
        ->get();

        return view('time.project', compact('project', 'timeLogs', 'stats', 'timeByMember'));
    }

    public function task(Card $card)
    {
        // Check if user has access to this card
        $project = $card->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $card->load(['board.project', 'assignments.user']);

        // Get time logs for this task
        $timeLogs = TimeLog::where('card_id', $card->card_id)
            ->with('user')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        // Calculate task time statistics
        $stats = [
            'total_hours' => round(TimeLog::where('card_id', $card->card_id)->sum('duration_minutes') / 60, 2),
            'logged_by_count' => TimeLog::where('card_id', $card->card_id)->distinct('user_id')->count(),
            'latest_entry' => TimeLog::where('card_id', $card->card_id)->latest('start_time')->first()
        ];

        return view('time.task', compact('card', 'timeLogs', 'stats'));
    }

    public function reports()
    {
        $user = auth()->user();

        // Calculate time data for different periods
        $timeData = [
            'daily' => TimeLog::where('user_id', $user->user_id)
                ->whereDate('start_time', today())
                ->sum('duration_minutes') / 60,
            
            'weekly' => TimeLog::where('user_id', $user->user_id)
                ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes') / 60,
            
            'monthly' => TimeLog::where('user_id', $user->user_id)
                ->whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year)
                ->sum('duration_minutes') / 60
        ];

        // Task completion trends (last 30 days)
        $completionTrends = TimeLog::where('user_id', $user->user_id)
            ->where('start_time', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('COUNT(DISTINCT card_id) as tasks_completed'),
                DB::raw('SUM(duration_minutes) / 60 as hours_logged')
            )
            ->groupBy(DB::raw('DATE(start_time)'))
            ->orderBy('date')
            ->get();

        // Monthly time report for current year
        $monthlyData = TimeLog::where('user_id', $user->user_id)
            ->whereYear('start_time', now()->year)
            ->select(
                DB::raw('MONTH(start_time) as month'),
                DB::raw('SUM(duration_minutes) / 60 as total_hours')
            )
            ->groupBy(DB::raw('MONTH(start_time)'))
            ->orderBy('month')
            ->get();

        // Weekly time report for current month
        $weeklyData = TimeLog::where('user_id', $user->user_id)
            ->whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->select(
                DB::raw('WEEK(start_time) as week'),
                DB::raw('SUM(duration_minutes) / 60 as total_hours')
            )
            ->groupBy(DB::raw('WEEK(start_time)'))
            ->orderBy('week')
            ->get();

        // Weekly time distribution by day of week
        $weeklyDistribution = TimeLog::where('user_id', $user->user_id)
            ->whereBetween('start_time', [now()->subDays(30), now()])
            ->select(
                DB::raw('DAYOFWEEK(start_time) as day_of_week'),
                DB::raw('SUM(duration_minutes) / 60 as total_hours')
            )
            ->groupBy(DB::raw('DAYOFWEEK(start_time)'))
            ->orderBy('day_of_week')
            ->get();

        // Convert to array with proper day names (1=Sunday, 2=Monday, etc.)
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $weeklyHours = array_fill(0, 7, 0);
        
        foreach ($weeklyDistribution as $day) {
            // MySQL DAYOFWEEK: 1=Sunday, 2=Monday, ... 7=Saturday
            $dayIndex = $day->day_of_week - 1;
            $weeklyHours[$dayIndex] = $day->total_hours;
        }

        // Reorder to start with Monday (1,2,3,4,5,6,0)
        $orderedWeekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $orderedWeeklyHours = [
            $weeklyHours[1], // Monday
            $weeklyHours[2], // Tuesday
            $weeklyHours[3], // Wednesday
            $weeklyHours[4], // Thursday
            $weeklyHours[5], // Friday
            $weeklyHours[6], // Saturday
            $weeklyHours[0], // Sunday
        ];

        // Time by project (project breakdown)
        $projectBreakdown = TimeLog::where('user_id', $user->user_id)
            ->join('cards', 'time_logs.card_id', '=', 'cards.card_id')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->join('projects', 'boards.project_id', '=', 'projects.project_id')
            ->select(
                'projects.project_name',
                'projects.project_id',
                DB::raw('SUM(time_logs.duration_minutes) / 60 as total_hours')
            )
            ->groupBy('projects.project_id', 'projects.project_name')
            ->orderByDesc('total_hours')
            ->get();

        // Keep projectData for backward compatibility
        $projectData = $projectBreakdown;

        return view('time.reports', compact(
            'timeData', 
            'completionTrends', 
            'monthlyData', 
            'weeklyData', 
            'projectData', 
            'projectBreakdown',
            'orderedWeekDays',
            'orderedWeeklyHours'
        ));
    }
}
