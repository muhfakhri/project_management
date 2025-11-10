@extends('layouts.app')

@section('title', $user->full_name ?? $user->username)

@section('content')
<div class="container">
    <div class="row">
        <!-- Profile Header -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="rounded-circle"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 120px; height: 120px; font-size: 3rem;">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md">
                            <h2 class="mb-1">{{ $user->full_name ?? $user->username }}</h2>
                            <p class="text-muted mb-2">{{ '@' . $user->username }}</p>
                            @if($user->bio)
                                <p class="mb-3">{{ $user->bio }}</p>
                            @endif
                            <div class="d-flex gap-3 flex-wrap">
                                @if($user->email)
                                    <span><i class="fas fa-envelope text-muted me-1"></i>{{ $user->email }}</span>
                                @endif
                                @if($user->phone)
                                    <span><i class="fas fa-phone text-muted me-1"></i>{{ $user->phone }}</span>
                                @endif
                                <span><i class="fas fa-calendar text-muted me-1"></i>Joined {{ $user->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                        @if(auth()->id() === $user->user_id)
                            <div class="col-md-auto">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>Edit Profile
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="col-md-3">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <i class="fas fa-project-diagram fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0">{{ $stats['total_projects'] }}</h3>
                    <small class="text-muted">Projects</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <i class="fas fa-tasks fa-2x text-info mb-2"></i>
                    <h3 class="mb-0">{{ $stats['total_tasks'] }}</h3>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="mb-0">{{ $stats['completed_tasks'] }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center mb-3">
                <div class="card-body">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0">{{ number_format($stats['time_logged'], 1) }}h</h3>
                    <small class="text-muted">Time Logged</small>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Recent Tasks</h5>
                </div>
                <div class="card-body">
                    @forelse($tasks as $task)
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                        {{ $task->card_title }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-folder me-1"></i>{{ $task->board->project->project_name }}
                                </small>
                            </div>
                            <div>
                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="badge bg-{{ $task->status === 'done' ? 'success' : 'info' }} ms-1">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">No recent tasks</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Projects -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Projects</h5>
                </div>
                <div class="card-body">
                    @forelse($projects as $project)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                        {{ $project->project_name }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    @php
                                        $userRole = $project->members->where('user_id', $user->user_id)->first();
                                    @endphp
                                    @if($userRole)
                                        <span class="badge bg-secondary">{{ $userRole->role }}</span>
                                    @endif
                                </small>
                            </div>
                            @if($project->deadline)
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>{{ $project->deadline->format('M d, Y') }}
                                </small>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">No projects yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
