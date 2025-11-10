@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>User Details: {{ $user->username }}</h1>
    <div class="btn-group">
        <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit User
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>User ID:</strong></div>
                    <div class="col-sm-8">{{ $user->user_id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Username:</strong></div>
                    <div class="col-sm-8">{{ $user->username }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Full Name:</strong></div>
                    <div class="col-sm-8">{{ $user->full_name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Email:</strong></div>
                    <div class="col-sm-8">{{ $user->email }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Current Status:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge {{ $user->current_task_status == 'working' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($user->current_task_status) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Created At:</strong></div>
                    <div class="col-sm-8">{{ $user->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><strong>Last Updated:</strong></div>
                    <div class="col-sm-8">{{ $user->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Memberships</h5>
            </div>
            <div class="card-body">
                @if($projectMemberships->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Role</th>
                                    <th>Joined At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projectMemberships as $membership)
                                    <tr>
                                        <td>{{ $membership->project_name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $membership->role }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($membership->joined_at)->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-info-circle"></i>
                        This user is not assigned to any projects yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
