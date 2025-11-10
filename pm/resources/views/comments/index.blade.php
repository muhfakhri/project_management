@extends('layouts.app')

@section('title', 'Comments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-comments me-2"></i>Comments</h1>
                    <p class="text-muted">View and manage comments across all your projects</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('comments.myComments') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user me-1"></i>My Comments
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
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

    <!-- Comments List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Comments</h5>
        </div>
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="comments-list">
                    @foreach($comments as $comment)
                        <div class="comment-item mb-4 pb-4 border-bottom">
                            <div class="d-flex">
                                @if($comment->user->profile_picture)
                                    <img src="{{ asset('storage/' . $comment->user->profile_picture) }}" 
                                         alt="{{ $comment->user->full_name ?? $comment->user->username }}" 
                                         class="rounded-circle me-3"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center text-white fw-bold" 
                                         style="width: 40px; height: 40px; background-color: #0d6efd; font-size: 14px; flex-shrink: 0;">
                                        {{ strtoupper(substr($comment->user->full_name ?? $comment->user->username, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $comment->user->full_name ?? $comment->user->username }}</h6>
                                            <div class="text-muted small">
                                                <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                <span class="mx-2">•</span>
                                                <a href="{{ route('projects.show', $comment->card->board->project) }}" class="text-decoration-none">
                                                    {{ $comment->card->board->project->project_name }}
                                                </a>
                                                <span class="mx-2">•</span>
                                                <a href="{{ route('tasks.show', $comment->card) }}" class="text-decoration-none">
                                                    {{ $comment->card->title }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group">
                                            @if($comment->user_id === auth()->id())
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
                                            @endif
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <p class="mb-2">{{ $comment->content }}</p>
                                    </div>
                                    <div class="comment-actions">
                                        <a href="{{ route('tasks.show', $comment->card) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye me-1"></i>View Task
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
                        {{ $comments->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Comments Found</h5>
                    <p class="text-muted">There are no comments to display.</p>
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                        <i class="fas fa-tasks me-1"></i>Browse Tasks
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('comments.search') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title">Search Comments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="search_query" class="form-label">Search for</label>
                        <input type="text" class="form-control" id="search_query" name="query" 
                               placeholder="Enter keywords to search in comments..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>
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
    margin-bottom: 8px;
}
</style>
@endsection
