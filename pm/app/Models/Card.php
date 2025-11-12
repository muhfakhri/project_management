<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $primaryKey = 'card_id';
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'card_id';
    }
    
    protected $fillable = [
        'board_id',
        'card_title',
        'description',
        'position',
        'created_by',
        'due_date',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'started_at',
        'paused_at',
        'total_pause_duration',
        'completed_at',
        'needs_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_overdue',
        'deadline_notified_at',
        'is_template'
    ];

    protected $casts = [
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_pause_duration' => 'integer',
        'needs_approval' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'is_overdue' => 'boolean',
        'deadline_notified_at' => 'datetime',
        'is_template' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        // When deleting a card, delete all related data
        static::deleting(function ($card) {
            // Delete all subtasks
            $card->subtasks()->delete();
            
            // Delete all assignments
            $card->assignments()->delete();
            
            // Delete all comments
            $card->comments()->delete();
            
            // Delete all time logs
            $card->timeLogs()->delete();
        });
    }

    // Relationships
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CardAssignment::class, 'card_id', 'card_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_assignments', 'card_id', 'user_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'card_id', 'card_id')->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'card_id', 'card_id')->where('comment_type', 'card');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'card_id', 'card_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    // Helper methods
    public function assignedUsers()
    {
        return $this->assignments()->with('user')->get()->pluck('user');
    }

    // ==================== DEADLINE METHODS ====================
    
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    /**
     * Get days remaining until deadline (negative if overdue)
     */
    public function daysRemaining(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get deadline status with color
     */
    public function getDeadlineStatus(): array
    {
        if (!$this->due_date || $this->status === 'done') {
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
        } elseif ($days === 0) {
            return [
                'status' => 'today',
                'color' => 'danger',
                'text' => 'Due today',
                'badge' => 'DUE TODAY'
            ];
        } elseif ($days === 1) {
            return [
                'status' => 'tomorrow',
                'color' => 'warning',
                'text' => 'Due tomorrow',
                'badge' => 'DUE TOMORROW'
            ];
        } elseif ($days <= 3) {
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

    /**
     * Mark task as overdue
     */
    public function markAsOverdue(): void
    {
        $this->update(['is_overdue' => true]);
    }

    /**
     * Clear overdue status
     */
    public function clearOverdue(): void
    {
        $this->update(['is_overdue' => false, 'deadline_notified_at' => null]);
    }

    public function getTotalTimeSpent(): float
    {
        return $this->timeLogs()->sum('duration_minutes') / 60; // Convert to hours
    }

    public function getSubtaskProgress(): array
    {
        $total = $this->subtasks()->count();
        $completed = $this->subtasks()->where('status', 'done')->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? ($completed / $total) * 100 : 0
        ];
    }

    /**
     * Determine if this task is locked from modifications
     * A task is considered locked once it's approved and in 'done' state
     */
    public function isLocked(): bool
    {
        return (bool) ($this->is_approved && $this->status === 'done');
    }

    /**
     * Check if a user can edit this card
     */
    public function canEdit($user): bool
    {
        if (!$user) {
            return false;
        }

        // Creator can always edit
        if ($this->created_by === $user->user_id) {
            return true;
        }

        // Check if user is project admin or team lead
        $project = $this->board->project;
        
        // Project owner can edit
        if ($project->created_by === $user->user_id) {
            return true;
        }

        // Check if user is Project Admin or Team Lead
        $member = $project->members()->where('user_id', $user->user_id)->first();
        if ($member && ($member->isAdmin() || $member->isTeamLead())) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can delete this card
     */
    public function canDelete($user): bool
    {
        // Locked (approved & done) tasks cannot be deleted
        if ($this->isLocked()) {
            return false;
        }

        // Only creator, project admin, or team lead can delete otherwise
        return $this->canEdit($user);
    }

    /**
     * Check if a user can manage subtasks (create, edit, delete)
     * More permissive than canEdit - allows assigned developers/designers
     */
    public function canManageSubtasks($user): bool
    {
        if (!$user) {
            return false;
        }

        // Locked (approved & done) tasks cannot have subtasks managed
        if ($this->isLocked()) {
            return false;
        }

        // If user can edit the card, they can manage subtasks
        if ($this->canEdit($user)) {
            return true;
        }

        // Check if user is assigned to this task
        $isAssigned = $this->assignments()->where('user_id', $user->user_id)->exists();
        
        // Any assigned user can manage subtasks
        if ($isAssigned) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can work on this task (time tracking, comments, attachments, blocker)
     * Only assigned users can work on the task
     */
    public function canWorkOn($user): bool
    {
        if (!$user) {
            return false;
        }

        // Locked (approved & done) tasks cannot be worked on
        if ($this->isLocked()) {
            return false;
        }

        // Project archived - cannot work
        if ($this->isProjectArchived()) {
            return false;
        }

        // Project Admins CANNOT work on tasks - they only manage
        $project = $this->board->project;
        $member = $project->members()->where('user_id', $user->user_id)->first();
        if ($member && $member->role === 'Project Admin') {
            return false; // Project Admin tidak bisa menggunakan fitur user
        }

        // Task creator can work on it (if not Project Admin)
        if ($this->created_by === $user->user_id) {
            return true;
        }

        // Team leads can work on any task
        if ($member && $member->isTeamLead()) {
            return true;
        }

        // Check if user is assigned to this task
        $isAssigned = $this->assignments()->where('user_id', $user->user_id)->exists();
        
        return $isAssigned;
    }

    /**
     * Check if user can report blocker for this task
     */
    public function canReportBlocker($user): bool
    {
        if (!$user) {
            return false;
        }

        // Project Admins CANNOT report blockers - they only manage
        $project = $this->board->project;
        $member = $project->members()->where('user_id', $user->user_id)->first();
        if ($member && $member->role === 'Project Admin') {
            return false;
        }

        // Cannot report blocker if task is already done and approved
        if ($this->isLocked()) {
            return false;
        }

        // Cannot report blocker if task is done (even if not approved yet)
        if ($this->status === 'done') {
            return false;
        }

        // Project archived - cannot report blocker
        if ($this->isProjectArchived()) {
            return false;
        }

        // Only assigned users or task creator can report blockers
        return $this->canWorkOn($user);
    }

    /**
     * Check if this task's project is archived
     */
    public function isProjectArchived(): bool
    {
        return $this->board->project->isArchived();
    }

    /**
     * Check if this task can be modified
     */
    public function canBeModified(): bool
    {
        return !$this->isProjectArchived();
    }

    /**
     * Start working on this task
     */
    public function startWork(): void
    {
        // Check if project is archived
        if ($this->isProjectArchived()) {
            throw new \Exception('Cannot modify tasks in an archived project.');
        }
        
        // If already started and paused, this will be handled by resumeWork
        if (!$this->started_at) {
            $this->started_at = now();
            $this->status = 'in_progress';
            $this->save();
            
            // Refresh relationship to ensure board.project is loaded
            $this->refresh();
            
            // Auto-move to "In Progress" board if exists
            $this->moveToMatchingBoard();
        }
    }

    /**
     * Pause work on this task
     */
    public function pauseWork(): void
    {
        if ($this->started_at && !$this->paused_at) {
            $this->paused_at = now();
            $this->save();
        }
    }

    /**
     * Resume work on this task
     */
    public function resumeWork(): void
    {
        if ($this->paused_at) {
            // Calculate pause duration and add to total
            $pauseDuration = now()->diffInMinutes($this->paused_at);
            $this->total_pause_duration += $pauseDuration;
            $this->paused_at = null;
            $this->save();
        }
    }

    /**
     * Complete work on this task - will need Team Lead approval
     */
    public function completeWork(): void
    {
        // If paused, resume first to calculate final pause duration
        if ($this->paused_at) {
            $this->resumeWork();
        }

        $this->completed_at = now();
        
        // Calculate actual working hours (excluding pauses)
        if ($this->started_at) {
            $totalMinutes = $this->started_at->diffInMinutes($this->completed_at);
            $workingMinutes = $totalMinutes - $this->total_pause_duration;
            $this->actual_hours = $workingMinutes / 60;

            // Create time log entry for this work session
            TimeLog::create([
                'card_id' => $this->card_id,
                'user_id' => auth()->id(),
                'start_time' => $this->started_at,
                'end_time' => $this->completed_at,
                'duration_minutes' => $workingMinutes,
                'description' => "Task completed: {$this->card_title} (Total: " . round($this->actual_hours, 2) . " hours, Paused: " . round($this->total_pause_duration / 60, 2) . " hours)"
            ]);
        }
        
        // Set needs approval - will be reviewed by Team Lead
        $this->needs_approval = true;
        $this->is_approved = false;
        $this->rejection_reason = null; // Clear any previous rejection reason
        
        // Set status to 'review' instead of 'done' (waiting for approval)
        $this->status = 'review';
        
        $this->save();
        
        // Auto-move to "Review" board if exists
        $this->moveToMatchingBoard();
    }

    /**
     * Check if task is currently paused
     */
    public function isPaused(): bool
    {
        return $this->started_at && $this->paused_at && !$this->completed_at;
    }

    /**
     * Check if task is currently in progress (started but not completed)
     */
    public function isInProgress(): bool
    {
        return $this->started_at && !$this->completed_at;
    }

    /**
     * Get total working time in minutes (excluding pauses)
     * Improved calculation for real-time accuracy
     */
    public function getWorkingMinutes(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        // End time is completion time or current time
        $endTime = $this->completed_at ?? now();
        
        // Total elapsed time
        $totalMinutes = $this->started_at->diffInMinutes($endTime);
        
        // Subtract total pause duration (from previous pauses)
        $workingMinutes = $totalMinutes - ($this->total_pause_duration ?? 0);
        
        // If currently paused, subtract current pause session
        if ($this->paused_at && !$this->completed_at) {
            $currentPauseDuration = now()->diffInMinutes($this->paused_at);
            $workingMinutes -= $currentPauseDuration;
        }
        
        return max(0, $workingMinutes);
    }

    /**
     * Get formatted working time (e.g., "2h 30m")
     */
    public function getFormattedWorkingTime(): string
    {
        $minutes = $this->getWorkingMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf("%dh %02dm", $hours, $mins);
        }
        return "{$mins}m";
    }
    
    /**
     * Get working time in hours (decimal)
     */
    public function getWorkingHours(): float
    {
        return round($this->getWorkingMinutes() / 60, 2);
    }
    
    /**
     * Get total pause duration in minutes
     */
    public function getTotalPauseDuration(): int
    {
        $total = $this->total_pause_duration ?? 0;
        
        // Add current pause if task is paused
        if ($this->paused_at && !$this->completed_at) {
            $total += now()->diffInMinutes($this->paused_at);
        }
        
        return $total;
    }
    
    /**
     * Get formatted pause duration
     */
    public function getFormattedPauseDuration(): string
    {
        $minutes = $this->getTotalPauseDuration();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf("%dh %02dm", $hours, $mins);
        }
        return "{$mins}m";
    }
    
    /**
     * Get time efficiency (working time vs estimated time)
     */
    public function getTimeEfficiency(): ?float
    {
        if (!$this->estimated_hours || $this->estimated_hours <= 0) {
            return null;
        }
        
        $actualHours = $this->getWorkingHours();
        if ($actualHours <= 0) {
            return null;
        }
        
        // Return percentage: 100% = on time, >100% = over time, <100% = under time
        return round(($this->estimated_hours / $actualHours) * 100, 1);
    }

    /**
     * Check if user can approve this task (Team Lead ONLY, NOT Project Admin)
     */
    public function canApprove($user): bool
    {
        if (!$user) {
            return false;
        }

        $project = $this->board->project;

        // Check if user is Team Lead ONLY (Project Admin cannot approve tasks)
        $member = $project->members()->where('user_id', $user->user_id)->first();
        return $member && $member->isTeamLead();
    }

    /**
     * Approve this task (Team Lead ONLY, NOT Project Admin)
     */
    public function approve($user): void
    {
        $this->is_approved = true;
        $this->approved_by = $user->user_id;
        $this->approved_at = now();
        $this->rejection_reason = null;
        $this->status = 'done'; // Change status to done when approved
        $this->save();
        
        // Auto-move to "Done" board if exists
        $this->moveToMatchingBoard();
    }

    /**
     * Reject this task and provide reason
     */
    public function reject($user, $reason): void
    {
        $this->is_approved = false;
        $this->needs_approval = true;
        $this->status = 'in_progress'; // Move back to in progress
        $this->completed_at = null; // Clear completion time
        $this->rejection_reason = $reason;
        $this->save();
        
        // Auto-move to "In Progress" board if exists
        $this->moveToMatchingBoard();
    }

    /**
     * Get approval status label for UI
     */
    public function getApprovalStatusLabel(): string
    {
        if (!$this->needs_approval) {
            return '';
        }

        if ($this->is_approved) {
            return 'Approved';
        }

        return 'Pending Approval';
    }

    /**
     * Automatically move task to appropriate board based on status
     */
    public function moveToMatchingBoard(): bool
    {
        try {
            // Ensure we have fresh board and project data
            $this->load('board.project');
            
            if (!$this->board || !$this->board->project) {
                \Log::warning('moveToMatchingBoard: Board or Project not found', [
                    'card_id' => $this->card_id,
                    'board_id' => $this->board_id
                ]);
                return false;
            }
            
            $project = $this->board->project;
            
            // Find board with matching status_mapping in the same project
            $targetBoard = Board::where('project_id', $project->project_id)
                ->where('status_mapping', $this->status)
                ->first();
            
            \Log::info('moveToMatchingBoard: Looking for board', [
                'card_id' => $this->card_id,
                'current_status' => $this->status,
                'current_board_id' => $this->board_id,
                'target_board_id' => $targetBoard ? $targetBoard->board_id : null,
                'target_board_name' => $targetBoard ? $targetBoard->board_name : null
            ]);
            
            // If found and different from current board, move task
            if ($targetBoard && $targetBoard->board_id !== $this->board_id) {
                $oldBoardId = $this->board_id;
                $this->board_id = $targetBoard->board_id;
                $this->save();
                
                \Log::info('moveToMatchingBoard: Task moved', [
                    'card_id' => $this->card_id,
                    'from_board_id' => $oldBoardId,
                    'to_board_id' => $this->board_id,
                    'to_board_name' => $targetBoard->board_name
                ]);
                
                // Refresh the board relationship after move
                $this->load('board');
                return true;
            }
            
            \Log::info('moveToMatchingBoard: No move needed', [
                'card_id' => $this->card_id,
                'reason' => !$targetBoard ? 'No matching board found' : 'Already in correct board'
            ]);
            
            return false;
        } catch (\Exception $e) {
            \Log::error('moveToMatchingBoard: Error', [
                'card_id' => $this->card_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ==================== RELATIONSHIPS FOR NEW FEATURES ====================
    
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'card_id', 'card_id');
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'card_id', 'card_id')->orderBy('created_at', 'desc');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id', 'card_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id', 'card_id');
    }
}
