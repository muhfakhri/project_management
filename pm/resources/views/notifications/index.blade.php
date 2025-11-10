@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-bell me-2"></i>Notifications</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteAllRead()">
                        <i class="fas fa-trash me-1"></i>Delete All Read
                    </button>
                </div>
            </div>

            @if($unreadCount > 0)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You have <strong>{{ $unreadCount }}</strong> unread notification(s)
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    @forelse($notifications as $notification)
                        <div class="notification-item {{ $notification->isUnread() ? 'unread' : '' }}" 
                             id="notification-{{ $notification->notification_id }}">
                            <div class="d-flex align-items-start p-3">
                                <!-- Icon -->
                                <div class="notification-icon-large bg-{{ $notification->color }} text-white me-3">
                                    <i class="fas {{ $notification->icon }}"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $notification->title }}
                                                @if($notification->isUnread())
                                                    <span class="badge bg-primary ms-2">New</span>
                                                @endif
                                            </h6>
                                            <p class="text-muted mb-2">{{ $notification->message }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> {{ $notification->time_ago }}
                                            </small>
                                        </div>

                                        <!-- Actions -->
                                        <div class="btn-group">
                                            @if($notification->action_url)
                                                <a href="{{ route('notifications.show', $notification) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                            
                                            @if($notification->isUnread())
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="markAsRead({{ $notification->notification_id }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteNotification({{ $notification->notification_id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No notifications yet</h5>
                            <p class="text-muted">When you receive notifications, they will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.notification-item {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd;
}

.notification-icon-large {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.notification-item:last-child {
    border-bottom: none;
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(t => { throw new Error(t || response.statusText); });
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            const item = document.getElementById(`notification-${notificationId}`);
            if (item) {
                item.classList.remove('unread');
                item.querySelector('.badge')?.remove();
            }
            if (window.toast) toast.success('Success', 'Notification marked as read');
        } else {
            if (window.toast) toast.error('Error', data?.message || 'Failed to mark notification as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.toast) toast.error('Error', 'Failed to mark notification as read');
    });
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) return;
    
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) return response.text().then(t => { throw new Error(t || response.statusText); });
        return response.json();
    })
    .then(data => {
        if (data && data.success) location.reload();
        else if (window.toast) toast.error('Error', data?.message || 'Failed to mark all as read');
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.toast) toast.error('Error', 'Failed to mark all as read');
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Delete this notification?')) return;
    
    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) return response.text().then(t => { throw new Error(t || response.statusText); });
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            const el = document.getElementById(`notification-${notificationId}`);
            if (el) el.remove();
            if (window.toast) toast.success('Success', 'Notification deleted');
            if (document.querySelectorAll('.notification-item').length === 0) location.reload();
        } else {
            if (window.toast) toast.error('Error', data?.message || 'Failed to delete notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.toast) toast.error('Error', 'Failed to delete notification');
    });
}

function deleteAllRead() {
    if (!confirm('Delete all read notifications?')) return;
    
    fetch('/notifications/delete-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) return response.text().then(t => { throw new Error(t || response.statusText); });
        return response.json();
    })
    .then(data => {
        if (data && data.success) location.reload();
        else if (window.toast) toast.error('Error', data?.message || 'Failed to delete notifications');
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.toast) toast.error('Error', 'Failed to delete notifications');
    });
}
</script>

@endsection
