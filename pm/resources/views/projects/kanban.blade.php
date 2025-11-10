@extends('layouts.app')

@section('title', 'Kanban Board - ' . $project->project_name)

@php
// Define colors and icons for boards
$boardColors = ['#6c757d', '#0d6efd', '#ffc107', '#198754', '#dc3545', '#0dcaf0', '#6610f2'];
$boardIcons = ['fa-list', 'fa-spinner', 'fa-eye', 'fa-check-circle', 'fa-flag', 'fa-star', 'fa-rocket'];

// Check if user can drag and drop (only Project Admin and Team Lead)
$canDragDrop = $project->canManageTeam(auth()->id());
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-columns me-2"></i>{{ $project->project_name }} - Kanban</h1>
            <p class="text-muted mb-0">
                <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Project
                </a>
            </p>
        </div>
        <div>
            @if($project->canManageTeam(auth()->id()))
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="fas fa-plus me-1"></i>New Task
                </button>
            @endif
        </div>
    </div>

    @if(!$canDragDrop)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>View Only Mode:</strong> You can view the kanban board, but only Project Admin and Team Lead can move cards between columns.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($boards->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No boards found in this project. 
            @if($project->isAdmin(auth()->id()))
                <a href="{{ route('projects.show', $project) }}" class="alert-link">Create boards first</a> to start using Kanban.
            @else
                Ask your project admin to create boards.
            @endif
        </div>
    @else
        <!-- Kanban Boards -->
        <div class="kanban-container">
            <div class="row g-3">
                @foreach($boards as $index => $board)
                    <div class="col-lg-{{ $boards->count() <= 3 ? (12 / $boards->count()) : 3 }} col-md-6">
                        <div class="kanban-column">
                            <div class="kanban-header" style="background: {{ $boardColors[$index % count($boardColors)] }};">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas {{ $boardIcons[$index % count($boardIcons)] }} me-2"></i>{{ $board->board_name }}
                                        <span class="badge bg-white text-dark ms-2">{{ $board->cards->count() }}</span>
                                    </h5>
                                    @if($board->description)
                                        <i class="fas fa-info-circle" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $board->description }}">
                                        </i>
                                    @endif
                                </div>
                            </div>
                            <div class="kanban-body" data-board-id="{{ $board->board_id }}" id="board-{{ $board->board_id }}">
                                @forelse($board->cards->sortBy('position') as $card)
                                    @include('projects.partials.kanban-card', ['card' => $card])
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small">No tasks in this board</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->project_id }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Board <span class="text-danger">*</span></label>
                        <select name="board_id" class="form-select" required>
                            <option value="">Select Board</option>
                            @foreach($boards as $board)
                                <option value="{{ $board->board_id }}">{{ $board->board_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->user_id }}">{{ $member->user->full_name ?? $member->user->username }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
.kanban-container {
    overflow-x: auto;
    padding-bottom: 20px;
}

.kanban-column {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: calc(100vh - 250px);
    display: flex;
    flex-direction: column;
}

.kanban-header {
    padding: 15px;
    color: white;
}

.kanban-header h5 {
    font-size: 16px;
    font-weight: 600;
}

.kanban-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    min-height: 200px;
}

.kanban-card {
    background: white;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: {{ $canDragDrop ? 'grab' : 'default' }};
    transition: all 0.2s;
}

.kanban-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    @if($canDragDrop)
    transform: translateY(-2px);
    @endif
}

.kanban-card.sortable-ghost {
    opacity: 0.4;
    background: #e9ecef;
}

.kanban-card.sortable-drag {
    cursor: grabbing;
    transform: rotate(2deg);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.priority-badge {
    font-size: 10px;
    padding: 2px 6px;
}

.kanban-card-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #212529;
}

.kanban-card-meta {
    font-size: 12px;
    color: #6c757d;
}

.kanban-card-footer {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

/* Scrollbar styling */
.kanban-body::-webkit-scrollbar {
    width: 6px;
}

.kanban-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.kanban-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.kanban-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Check if user can drag and drop (from PHP)
    const canDragDrop = {{ $canDragDrop ? 'true' : 'false' }};

    // Initialize Sortable for each board column
    const columns = document.querySelectorAll('.kanban-body');
    
    columns.forEach(column => {
        new Sortable(column, {
            group: 'kanban-boards',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            disabled: !canDragDrop, // Disable drag if user doesn't have permission
            onEnd: function(evt) {
                const cardId = evt.item.dataset.cardId;
                const newBoardId = evt.to.dataset.boardId;
                const newPosition = evt.newIndex;
                
                // Update card board via AJAX
                updateCardBoard(cardId, newBoardId, newPosition);
            }
        });
    });

    // Add visual indicator if drag is disabled
    if (!canDragDrop) {
        document.querySelectorAll('.kanban-card').forEach(card => {
            card.style.cursor = 'default';
            card.title = 'Only Project Admin and Team Lead can move cards';
        });
    }
    
    function updateCardBoard(cardId, boardId, position) {
        fetch(`/tasks/${cardId}/update-board`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                board_id: boardId,
                position: position
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to move task');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update badge counts
                updateBadgeCounts();
                
                // Show success toast
                if (window.toast) {
                    toast.success('Success', `Task moved to ${data.board_name}`);
                }
            } else {
                // Revert the move on error
                location.reload();
                if (window.toast) {
                    toast.error('Error', data.message || 'Failed to move task');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert the move and show error
            location.reload();
            alert(error.message || 'Failed to move task. Only Project Admin and Team Lead can move cards.');
            location.reload();
            if (window.toast) {
                toast.error('Error', 'An error occurred while moving task');
            }
        });
    }
    
    function updateBadgeCounts() {
        document.querySelectorAll('.kanban-body').forEach(column => {
            const count = column.querySelectorAll('.kanban-card').length;
            const badge = column.closest('.kanban-column').querySelector('.badge');
            if (badge) {
                badge.textContent = count;
            }
            
            // Show/hide empty state
            const emptyState = column.querySelector('.empty-state');
            if (count === 0 && !emptyState) {
                column.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted small">No tasks in this board</p>
                    </div>
                `;
            } else if (count > 0 && emptyState) {
                emptyState.remove();
            }
        });
    }
});
</script>

@endsection
