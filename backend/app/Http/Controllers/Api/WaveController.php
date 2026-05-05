<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wave\InitializeUploadRequest;
use App\Http\Requests\Wave\StoreWaveRequest;
use App\Http\Requests\Wave\UpdateWaveRequest;
use App\Http\Requests\Wave\GiftWaveRequest;
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
        $waves = $this->waveService->getDiscoveryFeed(
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        
        return WaveResource::collection($waves);
    }

    public function followedFeed(Request $request)
    {
        $waves = $this->waveService->getFollowedFeed(
            $request->user(), 
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        
        return WaveResource::collection($waves);
    }

    public function initializeUpload(InitializeUploadRequest $request): JsonResponse
    {

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
        $result = $this->waveService->purchaseWave($request->user(), $wave);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Wave unlocked successfully',
        ]);
    }

    public function recordView(Request $request, Wave $wave): JsonResponse
    {
        $this->waveService->incrementViews($wave);

        return response()->json(['message' => 'View recorded']);
    }

    public function share(Request $request, Wave $wave): JsonResponse
    {
        $this->waveService->recordShare($wave);

        return response()->json([
            'message'      => 'Share recorded',
            'shares_count' => $wave->shares_count,
        ]);
    }

    public function toggleBookmark(Request $request, Wave $wave): JsonResponse
    {
        $bookmarked = $this->waveService->toggleBookmark($request->user(), $wave);

        return response()->json([
            'message'    => $bookmarked ? 'Wave bookmarked' : 'Bookmark removed',
            'bookmarked' => $bookmarked,
        ]);
    }

    public function bookmarks(Request $request): JsonResponse
    {
        $waves = $this->waveService->getBookmarks(
            $request->user(),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return response()->json(WaveResource::collection($waves)->response()->getData(true));
    }

    public function gift(GiftWaveRequest $request, Wave $wave): JsonResponse
    {
        $result = $this->waveService->giftWave($request->user(), $wave, $request->amount);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Gift sent successfully!',
            'balance' => $request->user()->fresh()->drops_balance,
        ]);
    }
}
