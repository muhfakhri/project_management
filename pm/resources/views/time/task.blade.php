@extends('layouts.app')

@section('title', 'Task Time Tracking')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1"><i class="fas fa-stopwatch me-2"></i>Time Tracking - Task</h1>
                <p class="text-muted mb-0">Review actual time for this task</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('tasks.show', $card) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Task
                </a>
                <a href="{{ route('time.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Log Time
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Task</div>
                        <div class="fw-bold">{{ $card->card_title }}</div>
                        <div class="small text-muted">Project: {{ $card->board->project->project_name }} · Board: {{ $card->board->board_name }}</div>
                    </div>

                    <div class="text-center mb-3">
                        <div class="text-muted small">Total Time Logged</div>
                        <div class="h4 mb-0 text-primary">{{ number_format($stats['total_hours'] ?? 0, 1) }}h</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Time Logs</h5>
                    <span class="badge bg-secondary">{{ $timeLogs->total() }} entries</span>
                </div>
                <div class="card-body">
                    @if($timeLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Description</th>
                                        <th class="text-end">Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeLogs as $log)
                                        <tr>
                                            <td>
                                                <strong>{{ $log->start_time?->format('M d, Y') }}</strong>
                                                <br><small class="text-muted">{{ $log->start_time?->format('l') }}</small>
                                            </td>
                                            <td>
                                                <i class="fas fa-user me-1 text-muted"></i>{{ $log->user->full_name ?? $log->user->username }}
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($log->description, 80) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-primary">{{ number_format(($log->duration_minutes ?? 0)/60, 1) }}h</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($timeLogs->hasPages())
                            <div class="d-flex justify-content-center">{{ $timeLogs->links() }}</div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No time logs yet for this task</h5>
                            <a href="{{ route('time.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>Log Time
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
