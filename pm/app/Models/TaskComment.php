<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskComment extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'comment'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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

    public function mentions(): HasMany
    {
        return $this->hasMany(CommentMention::class, 'comment_id');
    }

    /**
     * Extract @mentions from comment
     */
    public function extractMentions(): array
    {
        preg_match_all('/@(\w+)/', $this->comment, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Parse comment and convert @mentions to links
     */
    public function getParsedCommentAttribute(): string
    {
        $comment = e($this->comment);
        
        // Replace @username with clickable link
        return preg_replace_callback('/@(\w+)/', function($matches) {
            $username = $matches[1];
            $user = User::where('username', $username)->first();
            
            if ($user) {
                return '<span class="badge bg-primary">@' . $username . '</span>';
            }
            
            return $matches[0];
        }, $comment);
    }
}
