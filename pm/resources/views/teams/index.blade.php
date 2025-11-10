@extends('layouts.app')

@section('title', 'Team Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-users me-2"></i>Team Management</h1>
                    <p class="text-muted">Manage your project teams and member roles</p>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Projects Overview -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Your Projects</h5>
        </div>
        <div class="card-body">
            @if($projects->count() > 0)
                <div class="row">
                    @foreach($projects as $project)
                        <div class="col-lg-6 mb-4">
                            <div class="card border-start border-primary border-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $project->project_name }}</h6>
                                    @switch($project->status)
                                        @case('planning')
                                            <span class="badge bg-secondary">Planning</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('on_hold')
                                            <span class="badge bg-warning">On Hold</span>
                                            @break
                                    @endswitch
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">{{ Str::limit($project->description, 100) }}</p>
                                    
                                    <!-- Team Members Preview -->
                                    <div class="mb-3">
                                        <h6 class="small mb-2">Team Members ({{ $project->members->count() }})</h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($project->members->take(5) as $member)
                                                @if($member->user->profile_picture)
                                                    <img src="{{ asset('storage/' . $member->user->profile_picture) }}" 
                                                         alt="{{ $member->user->full_name ?? $member->user->username }}" 
                                                         class="rounded-circle img-fluid flex-shrink-0"
                                                         style="width: 32px; height: 32px; object-fit: cover; max-width: 100%;"
                                                         title="{{ $member->user->full_name ?? $member->user->username }} ({{ ucwords(str_replace('_', ' ', $member->role)) }})">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: bold;"
                                                         title="{{ $member->user->full_name ?? $member->user->username }} ({{ ucwords(str_replace('_', ' ', $member->role)) }})">
                                                        {{ strtoupper(substr($member->user->username, 0, 1)) }}
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if($project->members->count() > 5)
                                                <span class="badge bg-light text-dark">+{{ $project->members->count() - 5 }} more</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team Roles Summary -->
                                    <div class="mb-3">
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <small class="text-muted d-block">Project Admin</small>
                                                <strong>{{ $project->members->where('role', 'Project Admin')->count() ?: ($project->created_by === auth()->id() ? 1 : 0) }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block">Team Leads</small>
                                                <strong>{{ $project->members->where('role', 'Team Lead')->count() }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block">Developers</small>
                                                <strong>{{ $project->members->where('role', 'Developer')->count() }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block">Designers</small>
                                                <strong>{{ $project->members->where('role', 'Designer')->count() }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User's Role in Project -->
                                    @php
                                        $userMember = $project->members->where('user_id', auth()->id())->first();
                                        $userRole = $userMember ? $userMember->role : ($project->created_by === auth()->id() ? 'creator' : 'not_member');
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Your role:</small>
                                            <span class="badge bg-info">
                                                {{ $userRole === 'creator' ? 'Project Creator' : ucwords(str_replace('_', ' ', $userRole)) }}
                                            </span>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teams.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-users me-1"></i>Manage Team
                                            </a>
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye me-1"></i>View Project
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($projects->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $projects->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Projects Found</h5>
                    <p class="text-muted">You are not a member of any projects yet.</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Browse Projects
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Team Management Tips removed per request -->
</div>

<style>
.avatar-circle-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
    margin-right: 4px;
}
</style>
@endsection
