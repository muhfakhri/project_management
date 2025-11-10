@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>My Projects</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.archived') }}" class="btn btn-outline-secondary">
            <i class="fas fa-archive me-1"></i>View Archived Projects
        </a>
        @if(auth()->user()->canCreateProject())
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Project
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

<div class="row">
    @forelse($projects as $project)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $project->project_name }}</h5>
                    <div class="d-flex gap-2">
                        @php
                            $statusConfig = [
                                'planning' => ['badge' => 'secondary', 'icon' => 'fa-clipboard-list', 'text' => 'Planning'],
                                'in_progress' => ['badge' => 'primary', 'icon' => 'fa-spinner', 'text' => 'In Progress'],
                                'done' => ['badge' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Completed'],
                                'on_hold' => ['badge' => 'warning', 'icon' => 'fa-pause-circle', 'text' => 'On Hold']
                            ];
                            $config = $statusConfig[$project->status] ?? $statusConfig['planning'];
                        @endphp
                        <span class="badge bg-{{ $config['badge'] }}">
                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                        </span>
                        @if($project->status != 'done')
                            @if($project->deadline && $project->deadline->isPast())
                                <span class="badge bg-danger">Overdue</span>
                            @elseif($project->deadline && $project->deadline->isFuture() && $project->deadline->diffInDays(now()) <= 7)
                                <span class="badge bg-warning text-dark">Due Soon</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ Str::limit($project->description, 100) }}</p>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-person"></i> Created by: {{ $project->creator->full_name ?? $project->creator->username }}
                        </small>
                    </div>

                    @if($project->deadline)
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> Deadline: {{ $project->deadline->format('M d, Y') }}
                            </small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-people"></i> {{ $project->members->count() }} members
                        </small>
                    </div>

                    {{-- Progress Bar --}}
                    @php
                        $progress = $project->progress ?? 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>Progress</small>
                            <small>{{ number_format($progress, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $progress }}%"
                                 aria-valuenow="{{ $progress }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> View
                        </a>
                        @if($project->isAdmin(auth()->id()))
                            <a href="{{ route('projects.edit', $project->project_id) }}" 
                               class="btn btn-outline-warning btn-sm"
                               title="Edit Project">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal{{ $project->project_id }}"
                                    title="Delete Project">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal for {{ $project->project_name }} -->
        @if($project->isAdmin(auth()->id()))
        <div class="modal fade" id="deleteModal{{ $project->project_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>Delete Project
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong>"{{ $project->project_name }}"</strong>?</p>
                        <p class="text-danger mb-0">
                            <small><i class="fas fa-exclamation-circle me-1"></i>This will delete all boards, tasks, and data associated with this project.</small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('projects.destroy', $project->project_id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-folder-x" style="font-size: 3rem; color: #6c757d;"></i>
                <h4 class="mt-3 text-muted">No Projects Found</h4>
                <p class="text-muted">You're not a member of any projects yet.</p>
                @if(auth()->user()->canCreateProject())
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Project
                    </a>
                @else
                    <p class="text-muted small">Contact a Project Admin to be added to a project.</p>
                @endif
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $projects->links() }}
</div>
@endsection
