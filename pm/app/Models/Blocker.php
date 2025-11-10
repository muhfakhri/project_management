<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blocker extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'reporter_id',
        'reason',
        'priority',
        'status',
        'assigned_to',
        'resolution_note',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the task/card that is blocked
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    /**
     * Get the user who reported the blocker
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }

    /**
     * Get the user assigned to help
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    /**
     * Get comments for this blocker
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BlockerComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope for active blockers
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['reported', 'assigned', 'in_progress']);
    }

    /**
     * Scope for resolved blockers
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for high priority
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    /**
     * Get time blocked in hours
     */
    public function getTimeBlockedAttribute()
    {
        $end = $this->resolved_at ?? now();
        return $this->created_at->diffInHours($end);
    }

    /**
     * Check if blocker is overdue (>24 hours without resolution)
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'resolved') {
            return false;
        }
        return $this->created_at->addHours(24)->isPast();
    }
}
