<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Circle\StoreCircleRequest;
use App\Http\Resources\CircleResource;
use App\Models\Circle;
use App\Models\CircleJoinRequest;
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
        $circles = $this->circleService->getDiscoveryCircles(
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        return CircleResource::collection($circles);
    }

    public function myCircles(Request $request)
    {
        $circles = $this->circleService->getUserCircles(
            $request->user(), 
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        return CircleResource::collection($circles);
    }

    public function store(StoreCircleRequest $request): JsonResponse
    {
        $this->authorize('create', Circle::class);

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

        $isPrivate = $circle->type === \App\Enums\CircleType::Private;

        $message = $isPrivate 
            ? 'Join request sent successfully' 
            : 'Joined circle successfully';
            
        $status = $isPrivate ? Response::HTTP_ACCEPTED : Response::HTTP_OK;

        return response()->json(['message' => $message], $status);
    }

    public function requests(Circle $circle): JsonResponse
    {
        $this->authorize('manageJoinRequests', $circle);

        $requests = $this->circleService->getPendingRequests($circle);
        
        return response()->json($requests);
    }

    public function approveRequest(Request $request, string $requestId): JsonResponse
    {
        $joinRequest = CircleJoinRequest::with('circle')->findOrFail($requestId);
        $this->authorize('manageJoinRequests', $joinRequest->circle);

        $this->circleService->approveJoinRequest($joinRequest);

        return response()->json(['message' => 'Request approved successfully']);
    }

    public function declineRequest(Request $request, string $requestId): JsonResponse
    {
        $joinRequest = CircleJoinRequest::with('circle')->findOrFail($requestId);
        $this->authorize('manageJoinRequests', $joinRequest->circle);

        $this->circleService->declineJoinRequest($joinRequest);

        return response()->json(['message' => 'Request declined successfully']);
    }

    public function insights(Circle $circle): JsonResponse
    {
        $this->authorize('viewInsights', $circle);

        $insights = $this->circleService->getInsights($circle);

        return response()->json($insights);
    }
}
