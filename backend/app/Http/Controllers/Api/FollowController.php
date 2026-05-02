<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService
    ) {}

    /**
     * Follow a user.
     */
    public function follow(Request $request, User $user): JsonResponse
    {
        $success = $this->followService->follow($request->user(), $user);

        if (!$success) {
            return response()->json([
                'message' => 'Unable to follow user.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => "You are now following {$user->username}.",
        ], Response::HTTP_OK);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, User $user): JsonResponse
    {
        $this->followService->unfollow($request->user(), $user);

        return response()->json([
            'message' => "You have unfollowed {$user->username}.",
        ], Response::HTTP_OK);
    }

    /**
     * Get followers of a user.
     */
    public function followers(User $user): JsonResponse
    {
        $followers = $user->followers()->paginate(20);
        return response()->json($followers);
    }

    /**
     * Get following of a user.
     */
    public function following(User $user): JsonResponse
    {
        $following = $user->following()->paginate(20);
        return response()->json($following);
    }
}
