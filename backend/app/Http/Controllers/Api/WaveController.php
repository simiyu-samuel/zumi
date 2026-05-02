<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wave\StoreWaveRequest;
use App\Http\Resources\WaveResource;
use App\Services\WaveService;
use Illuminate\Http\Request;

class WaveController extends Controller
{
    public function __construct(
        protected WaveService $waveService
    ) {}

    public function index(Request $request)
    {
        $type = $request->query('type', 'random');
        $waves = $this->waveService->getFeed($type);

        return WaveResource::collection($waves);
    }

    public function store(StoreWaveRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $wave = $this->waveService->createWave($data);

        return new WaveResource($wave->load('user'));
    }

    public function toggleLike(Request $request, $id)
    {
        $isLiked = $this->waveService->toggleLike($id, $request->user()->id);

        if ($isLiked === null) {
            return response()->json(['message' => 'Wave not found'], 404);
        }

        return response()->json([
            'liked'   => $isLiked,
            'message' => $isLiked ? 'Wave liked' : 'Wave unliked',
        ]);
    }
}
