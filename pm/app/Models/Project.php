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
        'archived_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
}
