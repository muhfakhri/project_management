@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Blockers & Help Requests
                    </h2>
                    <p class="text-muted mb-0">Manage blockers that prevent task completion</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('blockers.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ request('priority', 'all') === 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="{{ route('blockers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Critical</h6>
                            <h3 class="mb-0">{{ $blockers->where('priority', 'critical')->where('status', '!=', 'resolved')->count() }}</h3>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">High Priority</h6>
                            <h3 class="mb-0">{{ $blockers->where('priority', 'high')->where('status', '!=', 'resolved')->count() }}</h3>
                        </div>
                        <i class="fas fa-arrow-up fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0">{{ $blockers->whereIn('status', ['reported', 'assigned', 'in_progress'])->count() }}</h3>
                        </div>
                        <i class="fas fa-tasks fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Resolved</h6>
                            <h3 class="mb-0">{{ $blockers->where('status', 'resolved')->count() }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blockers List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Blockers</h5>
                </div>
                <div class="card-body p-0">
                    @if($blockers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">Priority</th>
                                        <th style="width: 120px;">Status</th>
                                        <th>Task</th>
                                        <th>Project</th>
                                        <th>Reason</th>
                                        <th>Reporter</th>
                                        <th>Assigned To</th>
                                        <th style="width: 120px;">Created</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blockers as $blocker)
                                        <tr class="{{ $blocker->is_overdue ? 'table-danger' : '' }}">
                                            <td>
                                                @php
                                                    $priorityColors = [
                                                        'critical' => 'danger',
                                                        'high' => 'warning',
                                                        'medium' => 'info',
                                                        'low' => 'secondary'
                                                    ];
                                                    $color = $priorityColors[$blocker->priority] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }} text-uppercase">
                                                    {{ $blocker->priority }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'reported' => 'danger',
                                                        'assigned' => 'warning',
                                                        'in_progress' => 'info',
                                                        'resolved' => 'success'
                                                    ];
                                                    $statusColor = $statusColors[$blocker->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $blocker->status)) }}
                                                </span>
                                                @if($blocker->is_overdue)
                                                    <br><small class="text-danger"><i class="fas fa-clock"></i> Overdue</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($blocker->card)
                                                    <div>
                                                        <strong class="d-block">{{ $blocker->card->card_title }}</strong>
                                                        @if($blocker->card->board)
                                                            <small class="text-muted">
                                                                <i class="bi bi-kanban me-1"></i>{{ $blocker->card->board->board_name }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted fst-italic">
                                                        <i class="bi bi-file-x me-1"></i>Task not found
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($blocker->card && $blocker->card->board && $blocker->card->board->project)
                                                    <span class="badge bg-primary" style="font-size: 0.85rem;">
                                                        <i class="bi bi-folder me-1"></i>{{ $blocker->card->board->project->project_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    {{ Str::limit($blocker->reason, 80) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                                        {{ substr($blocker->reporter->full_name ?? $blocker->reporter->username, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        {{ $blocker->reporter->full_name ?? $blocker->reporter->username }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($blocker->assignee)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                                            {{ substr($blocker->assignee->full_name ?? $blocker->assignee->username, 0, 2) }}
                                                        </div>
                                                        <div>
                                                            {{ $blocker->assignee->full_name ?? $blocker->assignee->username }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $blocker->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('blockers.show', $blocker) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $blockers->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No Blockers Found</h5>
                            <p class="text-muted">
                                @if(request()->has('status') || request()->has('priority'))
                                    Try adjusting your filters
                                @else
                                    All tasks are running smoothly!
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    background-color: #3b82f6;
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-radius: 50%;
}
</style>
@endsection
