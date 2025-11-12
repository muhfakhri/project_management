<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-list-check me-2"></i>Pending Subtask Approvals
            <span class="badge bg-dark ms-2">{{ $pendingSubtasks->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($pendingSubtasks->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No subtasks pending approval.</p>
            </div>
        @else
            <div class="list-group">
                @foreach($pendingSubtasks as $subtask)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-2">
                                    {{ $subtask->subtask_title }}
                                </h6>
                                <div class="d-flex gap-3 small text-muted">
                                    <span>
                                        <i class="fas fa-tasks me-1"></i>
                                        <a href="{{ route('tasks.show', $subtask->card->card_id) }}">
                                            {{ $subtask->card->title }}
                                        </a>
                                    </span>
                                    <span>
                                        <i class="fas fa-folder me-1"></i>{{ $subtask->card->board->project->project_name }}
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar me-1"></i>{{ $subtask->completed_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="btn-group-vertical">
                                    <a href="{{ route('tasks.show', $subtask->card->card_id) }}" class="btn btn-sm btn-outline-primary mb-1">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success mb-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveSubtaskModal{{ $subtask->subtask_id }}">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectSubtaskModal{{ $subtask->subtask_id }}">
                                        <i class="fas fa-undo me-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approve Modal -->
                    <div class="modal fade" id="approveSubtaskModal{{ $subtask->subtask_id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Approve Subtask</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('subtasks.approve', $subtask->subtask_id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Approve subtask: <strong>{{ $subtask->subtask_title }}</strong>?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectSubtaskModal{{ $subtask->subtask_id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Reject Subtask</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('subtasks.reject', $subtask->subtask_id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Reopen: <strong>{{ $subtask->subtask_title }}</strong>?</p>
                                        <textarea class="form-control" name="reason" rows="3" required placeholder="Reason..."></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
