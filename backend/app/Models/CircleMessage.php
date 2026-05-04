<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleMessage extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'circle_id',
        'user_id',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
