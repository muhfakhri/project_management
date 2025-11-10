@extends('layouts.app')

@section('title', 'All Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>All Tasks</h1>
    <div class="btn-group">
        <a href="{{ route('tasks.my') }}" class="btn btn-outline-primary">
            <i class="bi bi-person-check"></i> My Tasks
        </a>
        @if($canManageTasks ?? false)
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Task
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assignees</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <strong>{{ $task->card_title }}</strong>
                                @if($task->description)
                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $task->board->project->project_name }}</span>
                                <br><small class="text-muted">{{ $task->board->board_name }}</small>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'todo' => 'secondary',
                                        'in_progress' => 'primary',
                                        'review' => 'warning',
                                        'done' => 'success'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $priorityColors = [
                                        'low' => 'success',
                                        'medium' => 'warning',
                                        'high' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td>
                                @if($task->assignments->count() > 0)
                                    <div class="d-flex align-items-center">
                                        @foreach($task->assignments->take(3) as $assignment)
                                            @if($assignment->user->profile_picture)
                                                <img src="{{ asset('storage/' . $assignment->user->profile_picture) }}" 
                                                     alt="{{ $assignment->user->full_name ?? $assignment->user->username }}"
                                                     class="rounded-circle border border-white"
                                                     style="width: 32px; height: 32px; object-fit: cover; margin-left: -8px;"
                                                     title="{{ $assignment->user->full_name ?? $assignment->user->username }}">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center border border-white"
                                                     style="width: 32px; height: 32px; font-size: 12px; font-weight: bold; margin-left: -8px;"
                                                     title="{{ $assignment->user->full_name ?? $assignment->user->username }}">
                                                    {{ strtoupper(substr($assignment->user->full_name ?? $assignment->user->username, 0, 2)) }}
                                                </div>
                                            @endif
                                        @endforeach
                                        @if($task->assignments->count() > 3)
                                            <span class="badge bg-secondary ms-2">+{{ $task->assignments->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted"><i class="fas fa-user-slash me-1"></i>Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($task->due_date)
                                    @if($task->isOverdue())
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            {{ $task->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        {{ $task->due_date->format('M d, Y') }}
                                    @endif
                                @else
                                    <span class="text-muted">No deadline</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tasks.show', $task->card_id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Task">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($task->canEdit(auth()->user()))
                                        <a href="{{ route('tasks.edit', $task->card_id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit Task">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($task->canDelete(auth()->user()))
                                        <form action="{{ route('tasks.destroy', $task->card_id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this task?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Delete Task">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-list-task" style="font-size: 2rem; color: #6c757d;"></i>
                                <h5 class="mt-2 text-muted">No Tasks Found</h5>
                                <p class="text-muted">No tasks are available in your projects.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
