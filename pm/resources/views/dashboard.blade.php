@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    @php
        $user = auth()->user();
    @endphp
    
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-home me-2"></i>{{ $user->full_name ?? $user->username }}
                            </h2>
                            <p class="mb-0 opacity-75">Welcome to your dashboard. Here’s what’s happening with your projects today.</p>
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <span class="badge {{ $user->current_task_status == 'working' ? 'bg-success' : 'bg-secondary' }} fs-6">
                                    <i class="fas fa-circle me-1"></i>{{ ucfirst($user->current_task_status) }}
                                </span>
                            </div>
                            <small class="opacity-75">{{ now()->format('l, F d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        @php
            $user = Auth::user();
            $myProjects = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->count();
            
            $myTasks = DB::table('card_assignments')
                ->where('user_id', $user->user_id)
                ->count();
            
            $completedTasks = DB::table('card_assignments')
                ->join('cards', 'card_assignments.card_id', '=', 'cards.card_id')
                ->where('card_assignments.user_id', $user->user_id)
                ->where('cards.status', 'done')
                ->count();
            
            // For Project Admin and Team Lead - count SUBTASKS awaiting approval
            $pendingApprovalTasks = 0;
            if (in_array($user->role, ['Project Admin', 'Team Lead'])) {
                $pendingApprovalTasks = DB::table('subtasks')
                    ->join('cards', 'subtasks.card_id', '=', 'cards.card_id')
                    ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                    ->join('project_members', 'boards.project_id', '=', 'project_members.project_id')
                    ->where('project_members.user_id', $user->user_id)
                    ->where('subtasks.status', 'done')
                    ->where('subtasks.needs_approval', true)
                    ->where('subtasks.is_approved', false)
                    ->distinct()
                    ->count('subtasks.subtask_id');
            }
            
            $totalTimeLogged = DB::table('time_logs')
                ->where('user_id', $user->user_id)
                ->sum('duration_minutes');
        @endphp

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary p-3 rounded">
                                <i class="fas fa-project-diagram fa-2x text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">My Projects</h6>
                            <h3 class="mb-0">{{ $myProjects }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-tasks fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Assigned Tasks</h6>
                            <h3 class="mb-0">{{ $myTasks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0">{{ $completedTasks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($user->role, ['Project Admin', 'Team Lead']))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Approval</h6>
                            <h3 class="mb-0">{{ $pendingApprovalTasks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Time Logged</h6>
                            <h3 class="mb-0">{{ number_format($totalTimeLogged / 60, 1) }}h</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list-check me-2"></i>My Recent Tasks
                        </h5>
                        <a href="{{ route('tasks.my') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $recentTasks = DB::table('card_assignments')
                            ->join('cards', 'card_assignments.card_id', '=', 'cards.card_id')
                            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                            ->join('projects', 'boards.project_id', '=', 'projects.project_id')
                            ->where('card_assignments.user_id', $user->user_id)
                            ->select('cards.*', 'projects.project_name', 'boards.board_name')
                            ->orderBy('cards.updated_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($recentTasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTasks as $task)
                                <a href="{{ route('tasks.show', $task->card_id) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $task->card_title }}</h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-folder me-1"></i>{{ $task->project_name }}
                                                        <i class="fas fa-chevron-right mx-1"></i>
                                                        {{ $task->board_name }}
                                                    </small>
                                                </div>
                                                <div class="ms-3">
                                                    @switch($task->status)
                                                        @case('todo')
                                                            <span class="badge bg-secondary">To Do</span>
                                                            @break
                                                        @case('in_progress')
                                                            <span class="badge bg-primary">In Progress</span>
                                                            @break
                                                        @case('review')
                                                            <span class="badge bg-warning">Review</span>
                                                            @break
                                                        @case('done')
                                                            <span class="badge bg-success">Done</span>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </div>
                                            @if($task->due_date)
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tasks assigned yet</p>
                            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Browse Tasks
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-project-diagram me-2"></i>View Projects
                        </a>
                        <a href="{{ route('tasks.my') }}" class="btn btn-outline-info">
                            <i class="fas fa-tasks me-2"></i>My Tasks
                        </a>
                        <a href="{{ route('time-logs.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-clock me-2"></i>Log Time
                        </a>
                        <a href="{{ route('statistics.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chart-bar me-2"></i>Statistics
                        </a>
                    </div>
                </div>
            </div>

            <!-- ...existing code... -->
        </div>
    </div>
</div>

<style>
.bg-primary {
    background-color: #007bff !important;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.list-group-item-action:hover {
    background-color: #f8f9fa;
}
</style>
@endsection
