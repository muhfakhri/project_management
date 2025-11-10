@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-5x text-danger"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-danger mb-3">403</h1>
                    <h3 class="mb-4">Access Denied</h3>
                    <p class="text-muted mb-4">
                        {{ $exception->getMessage() ?: 'This action is unauthorized.' }}
                    </p>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Need access?</strong> Contact your Project Admin for assistance.
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-1"></i>Go to Dashboard
                        </a>
                        <button onclick="window.history.back()" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Go Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show toast notification when page loads
    document.addEventListener('DOMContentLoaded', function() {
        if (window.toast) {
            const message = '{{ $exception->getMessage() ?: "This action is unauthorized." }}';
            toast.permission('Access Denied', message, 0); // No auto dismiss
        }
    });
</script>
@endsection
