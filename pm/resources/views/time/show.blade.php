@extends('layouts.app')

@section('title', 'Time Log Detail')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-clock me-2"></i>Time Log Detail</h1>
                    <p class="text-muted">View time log information</p>
                </div>
                <div>
                    <a href="{{ route('time.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Time Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Time Log Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Time Log Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Start Time</label>
                                <div class="fw-bold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    {{ $timeLog->start_time ? $timeLog->start_time->format('l, F d, Y h:i A') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Duration</label>
                                <div class="fw-bold">
                                    <i class="fas fa-clock me-2 text-success"></i>
                                    {{ number_format(($timeLog->duration_minutes ?? 0) / 60, 1) }} hours
                                    <span class="text-muted small">({{ $timeLog->duration_minutes ?? 0 }} minutes)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($timeLog->end_time)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">End Time</label>
                                    <div class="fw-bold">
                                        <i class="fas fa-flag-checkered me-2 text-warning"></i>
                                        {{ $timeLog->end_time->format('l, F d, Y h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="text-muted small mb-1">User</label>
                        <div class="fw-bold">
                            <i class="fas fa-user me-2 text-info"></i>
                            {{ $timeLog->user->full_name ?? $timeLog->user->username }}
                            <span class="badge bg-light text-dark ms-2">{{ $timeLog->user->username }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small mb-1">Description</label>
                        <div class="p-3 bg-light rounded">
                            {{ $timeLog->description ?: 'No description provided' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Task Card -->
            @if($timeLog->card)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>Related Task
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Task Name</label>
                            <div>
                                <a href="{{ route('tasks.show', $timeLog->card) }}" class="h5 text-decoration-none">
                                    {{ $timeLog->card->title }}
                                </a>
                                @if($timeLog->card->status)
                                    <span class="badge 
                                        @if($timeLog->card->status == 'done') bg-success
                                        @elseif($timeLog->card->status == 'in-progress') bg-primary
                                        @elseif($timeLog->card->status == 'review') bg-warning
                                        @else bg-secondary
                                        @endif ms-2">
                                        {{ ucfirst(str_replace('-', ' ', $timeLog->card->status)) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($timeLog->card->description)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Task Description</label>
                                <div class="text-muted">{{ Str::limit($timeLog->card->description, 200) }}</div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Project</label>
                                <div>
                                    <a href="{{ route('projects.show', $timeLog->card->board->project) }}">
                                        <i class="fas fa-project-diagram me-1"></i>
                                        {{ $timeLog->card->board->project->project_name }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1">Board</label>
                                <div>
                                    <a href="{{ route('boards.show', $timeLog->card->board) }}">
                                        <i class="fas fa-columns me-1"></i>
                                        {{ $timeLog->card->board->board_name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($timeLog->user_id === auth()->id())
                        <a href="{{ route('time.edit', $timeLog) }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i>Edit Time Log
                        </a>
                        <form action="{{ route('time.destroy', $timeLog) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this time log?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>Delete Time Log
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            You can only edit or delete your own time logs.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Hours Logged</span>
                            <span class="fw-bold">{{ number_format(($timeLog->duration_minutes ?? 0) / 60, 1) }}h</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Date</span>
                            <span class="fw-bold">{{ $timeLog->start_time ? $timeLog->start_time->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">By</span>
                            <span class="fw-bold">{{ $timeLog->user->username ?? 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endsection
