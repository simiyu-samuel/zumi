<?php

namespace App\Models;

use App\Enums\DropsTransactionDirection;
use App\Enums\DropsTransactionStatus;
use App\Enums\DropsTransactionType;
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
        'type'       => DropsTransactionType::class,
        'direction'  => DropsTransactionDirection::class,
        'status'     => DropsTransactionStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
