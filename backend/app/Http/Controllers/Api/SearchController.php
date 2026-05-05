<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Perform a global search across Users, Waves, and Circles.
     */
    public function global(Request $request): JsonResponse
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([
                'users'   => [],
                'waves'   => [],
                'circles' => [],
            ]);
        }

        $limit = (int) $request->get('limit', config('zumi.pagination.default_per_page', 15));
        
        $results = $this->searchService->globalSearch($query, $limit);

        return response()->json($results);
    }

    /**
     * Search for users only.
     */
    public function users(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $perPage = (int) $request->get('per_page', config('zumi.pagination.default_per_page', 15));
        
        $users = $this->searchService->searchUsers($query, $perPage);

        return response()->json($users);
    }

    /**
     * Search for waves only.
     */
    public function waves(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $perPage = (int) $request->get('per_page', config('zumi.pagination.default_per_page', 15));
        
        $waves = $this->searchService->searchWaves($query, $perPage);

        return response()->json($waves);
    }

    /**
     * Search for circles only.
     */
    public function circles(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $perPage = (int) $request->get('per_page', config('zumi.pagination.default_per_page', 15));
        
        $circles = $this->searchService->searchCircles($query, $perPage);

        return response()->json($circles);
    }

    public function suggestedUsers(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 5);
        $users = $this->searchService->getSuggestedUsers($limit);

        return response()->json([
            'data' => \App\Http\Resources\UserResource::collection($users),
        ]);
    }
}
