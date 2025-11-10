@extends('layouts.app')

@section('title', 'Assign Team Members')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-plus me-2"></i>Assign Team Members
                </h1>
                <a href="{{ route('team.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Teams
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Assign Member to Project
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('team.store-assignment') }}">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Select Project</label>
                                    <select class="form-select @error('project_id') is-invalid @enderror" 
                                            id="project_id" name="project_id" required>
                                        <option value="">Choose a project...</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->project_id }}" 
                                                    {{ old('project_id') == $project->project_id ? 'selected' : '' }}>
                                                {{ $project->project_name }}
                                                <small>({{ $project->members->count() }} members)</small>
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required>
                                        <option value="">Choose a user...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->user_id }}" 
                                                    data-user-role="{{ $user->role }}"
                                                    {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                                {{ $user->full_name }} ({{ $user->email }})
                                                @if($user->role === 'admin') - System Admin @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="role-selection-assign">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        <option value="">Choose a role...</option>
                                        <option value="Developer" {{ old('role') == 'Developer' ? 'selected' : '' }}>
                                            Developer
                                        </option>
                                        <option value="Team Lead" {{ old('role') == 'Team Lead' ? 'selected' : '' }}>
                                            Team Lead
                                        </option>
                                        <option value="Designer" {{ old('role') == 'Designer' ? 'selected' : '' }}>
                                            Designer
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 d-none" id="admin-notice-assign">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-crown me-2"></i>
                                        <strong>System Admin selected:</strong> This user will automatically be assigned as <strong>Project Admin</strong>.
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('team.index') }}" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-1"></i>Assign Member
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Assignment Info
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6>Role Permissions:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Member:</strong>
                                    <ul class="mt-1">
                                        <li>View project details</li>
                                        <li>Create and manage tasks</li>
                                        <li>Log time entries</li>
                                        <li>Add comments</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Team Lead:</strong>
                                    <ul class="mt-1">
                                        <li>All member permissions</li>
                                        <li>Assign tasks to members</li>
                                        <li>Manage team activities</li>
                                        <li>View team reports</li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($projects->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-project-diagram me-2"></i>Your Projects
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($projects as $project)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $project->project_name }}</strong>
                                        <br><small class="text-muted">{{ $project->members->count() }} members</small>
                                    </div>
                                    <a href="{{ route('teams.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const userSelect = document.getElementById('user_id');
    const roleSelection = document.getElementById('role-selection-assign');
    const adminNotice = document.getElementById('admin-notice-assign');
    const roleSelect = document.getElementById('role');
    
    // Toggle role selector based on selected user
    userSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const userRole = selectedOption.getAttribute('data-user-role');
        
        if (userRole === 'admin') {
            // Hide role selector, show admin notice
            roleSelection.classList.add('d-none');
            adminNotice.classList.remove('d-none');
            roleSelect.removeAttribute('required');
        } else {
            // Show role selector, hide admin notice
            roleSelection.classList.remove('d-none');
            adminNotice.classList.add('d-none');
            roleSelect.setAttribute('required', 'required');
        }
    });
    
    // Filter users based on project selection to avoid duplicates
    projectSelect.addEventListener('change', function() {
        if (this.value) {
            // You can add AJAX call here to filter users who are not already members
            console.log('Project selected:', this.value);
        }
    });
});
</script>
@endsection
