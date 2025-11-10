<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';
    
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mark this notification as read
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Scope to get only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get only read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Get icon based on notification type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'task_assigned' => 'fa-tasks',
            'task_pending_approval' => 'fa-hourglass-half',
            'task_approved' => 'fa-check-circle',
            'task_rejected' => 'fa-times-circle',
            'task_completed' => 'fa-check-circle',
            'subtask_approved' => 'fa-check-circle',
            'comment_added' => 'fa-comment',
            'deadline_approaching' => 'fa-exclamation-triangle',
            'project_invitation' => 'fa-envelope',
            'mention' => 'fa-at',
            'time_logged' => 'fa-clock',
            'board_created' => 'fa-clipboard',
            'status_changed' => 'fa-exchange-alt',
            'subtask_rejected' => 'fa-times-circle',
            'subtask_pending_approval' => 'fa-hourglass-half',
            default => 'fa-bell'
        };
    }

    /**
     * Get color based on notification type
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'task_assigned' => 'primary',
            'task_pending_approval' => 'warning',
            'task_approved' => 'success',
            'task_rejected' => 'danger',
            'task_completed' => 'success',
            'subtask_approved' => 'success',
            'comment_added' => 'info',
            'deadline_approaching' => 'warning',
            'project_invitation' => 'purple',
            'mention' => 'danger',
            'time_logged' => 'secondary',
            'board_created' => 'dark',
            'status_changed' => 'info',
            'subtask_rejected' => 'danger',
            'subtask_pending_approval' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get relative time (e.g., "2 hours ago")
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Create a new notification
     */
    public static function create_notification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl
        ]);
    }
}
