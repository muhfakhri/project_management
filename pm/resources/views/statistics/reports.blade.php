@extends('layouts.app')

@section('title', 'Detailed Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-file-alt me-2"></i>Detailed Reports</h1>
                    <p class="text-muted">In-depth analysis of your productivity metrics</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('statistics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="fas fa-print me-1"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Tracking Report -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Time Tracking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-calendar-day fa-2x text-info mb-2"></i>
                                <h3 class="text-info">{{ number_format($timeData['daily'], 1) }}h</h3>
                                <p class="text-muted mb-0">Today</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-calendar-week fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary">{{ number_format($timeData['weekly'], 1) }}h</h3>
                                <p class="text-muted mb-0">This Week</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-calendar-alt fa-2x text-success mb-2"></i>
                                <h3 class="text-success">{{ number_format($timeData['monthly'], 1) }}h</h3>
                                <p class="text-muted mb-0">This Month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Time Breakdown -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Time by Project</h5>
                </div>
                <div class="card-body">
                    @if($projectBreakdown->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th class="text-end">Hours</th>
                                        <th class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalHours = $projectBreakdown->sum('total_hours');
                                    @endphp
                                    @foreach($projectBreakdown as $project)
                                        <tr>
                                            <td>
                                                <i class="fas fa-folder me-2 text-primary"></i>
                                                {{ $project->project_name }}
                                            </td>
                                            <td class="text-end">
                                                <strong>{{ number_format($project->total_hours, 1) }}h</strong>
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $percentage = $totalHours > 0 ? ($project->total_hours / $totalHours) * 100 : 0;
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="progress flex-grow-1 me-2" style="height: 10px; max-width: 100px;">
                                                        <div class="progress-bar bg-info" 
                                                             role="progressbar" 
                                                             style="width: {{ $percentage }}%"
                                                             aria-valuenow="{{ $percentage }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted">{{ number_format($percentage, 0) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <th>Total</th>
                                        <th class="text-end">{{ number_format($totalHours, 1) }}h</th>
                                        <th class="text-end">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No time logged yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Task Completion Trends -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Task Completion Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if($completionTrends->count() > 0)
                        <canvas id="completionChart" height="300"></canvas>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No completed tasks in the last 30 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Report Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted">Time Period</h6>
                            <p>{{ now()->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Total Projects</h6>
                            <p>{{ $projectBreakdown->count() }} projects tracked</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Tasks Completed</h6>
                            <p>{{ $completionTrends->sum('completed') }} tasks in last 30 days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($completionTrends->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('completionChart');
    
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
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
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
});
</script>
@endif

<style>
@media print {
    .btn-group, .sidebar, .navbar {
        display: none !important;
    }
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
@endsection
