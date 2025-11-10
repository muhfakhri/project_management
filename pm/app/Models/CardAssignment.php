<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardAssignment extends Model
{
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;
    
    protected $fillable = [
        'card_id',
        'user_id',
        'assignment_status',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'assigned_at' => 'datetime'
    ];

    // Relationships
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function start()
    {
        $this->update([
            'assignment_status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function complete()
    {
        $this->update([
            'assignment_status' => 'completed',
            'completed_at' => now()
        ]);
    }

    // Accessor for backward compatibility
    public function getCreatedAtAttribute()
    {
        return $this->assigned_at;
    }
}
