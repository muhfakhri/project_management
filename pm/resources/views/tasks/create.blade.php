@extends('layouts.app')

@section('title', 'Create New Task')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Create New Task</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Tasks
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($projects->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>No Active Projects Available</strong>
                    <p class="mb-0">You need to be a Project Admin or Team Lead in at least one active project to create tasks.</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-project-diagram me-1"></i>View Projects
                    </a>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Task Information</h5>
                </div>
                <div class="card-body">
                    @if($projects->isEmpty())
                        <p class="text-muted text-center py-4">No projects available to create tasks.</p>
                    @else
                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('card_title') is-invalid @enderror" 
                                           id="card_title" 
                                           name="card_title" 
                                           value="{{ old('card_title') }}" 
                                           placeholder="Enter task title..."
                                           required>
                                    @error('card_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority">
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
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
                                      placeholder="Describe the task details...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Select Project <span class="text-danger">*</span></label>
                                    <select class="form-select @error('project_id') is-invalid @enderror" 
                                            id="project_id" 
                                            name="project_id" 
                                            required>
                                        <option value="">Choose a project...</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->project_id }}" {{ old('project_id') == $project->project_id ? 'selected' : '' }}>
                                                {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Task will be created in "To Do" board automatically</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date') }}"
                                           min="{{ date('Y-m-d') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Assignee Section (single assignee only) -->
                        <div class="mb-3">
                            <label class="form-label">Assign to Team Member</label>
                            <div class="border rounded p-3">
                                <div id="assignee-selection">
                                    <p class="text-muted mb-2">Select a project first to see available team members</p>
                                </div>
                                <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>Only one assignee is allowed per task. Users cannot have multiple active tasks.</small>
                            </div>
                        </div>

                        <!-- Subtasks Section -->
                        <div class="mb-4">
                            <label class="form-label">Subtasks</label>
                            <div class="border rounded p-3">
                                <div id="subtasks-container">
                                    <div class="subtask-item mb-2">
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="subtasks[]" 
                                                   placeholder="Enter subtask title...">
                                            <button type="button" class="btn btn-outline-danger remove-subtask" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-subtask" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i>Add Subtask
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Task
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const assigneeSelection = document.getElementById('assignee-selection');
    const subtasksContainer = document.getElementById('subtasks-container');
    const addSubtaskBtn = document.getElementById('add-subtask');

    // Load team members when project is selected
    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        if (projectId) {
            fetch(`/api/projects/${projectId}/members`)
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="row">';
                    data.forEach(member => {
                        const hasActiveTask = member.has_active_task;
                        const activeTaskTitle = member.active_task_title;
                        const isProjectAdmin = member.is_project_admin || false;
                        const canMultitask = member.can_multitask || false;
                        
                        // Only disable if has active task AND not a Project Admin
                        const isDisabled = (hasActiveTask && !canMultitask) ? 'disabled' : '';
                        const opacityClass = (hasActiveTask && !canMultitask) ? 'opacity-50' : '';
                        
                        const profilePicture = member.user.profile_picture 
                            ? `<img src="/storage/${member.user.profile_picture}" 
                                    alt="${member.user.full_name || member.user.username}"
                                    class="rounded-circle me-2"
                                    style="width: 40px; height: 40px; object-fit: cover;">`
                            : `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                    style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                    ${(member.user.full_name || member.user.username).substring(0, 2).toUpperCase()}
                               </div>`;
                        
                        // Status message for busy users
                        let statusBadge = '';
                        if (hasActiveTask) {
                            if (canMultitask) {
                                statusBadge = `<br><small class="text-info"><i class="fas fa-info-circle me-1"></i>Working on: ${activeTaskTitle} <span class="badge bg-info">Can Multitask</span></small>`;
                            } else {
                                statusBadge = `<br><small class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Busy: ${activeTaskTitle}</small>`;
                            }
                        }
                        
                        html += `
                            <div class="col-md-6 mb-2">
                                <div class="form-check ${opacityClass}">
                                    <input class="form-check-input" 
                                        type="radio" 
                                        name="assignee_id" 
                                        value="${member.user_id}" 
                                        id="assignee_${member.user_id}"
                                        ${isDisabled}>
                                    <label class="form-check-label" for="assignee_${member.user_id}">
                                        <div class="d-flex align-items-center">
                                            ${profilePicture}
                                            <div class="flex-grow-1">
                                                <strong>${member.user.full_name || member.user.username}</strong>
                                                <br><small class="text-muted">${member.role.replace('_', ' ')}</small>
                                                ${statusBadge}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    assigneeSelection.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching members:', error);
                    assigneeSelection.innerHTML = '<p class="text-danger">Error loading team members</p>';
                });
        } else {
            assigneeSelection.innerHTML = '<p class="text-muted mb-2">Select a project first to see available team members</p>';
        }
    });

    // Add subtask functionality
    addSubtaskBtn.addEventListener('click', function() {
        const subtaskHtml = `
            <div class="subtask-item mb-2">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           name="subtasks[]" 
                           placeholder="Enter subtask title...">
                    <button type="button" class="btn btn-outline-danger remove-subtask">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        subtasksContainer.insertAdjacentHTML('beforeend', subtaskHtml);
        updateRemoveButtons();
    });

    // Remove subtask functionality
    subtasksContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-subtask') || e.target.closest('.remove-subtask')) {
            const subtaskItem = e.target.closest('.subtask-item');
            subtaskItem.remove();
            updateRemoveButtons();
        }
    });

    // Update remove button states
    function updateRemoveButtons() {
        const subtaskItems = subtasksContainer.querySelectorAll('.subtask-item');
        const removeButtons = subtasksContainer.querySelectorAll('.remove-subtask');
        
        removeButtons.forEach((btn, index) => {
            btn.disabled = subtaskItems.length <= 1;
        });
    }

    // Initialize remove button states
    updateRemoveButtons();
});
</script>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: white;
    display: flex;
    align-items-center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}
</style>
@endsection
