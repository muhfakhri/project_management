@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">
                        <i class="fas fa-chart-bar text-primary me-2"></i>Reports & Analytics
                    </h1>
                    <p class="text-muted mb-0">Export comprehensive reports in CSV format</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px; background: #3b82f6;">
                                <i class="fas fa-project-diagram text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Total Projects</div>
                            <h4 class="mb-0">{{ $totalProjects }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px; background: #ec4899;">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Total Tasks</div>
                            <h4 class="mb-0">{{ $totalTasks }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px; background: #06b6d4;">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Total Users</div>
                            <h4 class="mb-0">{{ $totalUsers }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px; background: #10b981;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Time Logged</div>
                            <h4 class="mb-0">{{ number_format($totalTimeLogged, 1) }}h</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row g-4">
        <!-- Projects Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(102, 126, 234, 0.1);">
                            <i class="fas fa-project-diagram text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Projects Report</h5>
                            <p class="text-muted small mb-0">Export all projects with detailed information</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ url('/reports/export/projects') }}" method="GET" class="mb-0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="on_hold">On Hold</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Start Date From</label>
                                <input type="date" name="start_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-download me-2"></i>Export Projects CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tasks Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(245, 87, 108, 0.1);">
                            <i class="fas fa-tasks text-danger"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Tasks Report</h5>
                            <p class="text-muted small mb-0">Export tasks with assignments and time tracking</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ url('/reports/export/tasks') }}" method="GET" class="mb-0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small">Project</label>
                                <select name="project_id" class="form-select form-select-sm">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                    <option value="on_hold">On Hold</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Priority</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Date Range</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" placeholder="From">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-download me-2"></i>Export Tasks CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(79, 172, 254, 0.1);">
                            <i class="fas fa-users text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Users Report</h5>
                            <p class="text-muted small mb-0">Export all users with activity statistics</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted small mb-2">This report includes:</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>User information and roles</li>
                            <li><i class="fas fa-check text-success me-2"></i>Project memberships</li>
                            <li><i class="fas fa-check text-success me-2"></i>Task assignments and completion</li>
                            <li><i class="fas fa-check text-success me-2"></i>Time logging statistics</li>
                        </ul>
                    </div>
                    <a href="{{ url('/reports/export/users') }}" class="btn btn-info btn-sm w-100">
                        <i class="fas fa-download me-2"></i>Export Users CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Time Logs Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(67, 233, 123, 0.1);">
                            <i class="fas fa-clock text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Time Logs Report</h5>
                            <p class="text-muted small mb-0">Export detailed time tracking logs</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ url('/reports/export/time-logs') }}" method="GET" class="mb-0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small">Project</label>
                                <select name="project_id" class="form-select form-select-sm">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Date From</label>
                                <input type="date" name="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Date To</label>
                                <input type="date" name="end_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-download me-2"></i>Export Time Logs CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Project Performance Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(255, 159, 67, 0.1);">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Project Performance</h5>
                            <p class="text-muted small mb-0">Detailed analytics for each project</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ url('/reports/export/project-performance') }}" method="GET" class="mb-3">
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label small">Select Project (Optional)</label>
                                <select name="project_id" class="form-select form-select-sm">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-download me-2"></i>Export Performance CSV
                        </button>
                    </form>
                    <p class="text-muted small mb-0">
                        Includes completion rates, time tracking, and project metrics
                    </p>
                </div>
            </div>
        </div>

        <!-- User Performance Report -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; background: rgba(138, 43, 226, 0.1);">
                            <i class="fas fa-user-chart text-purple"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">User Performance</h5>
                            <p class="text-muted small mb-0">Comprehensive user activity analytics</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted small mb-2">This report includes:</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>Task completion rates</li>
                            <li><i class="fas fa-check text-success me-2"></i>Time tracking statistics</li>
                            <li><i class="fas fa-check text-success me-2"></i>Subtask completion</li>
                            <li><i class="fas fa-check text-success me-2"></i>Approval ratings (for Team Leads)</li>
                        </ul>
                    </div>
                    <a href="{{ url('/reports/export/user-performance') }}" class="btn btn-sm w-100" style="background-color: #8a2be2; color: white;">
                        <i class="fas fa-download me-2"></i>Export User Performance CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Guide -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Report Export Guide</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-csv text-success fs-4 me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">CSV Format</h6>
                                    <p class="text-muted small mb-0">All reports are exported in CSV format, compatible with Excel, Google Sheets, and other spreadsheet applications.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-filter text-info fs-4 me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Filters</h6>
                                    <p class="text-muted small mb-0">Use filters to narrow down your reports by project, status, date range, and other criteria.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-warning fs-4 me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Real-time Data</h6>
                                    <p class="text-muted small mb-0">All reports reflect the current state of your data at the time of export.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-purple {
    color: #8a2be2 !important;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>
@endsection
