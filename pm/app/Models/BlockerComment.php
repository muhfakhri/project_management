<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockerComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'user_id',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the blocker this comment belongs to
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(Blocker::class, 'blocker_id', 'id');
    }

    /**
     * Get the user who wrote the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
