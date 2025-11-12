<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-clock me-2"></i>Pending Project Completion
            <span class="badge bg-dark ms-2">{{ $pendingProjects->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($pendingProjects->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No projects pending approval.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Requested By</th>
                            <th>Requested At</th>
                            <th>Progress</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProjects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('projects.show', $project->project_id) }}" class="fw-bold text-decoration-none">
                                        {{ $project->project_name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                                </td>
                                <td>
                                    {{ $project->requester->full_name ?? $project->requester->username }}
                                    <br>
                                    <small class="text-muted">Team Lead</small>
                                </td>
                                <td>
                                    {{ $project->requested_at->format('M d, Y H:i') }}
                                </td>
                                <td>
                                    <span class="badge bg-success">All Tasks Completed</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveProjectModal{{ $project->project_id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectProjectModal{{ $project->project_id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveProjectModal{{ $project->project_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Approve Project</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('projects.approveCompletion', $project->project_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Approve <strong>{{ $project->project_name }}</strong>?</p>
                                                <textarea class="form-control" name="approval_notes" rows="3" placeholder="Optional notes..."></textarea>
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
                            <div class="modal fade" id="rejectProjectModal{{ $project->project_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Reject Project</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('projects.rejectCompletion', $project->project_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Reject <strong>{{ $project->project_name }}</strong>?</p>
                                                <textarea class="form-control" name="approval_notes" rows="3" required placeholder="Reason..."></textarea>
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
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
