<?php

namespace App\Models;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'reporter_id',
        'reported_type',
        'reported_id',
        'reason',
        'description',
        'status',
        'moderator_notes',
        'action_taken',
        'resolved_at',
        'moderator_id',
    ];

    protected function casts(): array
    {
        return [
            'status'       => \App\Enums\ReportStatus::class,
            'reason'       => \App\Enums\ReportReason::class,
            'action_taken' => \App\Enums\ModerationAction::class,
            'resolved_at'  => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function reported(): MorphTo
    {
        return $this->morphTo();
    }
}
