<?php

namespace App\Models;

use App\Enums\GatedRoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GatedRoom extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const DEFAULT_EAGER_LOAD = ['host'];

    protected $fillable = [
        'user_id',
        'title',
        'livekit_room_name',
        'description',
        'entry_fee_drops',
        'status',
        'is_replay_enabled',
        'replay_url',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'entry_fee_drops'   => 'integer',
        'status'            => GatedRoomStatus::class,
        'is_replay_enabled' => 'boolean',
        'scheduled_at'      => 'datetime',
        'started_at'        => 'datetime',
        'ended_at'          => 'datetime',
    ];

    /**
     * The host of the room.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users who have joined this room.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gated_room_participants')
            ->withPivot(['id', 'amount_paid', 'joined_at'])
            ->withTimestamps();
    }
}
