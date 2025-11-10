@extends('layouts.app')

@section('title', $project->project_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>{{ $project->project_name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $project->project_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('projects.kanban', $project) }}" class="btn btn-primary">
                        <i class="fas fa-th me-1"></i>Kanban View
                    </a>
                    @if($project->isAdmin(auth()->id()))
                        @if(!$project->is_archived)
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Edit Project
                            </a>
                        @else
                            <form action="{{ route('projects.unarchive', $project->project_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Unarchive this project? Status will be changed to In Progress.')">
                                    <i class="fas fa-undo me-1"></i>Unarchive Project
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                            <i class="fas fa-trash me-1"></i>Delete Project
                        </button>
                    @endif
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Projects
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($project->is_archived)
        <div class="alert alert-warning border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-archive fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">
                        <i class="fas fa-info-circle me-1"></i>This project is archived
                    </h5>
                    <p class="mb-0">
                        <small>
                            Archived on {{ $project->archived_at->format('M d, Y \a\t H:i') }}
                            @if($project->archivedBy)
                                by {{ $project->archivedBy->full_name ?? $project->archivedBy->username }}
                            @endif
                        </small>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Project Overview -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Project Overview</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="overview-item">
                                <label class="overview-label">
                                    <i class="fas fa-align-left me-2 text-muted"></i>Description
                                </label>
                                <p class="overview-text">{{ $project->description ?: 'No description provided.' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="overview-item">
                                        <label class="overview-label">
                                            <i class="fas fa-flag me-2 text-muted"></i>Status
                                        </label>
                                        <p class="overview-text">
                                            @php
                                                $statusConfig = [
                                                    'planning' => ['badge' => 'secondary', 'icon' => 'fa-clipboard-list', 'text' => 'Planning'],
                                                    'in_progress' => ['badge' => 'primary', 'icon' => 'fa-spinner', 'text' => 'In Progress'],
                                                    'done' => ['badge' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Completed'],
                                                    'on_hold' => ['badge' => 'warning', 'icon' => 'fa-pause-circle', 'text' => 'On Hold']
                                                ];
                                                $config = $statusConfig[$project->status] ?? $statusConfig['planning'];
                                            @endphp
                                            <span class="badge bg-{{ $config['badge'] }} px-3 py-2">
                                                <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="overview-item">
                                        <label class="overview-label">
                                            <i class="fas fa-calendar-plus me-2 text-muted"></i>Start Date
                                        </label>
                                        <p class="overview-text">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="overview-item">
                                        <label class="overview-label">
                                            <i class="fas fa-calendar-check me-2 text-muted"></i>Deadline
                                        </label>
                                        <p class="overview-text">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($project->progress !== null)
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="overview-label mb-0">
                                    <i class="fas fa-tasks me-2 text-muted"></i>Overall Progress
                                </label>
                                <span class="badge bg-primary px-3 py-2">{{ number_format($project->progress, 1) }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div class="progress-bar bg-gradient-primary" role="progressbar" 
                                     style="width: {{ $project->progress }}%"
                                     aria-valuenow="{{ $project->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Project Boards -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-columns me-2"></i>Project Boards</h5>
                    @if($project->isAdmin(auth()->id()))
                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#createBoardModal">
                            <i class="fas fa-plus me-1"></i>Add Board
                        </button>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($project->boards->count() > 0)
                        <div class="row g-3">
                            @foreach($project->boards as $board)
                                @php
                                    $statusColors = [
                                        'todo' => ['bg' => '#6c757d', 'icon' => 'fa-list-check', 'text' => 'To Do'],
                                        'in_progress' => ['bg' => '#0d6efd', 'icon' => 'fa-spinner', 'text' => 'In Progress'],
                                        'review' => ['bg' => '#ffc107', 'icon' => 'fa-eye', 'text' => 'Review'],
                                        'done' => ['bg' => '#198754', 'icon' => 'fa-circle-check', 'text' => 'Done']
                                    ];
                                    $boardConfig = $statusColors[$board->status_mapping] ?? ['bg' => '#6c757d', 'icon' => 'fa-columns', 'text' => 'Board'];
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="board-card h-100" style="border-top: 4px solid {{ $boardConfig['bg'] }};">
                                        <div class="board-card-header">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="board-icon" style="background-color: {{ $boardConfig['bg'] }}20; color: {{ $boardConfig['bg'] }};">
                                                    <i class="fas {{ $boardConfig['icon'] }}"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="board-title mb-1">{{ $board->board_name }}</h6>
                                                    @if($board->status_mapping)
                                                        <span class="badge badge-sm" style="background-color: {{ $boardConfig['bg'] }};">
                                                            {{ $boardConfig['text'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="board-card-body">
                                            <p class="board-description text-muted small mb-3">
                                                {{ $board->description ?: 'No description' }}
                                            </p>
                                            
                                            @php
                                                $totalCards = $board->cards->count();
                                                $doneCards = $board->cards->where('status', 'done')->count();
                                            @endphp
                                            
                                            @if($totalCards > 0)
                                                <div class="board-stats mb-3">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-tasks text-primary me-2"></i>
                                                            <span class="fw-semibold">{{ $totalCards }} {{ Str::plural('Task', $totalCards) }}</span>
                                                        </div>
                                                        @if($doneCards > 0)
                                                            <div>
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check me-1"></i>{{ $doneCards }} Done
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="board-stats mb-3">
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-info-circle me-1"></i>No tasks yet
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="board-card-footer">
                                            <a href="{{ route('boards.show', $board) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-arrow-right me-1"></i>View Board
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-columns fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">No Boards Yet</h5>
                            <p class="text-muted mb-4">Create your first board to start organizing tasks</p>
                            @if($project->isAdmin(auth()->id()))
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBoardModal">
                                    <i class="fas fa-plus me-2"></i>Create First Board
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Project Members -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Team Members</h5>
                    @if($project->isAdmin(auth()->id()))
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="fas fa-user-plus me-1"></i>Add
                        </button>
                    @endif
                </div>
                <div class="card-body p-3">
                    @foreach($project->members as $member)
                        <div class="member-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle-modern me-3">
                                    @if($member->user->profile_picture)
                                        <img src="{{ asset('storage/' . $member->user->profile_picture) }}" 
                                             alt="{{ $member->user->full_name ?? $member->user->username }}"
                                             class="rounded"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{ strtoupper(substr($member->user->full_name ?? $member->user->username, 0, 2)) }}
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="member-name mb-0">{{ $member->user->full_name ?? $member->user->username }}</h6>
                                    <small class="member-role">
                                        @php
                                            $roleIcons = [
                                                'Project Admin' => 'fa-crown',
                                                'Team Lead' => 'fa-user-tie',
                                                'Developer' => 'fa-code',
                                                'Designer' => 'fa-palette'
                                            ];
                                            $roleColors = [
                                                'Project Admin' => 'danger',
                                                'Team Lead' => 'warning',
                                                'Developer' => 'info',
                                                'Designer' => 'purple'
                                            ];
                                        @endphp
                                        <i class="fas {{ $roleIcons[$member->role] ?? 'fa-user' }} me-1"></i>
                                        <span class="text-{{ $roleColors[$member->role] ?? 'secondary' }}">{{ $member->role }}</span>
                                    </small>
                                </div>
                                @if($project->isAdmin(auth()->id()) && $member->user_id !== auth()->id())
                                    <form action="{{ route('projects.removeMember', [$project, $member]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" 
                                                onclick="return confirm('Remove this member from the project?')"
                                                title="Remove member">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statistics
                    </h5>
                </div>
                <div class="card-body p-3">
                    @php
                        $totalTasks = $project->boards->sum(fn($board) => $board->cards->count());
                        $completedTasks = $project->boards->sum(fn($board) => $board->cards->where('status', 'done')->count());
                        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progress</small>
                            <small class="fw-bold text-primary">{{ $completionRate }}%</small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-primary" 
                                 style="width: {{ $completionRate }}%">
                                <small>{{ $completedTasks }}/{{ $totalTasks }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-tasks text-primary mb-1"></i>
                                <h4 class="mb-0 fw-bold text-primary">{{ $totalTasks }}</h4>
                                <small class="text-muted d-block">Tasks</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-check-circle text-success mb-1"></i>
                                <h4 class="mb-0 fw-bold text-success">{{ $completedTasks }}</h4>
                                <small class="text-muted d-block">Done</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                <i class="fas fa-users text-info mb-1"></i>
                                <h4 class="mb-0 fw-bold text-info">{{ $project->members->count() }}</h4>
                                <small class="text-muted d-block">Members</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-secondary bg-opacity-10 rounded">
                                <i class="fas fa-columns text-secondary mb-1"></i>
                                <h4 class="mb-0 fw-bold text-secondary">{{ $project->boards->count() }}</h4>
                                <small class="text-muted d-block">Boards</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Board Modal -->
<div class="modal fade" id="createBoardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('/boards') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->project_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Board</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="board_name" class="form-label">Board Name</label>
                        <input type="text" class="form-control" id="board_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="board_description" class="form-label">Description</label>
                        <textarea class="form-control" id="board_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="board_status_mapping" class="form-label">Status Mapping</label>
                        <select name="status_mapping" id="board_status_mapping" class="form-select">
                            <option value="">-- No Status Mapping --</option>
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Tasks moved to this board will automatically get this status
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Board</button>
                </div>
            </form>
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
                                <option value="{{ $user->user_id }}" data-user-role="{{ $user->role }}">
                                    {{ $user->full_name ?? $user->username }} ({{ $user->email }})
                                    @if($user->role === 'admin')
                                        - System Admin
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="role-selection">
                        <label for="member_role" class="form-label">Role</label>
                        <select class="form-select" id="member_role" name="role" required>
                            <option value="Team Lead">Team Lead</option>
                            <option value="Developer">Developer</option>
                            <option value="Designer">Designer</option>
                        </select>
                        <small class="text-muted">Select a role for this member</small>
                    </div>
                    <div class="mb-3 d-none" id="admin-notice">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>System Admin selected:</strong> This user will automatically be assigned as <strong>Project Admin</strong> (highest role).
                        </div>
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

.avatar-circle-modern {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: #0d6efd;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

/* Member Items */
.member-item {
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    background: #f8f9fa;
}

.member-item:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.member-item:last-child {
    margin-bottom: 0;
}

.member-name {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

.member-role {
    font-size: 12px;
    color: #6c757d;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

/* Stat Boxes */
.stat-box {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-box-primary {
    border-color: rgba(13, 110, 253, 0.2);
}

.stat-box-primary:hover {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.02);
}

.stat-box-success {
    border-color: rgba(25, 135, 84, 0.2);
}

.stat-box-success:hover {
    border-color: #198754;
    background: rgba(25, 135, 84, 0.02);
}

.stat-box-info {
    border-color: rgba(13, 202, 240, 0.2);
}

.stat-box-info:hover {
    border-color: #0dcaf0;
    background: rgba(13, 202, 240, 0.02);
}

.stat-box-warning {
    border-color: rgba(255, 193, 7, 0.2);
}

.stat-box-warning:hover {
    border-color: #ffc107;
    background: rgba(255, 193, 7, 0.02);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.stat-box-primary .stat-icon {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.stat-box-success .stat-icon {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.stat-box-info .stat-icon {
    background: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.stat-box-warning .stat-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.stat-content {
    flex-grow: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #212529;
    margin: 0;
    line-height: 1;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Color utilities */
.text-purple {
    color: #6f42c1 !important;
}

/* Overview Section */
.overview-item {
    margin-bottom: 0;
}

.overview-label {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
}

.overview-text {
    font-size: 15px;
    color: #212529;
    margin: 0;
    line-height: 1.6;
}

.bg-gradient-primary {
    background: #0d6efd !important;
}

.progress {
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.progress-bar {
    background: #0d6efd;
    transition: width 0.6s ease;
}

/* Board Cards Modern Design */
.board-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    padding: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.board-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    border-color: #dee2e6;
}

.board-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--board-color, #0d6efd);
    transition: height 0.3s ease;
}

.board-card:hover::before {
    height: 6px;
}

.board-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.board-card:hover .board-icon {
    transform: scale(1.1) rotate(5deg);
}

.board-title {
    font-size: 16px;
    font-weight: 600;
    color: #212529;
    margin: 0;
    line-height: 1.4;
}

.board-description {
    font-size: 13px;
    line-height: 1.6;
    color: #6c757d;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 36px;
}

.board-stats .stat-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    transition: background 0.2s ease;
}

.board-stats .stat-item:hover {
    background: #e9ecef;
}

.board-stats .stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    display: block;
    margin: 4px 0;
}

.board-stats .stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
}

.board-card-footer {
    margin-top: 16px;
}

.board-card-footer .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.board-card-footer .btn:hover {
    transform: translateX(4px);
}

/* Badge Styling */
.badge-sm {
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Empty State */
.empty-state-icon {
    opacity: 0.5;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Card Shadows */
.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: none;
}

.card.shadow-sm:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .board-card {
        padding: 16px;
    }
    
    .board-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .board-title {
        font-size: 14px;
    }
    
    .stat-box {
        padding: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .stat-number {
        font-size: 20px;
    }
}

/* Grid improvements */
.row.g-3 {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

.row.g-4 {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}
</style>

<!-- Delete Project Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProjectModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Project
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete the project <strong>"{{ $project->project_name }}"</strong>?</p>
                <p class="text-muted mb-0">
                    <small>This will permanently delete:
                        <ul class="mt-2">
                            <li>All boards in this project</li>
                            <li>All cards/tasks</li>
                            <li>All subtasks</li>
                            <li>All time logs</li>
                            <li>All comments</li>
                            <li>All team member assignments</li>
                        </ul>
                    </small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle role selector based on selected user
document.getElementById('user_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const userRole = selectedOption.getAttribute('data-user-role');
    const roleSelection = document.getElementById('role-selection');
    const adminNotice = document.getElementById('admin-notice');
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
</script>

@endsection
