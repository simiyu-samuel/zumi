<?php

namespace App\Models;

use App\Enums\ChallengeStatus;
use App\Enums\ChallengeType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    
    const RELATION_USER = 'user';
    const RELATION_WINNER = 'winner';
    const RELATION_PARTICIPATIONS = 'participations';

    const DEFAULT_EAGER_LOAD = [
        self::RELATION_USER,
        self::RELATION_WINNER,
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'prize_pool',
        'ends_at',
        'winner_id',
    ];

    protected $casts = [
        'type' => ChallengeType::class,
        'status' => ChallengeStatus::class,
        'ends_at' => 'datetime',
        'prize_pool' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(ChallengeParticipation::class);
    }
}
