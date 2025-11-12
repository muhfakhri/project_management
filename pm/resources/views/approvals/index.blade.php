@extends('layouts.app')

@section('title', 'All Approvals')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i>All Approvals</h1>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('approvals.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Type</label>
                        <select name="filter" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Items</option>
                            <option value="subtasks" {{ $filter === 'subtasks' ? 'selected' : '' }}>Subtasks Only</option>
                            <option value="tasks" {{ $filter === 'tasks' ? 'selected' : '' }}>Tasks Only</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Project</label>
                        <select name="project" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="all" {{ $projectFilter === 'all' ? 'selected' : '' }}>All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->project_id }}" {{ $projectFilter == $project->project_id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Project Completion Approvals (Project Admin only) -->
    @if($isProjectAdmin && $pendingProjects->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-folder-check text-success me-2"></i>
                Project Completion Requests
                <span class="badge bg-success">{{ $pendingProjects->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Requested By</th>
                            <th>Requested At</th>
                            <th>Team Members</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProjects as $project)
                        <tr>
                            <td>
                                <strong>{{ $project->project_name }}</strong>
                                @if($project->description)
                                    <br><small class="text-muted">{{ Str::limit($project->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($project->requester)
                                    <span class="badge bg-secondary">{{ $project->requester->username }}</span>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($project->requested_at)
                                    {{ $project->requested_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($project->members->count() > 0)
                                    @foreach($project->members->take(3) as $member)
                                        <span class="badge bg-info">{{ $member->user->username }}</span>
                                    @endforeach
                                    @if($project->members->count() > 3)
                                        <span class="badge bg-light text-dark">+{{ $project->members->count() - 3 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">No members</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">Pending Review</span>
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-info" title="View Project">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveProjectModal{{ $project->project_id }}" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectProjectModal{{ $project->project_id }}" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveProjectModal{{ $project->project_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('projects.approveCompletion', $project->project_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Project Completion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <strong>Approving:</strong> {{ $project->project_name }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Approval Notes (Optional)</label>
                                                <textarea class="form-control" name="approval_notes" rows="3" 
                                                          placeholder="Add any notes about this approval..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectProjectModal{{ $project->project_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('projects.rejectCompletion', $project->project_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Project Completion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Rejecting:</strong> {{ $project->project_name }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Reason for Rejection <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" name="approval_notes" rows="3" required 
                                                          placeholder="Explain why this project completion is being rejected..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Subtasks -->
    @if(($filter === 'all' || $filter === 'subtasks') && $subtasks->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-list-check text-warning me-2"></i>
                Subtasks 
                <span class="badge bg-warning text-dark">{{ $subtasks->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subtask</th>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Completed By</th>
                            <th>Completed At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subtasks as $subtask)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $subtask->subtask_title }}</strong>
                                    @if($subtask->description)
                                        <br><small class="text-muted">{{ Str::limit($subtask->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('tasks.show', $subtask->card) }}" class="text-decoration-none">
                                    {{ $subtask->card->card_title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $subtask->card->board->project->project_name }}</span>
                            </td>
                            <td>
                                @if($subtask->card->assignments->isNotEmpty())
                                    @foreach($subtask->card->assignments->take(2) as $assignment)
                                        <span class="badge bg-secondary">{{ $assignment->user->username }}</span>
                                    @endforeach
                                    @if($subtask->card->assignments->count() > 2)
                                        <span class="badge bg-light text-dark">+{{ $subtask->card->assignments->count() - 2 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($subtask->completed_at)
                                    <small>{{ $subtask->completed_at->format('M d, Y H:i') }}</small>
                                    <br><small class="text-muted">{{ $subtask->completed_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($subtask->is_approved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Approved
                                    </span>
                                    @if($subtask->approver)
                                        <br><small class="text-muted">by {{ $subtask->approver->username }}</small>
                                    @endif
                                @elseif($subtask->rejection_reason)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$subtask->is_approved && !$subtask->rejection_reason)
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('subtasks.approve', $subtask) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectSubtaskModal{{ $subtask->subtask_id }}"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <a href="{{ route('tasks.show', $subtask->card) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectSubtaskModal{{ $subtask->subtask_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('subtasks.reject', $subtask) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Subtask</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Rejecting:</strong> {{ $subtask->subtask_title }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Reason for Rejection <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" name="rejection_reason" rows="3" required 
                                                          placeholder="Explain why this subtask is being rejected..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-times me-1"></i>Reject Subtask
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Tasks -->
    @if(($filter === 'all' || $filter === 'tasks') && $tasks->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-tasks text-info me-2"></i>
                Tasks 
                <span class="badge bg-info">{{ $tasks->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Board</th>
                            <th>Project</th>
                            <th>Assigned To</th>
                            <th>Completed At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $task->card_title }}</strong>
                                    @if($task->description)
                                        <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $task->board->board_name }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $task->board->project->project_name }}</span>
                            </td>
                            <td>
                                @if($task->assignments->isNotEmpty())
                                    @foreach($task->assignments->take(2) as $assignment)
                                        <span class="badge bg-secondary">{{ $assignment->user->username }}</span>
                                    @endforeach
                                    @if($task->assignments->count() > 2)
                                        <span class="badge bg-light text-dark">+{{ $task->assignments->count() - 2 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($task->completed_at)
                                    <small>{{ $task->completed_at->format('M d, Y H:i') }}</small>
                                    <br><small class="text-muted">{{ $task->completed_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($task->is_approved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Approved
                                    </span>
                                    @if($task->approver)
                                        <br><small class="text-muted">by {{ $task->approver->username }}</small>
                                    @endif
                                @elseif($task->rejection_reason)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$task->is_approved && !$task->rejection_reason)
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('tasks.approve', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectTaskModal{{ $task->card_id }}"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectTaskModal{{ $task->card_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('tasks.reject', $task) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Task</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Rejecting:</strong> {{ $task->card_title }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Reason for Rejection <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" name="rejection_reason" rows="3" required 
                                                          placeholder="Explain why this task is being rejected..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-times me-1"></i>Reject Task
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if($subtasks->count() === 0 && $tasks->count() === 0 && (!$isProjectAdmin || $pendingProjects->count() === 0))
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-double fa-4x text-muted mb-3"></i>
            <h4>No Items to Review</h4>
            <p class="text-muted">
                @if($statusFilter === 'pending')
                    There are no pending approvals at the moment.
                @elseif($statusFilter === 'approved')
                    No approved items found with the current filters.
                @else
                    No rejected items found with the current filters.
                @endif
            </p>
            <a href="{{ route('approvals.index') }}" class="btn btn-primary">
                <i class="fas fa-sync me-1"></i>Reset Filters
            </a>
        </div>
    </div>
    @endif
</div>
@endsection