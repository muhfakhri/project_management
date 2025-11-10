@extends('layouts.app')

@section('title', 'Time Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-chart-bar me-2"></i>Time Reports</h1>
                    <p class="text-muted">Analyze your time tracking patterns and productivity</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('time.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Time Logs
                    </a>
                    <a href="{{ route('time.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Log Time
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary">{{ number_format($timeData['daily'], 1) }}h</h3>
                    <small class="text-muted">Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                    <h3 class="text-info">{{ number_format($timeData['weekly'], 1) }}h</h3>
                    <small class="text-muted">This Week</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                    <h3 class="text-warning">{{ number_format($timeData['monthly'], 1) }}h</h3>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Completion Trends -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Task Completion Trends (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if($completionTrends->count() > 0)
                        <canvas id="completionChart" height="100"></canvas>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No task completion data available for the last 30 days.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weekly Time Distribution -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Weekly Time Distribution (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @php
                        $maxHours = max($orderedWeeklyHours);
                    @endphp
                    @if($maxHours > 0)
                        <div class="row">
                            @foreach($orderedWeekDays as $index => $day)
                                @php
                                    $hours = $orderedWeeklyHours[$index];
                                    $heightPercentage = $maxHours > 0 ? ($hours / $maxHours) * 100 : 0;
                                @endphp
                                <div class="col">
                                    <div class="text-center">
                                        <div class="vertical-bar-container mb-2" style="height: 120px; position: relative;">
                                            <div class="vertical-bar" 
                                                 style="position: absolute; bottom: 0; width: 100%; height: {{ $heightPercentage }}%; background: linear-gradient(to top, #0d6efd, #6ea8fe); border-radius: 4px 4px 0 0;"
                                                 data-bs-toggle="tooltip"
                                                 title="{{ $day }}: {{ number_format($hours, 1) }}h">
                                            </div>
                                        </div>
                                        <small class="text-muted d-block">{{ substr($day, 0, 3) }}</small>
                                        <strong class="d-block mt-1" style="font-size: 0.85rem;">{{ number_format($hours, 1) }}h</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No time logged in the last 30 days.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Project Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Time by Project</h5>
                </div>
                <div class="card-body">
                    @if($projectBreakdown->count() > 0)
                        @foreach($projectBreakdown->take(5) as $project)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">{{ Str::limit($project->project_name, 20) }}</span>
                                    <span class="badge bg-primary">{{ number_format($project->total_hours, 1) }}h</span>
                                </div>
                                @php
                                    $maxHours = $projectBreakdown->max('total_hours');
                                    $percentage = $maxHours > 0 ? ($project->total_hours / $maxHours) * 100 : 0;
                                @endphp
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($projectBreakdown->count() > 5)
                            <div class="text-center">
                                <small class="text-muted">+{{ $projectBreakdown->count() - 5 }} more projects</small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-project-diagram fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No project data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productivity Score -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Productivity Metrics</h5>
                </div>
                <div class="card-body">
                    @php
                        $avgHoursPerDay = $timeData['weekly'] / 7;
                        $productivityScore = min(100, ($avgHoursPerDay / 8) * 100);
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Daily Average</span>
                            <span class="small">{{ number_format($avgHoursPerDay, 1) }}h</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ min(100, ($avgHoursPerDay / 8) * 100) }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Productivity Score</span>
                            <span class="small">{{ number_format($productivityScore, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar 
                                {{ $productivityScore >= 80 ? 'bg-success' : ($productivityScore >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                style="width: {{ $productivityScore }}%">
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        @if($productivityScore >= 80)
                            <span class="badge bg-success">Excellent Performance</span>
                        @elseif($productivityScore >= 60)
                            <span class="badge bg-warning">Good Performance</span>
                        @else
                            <span class="badge bg-danger">Needs Improvement</span>
                        @endif
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
                        <a href="{{ route('time.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Log New Time Entry
                        </a>
                        <a href="{{ route('time.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>View All Time Logs
                        </a>
                        <button class="btn btn-outline-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Report
                        </button>
                        <button class="btn btn-outline-success" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    // Task Completion Trends Chart
    @if($completionTrends->count() > 0)
    const ctx = document.getElementById('completionChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                @foreach($completionTrends as $trend)
                    '{{ \Carbon\Carbon::parse($trend->date)->format('M d') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Tasks Completed',
                data: [
                    @foreach($completionTrends as $trend)
                        {{ $trend->completed }},
                    @endforeach
                ],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif
});

function exportData() {
    // This would implement data export functionality
    alert('Export functionality would be implemented here');
}
</script>

<style>
.vertical-bar-container {
    background: linear-gradient(to top, #f8f9fa 0%, #f8f9fa 100%);
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.vertical-bar {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.vertical-bar:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

.vertical-progress {
    width: 20px;
    margin: 0 auto;
    background-color: #e9ecef;
    border-radius: 4px;
    display: flex;
    align-items: flex-end;
}

.vertical-progress .progress-bar {
    width: 100%;
    background-color: #007bff;
    border-radius: 4px;
    transition: height 0.3s ease;
}

@media print {
    .btn, .card-header .fa-bolt, .card-header .fa-chart-line {
        display: none !important;
    }
}
</style>
@endsection
