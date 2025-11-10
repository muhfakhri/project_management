@extends('layouts.app')

@section('title', 'Time Tracking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-clock me-2"></i>Time Tracking</h1>
                    <p class="text-muted">Track your work hours and manage time logs</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('time.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Log Time
                    </a>
                    <a href="{{ route('time.reports') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-1"></i>Reports
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

    <!-- Time Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary">{{ number_format($stats['total_hours'], 1) }}h</h3>
                    <small class="text-muted">Total Hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                    <h3 class="text-info">{{ number_format($stats['this_week'], 1) }}h</h3>
                    <small class="text-muted">This Week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                    <h3 class="text-warning">{{ number_format($stats['this_month'], 1) }}h</h3>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-list fa-2x text-success mb-2"></i>
                    <h3 class="text-success">{{ $stats['entries_count'] }}</h3>
                    <small class="text-muted">Total Entries</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Logs -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Time Logs</h5>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('time.index') }}">All Logs</a></li>
                    <li><a class="dropdown-item" href="{{ route('time.index', ['filter' => 'today']) }}">Today</a></li>
                    <li><a class="dropdown-item" href="{{ route('time.index', ['filter' => 'week']) }}">This Week</a></li>
                    <li><a class="dropdown-item" href="{{ route('time.index', ['filter' => 'month']) }}">This Month</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($timeLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Description</th>
                                <th>Time Spent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeLogs as $log)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $log->date->format('M d, Y') }}</strong>
                                            <br><small class="text-muted">{{ $log->date->format('l') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('tasks.show', $log->card) }}" class="text-decoration-none">
                                            {{ $log->card->card_title }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('projects.show', $log->card->board->project) }}" class="text-decoration-none">
                                            {{ $log->card->board->project->project_name }}
                                        </a>
                                        <br><small class="text-muted">{{ $log->card->board->board_name }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($log->description, 50) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ number_format($log->hours_spent, 1) }}h</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('time.show', $log) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="View Time Log">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('time.edit', $log) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Edit Time Log">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('time.destroy', $log) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Delete Time Log"
                                                        onclick="return confirm('Delete this time log?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($timeLogs->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $timeLogs->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Time Logs Found</h5>
                    <p class="text-muted">You haven't logged any time yet.</p>
                    <a href="{{ route('time.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Log Your First Time Entry
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
