<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FollowService;
use App\Repositories\Interfaces\FollowRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService,
        protected FollowRepositoryInterface $followRepository
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
    public function followers(Request $request, User $user): JsonResponse
    {
        $followers = $this->followRepository->getFollowers(
            $user, 
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        
        return response()->json(UserResource::collection($followers)->response()->getData(true));
    }

    /**
     * Get following of a user.
     */
    public function following(Request $request, User $user): JsonResponse
    {
        $following = $this->followRepository->getFollowing(
            $user, 
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        
        return response()->json(UserResource::collection($following)->response()->getData(true));
    }
}
