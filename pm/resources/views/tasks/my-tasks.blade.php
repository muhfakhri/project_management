@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-tasks me-2"></i>My Tasks</h1>
                    <p class="text-muted">Manage all your assigned tasks across projects</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('tasks.my') }}">All Tasks</a></li>
                        <li><a class="dropdown-item" href="{{ route('tasks.my', ['status' => 'todo']) }}">To Do</a></li>
                        <li><a class="dropdown-item" href="{{ route('tasks.my', ['status' => 'in_progress']) }}">In Progress</a></li>
                        <li><a class="dropdown-item" href="{{ route('tasks.my', ['status' => 'done']) }}">Completed</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Task Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-list-ul fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary">{{ $taskStats['total'] }}</h3>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="text-warning">{{ $taskStats['todo'] }}</h3>
                    <small class="text-muted">To Do</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                    <h3 class="text-info">{{ $taskStats['in_progress'] }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="text-success">{{ $taskStats['completed'] }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-tasks me-2"></i>Tasks
                @if(request('status'))
                    - {{ ucwords(str_replace('_', ' ', request('status'))) }}
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Board</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr class="{{ $task->due_date && $task->due_date->isPast() && $task->status !== 'done' ? 'table-danger' : '' }}">
                                    <td>
                                        <div>
                                            <strong>{{ $task->card_title }}</strong>
                                            @if($task->description)
                                                <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('projects.show', $task->board->project) }}" class="text-decoration-none">
                                            {{ $task->board->project->project_name }}
                                        </a>
                                    </td>
                                    <td>{{ $task->board->board_name }}</td>
                                    <td>
                                        @switch($task->priority)
                                            @case('low')
                                                <span class="badge bg-secondary">Low</span>
                                                @break
                                            @case('medium')
                                                <span class="badge bg-warning">Medium</span>
                                                @break
                                            @case('high')
                                                <span class="badge bg-danger">High</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($task->status)
                                            @case('todo')
                                                <span class="badge bg-secondary">To Do</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                                @break
                                            @case('done')
                                                @if($task->needs_approval && !$task->is_approved)
                                                    <span class="badge bg-warning">Pending Approval</span>
                                                @elseif($task->is_approved)
                                                    <span class="badge bg-success">Approved</span>
                                                @else
                                                    <span class="badge bg-success">Done</span>
                                                @endif
                                                @break
                                        @endswitch
                                        
                                        @if($task->rejection_reason)
                                            <span class="badge bg-danger ms-1" title="{{ $task->rejection_reason }}">
                                                <i class="fas fa-exclamation-circle"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            <span class="{{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-danger' : 'text-muted' }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                                @if($task->due_date->isPast() && $task->status !== 'done')
                                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">No deadline</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->status === 'done')
                                            <span class="text-success">100%</span>
                                        @else
                                            @php
                                                // Hitung subtask yang benar-benar selesai (approved atau tidak perlu approval)
                                                $completedSubtasks = $task->subtasks->filter(function($subtask) {
                                                    return $subtask->status == 'done' && ($subtask->is_approved || !$subtask->needs_approval);
                                                })->count();
                                                $totalSubtasks = $task->subtasks->count();
                                                $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                                            @endphp
                                            {{ $progress }}%
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('tasks.show', $task->card_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($tasks->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $tasks->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Tasks Found</h5>
                    <p class="text-muted">
                        @if(request('status'))
                            No {{ str_replace('_', ' ', request('status')) }} tasks found.
                        @else
                            You don't have any assigned tasks yet.
                        @endif
                    </p>
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Browse All Tasks
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

