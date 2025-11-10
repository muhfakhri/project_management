<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    protected $primaryKey = 'member_id';
    public $timestamps = false;
    
    protected $fillable = [
        'project_id',
        'user_id',
        'role'
    ];

    protected $casts = [
        'joined_at' => 'datetime'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'Project Admin';
    }

    public function isTeamLead(): bool
    {
        return $this->role === 'Team Lead';
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'Developer';
    }

    public function isDesigner(): bool
    {
        return $this->role === 'Designer';
    }

    public function canManageTeam(): bool
    {
        return $this->isAdmin() || $this->isTeamLead();
    }

    public function canAssignTasks(): bool
    {
        return $this->isAdmin() || $this->isTeamLead();
    }

    public function canDeleteProject(): bool
    {
        return $this->isAdmin();
    }

    // Accessor for backward compatibility
    public function getCreatedAtAttribute()
    {
        return $this->joined_at;
    }
}
