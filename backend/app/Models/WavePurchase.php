<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WavePurchase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'wave_id',
        'amount_paid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wave(): BelongsTo
    {
        return $this->belongsTo(Wave::class);
    }
}
