@extends('layouts.app')

@section('title', 'Edit Project - ' . $project->project_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Edit Project</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->project_name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Project
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
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Project Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('project_name') is-invalid @enderror" 
                                           id="project_name" 
                                           name="project_name" 
                                           value="{{ old('project_name', $project->project_name) }}" 
                                           required>
                                    @error('project_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status">
                                        <option value="planning" {{ old('status', $project->status) === 'planning' ? 'selected' : '' }}>Planning</option>
                                        <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="done" {{ old('status', $project->status) === 'done' ? 'selected' : '' }}>Completed</option>
                                        <option value="on_hold" {{ old('status', $project->status) === 'on_hold' ? 'selected' : '' }}>On Hold</option>
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
                                      placeholder="Describe your project...">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="deadline" class="form-label">Deadline</label>
                                    <input type="date" 
                                           class="form-control @error('deadline') is-invalid @enderror" 
                                           id="deadline" 
                                           name="deadline" 
                                           value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}">
                                    @error('deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="progress" class="form-label">Progress (%)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('progress') is-invalid @enderror" 
                                       id="progress" 
                                       name="progress" 
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       value="{{ old('progress', $project->progress) }}"
                                       placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('progress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Project Members Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Project Members</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-user-plus me-1"></i>Add Member
                    </button>
                </div>
                <div class="card-body">
                    @if($project->members->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->members as $member)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3">
                                                        {{ strtoupper(substr($member->user->full_name ?? $member->user->username, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $member->user->full_name ?? $member->user->username }}</strong>
                                                        @if($member->user_id === $project->created_by)
                                                            <span class="badge bg-warning ms-2">Creator</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $member->user->email }}</td>
                                            <td>
                                                @if($member->user_id === $project->created_by)
                                                    <span class="badge bg-primary">Project Admin</span>
                                                @else
                                                    <form action="{{ route('projects.updateMemberRole', [$project, $member]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="Team Lead" {{ $member->role === 'Team Lead' ? 'selected' : '' }}>Team Lead</option>
                                                            <option value="Developer" {{ $member->role === 'Developer' ? 'selected' : '' }}>Developer</option>
                                                            <option value="Designer" {{ $member->role === 'Designer' ? 'selected' : '' }}>Designer</option>
                                                        </select>
                                                    </form>
                                                @endif
                                            </td>
                                            <td>{{ $member->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                                            <td>
                                                @if($member->user_id !== $project->created_by && $member->user_id !== auth()->id())
                                                    <form action="{{ route('projects.removeMember', [$project, $member]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Remove {{ $member->user->full_name ?? $member->user->username }} from this project?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No team members yet.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                <i class="fas fa-user-plus me-1"></i>Add First Member
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('projects.addMember', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose a user...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->user_id }}">{{ $user->full_name ?? $user->username }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="member_role" class="form-label">Role</label>
                        <select class="form-select" id="member_role" name="role" required>
                            <option value="Team Lead">Team Lead</option>
                            <option value="Developer">Developer</option>
                            <option value="Designer">Designer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
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
    background-color: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection

