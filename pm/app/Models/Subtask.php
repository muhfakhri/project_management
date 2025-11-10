<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtask extends Model
{
    protected $primaryKey = 'subtask_id';
    
    protected $fillable = [
        'card_id',
        'subtask_title',
        'description',
        'status',
        'estimated_hours',
        'actual_hours',
        'position',
        'started_at',
        'completed_at',
        'duration_minutes',
        'needs_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'needs_approval' => 'boolean',
        'is_approved' => 'boolean'
    ];

    // Relationships
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'subtask_id', 'subtask_id')->where('comment_type', 'subtask');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'subtask_id', 'subtask_id');
    }

    // Helper methods
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function complete($user)
    {
        $data = [
            'status' => 'done',
            'completed_at' => now(),
            'rejection_reason' => null // Clear rejection reason when re-completing
        ];
        
        // Calculate duration if started
        if ($this->started_at) {
            $data['duration_minutes'] = now()->diffInMinutes($this->started_at);
        }
        
        // Auto-approve if subtask doesn't need approval
        if (!$this->needs_approval) {
            $data['is_approved'] = true;
            $data['approved_by'] = $user->user_id;
            $data['approved_at'] = now();
        }
        
        $this->update($data);
    }

    public function approve($user)
    {
        $this->update([
            'is_approved' => true,
            'approved_by' => $user->user_id,
            'approved_at' => now(),
            'needs_approval' => true, // Keep true for consistency
            'rejection_reason' => null // Clear any previous rejection reason
        ]);
    }

    public function reject($user, $reason = null)
    {
        $this->update([
            'status' => 'in_progress',
            'is_approved' => false,
            'needs_approval' => true, // Keep true so it still requires approval when re-completed
            'rejection_reason' => $reason,
            'completed_at' => null
        ]);
    }

    public function canApprove($user): bool
    {
        if (!$user) {
            return false;
        }
        
        // Check if user is global Project Admin
        if ($user->role === 'Project Admin') {
            return true;
        }
        
        // Get the project ID from this subtask
        // Use eager loaded relationships if available, otherwise query
        if ($this->relationLoaded('card') && $this->card->relationLoaded('board') && $this->card->board->relationLoaded('project')) {
            $projectId = $this->card->board->project->project_id;
        } else {
            $projectId = $this->card()->with('board.project')->first()->board->project->project_id;
        }
        
        // Check if user is Team Lead or Project Admin in this project
        $projectMember = \App\Models\ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->whereIn('role', ['Project Admin', 'Team Lead'])
            ->first();
        
        return $projectMember !== null;
    }

    public function getApprovalStatusLabel(): string
    {
        if ($this->status !== 'done') {
            return '';
        }
        
        if ($this->needs_approval && !$this->is_approved) {
            return 'Pending Approval';
        }
        
        if ($this->is_approved) {
            return 'Approved';
        }
        
        return 'Completed';
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'done']);
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function getTotalTimeSpent(): float
    {
        return $this->timeLogs()->sum('duration_minutes') / 60; // Convert to hours
    }

    // Accessors for backward compatibility with views
    public function getTitleAttribute()
    {
        return $this->subtask_title;
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'done';
    }
}
