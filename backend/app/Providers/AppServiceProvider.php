<?php

namespace App\Providers;

use App\Models\Wave;
use App\Models\Comment;
use App\Policies\WavePolicy;
use App\Policies\CommentPolicy;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WaveRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\EloquentWaveRepository;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WaveRepositoryInterface::class, EloquentWaveRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
