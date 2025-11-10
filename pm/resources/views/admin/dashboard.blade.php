@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        @if($isSystemAdmin)
                            System Admin Dashboard
                        @else
                            Project Admin Dashboard
                        @endif
                    </h2>
                    <p class="text-muted mb-0">
                        @if($isSystemAdmin)
                            Comprehensive System-Wide Statistics
                        @else
                            Your Projects Management Statistics
                        @endif
                    </p>
                </div>
                <a href="{{ route('statistics.index') }}" class="btn btn-primary">
                    <i class="fas fa-chart-bar me-2"></i>Detailed Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Project Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Projects</p>
                            <h3 class="mb-0 fw-bold">{{ $totalProjects }}</h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>All time
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Active Projects</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $activeProjects }}</h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-primary">
                            <i class="fas fa-circle me-1"></i>In Progress
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Completed</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $completedProjects }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i>Done
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">On Hold</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $onHoldProjects }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>Needs Attention
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task & User Statistics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Task Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">Total Tasks</p>
                                <h4 class="mb-0 fw-bold">{{ $totalTasks }}</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">To Do</p>
                                <h4 class="mb-0 fw-bold text-secondary">{{ $todoTasks }}</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <p class="mb-1 small text-muted">In Progress</p>
                                <h4 class="mb-0 fw-bold text-primary">{{ $inProgressTasks }}</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <p class="mb-1 small text-muted">In Review</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ $reviewTasks }}</h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-1 small text-muted">Completed Tasks</p>
                                        <h4 class="mb-0 fw-bold text-success">{{ $completedTasks }}</h4>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-double fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>User & Team Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4 p-4 rounded bg-primary text-center">
                        <div class="text-white">
                            <p class="mb-2">Total Users</p>
                            <h1 class="mb-0 fw-bold">{{ $totalUsers }}</h1>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">Team Members</p>
                                <h4 class="mb-0 fw-bold">{{ $totalTeamMembers }}</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">Project Admins</p>
                                <h4 class="mb-0 fw-bold text-primary">{{ $projectAdmins }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">Team Leads</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ $teamLeads }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1 small text-muted">Members</p>
                                <h4 class="mb-0 fw-bold text-info">{{ $members }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Status & Deadline Alerts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Projects by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="projectStatusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Deadline Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger mb-3" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                            <div>
                                <h4 class="alert-heading mb-0">{{ $overdueProjects }}</h4>
                                <p class="mb-0 small">Overdue Projects</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-3" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fa-2x me-3"></i>
                            <div>
                                <h4 class="alert-heading mb-0">{{ $dueSoonProjects }}</h4>
                                <p class="mb-0 small">Due Soon (Within 7 Days)</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clipboard-list fa-2x text-secondary me-3"></i>
                            <div>
                                <h4 class="mb-0">{{ $planningProjects }}</h4>
                                <p class="mb-0 small text-muted">In Planning Phase</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent Projects</h5>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Deadline</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project->project_id) }}" class="text-decoration-none fw-semibold">
                                            {{ $project->project_name }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'planning' => ['badge' => 'secondary', 'icon' => 'fa-clipboard-list', 'text' => 'Planning'],
                                                'in_progress' => ['badge' => 'primary', 'icon' => 'fa-spinner', 'text' => 'In Progress'],
                                                'done' => ['badge' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Completed'],
                                                'on_hold' => ['badge' => 'warning', 'icon' => 'fa-pause-circle', 'text' => 'On Hold']
                                            ];
                                            $config = $statusConfig[$project->status] ?? $statusConfig['planning'];
                                        @endphp
                                        <span class="badge bg-{{ $config['badge'] }}">
                                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                        </span>
                                    </td>
                                    <td>{{ $project->creator->full_name ?? $project->creator->username }}</td>
                                    <td>
                                        @if($project->deadline)
                                            {{ $project->deadline->format('M d, Y') }}
                                            @if($project->status != 'done' && $project->deadline->isPast())
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @elseif($project->status != 'done' && $project->deadline->isFuture() && $project->deadline->diffInDays(now()) <= 7)
                                                <span class="badge bg-warning text-dark ms-1">Due Soon</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No deadline</span>
                                        @endif
                                    </td>
                                    <td>{{ $project->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No projects yet</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Projects by Progress -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Active Projects by Progress</h5>
                </div>
                <div class="card-body">
                    @forelse($topProjects as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <a href="{{ route('projects.show', $item['project']->project_id) }}" class="text-decoration-none fw-semibold">
                                    {{ $item['project']->project_name }}
                                </a>
                                <small class="text-muted d-block">
                                    by {{ $item['project']->creator->full_name ?? $item['project']->creator->username }}
                                </small>
                            </div>
                            <span class="badge bg-primary">{{ number_format($item['progress'], 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" 
                                 style="width: {{ $item['progress'] }}%;" 
                                 role="progressbar" 
                                 aria-valuenow="{{ $item['progress'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <p class="mb-0">No active projects to display</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    cursor: pointer;
}

.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.5s ease-out;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Status Chart
    const ctx = document.getElementById('projectStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Planning', 'In Progress', 'Completed', 'On Hold'],
            datasets: [{
                data: [
                    {{ $projectsByStatus['planning'] }},
                    {{ $projectsByStatus['in_progress'] }},
                    {{ $projectsByStatus['done'] }},
                    {{ $projectsByStatus['on_hold'] }}
                ],
                backgroundColor: [
                    '#6c757d',  // Planning - Secondary
                    '#0d6efd',  // In Progress - Primary
                    '#198754',  // Completed - Success
                    '#ffc107'   // On Hold - Warning
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' projects';
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
