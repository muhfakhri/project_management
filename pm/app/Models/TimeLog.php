<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    
    protected $fillable = [
        'card_id',
        'subtask_id',
        'user_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'description'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
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
    public function calculateDuration()
    {
        if ($this->start_time && $this->end_time) {
            $this->duration_minutes = $this->start_time->diffInMinutes($this->end_time);
            $this->save();
        }
    }

    public function getDurationInHours(): float
    {
        return $this->duration_minutes / 60;
    }

    public function isForCard(): bool
    {
        return !is_null($this->card_id) && is_null($this->subtask_id);
    }

    public function isForSubtask(): bool
    {
        return !is_null($this->subtask_id);
    }

    public function getTargetTitle(): string
    {
        if ($this->isForCard() && $this->card) {
            return $this->card->card_title;
        }
        
        if ($this->isForSubtask() && $this->subtask) {
            return $this->subtask->subtask_title;
        }
        
        return 'Unknown';
    }

    // Accessors for backward compatibility
    public function getHoursSpentAttribute()
    {
        return $this->duration_minutes / 60;
    }

    public function getDateAttribute()
    {
        // Return Carbon object for backward compatibility with ->format() calls
        return $this->start_time ?? $this->end_time;
    }
}
