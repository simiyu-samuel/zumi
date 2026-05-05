<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    const RELATION_USER = 'user';
    const RELATION_REPLIES = 'replies';
    const RELATION_LIKES = 'likes';

    const DEFAULT_EAGER_LOAD = [
        self::RELATION_USER,
    ];

    const RECURSIVE_EAGER_LOAD = [
        self::RELATION_USER,
        self::RELATION_REPLIES . '.' . self::RELATION_USER,
    ];

    protected $withCount = ['likes'];

    protected $fillable = [
        'user_id',
        'commentable_id',
        'commentable_type',
        'content',
        'parent_id',
        'likes_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Check if the comment is liked by a specific user.
     */
    public function isLikedBy(string $userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
