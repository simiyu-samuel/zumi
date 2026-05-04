<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeVote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'participation_id',
        'user_id',
    ];

    public function participation(): BelongsTo
    {
        return $this->belongsTo(ChallengeParticipation::class, 'participation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
