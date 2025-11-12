<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $primaryKey = 'project_id';
    
    protected $fillable = [
        'project_name',
        'description',
        'status',
        'created_by',
        'start_date',
        'deadline',
        'is_archived',
        'archived_at',
        'archived_by',
        'is_overdue',
        'deadline_notified_at',
        'completion_percentage',
        'is_template',
        'completion_status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_overdue' => 'boolean',
        'deadline_notified_at' => 'datetime',
        'completion_percentage' => 'decimal:2',
        'is_template' => 'boolean',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        // When deleting a project, delete all related data
        static::deleting(function ($project) {
            // Delete all boards (which will cascade to cards, subtasks, etc.)
            foreach ($project->boards as $board) {
                $board->delete();
            }
            
            // Delete all project members
            $project->members()->delete();
        });
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id', 'project_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'project_id', 'project_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by', 'user_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    // Helper methods
    public function isMember($userId): bool
    {
        return $this->created_by == $userId || 
               $this->members()->where('user_id', $userId)->exists();
    }

    public function isAdmin($userId): bool
    {
        // Project creator is always an admin
        if ($this->created_by == $userId) {
            return true;
        }
        
        return $this->members()
            ->where('user_id', $userId)
            ->where('role', 'Project Admin')
            ->exists();
    }

    public function isTeamLead($userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->where('role', 'Team Lead')
            ->exists();
    }

    public function canManageTeam($userId): bool
    {
        return $this->isAdmin($userId) || $this->isTeamLead($userId);
    }

    public function getMemberRole($userId): ?string
    {
        $member = $this->members()
            ->where('user_id', $userId)
            ->first();
        
        return $member ? $member->role : null;
    }

    public function isArchived(): bool
    {
        return $this->is_archived === true || $this->is_archived === 1;
    }

    public function canBeModified(): bool
    {
        return !$this->isArchived();
    }

    public function getProgressAttribute(): float
    {
        $totalCards = $this->boards()->withCount('cards')->get()->sum('cards_count');
        
        // Simply count completed cards (no approval needed at task level)
        $completedCards = Card::whereIn('board_id', $this->boards()->pluck('board_id'))
            ->where('status', 'done')
            ->count();

        return $totalCards > 0 ? ($completedCards / $totalCards) * 100 : 0;
    }

    // ==================== DEADLINE METHODS ====================
    
    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !$this->is_archived;
    }

    public function daysRemaining(): ?int
    {
        if (!$this->deadline) {
            return null;
        }
        
        return now()->diffInDays($this->deadline, false);
    }

    public function getDeadlineStatus(): array
    {
        if (!$this->deadline || $this->is_archived) {
            return [
                'status' => 'none',
                'color' => 'secondary',
                'text' => 'No deadline',
                'badge' => 'NO DEADLINE'
            ];
        }

        $days = $this->daysRemaining();

        if ($days < 0) {
            return [
                'status' => 'overdue',
                'color' => 'danger',
                'text' => 'Overdue by ' . abs($days) . ' day(s)',
                'badge' => 'OVERDUE'
            ];
        } elseif ($days <= 7) {
            return [
                'status' => 'soon',
                'color' => 'warning',
                'text' => 'Due in ' . $days . ' days',
                'badge' => $days . ' DAYS LEFT'
            ];
        } else {
            return [
                'status' => 'upcoming',
                'color' => 'info',
                'text' => 'Due in ' . $days . ' days',
                'badge' => $days . ' DAYS'
            ];
        }
    }

    // Check if all tasks are completed
    public function allTasksCompleted(): bool
    {
        $totalTasks = Card::whereHas('board', function($q) {
            $q->where('project_id', $this->project_id);
        })->count();

        if ($totalTasks === 0) {
            return false; // No tasks means project not ready
        }

        $completedTasks = Card::whereHas('board', function($q) {
            $q->where('project_id', $this->project_id);
        })->where('status', 'done')
          ->where('is_approved', true)
          ->count();

        return $totalTasks === $completedTasks;
    }

    // Check if can request completion
    public function canRequestCompletion(): bool
    {
        // Can request if status is 'working' or 'rejected' (to allow re-request after rejection)
        return in_array($this->completion_status, ['working', 'rejected'])
            && $this->allTasksCompleted() 
            && !$this->is_archived;
    }

    // Check if pending approval
    public function isPendingApproval(): bool
    {
        return $this->completion_status === 'pending_approval';
    }

    // Check if completed
    public function isCompleted(): bool
    {
        return $this->completion_status === 'completed';
    }
}
