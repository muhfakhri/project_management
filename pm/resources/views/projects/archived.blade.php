@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-archive me-2"></i>Archived Projects
                    </h2>
                    <p class="text-muted mb-0">Completed projects history</p>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Active Projects
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($projects as $project)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm archived-card">
                    <div class="card-header bg-secondary bg-opacity-10 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $project->project_name }}</h5>
                            <span class="badge bg-secondary">
                                <i class="fas fa-archive me-1"></i>Archived
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted">{{ Str::limit($project->description, 100) }}</p>
                        
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Created by: {{ $project->creator->full_name ?? $project->creator->username }}
                            </small>
                        </div>

                        @if($project->archived_at)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Archived: {{ $project->archived_at->format('M d, Y') }}
                                </small>
                            </div>
                        @endif

                        @if($project->archivedBy)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-user-check me-1"></i>
                                    By: {{ $project->archivedBy->full_name ?? $project->archivedBy->username }}
                                </small>
                            </div>
                        @endif

                        @if($project->deadline)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Deadline: {{ $project->deadline->format('M d, Y') }}
                                </small>
                            </div>
                        @endif

                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $project->progress }}%" 
                                 role="progressbar">
                            </div>
                        </div>
                        <small class="text-muted">Progress: {{ number_format($project->progress, 1) }}%</small>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('projects.show', $project->project_id) }}" 
                               class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            @if($project->isAdmin(auth()->id()))
                                <form action="{{ route('projects.unarchive', $project->project_id) }}" 
                                      method="POST" 
                                      class="flex-grow-1">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-success w-100"
                                            onclick="return confirm('Are you sure you want to unarchive this project?')">
                                        <i class="fas fa-undo me-1"></i>Unarchive
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Archived Projects</h5>
                        <p class="text-muted">Completed projects will appear here</p>
                        <a href="{{ route('projects.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Active Projects
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($projects->hasPages())
        <div class="row mt-4">
            <div class="col">
                {{ $projects->links() }}
            </div>
        </div>
    @endif
</div>

<style>
.archived-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    opacity: 0.9;
}

.archived-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    opacity: 1;
}

.card-footer {
    padding: 1rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 0.9;
        transform: translateY(0);
    }
}

.archived-card {
    animation: fadeIn 0.5s ease-out;
}
</style>
@endsection
