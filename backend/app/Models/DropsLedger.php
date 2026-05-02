<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DropsLedger extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'drops_ledger';

    public $timestamps = false; // We use only created_at

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'direction',
        'reference_type',
        'reference_id',
        'status',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'json',
        'created_at' => 'datetime',
    ];

    const DIRECTION_CREDIT = 'credit';
    const DIRECTION_DEBIT  = 'debit';

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';
    const STATUS_REVERSED  = 'reversed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
