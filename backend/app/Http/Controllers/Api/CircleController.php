<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Circle\StoreCircleRequest;
use App\Http\Resources\CircleResource;
use App\Models\Circle;
use App\Services\CircleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CircleController extends Controller
{
    public function __construct(
        protected CircleService $circleService
    ) {}

    public function index(Request $request)
    {
        $circles = $this->circleService->getDiscoveryCircles($request->input('per_page', 15));
        return CircleResource::collection($circles);
    }

    public function myCircles(Request $request)
    {
        $circles = $this->circleService->getUserCircles($request->user(), $request->input('per_page', 15));
        return CircleResource::collection($circles);
    }

    public function store(StoreCircleRequest $request): JsonResponse
    {
        $circle = $this->circleService->createCircle($request->user(), $request->validated());
        
        return response()->json([
            'message' => 'Circle created successfully',
            'circle'  => new CircleResource($circle),
        ], Response::HTTP_CREATED);
    }

    public function show(Circle $circle): CircleResource
    {
        return new CircleResource($circle->load(['owner', 'members']));
    }

    public function join(Request $request, Circle $circle): JsonResponse
    {
        $this->authorize('join', $circle);

        $this->circleService->joinCircle($request->user(), $circle);
        
        return response()->json(['message' => 'Joined circle successfully']);
    }

    public function leave(Request $request, Circle $circle): JsonResponse
    {
        $this->authorize('leave', $circle);

        $this->circleService->leaveCircle($request->user(), $circle);
        
        return response()->json(['message' => 'Left circle successfully']);
    }
}
