<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrendingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrendingController extends Controller
{
    public function __construct(
        protected TrendingService $trendingService
    ) {}

    /**
     * Get trending data for the platform.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->trendingService->getTrendingData();

        return response()->json($data);
    }
}

