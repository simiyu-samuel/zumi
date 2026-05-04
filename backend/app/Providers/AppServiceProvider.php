<?php

namespace App\Providers;

use App\Models\Wave;
use App\Models\Comment;
use App\Policies\WavePolicy;
use App\Policies\CommentPolicy;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use App\Repositories\Interfaces\FollowRepositoryInterface;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\Interfaces\CircleRepositoryInterface;
use App\Repositories\Interfaces\ChallengeRepositoryInterface;
use App\Repositories\Interfaces\SkillDropRepositoryInterface;
use App\Repositories\Interfaces\GatedRoomRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\EloquentWaveRepository;
use App\Repositories\Eloquent\EloquentDropsRepository;
use App\Repositories\Eloquent\EloquentFollowRepository;
use App\Repositories\Eloquent\EloquentCommentRepository;
use App\Repositories\Eloquent\EloquentCircleRepository;
use App\Repositories\Eloquent\EloquentChallengeRepository;
use App\Repositories\Eloquent\EloquentSkillDropRepository;
use App\Repositories\Eloquent\EloquentGatedRoomRepository;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Wave::class => WavePolicy::class,
        Comment::class => CommentPolicy::class,
        \App\Models\Circle::class => \App\Policies\CirclePolicy::class,
        \App\Models\Challenge::class => \App\Policies\ChallengePolicy::class,
        \App\Models\SkillDrop::class => \App\Policies\SkillDropPolicy::class,
        \App\Models\GatedRoom::class => \App\Policies\GatedRoomPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WaveRepositoryInterface::class, EloquentWaveRepository::class);
        $this->app->bind(DropsRepositoryInterface::class, EloquentDropsRepository::class);
        $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(CircleRepositoryInterface::class, EloquentCircleRepository::class);
        $this->app->bind(ChallengeRepositoryInterface::class, EloquentChallengeRepository::class);
        $this->app->bind(SkillDropRepositoryInterface::class, EloquentSkillDropRepository::class);
        $this->app->bind(GatedRoomRepositoryInterface::class, EloquentGatedRoomRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-wallet', [\App\Policies\DropsPolicy::class, 'viewWallet']);
        Gate::define('gift-drops', [\App\Policies\DropsPolicy::class, 'viewWallet']); // Reuse for now
    }
}
