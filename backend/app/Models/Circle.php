<?php

namespace App\Models;

use App\Enums\CircleStatus;
use App\Enums\CircleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Circle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'avatar',
        'cover_image',
        'members_count',
    ];

    protected $casts = [
        'type'   => CircleType::class,
        'status' => CircleStatus::class,
    ];

    /**
     * Relations constants.
     */
    const RELATION_OWNER = 'owner';
    const RELATION_MEMBERS = 'members';
    const RELATION_WAVES = 'waves';

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'circle_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function waves(): HasMany
    {
        return $this->hasMany(Wave::class);
    }
}
