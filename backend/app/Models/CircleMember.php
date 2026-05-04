<?php

namespace App\Models;

use App\Enums\CircleMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleMember extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'circle_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'role' => CircleMemberRole::class,
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
