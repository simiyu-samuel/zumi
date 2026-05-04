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
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, InteractsWithMedia, HasUuids, Billable, Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id'       => (string) $this->id,
            'name'     => $this->name,
            'username' => $this->username,
            'bio'      => $this->bio,
        ];
    }

    /**
     * Check if user is a Pro subscriber.
     */
    public function isPro(): bool
    {
        return $this->role === UserRole::Pro;
    }

    /**
     * Check if user is a Studio subscriber.
     */
    public function isStudio(): bool
    {
        return $this->role === UserRole::Studio;
    }

    /**
     * Check if user has any premium subscription.
     */
    public function isPremium(): bool
    {
        return in_array($this->role, [UserRole::Pro, UserRole::Studio, UserRole::Admin]);
    }

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
        'stripe_connect_id',
        'stripe_onboarding_completed',
        'notification_settings',
        'interests',
        'fcm_token',
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
            'stripe_onboarding_completed' => 'boolean',
            'notification_settings' => 'array',
            'interests' => 'array',
        ];
    }

    /**
     * Check if user has opted in for a specific notification type.
     * Assumes true (opt-in) by default unless explicitly set to false.
     */
    public function wantsNotification(\App\Enums\NotificationType $type): bool
    {
        $settings = $this->notification_settings ?? [];
        return $settings[$type->value] ?? true;
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
    public function circles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Circle::class, 'circle_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function ownedCircles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Circle::class, 'owner_id');
    }

    public function joinRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CircleJoinRequest::class);
    }

    /**
     * Skill Drops purchased by the user.
     */
    public function purchasedSkillDrops(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SkillDrop::class, 'skill_drop_purchases')
            ->withPivot(['id', 'amount_paid', 'purchased_at'])
            ->withTimestamps();
    }
}
