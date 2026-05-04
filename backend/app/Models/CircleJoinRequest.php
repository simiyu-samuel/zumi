<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleJoinRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'circle_id',
        'status',
    ];

    protected $casts = [
        'status' => \App\Enums\CircleJoinRequestStatus::class,
    ];

    /**
     * Relations constants.
     */
    const RELATION_USER = 'user';
    const RELATION_CIRCLE = 'circle';

    /**
     * The user who requested to join.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The circle being requested.
     */
    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }
}
