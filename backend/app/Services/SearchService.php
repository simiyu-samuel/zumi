<?php

namespace App\Services;

use App\Models\Circle;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Perform a global search across Users, Waves, and Circles.
     */
    public function globalSearch(string $query, int $limit = null): array
    {
        $limit = $limit ?? config('zumi.pagination.default_per_page', 15);

        return [
            'users'   => User::search($query)->take($limit)->get(),
            'waves'   => Wave::search($query)->take($limit)->get(),
            'circles' => Circle::search($query)->take($limit)->get(),
        ];
    }

    /**
     * Search for users with pagination.
     */
    public function searchUsers(string $query, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('zumi.pagination.default_per_page', 15);
        return User::search($query)->paginate($perPage);
    }

    /**
     * Search for waves with pagination.
     */
    public function searchWaves(string $query, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('zumi.pagination.default_per_page', 15);
        return Wave::search($query)->paginate($perPage);
    }

    /**
     * Search for circles with pagination.
     */
    public function searchCircles(string $query, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('zumi.pagination.default_per_page', 15);
        return Circle::search($query)->paginate($perPage);
    }
}
