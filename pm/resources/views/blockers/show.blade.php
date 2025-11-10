@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('blockers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Blockers
                    </a>
                    <h2 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Blocker Details
                    </h2>
                </div>
                <div>
                    @php
                        $priorityColors = [
                            'critical' => 'danger',
                            'high' => 'warning',
                            'medium' => 'info',
                            'low' => 'secondary'
                        ];
                        $color = $priorityColors[$blocker->priority] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6 text-uppercase">
                        {{ $blocker->priority }} Priority
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Blocker Info -->
        <div class="col-lg-8">
            <!-- Main Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Blocker Information</h5>
                        @php
                            $statusColors = [
                                'reported' => 'danger',
                                'assigned' => 'warning',
                                'in_progress' => 'info',
                                'resolved' => 'success'
                            ];
                            $statusColor = $statusColors[$blocker->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $statusColor }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $blocker->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Task Info -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Related Task</h6>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-tasks fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">{{ $blocker->card->title }}</h5>
                                <p class="text-muted mb-0">
                                    <span class="badge bg-primary">{{ $blocker->card->board->project->name }}</span>
                                    <i class="fas fa-arrow-right mx-2"></i>
                                    {{ $blocker->card->board->name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Blocker Reason -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Reason / Description</h6>
                        <div class="alert alert-light">
                            <p class="mb-0">{{ $blocker->reason }}</p>
                        </div>
                    </div>

                    <!-- Reporter Info -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Reported By</h6>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3" style="width: 48px; height: 48px; font-size: 18px;">
                                {{ substr($blocker->reporter->full_name ?? $blocker->reporter->username, 0, 2) }}
                            </div>
                            <div>
                                <strong>{{ $blocker->reporter->full_name ?? $blocker->reporter->username }}</strong>
                                <br>
                                <small class="text-muted">{{ $blocker->created_at->format('d M Y, H:i') }} ({{ $blocker->created_at->diffForHumans() }})</small>
                            </div>
                        </div>
                    </div>

                    <!-- Resolution (if resolved) -->
                    @if($blocker->status === 'resolved' && $blocker->resolution_note)
                        <div class="mb-0">
                            <h6 class="text-muted mb-2">Resolution Note</h6>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ $blocker->resolution_note }}
                            </div>
                            <small class="text-muted">Resolved at: {{ $blocker->resolved_at->format('d M Y, H:i') }}</small>
                        </div>
                    @endif

                    <!-- Overdue Warning -->
                    @if($blocker->is_overdue && $blocker->status !== 'resolved')
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Overdue!</strong> This blocker has been active for more than 24 hours.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Comments ({{ $blocker->comments->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($blocker->comments->count() > 0)
                        <div class="comments-list">
                            @foreach($blocker->comments as $comment)
                                <div class="comment-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex">
                                        <div class="avatar-circle me-3" style="width: 40px; height: 40px; font-size: 14px;">
                                            {{ substr($comment->user->full_name ?? $comment->user->username, 0, 2) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong>{{ $comment->user->full_name ?? $comment->user->username }}</strong>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0">{{ $comment->comment }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p class="mb-0">No comments yet. Be the first to comment!</p>
                        </div>
                    @endif

                    <!-- Add Comment Form -->
                    @if($blocker->status !== 'resolved')
                        <div class="mt-4">
                            <form action="{{ route('blockers.addComment', $blocker) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Add a Comment</label>
                                    <textarea 
                                        class="form-control @error('comment') is-invalid @enderror" 
                                        id="comment" 
                                        name="comment" 
                                        rows="3" 
                                        placeholder="Share updates, ask questions, or provide help..."
                                        required
                                    >{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Post Comment
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            <!-- Assigned Helper Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Assigned Helper</h6>
                </div>
                <div class="card-body">
                    @if($blocker->assignee)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle me-3" style="width: 48px; height: 48px; font-size: 18px;">
                                {{ substr($blocker->assignee->full_name ?? $blocker->assignee->username, 0, 2) }}
                            </div>
                            <div>
                                <strong>{{ $blocker->assignee->full_name ?? $blocker->assignee->username }}</strong>
                                <br>
                                <small class="text-muted">{{ $blocker->assignee->email }}</small>
                            </div>
                        </div>
                        
                        @if(auth()->user()->isAdmin() || auth()->user()->isProjectAdmin() || auth()->user()->isTeamLead())
                            <button class="btn btn-outline-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#reassignModal">
                                <i class="fas fa-user-edit me-1"></i>Reassign
                            </button>
                        @endif
                    @else
                        <div class="text-center mb-3">
                            <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No helper assigned yet</p>
                        </div>
                        
                        @if(auth()->user()->isAdmin() || auth()->user()->isProjectAdmin() || auth()->user()->isTeamLead())
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#assignModal">
                                <i class="fas fa-user-plus me-1"></i>Assign Helper
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Status Update Card -->
            @if($blocker->status !== 'resolved' && (auth()->user()->isAdmin() || auth()->user()->isProjectAdmin() || auth()->user()->isTeamLead() || $blocker->assigned_to === auth()->id()))
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('blockers.updateStatus', $blocker) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">New Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="reported" {{ $blocker->status === 'reported' ? 'selected' : '' }}>Reported</option>
                                    <option value="assigned" {{ $blocker->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $blocker->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $blocker->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3" id="resolution-note-container" style="display: none;">
                                <label for="resolution_note" class="form-label">Resolution Note</label>
                                <textarea 
                                    class="form-control @error('resolution_note') is-invalid @enderror" 
                                    id="resolution_note" 
                                    name="resolution_note" 
                                    rows="3"
                                    placeholder="Describe how this blocker was resolved..."
                                >{{ old('resolution_note') }}</textarea>
                                @error('resolution_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i>Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Timeline Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $blocker->created_at->format('d M Y, H:i') }}</small>
                                <p class="mb-0"><strong>Reported</strong></p>
                            </div>
                        </div>
                        
                        @if($blocker->assignee)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $blocker->updated_at->format('d M Y, H:i') }}</small>
                                    <p class="mb-0"><strong>Assigned to {{ $blocker->assignee->full_name ?? $blocker->assignee->username }}</strong></p>
                                </div>
                            </div>
                        @endif
                        
                        @if($blocker->status === 'in_progress')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $blocker->updated_at->format('d M Y, H:i') }}</small>
                                    <p class="mb-0"><strong>In Progress</strong></p>
                                </div>
                            </div>
                        @endif
                        
                        @if($blocker->status === 'resolved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $blocker->resolved_at?->format('d M Y, H:i') }}</small>
                                    <p class="mb-0"><strong>Resolved</strong></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Helper Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blockers.assign', $blocker) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Helper</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Select Helper</label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Choose a helper...</option>
                            @foreach($helpers as $helper)
                                <option value="{{ $helper->user_id }}">
                                    {{ $helper->full_name ?? $helper->username }} ({{ $helper->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blockers.assign', $blocker) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reassign Helper</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reassign_to" class="form-label">Select New Helper</label>
                        <select class="form-select" id="reassign_to" name="assigned_to" required>
                            <option value="">Choose a helper...</option>
                            @foreach($helpers as $helper)
                                <option value="{{ $helper->user_id }}" {{ $blocker->assigned_to == $helper->user_id ? 'selected' : '' }}>
                                    {{ $helper->full_name ?? $helper->username }} ({{ $helper->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reassign</button>
                </div>
            </form>
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

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    width: 2px;
    height: calc(100% - 10px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -27px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-content {
    padding-left: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const resolutionNoteContainer = document.getElementById('resolution-note-container');
    const resolutionNote = document.getElementById('resolution_note');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'resolved') {
                resolutionNoteContainer.style.display = 'block';
                resolutionNote.setAttribute('required', 'required');
            } else {
                resolutionNoteContainer.style.display = 'none';
                resolutionNote.removeAttribute('required');
            }
        });
        
        // Trigger on page load
        if (statusSelect.value === 'resolved') {
            resolutionNoteContainer.style.display = 'block';
            resolutionNote.setAttribute('required', 'required');
        }
    }
});
</script>
@endsection
