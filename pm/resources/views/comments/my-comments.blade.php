@extends('layouts.app')

@section('title', 'My Comments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-user-comments me-2"></i>My Comments</h1>
                    <p class="text-muted">All comments you've made across projects</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('comments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>All Comments
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

    <!-- My Comments Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary">{{ $comments->total() }}</h3>
                    <small class="text-muted">Total Comments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-info mb-2"></i>
                    <h3 class="text-info">{{ $comments->where('created_at', '>=', now()->startOfDay())->count() }}</h3>
                    <small class="text-muted">Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-warning mb-2"></i>
                    <h3 class="text-warning">{{ $comments->where('created_at', '>=', now()->startOfWeek())->count() }}</h3>
                    <small class="text-muted">This Week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-project-diagram fa-2x text-success mb-2"></i>
                    <h3 class="text-success">{{ $comments->pluck('card.board.project_id')->unique()->count() }}</h3>
                    <small class="text-muted">Projects</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Comments</h5>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Sort
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('comments.myComments', ['sort' => 'newest']) }}">Newest First</a></li>
                    <li><a class="dropdown-item" href="{{ route('comments.myComments', ['sort' => 'oldest']) }}">Oldest First</a></li>
                    <li><a class="dropdown-item" href="{{ route('comments.myComments', ['sort' => 'project']) }}">By Project</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="comments-list">
                    @foreach($comments as $comment)
                        <div class="comment-item mb-4 pb-4 border-bottom">
                            <div class="d-flex">
                                @if(auth()->user()->profile_picture)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                                         alt="{{ auth()->user()->full_name ?? auth()->user()->username }}" 
                                         class="rounded-circle me-3"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center text-white fw-bold" 
                                         style="width: 40px; height: 40px; background-color: #0d6efd; font-size: 14px; flex-shrink: 0;">
                                        {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h6 class="mb-0">You</h6>
                                                <span class="badge bg-info">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-project-diagram me-1"></i>
                                                <a href="{{ route('projects.show', $comment->card->board->project) }}" class="text-decoration-none">
                                                    {{ $comment->card->board->project->project_name }}
                                                </a>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-tasks me-1"></i>
                                                <a href="{{ route('tasks.show', $comment->card) }}" class="text-decoration-none">
                                                    {{ $comment->card->title }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('comments.edit', $comment) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this comment?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <p class="mb-0">{{ $comment->content }}</p>
                                    </div>
                                    <div class="comment-actions mt-2">
                                        <a href="{{ route('tasks.show', $comment->card) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye me-1"></i>View Task
                                        </a>
                                        <a href="{{ route('comments.edit', $comment) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($comments->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $comments->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Comments Yet</h5>
                    <p class="text-muted">You haven't made any comments yet.</p>
                    <div>
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary me-2">
                            <i class="fas fa-tasks me-1"></i>Browse Tasks
                        </a>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-project-diagram me-1"></i>Browse Projects
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Comment Activity Timeline -->
    @if($comments->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Comment Activity</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $commentsByProject = $comments->groupBy('card.board.project.project_name');
                    @endphp
                    @foreach($commentsByProject->take(4) as $projectName => $projectComments)
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $projectComments->count() }}</h4>
                                <small class="text-muted">{{ Str::limit($projectName, 20) }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

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
    flex-shrink: 0;
}

.avatar-circle img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.comment-content {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    margin: 8px 0;
}
</style>
@endsection
