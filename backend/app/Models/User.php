<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, InteractsWithMedia, HasUuids;

    const COLLECTION_AVATAR = 'avatar';
    const COLLECTION_BANNER = 'banner';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'google_id',
        'apple_id',
        'drops_balance',
        'flow_score',
        'onboarding_completed',
        'verified_at',
        'bio',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'drops_balance' => 'integer',
            'flow_score' => 'integer',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    /**
     * Register media collections for User.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_AVATAR)
            ->singleFile()
            ->useFallbackUrl(config('app.url') . '/images/default-avatar.png');

        $this->addMediaCollection(self::COLLECTION_BANNER)
            ->singleFile()
            ->useFallbackUrl(config('app.url') . '/images/default-banner.png');
    }

    public function dropsLedger(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DropsLedger::class);
    }

    public function following(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }
}
