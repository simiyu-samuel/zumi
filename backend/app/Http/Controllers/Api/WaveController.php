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

    public function store(StoreWaveRequest $request): JsonResponse
    {
        $wave = $this->waveService->createWave($request->user(), $request->validated());
        
        return response()->json([
            'message' => 'Wave created successfully',
            'wave'    => new WaveResource($wave),
        ], 201);
    }

    public function show(string $id): WaveResource
    {
        $wave = $this->waveService->getWave($id);

        if (!$wave) {
            abort(404, 'Wave not found');
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
}
