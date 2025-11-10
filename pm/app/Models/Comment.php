<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $primaryKey = 'comment_id';
    
    protected $fillable = [
        'card_id',
        'subtask_id',
        'user_id',
        'comment_text',
        'comment_type'
    ];

    // Relationships
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function subtask(): BelongsTo
    {
        return $this->belongsTo(Subtask::class, 'subtask_id', 'subtask_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function isCardComment(): bool
    {
        return $this->comment_type === 'card';
    }

    public function isSubtaskComment(): bool
    {
        return $this->comment_type === 'subtask';
    }

    public function getTargetTitle(): string
    {
        if ($this->isCardComment() && $this->card) {
            return $this->card->card_title;
        }
        
        if ($this->isSubtaskComment() && $this->subtask) {
            return $this->subtask->subtask_title;
        }
        
        return 'Unknown';
    }

    // Accessor for backward compatibility
    public function getContentAttribute()
    {
        return $this->comment_text;
    }
}
