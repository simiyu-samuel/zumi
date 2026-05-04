<?php

namespace App\Models;

use App\Enums\CircleStatus;
use App\Enums\CircleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Circle extends Model
{
    use HasFactory, HasUuids, Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => $this->name,
            'description' => $this->description,
        ];
    }

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
        'monthly_drops_price',
    ];

    protected $casts = [
        'type'                => CircleType::class,
        'status'              => CircleStatus::class,
        'monthly_drops_price' => 'integer',
    ];

    /**
     * Relations constants.
     */
    const RELATION_OWNER = 'owner';
    const RELATION_MEMBERS = 'members';
    const RELATION_WAVES = 'waves';

    const DEFAULT_EAGER_LOAD = [
        self::RELATION_OWNER,
    ];

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

    public function joinRequests(): HasMany
    {
        return $this->hasMany(CircleJoinRequest::class);
    }
}
