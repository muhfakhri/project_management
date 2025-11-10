@extends('layouts.app')

@section('title', $task->card_title)

@section('content')
<div class="container-fluid">
    <!-- Archived Project Warning -->
    @if($task->board->project->is_archived)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-archive me-2"></i>
        <strong>Read-Only Mode:</strong> This task belongs to an archived project. All modifications are disabled.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1>{{ $task->card_title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->board->project) }}">{{ $task->board->project->project_name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $task->card_title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group" role="group">
                    @if(!$task->board->project->is_archived)
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reportBlockerModal">
                            <i class="fas fa-exclamation-triangle me-1"></i>Report Blocker
                        </button>
                    @endif
                    @if($task->canEdit(auth()->user()) && !$task->board->project->is_archived)
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit Task
                        </a>
                    @endif
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Tasks
                    </a>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Approval Status Alert -->
    @if($task->needs_approval)
        @if($task->is_approved)
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Task Approved!</strong>
                This task has been approved by {{ $task->approver->name }} on {{ $task->approved_at->format('M d, Y H:i') }}.
            </div>
            @elseif($task->status === 'review')
                {{-- Only show approval options when task is completed and in review status --}}
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-clock me-2"></i>
                <strong>Pending Approval</strong>
                    This task has been completed and is waiting for Team Lead review and approval.
                
                @if($task->canApprove(auth()->user()))
                    <hr>
                    <div class="d-flex gap-2 mt-3">
                        <form action="{{ route('tasks.approve', $task) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Approve Task
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectTaskModal">
                            <i class="fas fa-times me-1"></i>Reject Task
                        </button>
                    </div>
                    @else
                        <p class="mb-0 mt-2"><small>Waiting for Team Lead or Project Admin to review this completed task.</small></p>
                @endif
            </div>
        @endif
        
        @if($task->rejection_reason)
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Task Rejected</strong>
                <p class="mb-0 mt-2">Reason: {{ $task->rejection_reason }}</p>
                <p class="mb-0 mt-1"><small>Please fix the issues and resubmit the task.</small></p>
            </div>
        @endif
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Details</h5>
                    <div class="d-flex gap-2">
                        @switch($task->priority)
                            @case('low')
                                <span class="badge bg-secondary">Low Priority</span>
                                @break
                            @case('medium')
                                <span class="badge bg-warning">Medium Priority</span>
                                @break
                            @case('high')
                                <span class="badge bg-danger">High Priority</span>
                                @break
                        @endswitch
                        
                        @switch($task->status)
                            @case('todo')
                                <span class="badge bg-secondary">To Do</span>
                                @break
                            @case('in_progress')
                                <span class="badge bg-primary">In Progress</span>
                                @break
                            @case('completed')
                                <span class="badge bg-success">Completed</span>
                                @break
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Description:</strong></p>
                            <p class="text-muted">{{ $task->description ?: 'No description provided.' }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Project:</strong></p>
                                    <p><a href="{{ route('projects.show', $task->board->project) }}" class="text-decoration-none">{{ $task->board->project->project_name }}</a></p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Board:</strong></p>
                                    <p class="text-muted">{{ $task->board->board_name }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Created:</strong></p>
                                    <p class="text-muted">{{ $task->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Due Date:</strong></p>
                                    <p class="text-muted">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'Not set' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Tracking Section -->
                    @if(auth()->user()->canTrackTimeInProject($task->board->project_id))
                    <div class="mt-4 border-top pt-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-clock me-2 text-primary"></i>Time Tracking
                                </h6>
                                
                                @if($task->started_at)
                                    <div class="row g-3">
                                        <!-- Working Time -->
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="text-primary mb-1">
                                                    <i class="fas fa-hourglass-half fa-2x"></i>
                                                </div>
                                                <h4 class="mb-0 fw-bold text-primary">{{ $task->getFormattedWorkingTime() }}</h4>
                                                <small class="text-muted">Working Time</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Pause Duration -->
                                        @if($task->getTotalPauseDuration() > 0)
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="text-warning mb-1">
                                                    <i class="fas fa-pause-circle fa-2x"></i>
                                                </div>
                                                <h4 class="mb-0 fw-bold text-warning">{{ $task->getFormattedPauseDuration() }}</h4>
                                                <small class="text-muted">Paused</small>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Status and Actions -->
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($task->isPaused())
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-pause me-1"></i>Paused
                                                </span>
                                            @elseif($task->completed_at)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Completed
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-play me-1"></i>In Progress
                                                </span>
                                            @endif
                                            
                                            @if($task->completed_at)
                                                <small class="text-muted ms-2">
                                                    at {{ $task->completed_at->format('M d, Y H:i') }}
                                                </small>
                                            @else
                                                <small class="text-muted ms-2">
                                                    since {{ $task->started_at->format('M d, H:i') }}
                                                </small>
                                            @endif
                                            
                                            @php
                                                $efficiency = $task->getTimeEfficiency();
                                            @endphp
                                            @if($efficiency && $task->completed_at)
                                                <span class="badge {{ $efficiency >= 100 ? 'bg-success' : 'bg-warning' }} ms-2">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    {{ $efficiency >= 100 ? 'On Time' : 'Ahead' }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            @if(!$task->completed_at && !$task->board->project->is_archived)
                                                @if($task->isPaused())
                                                    <form action="{{ route('tasks.resume', $task) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-play me-1"></i>Resume
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('tasks.pause', $task) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-pause me-1"></i>Pause
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <form action="{{ route('tasks.complete', $task) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Mark this task as complete?')">
                                                        <i class="fas fa-check me-1"></i>Complete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <!-- Not Started Yet -->
                                    <div class="text-center py-4">
                                        <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-3">Task not started yet</p>
                                        @if(!$task->board->project->is_archived)
                                            <form action="{{ route('tasks.start', $task) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-play me-1"></i>Start Work
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Subtasks -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list-check me-2 text-primary"></i>
                        <h5 class="mb-0">Subtasks</h5>
                        @if($task->subtasks->count() > 0)
                            @php
                                // Hitung subtask yang benar-benar selesai (approved atau tidak perlu approval)
                                $completedSubtasks = $task->subtasks->filter(function($subtask) {
                                    return $subtask->status == 'done' && ($subtask->is_approved || !$subtask->needs_approval);
                                })->count();
                                $totalSubtasks = $task->subtasks->count();
                            @endphp
                            <span class="badge bg-primary ms-2">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                        @endif
                    </div>
                    @if($task->canManageSubtasks(auth()->user()) && !$task->board->project->is_archived && !$task->isLocked())
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubtaskModal">
                            <i class="fas fa-plus me-1"></i>Add Subtask
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($task->subtasks->count() > 0)
                        @php
                            // Hitung subtask yang benar-benar selesai (approved atau tidak perlu approval)
                            $completedSubtasks = $task->subtasks->filter(function($subtask) {
                                return $subtask->status == 'done' && ($subtask->is_approved || !$subtask->needs_approval);
                            })->count();
                            $totalSubtasks = $task->subtasks->count();
                            $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                        @endphp
                        
                        <!-- Progress Bar -->
                        <div class="p-3 border-bottom bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Overall Progress</small>
                                <small class="fw-bold text-{{ $progress === 100 ? 'success' : 'primary' }}">{{ $progress }}%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $progress === 100 ? 'success' : 'primary' }}" 
                                     role="progressbar" 
                                     style="width: {{ $progress }}%"
                                     aria-valuenow="{{ $progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Subtasks List -->
                        <div class="list-group list-group-flush">
                            @foreach($task->subtasks as $index => $subtask)
                                <div class="list-group-item subtask-item {{ $subtask->status == 'done' ? 'completed' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <!-- Checkbox/Toggle -->
                                        <div class="me-3 mt-1">
                                            @if($task->canManageSubtasks(auth()->user()) && !$task->board->project->is_archived)
                                                <form action="{{ route('subtasks.toggle', $subtask) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent subtask-checkbox">
                                                        <i class="fas fa-{{ $subtask->status == 'done' ? 'check-circle text-success' : ($subtask->status == 'in_progress' ? 'circle text-primary' : 'circle text-muted') }}" 
                                                           style="font-size: 1.25rem;"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-{{ $subtask->status == 'done' ? 'check-circle text-success' : ($subtask->status == 'in_progress' ? 'circle text-primary' : 'circle text-muted') }}" 
                                                   style="font-size: 1.25rem;"></i>
                                            @endif
                                        </div>

                                        <!-- Subtask Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 subtask-title {{ $subtask->status == 'done' ? 'text-decoration-line-through text-muted' : '' }}">
                                                        {{ $subtask->subtask_title }}
                                                    </h6>                                                    <!-- Approval Status & Actions -->
                                                    @if($subtask->needs_approval)
                                                        <div class="mt-2">
                                                            @if($subtask->is_approved)
                                                                <!-- Approved Badge -->
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-check-circle me-1"></i>Approved
                                                                    </span>
                                                                    <small class="text-muted">
                                                                        by {{ $subtask->approver->full_name ?? $subtask->approver->username }}
                                                                        on {{ $subtask->approved_at->format('M d, H:i') }}
                                                                    </small>
                                                                </div>
                                                            @elseif($subtask->status === 'done')
                                                                <!-- Awaiting Approval -->
                                                                <div class="approval-section p-2 bg-warning bg-opacity-10 rounded border border-warning">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <span class="badge bg-warning text-dark">
                                                                                <i class="fas fa-clock me-1"></i>Awaiting Approval
                                                                            </span>
                                                                            <small class="text-muted ms-2">
                                                                                Completed {{ $subtask->completed_at ? $subtask->completed_at->diffForHumans() : 'recently' }}
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        @if($subtask->canApprove(auth()->user()) && !$task->board->project->is_archived)
                                                                            <div class="btn-group btn-group-sm">
                                                                                <form action="{{ route('subtasks.approve', $subtask) }}" method="POST" class="d-inline">
                                                                                    @csrf
                                                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve this subtask">
                                                                                        <i class="fas fa-check me-1"></i>Approve
                                                                                    </button>
                                                                                </form>
                                                                                <button type="button" class="btn btn-danger btn-sm" 
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#rejectSubtaskModal{{ $subtask->subtask_id }}"
                                                                                        title="Reject this subtask">
                                                                                    <i class="fas fa-times me-1"></i>Reject
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @elseif($subtask->rejection_reason)
                                                                <!-- Rejected Status -->
                                                                <div class="alert alert-danger py-2 px-3 mb-0">
                                                                    <div class="d-flex align-items-start">
                                                                        <i class="fas fa-times-circle text-danger me-2 mt-1"></i>
                                                                        <div class="flex-grow-1">
                                                                            <strong>Rejected</strong>
                                                                            <p class="mb-0 small">{{ $subtask->rejection_reason }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <!-- Needs Approval Flag -->
                                                                <span class="badge bg-info">
                                                                    <i class="fas fa-shield-alt me-1"></i>Requires Approval
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Actions -->
                                                @if($task->canManageSubtasks(auth()->user()) && !$task->board->project->is_archived && !$task->isLocked())
                                                    <div class="btn-group ms-2">
                                                        <form action="{{ route('subtasks.destroy', $subtask) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('Delete this subtask?')"
                                                                    title="Delete subtask">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Reject Modal for this subtask -->
                                    <div class="modal fade" id="rejectSubtaskModal{{ $subtask->subtask_id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('subtasks.reject', $subtask) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-times-circle me-2"></i>Reject Subtask
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Rejecting:</strong> {{ $subtask->subtask_title }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="rejection_reason{{ $subtask->subtask_id }}" class="form-label">
                                                                Reason for Rejection <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea class="form-control" 
                                                                      id="rejection_reason{{ $subtask->subtask_id }}" 
                                                                      name="rejection_reason" 
                                                                      rows="4" 
                                                                      placeholder="Explain why this subtask needs to be reworked..."
                                                                      required></textarea>
                                                            <small class="text-muted">
                                                                Be specific to help the developer understand what needs to be improved.
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-times me-1"></i>Reject Subtask
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-list-check fa-3x text-muted"></i>
                            </div>
                            <h6 class="text-muted mb-2">No Subtasks Yet</h6>
                            <p class="text-muted small mb-3">Break down this task into smaller actionable items</p>
                            @if($task->canManageSubtasks(auth()->user()))
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubtaskModal">
                                    <i class="fas fa-plus me-1"></i>Add First Subtask
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

<style>
.subtask-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.subtask-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0d6efd;
}

.subtask-item.completed {
    background-color: #f8f9fa;
}

.subtask-checkbox:hover i {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

.subtask-title {
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.4;
}
</style>

            <!-- Comments -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Comments</h5>
                </div>
                <div class="card-body">
                    @if($task->comments->count() > 0)
                        @foreach($task->comments as $comment)
                            <div class="d-flex mb-3">
                                @if($comment->user->profile_picture)
                                    <img src="{{ asset('storage/' . $comment->user->profile_picture) }}" 
                                         alt="{{ $comment->user->full_name ?? $comment->user->username }}"
                                         class="rounded-circle me-3"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3"
                                         style="width: 40px; height: 40px; font-size: 14px; font-weight: bold; background-color: #0d6efd;">
                                        {{ strtoupper(substr($comment->user->full_name ?? $comment->user->username, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $comment->user->full_name ?? $comment->user->username }}</h6>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        @if($comment->user_id === auth()->id() && !$task->board->project->is_archived)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this comment?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <p class="mb-0 mt-2">{{ $comment->content }}</p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No comments yet.</p>
                    @endif

                    <!-- Add Comment Form -->
                    @if(!$task->board->project->is_archived && !$task->isLocked())
                        <form action="{{ route('comments.store') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $task->card_id }}">
                            <div class="d-flex gap-2">
                                @if(auth()->user()->profile_picture)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                                         alt="{{ auth()->user()->full_name ?? auth()->user()->username }}"
                                         class="rounded-circle"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                        {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <textarea class="form-control" 
                                              name="content" 
                                              rows="3" 
                                              placeholder="Add a comment..." 
                                              required></textarea>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Post Comment
                                </button>
                            </div>
                        </form>
                    @elseif($task->isLocked())
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-lock me-2"></i>
                            This task has been approved and locked. Comments are disabled.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Assigned Members -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Assigned To</h5>
                    @if($task->canEdit(auth()->user()) && !$task->board->project->is_archived && $task->assignments->count() == 0)
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignMemberModal">
                            <i class="fas fa-user-plus me-1"></i>Assign
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($task->assignments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($task->assignments as $assignment)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            @if($assignment->user->profile_picture)
                                                <img src="{{ asset('storage/' . $assignment->user->profile_picture) }}" 
                                                     alt="{{ $assignment->user->full_name ?? $assignment->user->username }}"
                                                     class="rounded-circle me-3"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                     style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                                    {{ strtoupper(substr($assignment->user->full_name ?? $assignment->user->username, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $assignment->user->full_name ?? $assignment->user->username }}</h6>
                                                <small class="text-muted">{{ $assignment->user->email }}</small>
                                            </div>
                                        </div>
                                        @if($task->canEdit(auth()->user()) && !$task->board->project->is_archived)
                                            <form action="{{ route('tasks.unassign', [$task, $assignment]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Unassign this member?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No one assigned yet</p>
                            @if($task->canEdit(auth()->user()) && !$task->board->project->is_archived)
                                <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#assignMemberModal">
                                    <i class="fas fa-user-plus me-1"></i>Assign Member
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Time Logs -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Time Logs</h5>
                </div>
                <div class="card-body">
                    @if($task->timeLogs->count() > 0)
                        @php
                            $totalMinutes = $task->timeLogs->sum('duration_minutes');
                            $totalHours = $totalMinutes / 60;
                        @endphp
                        <div class="text-center mb-4 p-3 bg-light rounded">
                            <h2 class="text-primary mb-1">{{ number_format($totalHours, 1) }}</h2>
                            <small class="text-muted">Total Hours Logged</small>
                        </div>
                        
                        <div class="list-group list-group-flush">
                            @foreach($task->timeLogs->sortByDesc('start_time')->take(5) as $log)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                @if($log->user)
                                                    @if($log->user->profile_picture)
                                                        <img src="{{ asset('storage/' . $log->user->profile_picture) }}" 
                                                             alt="{{ $log->user->full_name ?? $log->user->username }}"
                                                             class="rounded-circle me-2"
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                             style="width: 32px; height: 32px; font-size: 12px;">
                                                            {{ strtoupper(substr($log->user->full_name ?? $log->user->username, 0, 2)) }}
                                                        </div>
                                                    @endif
                                                    <h6 class="mb-0">{{ $log->user->full_name ?? $log->user->username }}</h6>
                                                @endif
                                            </div>
                                            @if($log->description)
                                                <p class="mb-1 small text-muted">{{ $log->description }}</p>
                                            @endif
                                            <div class="d-flex gap-3 small text-muted">
                                                <span>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $log->start_time->format('M d, Y') }}
                                                </span>
                                                <span>
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $log->start_time->format('H:i') }} - 
                                                    {{ $log->end_time ? $log->end_time->format('H:i') : 'In Progress' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary fs-6">
                                                {{ number_format($log->duration_minutes / 60, 1) }}h
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($task->timeLogs->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('time-logs.task', $task) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>View All {{ $task->timeLogs->count() }} Logs
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock-rotate-left fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No time logs yet</p>
                            <small class="text-muted">Time will be logged automatically when task is completed</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('tasks.modals.add-subtask')
@include('tasks.modals.assign-member')
{{-- Removed legacy manual log-time modal: migrated to automated/new time tracking --}}

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>

<!-- Reject Task Modal -->
<div class="modal fade" id="rejectTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.reject', $task) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this task:</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The task will be moved back to "In Progress" status and the developer will be notified.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Blocker Modal -->
<div class="modal fade" id="reportBlockerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blockers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="card_id" value="{{ $task->card_id }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Report Blocker
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What is a blocker?</strong> A blocker is an issue that prevents you from completing this task. 
                        Team leads and admins will be notified and can help resolve it.
                    </div>
                    
                    <div class="mb-3">
                        <label for="blocker_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="blocker_priority" 
                                name="priority" 
                                required>
                            <option value="">Select priority...</option>
                            <option value="low">Low - Minor issue, can work on other things</option>
                            <option value="medium" selected>Medium - Significant obstacle</option>
                            <option value="high">High - Severely blocking progress</option>
                            <option value="critical">Critical - Complete work stoppage</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="blocker_reason" class="form-label">Describe the blocker <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control @error('reason') is-invalid @enderror" 
                            id="blocker_reason" 
                            name="reason" 
                            rows="4" 
                            placeholder="Explain what is blocking you from completing this task..."
                            required
                            minlength="10"
                            maxlength="1000"
                        >{{ old('reason') }}</textarea>
                        <div class="form-text">
                            Minimum 10 characters. Be specific so team leads can help effectively.
                        </div>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Team leads and project admins will be notified immediately.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Report Blocker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

