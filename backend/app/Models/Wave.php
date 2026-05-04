<?php

namespace App\Models;

use App\Enums\WaveStatus;
use App\Enums\WaveVisibility;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Wave extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasUuids;

    const RELATION_USER = 'user';
    const RELATION_LIKES = 'likes';
    const RELATION_COMMENTS = 'comments';
    
    const DEFAULT_EAGER_LOAD = [
        self::RELATION_USER,
    ];

    protected $fillable = [
        'user_id',
        'cloudflare_id',
        'status',
        'video_metadata',
        'title',
        'description',
        'stream_id',
        'thumbnail_url',
        'visibility',
        'gated_drops',
        'likes_count',
        'comments_count',
        'shares_count',
        'views_count',
        'circle_id',
    ];

    protected $casts = [
        'video_metadata' => 'json',
        'status'         => WaveStatus::class,
        'visibility'     => WaveVisibility::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(WaveLike::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(WavePurchase::class);
    }
}
