@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        @if($isSystemAdmin)
                            System Statistics & Analytics
                        @else
                            Project Statistics & Analytics
                        @endif
                    </h2>
                    <p class="text-muted">
                        @if($isSystemAdmin)
                            Comprehensive performance metrics across all projects
                        @else
                            Detailed analytics for your managed projects
                        @endif
                    </p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Time Tracking Overview -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-clock fa-3x text-primary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ number_format($totalTimeLogged / 60, 1) }}</h3>
                    <p class="text-muted mb-0">Total Hours Logged</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-tasks fa-3x text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $taskStatusDistribution['done'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Tasks Completed</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-list-check fa-3x text-warning"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $subtaskStats['pending'] }}</h3>
                    <p class="text-muted mb-0">Pending Approvals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Status Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Task Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Task Completion Trend (30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="completionTrendChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2 text-primary"></i>Project Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Status</th>
                                    <th>Total Tasks</th>
                                    <th>Completed</th>
                                    <th>Progress</th>
                                    <th>Created By</th>
                                    <th>Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectPerformance as $item)
                                <tr class="{{ $item['overdue'] ? 'table-danger' : '' }}">
                                    <td>
                                        <a href="{{ route('projects.show', $item['project']->project_id) }}" class="text-decoration-none fw-semibold">
                                            {{ $item['project']->project_name }}
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
                                            $config = $statusConfig[$item['project']->status] ?? $statusConfig['planning'];
                                        @endphp
                                        <span class="badge bg-{{ $config['badge'] }}">
                                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                        </span>
                                    </td>
                                    <td>{{ $item['total_tasks'] }}</td>
                                    <td>{{ $item['completed_tasks'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px; width: 100px;">
                                                <div class="progress-bar bg-{{ $item['progress'] >= 100 ? 'success' : ($item['progress'] >= 50 ? 'primary' : 'warning') }}" 
                                                     style="width: {{ $item['progress'] }}%" 
                                                     role="progressbar">
                                                </div>
                                            </div>
                                            <span class="small">{{ $item['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td>{{ $item['project']->creator->full_name ?? $item['project']->creator->username }}</td>
                                    <td>
                                        @if($item['project']->deadline)
                                            {{ $item['project']->deadline->format('M d, Y') }}
                                            @if($item['overdue'])
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No deadline</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No projects to analyze</p>
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

    <!-- Team Performance -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Performers by Completion</h5>
                </div>
                <div class="card-body">
                    @forelse($activeUsers as $item)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($item['user']->profile_picture)
                                    <img src="{{ asset('storage/' . $item['user']->profile_picture) }}" 
                                         alt="{{ $item['user']->full_name ?? $item['user']->username }}"
                                         class="rounded-circle"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($item['user']->full_name ?? $item['user']->username, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $item['user']->full_name ?? $item['user']->username }}</div>
                                <small class="text-muted">{{ $item['user']->completed_tasks }} / {{ $item['user']->total_tasks }} tasks</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-success fs-6">{{ $item['completion_rate'] }}%</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p class="mb-0">No active users to display</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2 text-info"></i>Top Time Contributors</h5>
                </div>
                <div class="card-body">
                    @forelse($timeByUser as $item)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($item->profile_picture)
                                    <img src="{{ asset('storage/' . $item->profile_picture) }}" 
                                         alt="{{ $item->full_name ?? $item->username }}"
                                         class="rounded-circle"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($item->full_name ?? $item->username, 0, 1)) }}
                                    </div>
                                @endif
                                </div>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $item->full_name ?? $item->username }}</div>
                                <small class="text-muted">Time logged</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-info fs-6">{{ number_format($item->total_hours, 1) }}h</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <p class="mb-0">No time logs to display</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Subtask Analytics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Subtask Approval Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="p-4 bg-light rounded text-center">
                                <h2 class="mb-2 fw-bold text-primary">{{ $subtaskStats['total'] }}</h2>
                                <p class="mb-0 text-muted">Total Subtasks</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-4 bg-warning bg-opacity-10 rounded text-center">
                                <h2 class="mb-2 fw-bold text-warning">{{ $subtaskStats['pending'] }}</h2>
                                <p class="mb-0 text-muted">Pending Approval</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-4 bg-success bg-opacity-10 rounded text-center">
                                <h2 class="mb-2 fw-bold text-success">{{ $subtaskStats['approved'] }}</h2>
                                <p class="mb-0 text-muted">Approved</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($subtaskStats['total'] > 0)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Approval Rate</span>
                            <span class="fw-bold">{{ number_format(($subtaskStats['approved'] / $subtaskStats['total']) * 100, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ ($subtaskStats['approved'] / $subtaskStats['total']) * 100 }}%" 
                                 role="progressbar">
                            </div>
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ ($subtaskStats['pending'] / $subtaskStats['total']) * 100 }}%" 
                                 role="progressbar">
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-center gap-3">
                            <small><span class="badge bg-success"></span> Approved</small>
                            <small><span class="badge bg-warning"></span> Pending</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.4s ease-out;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Task Status Chart
    const statusCtx = document.getElementById('taskStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['To Do', 'In Progress', 'Review', 'Done'],
            datasets: [{
                data: [
                    {{ $taskStatusDistribution['todo'] ?? 0 }},
                    {{ $taskStatusDistribution['in_progress'] ?? 0 }},
                    {{ $taskStatusDistribution['review'] ?? 0 }},
                    {{ $taskStatusDistribution['done'] ?? 0 }}
                ],
                backgroundColor: ['#6c757d', '#0d6efd', '#ffc107', '#198754'],
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
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // Completion Trend Chart
    const trendCtx = document.getElementById('completionTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($taskCompletionTrend as $trend)
                    '{{ \Carbon\Carbon::parse($trend->date)->format("M d") }}',
                @endforeach
            ],
            datasets: [{
                label: 'Tasks Completed',
                data: [
                    @foreach($taskCompletionTrend as $trend)
                        {{ $trend->count }},
                    @endforeach
                ],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endsection
