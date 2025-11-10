@extends('layouts.app')

@section('title', 'My Time Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-clock me-2 text-primary"></i>My Time Logs</h2>
            <p class="text-muted">Your personal time tracking history</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Hours Worked</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_hours'], 1) }}</h3>
                        </div>
                        <div class="fs-1 opacity-25">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-success text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Work Sessions</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_logs']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-25">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gradient-info text-white shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Avg Session</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['avg_duration'], 1) }}h</h3>
                        </div>
                        <div class="fs-1 opacity-25">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('time-logs.my-logs') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('time-logs.my-logs') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Time Logs Timeline -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Work History</h5>
        </div>
        <div class="card-body">
            @if($timeLogs->count() > 0)
                <div class="timeline">
                    @php
                        $currentDate = null;
                    @endphp
                    @foreach($timeLogs as $log)
                        @php
                            $logDate = $log->start_time->format('Y-m-d');
                        @endphp
                        
                        @if($currentDate !== $logDate)
                            @php $currentDate = $logDate; @endphp
                            <div class="timeline-date mt-4 mb-3">
                                <h6 class="text-muted">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    {{ $log->start_time->format('l, F j, Y') }}
                                </h6>
                                <hr>
                            </div>
                        @endif

                        <div class="timeline-item mb-3">
                            <div class="card border-start border-4 border-primary">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">
                                                @if($log->card)
                                                    <a href="{{ route('tasks.show', $log->card) }}" class="text-decoration-none">
                                                        {{ $log->card->card_title }}
                                                    </a>
                                                @elseif($log->subtask)
                                                    {{ $log->subtask->subtask_title }}
                                                @else
                                                    N/A
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                @if($log->card && $log->card->board && $log->card->board->project)
                                                    <i class="fas fa-folder me-1"></i>{{ $log->card->board->project->project_name }}
                                                @endif
                                            </small>
                                            @if($log->description)
                                                <p class="mb-0 mt-2 small text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>{{ $log->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="mb-1">
                                                <span class="badge bg-primary px-3 py-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ number_format($log->duration_minutes / 60, 1) }} hours
                                                </span>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ $log->start_time->format('H:i') }} - 
                                                {{ $log->end_time ? $log->end_time->format('H:i') : 'In Progress' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">No time logs yet</p>
                    <small class="text-muted">Start working on tasks to track your time</small>
                </div>
            @endif
        </div>
        @if($timeLogs->hasPages())
            <div class="card-footer bg-white">
                {{ $timeLogs->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.bg-gradient-primary {
    background: #3b82f6;
}

.bg-gradient-success {
    background: #10b981;
}

.bg-gradient-info {
    background: #06b6d4;
}

.timeline-item {
    position: relative;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -10px;
    top: 20px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0d6efd;
    border: 2px solid white;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
}
</style>
@endsection
