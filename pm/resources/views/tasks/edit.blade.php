@extends('layouts.app')

@section('title', 'Edit Task - ' . $task->card_title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Edit Task</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task->card_id) }}">{{ $task->card_title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('tasks.show', $task->card_id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Task
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Task Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('card_title') is-invalid @enderror" 
                                           id="card_title" 
                                           name="card_title" 
                                           value="{{ old('card_title', $task->card_title) }}" 
                                           required>
                                    @error('card_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status">
                                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>To Do</option>
                                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe the task details...">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority">
                                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="board_id" class="form-label">Project Board</label>
                                    <select class="form-select @error('board_id') is-invalid @enderror" 
                                            id="board_id" 
                                            name="board_id" 
                                            required>
                                        @foreach($boards as $board)
                                            <option value="{{ $board->board_id }}" 
                                                    {{ old('board_id', $task->board_id) == $board->board_id ? 'selected' : '' }}>
                                                {{ $board->project->project_name ?? 'Project' }} - {{ $board->board_name ?? 'Board' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('board_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tasks.show', $task->card_id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Assignments -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Current Assignments</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignMemberModal">
                        <i class="fas fa-user-plus me-1"></i>Assign Member
                    </button>
                </div>
                <div class="card-body">
                    @if($task->assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>Assigned Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->assignments as $assignment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($assignment->user->profile_picture)
                                                        <img src="{{ asset('storage/' . $assignment->user->profile_picture) }}" 
                                                             alt="{{ $assignment->user->full_name ?? $assignment->user->username }}"
                                                             class="rounded-circle me-3"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                             style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                                            {{ strtoupper(substr($assignment->user->full_name ?? $assignment->user->username, 0, 2)) }}
                                                        </div>
                                                    @endif
                                                    <strong>{{ $assignment->user->full_name ?? $assignment->user->username }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $assignment->user->email }}</td>
                                            <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <form action="{{ route('tasks.unassign', [$task, $assignment]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Unassign {{ $assignment->user->full_name ?? $assignment->user->username }} from this task?')">
                                                        <i class="fas fa-times"></i> Unassign
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No members assigned to this task.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignMemberModal">
                                <i class="fas fa-user-plus me-1"></i>Assign First Member
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subtasks Management -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Subtasks</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubtaskModal">
                        <i class="fas fa-plus me-1"></i>Add Subtask
                    </button>
                </div>
                <div class="card-body">
                    @if($task->subtasks->count() > 0)
                        @php
                            // Hitung subtask yang benar-benar selesai (approved atau tidak perlu approval)
                            $completedSubtasks = $task->subtasks->filter(function($subtask) {
                                return $subtask->status == 'done' && ($subtask->is_approved || !$subtask->needs_approval);
                            })->count();
                            $totalSubtasks = $task->subtasks->count();
                            $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                        @endphp
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Progress: {{ $completedSubtasks }}/{{ $totalSubtasks }} completed</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Subtask</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->subtasks as $subtask)
                                        <tr class="{{ $subtask->status == 'done' ? 'table-success' : '' }}">
                                            <td>
                                                <form action="{{ route('subtasks.toggle', $subtask) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $subtask->status == 'done' ? 'success' : 'secondary' }}">
                                                        <i class="fas fa-{{ $subtask->status == 'done' ? 'check' : 'square' }}"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="{{ $subtask->status == 'done' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $subtask->subtask_title }}
                                            </td>
                                            <td>{{ $subtask->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <form action="{{ route('subtasks.destroy', $subtask) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Delete this subtask?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list-ul fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No subtasks added yet.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubtaskModal">
                                <i class="fas fa-plus me-1"></i>Add First Subtask
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Member Modal -->
<div class="modal fade" id="assignMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.assign', $task) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select Member</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose a member...</option>
                            @foreach($availableMembers as $member)
                                <option value="{{ $member->user_id }}">{{ $member->user->full_name ?? $member->user->username }} ({{ $member->role }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subtask Modal -->
<div class="modal fade" id="addSubtaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('subtasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="card_id" value="{{ $task->card_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Add Subtask</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subtask_title" class="form-label">Subtask Title</label>
                        <input type="text" class="form-control" id="subtask_title" name="title" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subtask</button>
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
}
</style>
@endsection

