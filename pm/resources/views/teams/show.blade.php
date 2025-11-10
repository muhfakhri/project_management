@extends('layouts.app')

@section('title', 'Team - ' . $project->project_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1>{{ $project->project_name }} Team</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('teams.index') }}">Teams</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $project->project_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group" role="group">
                    @if($project->canManageTeam(auth()->id()))
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="fas fa-user-plus me-1"></i>Add Member
                        </button>
                    @endif
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Project
                    </a>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Team Members -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Members ({{ $project->members->count() }})</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i>Filter by Role
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('teams.show', $project) }}">All Roles</a></li>
                            <li><a class="dropdown-item" href="{{ route('teams.show', [$project, 'role' => 'Team Lead']) }}">Team Leads</a></li>
                            <li><a class="dropdown-item" href="{{ route('teams.show', [$project, 'role' => 'Developer']) }}">Developers</a></li>
                            <li><a class="dropdown-item" href="{{ route('teams.show', [$project, 'role' => 'Designer']) }}">Designers</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Joined Date</th>
                                    <th>Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->members as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if($member->user->profile_picture)
                                                        <img src="{{ asset('storage/' . $member->user->profile_picture) }}" 
                                                             alt="Profile Picture" 
                                                             class="rounded-circle img-fluid"
                                                             style="width: 40px; height: 40px; object-fit: cover; max-width: 100%;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 40px; height: 40px; font-size: 1rem; font-weight: bold;">
                                                            {{ strtoupper(substr($member->user->username, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>{{ $member->user->full_name ?? $member->user->username }}</strong>
                                                    @if($member->user_id === $project->created_by)
                                                        <span class="badge bg-warning ms-2">Creator</span>
                                                    @endif
                                                    @if($member->user_id === auth()->id())
                                                        <span class="badge bg-info ms-2">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($project->canManageTeam(auth()->id()) && $member->user_id !== $project->created_by)
                                                <form action="{{ route('teams.updateRole', [$project, $member]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="Team Lead" {{ $member->role === 'Team Lead' ? 'selected' : '' }}>Team Lead</option>
                                                        <option value="Developer" {{ $member->role === 'Developer' ? 'selected' : '' }}>Developer</option>
                                                        <option value="Designer" {{ $member->role === 'Designer' ? 'selected' : '' }}>Designer</option>
                                                    </select>
                                                </form>
                                            @else
                                                <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $member->role)) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->user->email }}</td>
                                        <td>{{ $member->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $tasksAssigned = $member->user->assignedTasks()
                                                    ->whereHas('board', function($q) use ($project) {
                                                        $q->where('project_id', $project->project_id);
                                                    })->count();
                                                $tasksCompleted = $member->user->assignedTasks()
                                                    ->whereHas('board', function($q) use ($project) {
                                                        $q->where('project_id', $project->project_id);
                                                    })
                                                    ->where('status', 'done')->count();
                                            @endphp
                                            <div class="small">
                                                <div><strong>{{ $tasksAssigned }}</strong> tasks assigned</div>
                                                <div><strong>{{ $tasksCompleted }}</strong> completed</div>
                                                @if($tasksAssigned > 0)
                                                    <div class="text-success">{{ round(($tasksCompleted / $tasksAssigned) * 100, 1) }}% completion rate</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('teams.memberProjects', $member->user_id) }}" class="btn btn-sm btn-outline-info" title="View Member's Projects">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($project->canManageTeam(auth()->id()) && $member->user_id !== $project->created_by && $member->user_id !== auth()->id())
                                                    <form action="{{ route('teams.removeMember', [$project, $member]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Remove {{ $member->user->full_name ?? $member->user->username }} from this project?')"
                                                                title="Remove Member">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($member->user_id === auth()->id() && $member->user_id !== $project->created_by)
                                                    <form action="{{ route('teams.leave', $project) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                onclick="return confirm('Are you sure you want to leave this project?')"
                                                                title="Leave Project">
                                                            <i class="fas fa-sign-out-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Team Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Team Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3 class="text-primary">{{ $teamStats['total_members'] }}</h3>
                            <small class="text-muted">Total Members</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-warning">{{ $teamStats['team_leads'] }}</h3>
                            <small class="text-muted">Team Leads</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">{{ $teamStats['developers'] }}</h3>
                            <small class="text-muted">Developers</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-info">{{ $teamStats['designers'] }}</h3>
                            <small class="text-muted">Designers</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Distribution -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Role Distribution</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalMembers = $teamStats['total_members'];
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Team Leads</span>
                            <span class="small">{{ $teamStats['team_leads'] }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalMembers > 0 ? ($teamStats['team_leads'] / $totalMembers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Developers</span>
                            <span class="small">{{ $teamStats['developers'] }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $totalMembers > 0 ? ($teamStats['developers'] / $totalMembers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Designers</span>
                            <span class="small">{{ $teamStats['designers'] }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $totalMembers > 0 ? ($teamStats['designers'] / $totalMembers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($project->isAdmin(auth()->id()))
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                <i class="fas fa-user-plus me-2"></i>Add New Member
                            </button>
                        @endif
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-project-diagram me-2"></i>View Project Details
                        </a>
                        <a href="{{ route('statistics.project', $project) }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-line me-2"></i>Project Statistics
                        </a>
                        @if($project->members->contains('user_id', auth()->id()) && auth()->id() !== $project->created_by)
                            <form action="{{ route('teams.leave', $project) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to leave this project?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Leave Project
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
@if($project->isAdmin(auth()->id()))
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teams.addMember', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($availableUsers->count() > 0)
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Choose a user...</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->user_id }}" data-user-role="{{ $user->role }}">
                                        {{ $user->full_name ?? $user->username }} 
                                        @if($user->role === 'admin') - System Admin @endif
                                        ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Role selection (hidden when admin is selected) -->
                        <div class="mb-3" id="role-selection-team">
                            <label for="member_role" class="form-label">Role</label>
                            <select class="form-select" id="member_role" name="role" required>
                                <option value="Team Lead">Team Lead</option>
                                <option value="Developer" selected>Developer</option>
                                <option value="Designer">Designer</option>
                            </select>
                        </div>
                        
                        <!-- Admin notice (shown when admin is selected) -->
                        <div class="mb-3 d-none" id="admin-notice-team">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-crown me-1"></i>
                                <strong>System Admin selected:</strong> This user will automatically be assigned as <strong>Project Admin</strong>.
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">All users are already members of this project.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($availableUsers->count() > 0)
                        <button type="submit" class="btn btn-primary">Add Member</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle role selector based on selected user
    const userSelect = document.getElementById('user_id');
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const userRole = selectedOption.getAttribute('data-user-role');
            const roleSelection = document.getElementById('role-selection-team');
            const adminNotice = document.getElementById('admin-notice-team');
            const memberRoleSelect = document.getElementById('member_role');
            
            if (userRole === 'admin') {
                // Hide role selector, show admin notice
                roleSelection.classList.add('d-none');
                adminNotice.classList.remove('d-none');
                memberRoleSelect.removeAttribute('required');
            } else {
                // Show role selector, hide admin notice
                roleSelection.classList.remove('d-none');
                adminNotice.classList.add('d-none');
                memberRoleSelect.setAttribute('required', 'required');
            }
        });
    }
});
</script>
@endsection
