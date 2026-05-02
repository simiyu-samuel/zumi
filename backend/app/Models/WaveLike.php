<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaveLike extends Model
{
    protected $fillable = ['user_id', 'wave_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wave(): BelongsTo
    {
        return $this->belongsTo(Wave::class);
    }
}
