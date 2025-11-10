@extends('layouts.app')

@section('title', 'Statistics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-chart-pie me-2"></i>Statistics Dashboard</h1>
                    <p class="text-muted">Your personal productivity and project insights</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('statistics.reports') }}" class="btn btn-outline-info">
                        <i class="fas fa-file-alt me-1"></i>Detailed Reports
                    </a>
                    @if(auth()->user()->role === 'Project Admin')
                        <a href="{{ route('statistics.team') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-1"></i>Team Overview
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-project-diagram fa-2x text-primary mb-3"></i>
                    <h3 class="text-primary">{{ $userStats['projects_count'] }}</h3>
                    <p class="text-muted mb-0">Active Projects</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x text-warning mb-3"></i>
                    <h3 class="text-warning">{{ $userStats['tasks_assigned'] }}</h3>
                    <p class="text-muted mb-0">Tasks Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <h3 class="text-success">{{ $userStats['tasks_completed'] }}</h3>
                    <p class="text-muted mb-0">Tasks Completed</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-info mb-3"></i>
                    <h3 class="text-info">{{ number_format($userStats['total_time_logged'], 1) }}h</h3>
                    <p class="text-muted mb-0">Time Logged</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Performance Metrics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="performance-circle">
                                    <div class="circle-progress" data-percentage="{{ $performanceMetrics['task_completion_rate'] }}">
                                        <span>{{ number_format($performanceMetrics['task_completion_rate'], 1) }}%</span>
                                    </div>
                                </div>
                                <h6 class="mt-3">Task Completion Rate</h6>
                                <p class="text-muted small">{{ $userStats['tasks_completed'] }} of {{ $userStats['tasks_assigned'] }} tasks completed</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="performance-circle">
                                    <div class="circle-progress" data-percentage="{{ min(100, ($performanceMetrics['avg_time_per_task'] / 10) * 100) }}">
                                        <span>{{ number_format($performanceMetrics['avg_time_per_task'], 1) }}h</span>
                                    </div>
                                </div>
                                <h6 class="mt-3">Avg Time per Task</h6>
                                <p class="text-muted small">Average hours spent on completed tasks</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="performance-circle">
                                    <div class="circle-progress" data-percentage="{{ $performanceMetrics['productivity_score'] }}">
                                        <span>{{ number_format($performanceMetrics['productivity_score'], 1) }}%</span>
                                    </div>
                                </div>
                                <h6 class="mt-3">Productivity Score</h6>
                                <p class="text-muted small">Based on completion rate, time tracking, and collaboration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivity as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        @switch($activity['type'])
                                            @case('task')
                                                <i class="fas fa-tasks text-primary"></i>
                                                @break
                                            @case('comment')
                                                <i class="fas fa-comment text-info"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-secondary"></i>
                                        @endswitch
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                        <p class="text-muted small mb-1">{{ $activity['subtitle'] }}</p>
                                        @if(isset($activity['content']))
                                            <p class="small mb-2">{{ $activity['content'] }}</p>
                                        @endif
                                        <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                        @if(isset($activity['status']))
                                            <span class="badge bg-{{ $activity['status'] === 'done' ? 'success' : ($activity['status'] === 'in_progress' ? 'primary' : 'secondary') }} ms-2">
                                                {{ ucwords(str_replace('_', ' ', $activity['status'])) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No recent activity to display.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-comments text-primary me-2"></i>
                            <span>Comments Made</span>
                        </div>
                        <strong>{{ $userStats['comments_made'] }}</strong>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-calendar-week text-info me-2"></i>
                            <span>Projects This Month</span>
                        </div>
                        <strong>{{ $userStats['projects_count'] }}</strong>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-trophy text-warning me-2"></i>
                            <span>Completion Rate</span>
                        </div>
                        <strong>{{ number_format($performanceMetrics['task_completion_rate'], 1) }}%</strong>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-fire text-danger me-2"></i>
                            <span>Productivity Level</span>
                        </div>
                        <span class="badge bg-{{ $performanceMetrics['productivity_score'] >= 80 ? 'success' : ($performanceMetrics['productivity_score'] >= 60 ? 'warning' : 'danger') }}">
                            @if($performanceMetrics['productivity_score'] >= 80)
                                Excellent
                            @elseif($performanceMetrics['productivity_score'] >= 60)
                                Good
                            @else
                                Needs Improvement
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Goals & Achievements -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-medal me-2"></i>Achievements</h6>
                </div>
                <div class="card-body">
                    <div class="achievement-badges">
                        @if($userStats['tasks_completed'] >= 10)
                            <div class="badge-item mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                <span class="small">Task Master (10+ completed)</span>
                            </div>
                        @endif
                        @if($userStats['total_time_logged'] >= 40)
                            <div class="badge-item mb-2">
                                <i class="fas fa-clock text-info me-2"></i>
                                <span class="small">Time Tracker (40+ hours)</span>
                            </div>
                        @endif
                        @if($userStats['comments_made'] >= 20)
                            <div class="badge-item mb-2">
                                <i class="fas fa-comments text-primary me-2"></i>
                                <span class="small">Collaborator (20+ comments)</span>
                            </div>
                        @endif
                        @if($userStats['projects_count'] >= 3)
                            <div class="badge-item mb-2">
                                <i class="fas fa-project-diagram text-success me-2"></i>
                                <span class="small">Multi-Project Pro (3+ projects)</span>
                            </div>
                        @endif
                        @if($performanceMetrics['task_completion_rate'] >= 90)
                            <div class="badge-item mb-2">
                                <i class="fas fa-trophy text-warning me-2"></i>
                                <span class="small">Perfectionist (90%+ completion)</span>
                            </div>
                        @endif
                        
                        @if($userStats['tasks_completed'] < 10 && $userStats['total_time_logged'] < 40 && $userStats['comments_made'] < 20)
                            <div class="text-center text-muted">
                                <i class="fas fa-seedling fa-2x mb-2"></i>
                                <p class="small">Keep working to unlock achievements!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tasks.my') }}" class="btn btn-outline-primary">
                            <i class="fas fa-tasks me-2"></i>View My Tasks
                        </a>
                        <a href="{{ route('time-logs.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-clock me-2"></i>Time Logs
                        </a>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-project-diagram me-2"></i>Browse Projects
                        </a>
                        <a href="{{ route('statistics.reports') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.performance-circle {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto;
}

.circle-progress {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: conic-gradient(#007bff calc(var(--percentage) * 1%), #e9ecef 0);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.circle-progress::before {
    content: '';
    position: absolute;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: white;
}

.circle-progress span {
    position: relative;
    z-index: 1;
    font-weight: bold;
    color: #007bff;
}

.timeline-item {
    display: flex;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.timeline-marker {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content {
    flex-grow: 1;
}

.activity-timeline {
    max-height: 400px;
    overflow-y: auto;
}

.badge-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress circles
    document.querySelectorAll('.circle-progress').forEach(function(circle) {
        const percentage = circle.getAttribute('data-percentage');
        circle.style.setProperty('--percentage', percentage);
    });
});
</script>
@endsection

