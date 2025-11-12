<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-tasks me-2"></i>Pending Task Approvals
            <span class="badge bg-dark ms-2">{{ $pendingTasks->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($pendingTasks->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No tasks pending approval.</p>
            </div>
        @else
            <div class="list-group">
                @foreach($pendingTasks as $task)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-2">
                                    <a href="{{ route('tasks.show', $task->card_id) }}" class="text-decoration-none">
                                        {{ $task->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                                <div class="d-flex gap-3 small text-muted">
                                    <span>
                                        <i class="fas fa-folder me-1"></i>{{ $task->board->project->project_name }}
                                    </span>
                                    @if($task->assignments->isNotEmpty())
                                        <span>
                                            <i class="fas fa-user me-1"></i>
                                            @foreach($task->assignments as $assignment)
                                                <span class="badge bg-secondary">{{ $assignment->user->username }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                    <span>
                                        <i class="fas fa-calendar me-1"></i>{{ $task->updated_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="btn-group-vertical">
                                    <a href="{{ route('tasks.show', $task->card_id) }}" class="btn btn-sm btn-outline-primary mb-1">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success mb-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveTaskModal{{ $task->card_id }}">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectTaskModal{{ $task->card_id }}">
                                        <i class="fas fa-undo me-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approve Modal -->
                    <div class="modal fade" id="approveTaskModal{{ $task->card_id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Approve Task</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('tasks.approve', $task->card_id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Approve task: <strong>{{ $task->title }}</strong>?</p>
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
                    <div class="modal fade" id="rejectTaskModal{{ $task->card_id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Reject Task</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('tasks.reject', $task->card_id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Send back to "In Progress": <strong>{{ $task->title }}</strong>?</p>
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
