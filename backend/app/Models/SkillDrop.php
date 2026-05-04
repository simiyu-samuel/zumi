<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SkillDrop extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'price_drops',
        'preview_url',
        'content_url',
        'sales_count',
        'rating_avg',
    ];

    protected $casts = [
        'price_drops' => 'integer',
        'sales_count' => 'integer',
        'rating_avg'  => 'integer',
    ];

    /**
     * The creator of the skill drop.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Users who have purchased this skill drop.
     */
    public function buyers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'skill_drop_purchases')
            ->withPivot(['id', 'amount_paid', 'purchased_at'])
            ->withTimestamps();
    }
}
