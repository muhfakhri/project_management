<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'phone',
        'google_id',
        'bio',
        'profile_picture',
        'current_task_status',
        'role',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'user_id';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationships
     */
    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class, 'user_id', 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members', 'user_id', 'project_id')
            ->withPivot('role', 'joined_at');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Card::class, 'card_assignments', 'user_id', 'card_id', 'user_id', 'card_id');
    }

    /**
     * Helper methods
     */
    public function isProjectAdmin(): bool
    {
        // Check both user.role (system admin) and project_members.role (project admin)
        return $this->role === 'admin' || $this->projectMembers()
            ->where('role', 'Project Admin')
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeamLead(): bool
    {
        return $this->role === 'team_lead' || $this->projectMembers()
            ->where('role', 'Team Lead')
            ->exists();
    }

    public function canCreateProject(): bool
    {
        // Users can create projects only if they are Admin or Project Admin
        return $this->isAdmin() || $this->isProjectAdmin();
    }

    /**
     * Check if user can do time tracking in a specific project
     * Only Developer and Designer can track time (do actual work)
     * Team Lead and Project Admin are supervisors only
     */
    public function canTrackTimeInProject($projectId): bool
    {
        // System admin can't track time (supervisor role)
        if ($this->role === 'admin') {
            return false;
        }

        // Check user's role in this specific project
        $projectMember = $this->projectMembers()
            ->where('project_id', $projectId)
            ->first();

        if (!$projectMember) {
            return false;
        }

        // Only Developer and Designer can track time
        return in_array($projectMember->role, ['Developer', 'Designer']);
    }

    /**
     * Check if user is supervisor (Team Lead or Project Admin) in any project
     */
    public function isSupervisor(): bool
    {
        return $this->isAdmin() || 
               $this->isTeamLead() || 
               $this->isProjectAdmin();
    }
}
