<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeParticipation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'wave_id',
        'votes_count',
    ];

    protected $casts = [
        'votes_count' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wave(): BelongsTo
    {
        return $this->belongsTo(Wave::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChallengeVote::class, 'participation_id');
    }
}
