@extends('layouts.app')

@section('title', 'Time Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-clock me-2 text-primary"></i>Time Logs</h2>
            <p class="text-muted">Track and analyze time spent on tasks across projects</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('time-logs.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Hours</h6>
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
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Logs</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_logs']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-25">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Average Duration</h6>
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

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('time-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('time-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Time Logs Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Time Log Entries</h5>
        </div>
        <div class="card-body p-0">
            @if($timeLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Project</th>
                                <th>Task</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeLogs as $log)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $log->start_time->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($log->user)
                                                @if($log->user->profile_picture)
                                                    <img src="{{ asset('storage/' . $log->user->profile_picture) }}" 
                                                         alt="{{ $log->user->full_name ?? $log->user->username }}"
                                                         class="rounded-circle me-2"
                                                         style="width: 30px; height: 30px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                         style="width: 30px; height: 30px; font-size: 12px;">
                                                        {{ strtoupper(substr($log->user->full_name ?? $log->user->username, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $log->user->full_name ?? $log->user->username }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->card && $log->card->board && $log->card->board->project)
                                            <a href="{{ route('projects.show', $log->card->board->project) }}" class="text-decoration-none">
                                                {{ $log->card->board->project->project_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->card)
                                            <a href="{{ route('tasks.show', $log->card) }}" class="text-decoration-none">
                                                {{ Str::limit($log->card->card_title, 30) }}
                                            </a>
                                        @elseif($log->subtask)
                                            <span class="text-muted">{{ Str::limit($log->subtask->subtask_title, 30) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log->start_time->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $log->end_time ? $log->end_time->format('H:i') : 'In Progress' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ number_format($log->duration_minutes / 60, 1) }}h
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ Str::limit($log->description ?? '-', 50) }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No time logs found</p>
                    <small class="text-muted">Time logs will appear here when tasks are completed</small>
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
@endsection
