<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    protected $primaryKey = 'board_id';
    
    protected $fillable = [
        'project_id',
        'board_name',
        'description',
        'position',
        'status_mapping'
    ];

    protected static function boot()
    {
        parent::boot();

        // When deleting a board, delete all related cards
        static::deleting(function ($board) {
            foreach ($board->cards as $card) {
                $card->delete();
            }
        });
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'board_id', 'board_id')->orderBy('position');
    }

    // Helper methods
    public function getCardCount(): int
    {
        return $this->cards()->count();
    }

    public function getCompletedCardCount(): int
    {
        // Simply count completed cards (no approval at task level)
        return $this->cards()
            ->where('status', 'done')
            ->count();
    }

    public function getProgressPercentage(): float
    {
        $total = $this->getCardCount();
        $completed = $this->getCompletedCardCount();
        
        return $total > 0 ? ($completed / $total) * 100 : 0;
    }
    
    /**
     * Get count of pending approval SUBTASKS in this board
     */
    public function getPendingApprovalCount(): int
    {
        return \DB::table('subtasks')
            ->join('cards', 'subtasks.card_id', '=', 'cards.card_id')
            ->where('cards.board_id', $this->board_id)
            ->where('subtasks.status', 'done')
            ->where('subtasks.needs_approval', true)
            ->where('subtasks.is_approved', false)
            ->count();
    }

    /**
     * Check if user can manage (edit/delete) this board
     * Only Project Admin can manage boards
     */
    public function canManage($user): bool
    {
        if (!$user) {
            return false;
        }

        $project = $this->project;
        
        // Project creator can manage
        if ($project->created_by === $user->user_id) {
            return true;
        }

        // Check if user is Project Admin only
        return $project->isAdmin($user->user_id);
    }
}
