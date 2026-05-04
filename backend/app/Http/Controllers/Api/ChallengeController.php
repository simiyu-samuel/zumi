<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Challenge\StoreChallengeRequest;
use App\Http\Requests\Challenge\JoinChallengeRequest;
use App\Http\Resources\ChallengeResource;
use App\Http\Resources\UserResource;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChallengeController extends Controller
{
    public function __construct(
        protected ChallengeService $challengeService
    ) {}

    /**
     * Display a listing of the active challenges.
     */
    public function index(Request $request)
    {
        $challenges = $this->challengeService->getActiveChallenges($request->input('per_page', 15));
        
        return ChallengeResource::collection($challenges);
    }

    /**
     * Store a newly created challenge.
     */
    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $result = $this->challengeService->createChallenge($request->user(), $request->validated());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message'   => 'Challenge created successfully',
            'challenge' => new ChallengeResource($result['challenge']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified challenge.
     */
    public function show(Challenge $challenge): ChallengeResource
    {
        return new ChallengeResource($challenge->load(['user', 'winner', 'participations.user', 'participations.wave']));
    }

    /**
     * Join a challenge by submitting a response wave.
     */
    public function join(JoinChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $this->authorize('join', $challenge);

        $result = $this->challengeService->joinChallenge(
            $request->user(),
            $challenge,
            $request->wave_id
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Joined challenge successfully']);
    }

    /**
     * Vote for a participation entry.
     */
    public function vote(Request $request, string $participationId): JsonResponse
    {
        $participation = \App\Models\ChallengeParticipation::findOrFail($participationId);
        $this->authorize('vote', $participation->challenge);

        $result = $this->challengeService->vote($request->user(), $participationId);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Vote recorded successfully']);
    }

    /**
     * Manually close a challenge (normally would be a scheduled task).
     */
    public function close(Challenge $challenge): JsonResponse
    {
        $this->authorize('close', $challenge);

        $result = $this->challengeService->closeChallenge($challenge);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => $result['message'] ?? 'Challenge closed successfully',
            'winner'  => isset($result['winner']) ? new UserResource($result['winner']) : null,
        ]);
    }
}
