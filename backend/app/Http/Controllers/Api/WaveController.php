<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wave\StoreWaveRequest;
use App\Http\Requests\Wave\UpdateWaveRequest;
use App\Http\Resources\WaveResource;
use App\Models\Wave;
use App\Services\WaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WaveController extends Controller
{
    public function __construct(
        protected WaveService $waveService
    ) {}

    public function index(Request $request)
    {
        $waves = $this->waveService->getDiscoveryFeed($request->input('per_page', 15));
        
        return WaveResource::collection($waves);
    }

    public function initializeUpload(Request $request): JsonResponse
    {
        $request->validate([
            'size_bytes' => 'required|integer|max:104857600', // 100MB max
            'title'      => 'nullable|string|max:255',
        ]);

        $uploadData = $this->waveService->initializeUpload(
            $request->user(),
            $request->size_bytes,
            ['title' => $request->title]
        );

        if (!$uploadData) {
            return response()->json([
                'message' => 'Failed to initialize upload with Cloudflare.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($uploadData);
    }

    public function store(StoreWaveRequest $request): JsonResponse
    {
        $wave = $this->waveService->createWave($request->user(), $request->validated());
        
        return response()->json([
            'message' => 'Wave created successfully',
            'wave'    => new WaveResource($wave),
        ], Response::HTTP_CREATED);
    }

    public function show(string $id): WaveResource
    {
        $wave = $this->waveService->getWave($id);

        if (!$wave) {
            abort(Response::HTTP_NOT_FOUND, 'Wave not found');
        }

        $wave->load(['user']);

        return new WaveResource($wave);
    }

    public function update(UpdateWaveRequest $request, Wave $wave): JsonResponse
    {
        $this->authorize('update', $wave);

        $updatedWave = $this->waveService->updateWave($wave, $request->validated());

        return response()->json([
            'message' => 'Wave updated successfully',
            'wave'    => new WaveResource($updatedWave),
        ]);
    }

    public function destroy(Wave $wave): JsonResponse
    {
        $this->authorize('delete', $wave);

        $this->waveService->deleteWave($wave);

        return response()->json([
            'message' => 'Wave deleted successfully',
        ]);
    }

    public function like(Request $request, Wave $wave): JsonResponse
    {
        $this->waveService->likeWave($request->user(), $wave);

        return response()->json([
            'message' => 'Success',
            'likes_count' => $wave->likes_count,
        ]);
    }

    public function purchase(Request $request, Wave $wave): JsonResponse
    {
        try {
            $this->waveService->purchaseWave($request->user(), $wave);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Wave unlocked successfully',
        ]);
    }
}
