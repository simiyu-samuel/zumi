<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GatedRoom\StoreGatedRoomRequest;
use App\Http\Resources\GatedRoomResource;
use App\Models\GatedRoom;
use App\Services\GatedRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GatedRoomController extends Controller
{
    public function __construct(
        protected GatedRoomService $roomService
    ) {}

    public function index(Request $request)
    {
        $rooms = $this->roomService->getActiveRooms(
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return GatedRoomResource::collection($rooms);
    }

    public function myRooms(Request $request)
    {
        $rooms = $this->roomService->getUserRooms(
            $request->user(),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return GatedRoomResource::collection($rooms);
    }

    public function store(StoreGatedRoomRequest $request): JsonResponse
    {
        $this->authorize('create', GatedRoom::class);

        $room = $this->roomService->createRoom($request->user(), $request->validated());

        return response()->json([
            'message' => 'Gated Room created successfully',
            'room'    => new GatedRoomResource($room),
        ], Response::HTTP_CREATED);
    }

    public function show(GatedRoom $gatedRoom): GatedRoomResource
    {
        return new GatedRoomResource($gatedRoom->load('host'));
    }

    public function join(Request $request, GatedRoom $gatedRoom): JsonResponse
    {
        $this->authorize('join', $gatedRoom);

        $result = $this->roomService->joinRoom($request->user(), $gatedRoom);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Successfully joined the room']);
    }

    public function start(GatedRoom $gatedRoom): JsonResponse
    {
        $this->authorize('update', $gatedRoom);

        $updated = $this->roomService->startRoom($gatedRoom);

        return response()->json([
            'message' => 'Room is now live',
            'room'    => new GatedRoomResource($updated),
        ]);
    }

    public function end(GatedRoom $gatedRoom): JsonResponse
    {
        $this->authorize('update', $gatedRoom);

        $updated = $this->roomService->endRoom($gatedRoom);

        return response()->json([
            'message' => 'Room has ended',
            'room'    => new GatedRoomResource($updated),
        ]);
    }
}
