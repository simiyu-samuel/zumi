<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Requests\Profile\UploadBannerRequest;
use App\Http\Requests\Wave\SearchRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use App\Services\FlowScoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService,
        protected FlowScoreService $flowScoreService,
    ) {}

    public function show($username)
    {
        $user = $this->profileService->findByUsername($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $this->profileService->updateProfile($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => new UserResource($user),
        ]);
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $avatarUrl = $this->profileService->uploadMedia($request->user(), $request->file('avatar'), User::COLLECTION_AVATAR);

        return response()->json([
            'message'    => 'Avatar uploaded successfully',
            'avatar_url' => $avatarUrl,
        ]);
    }

    public function uploadBanner(UploadBannerRequest $request)
    {
        $bannerUrl = $this->profileService->uploadMedia($request->user(), $request->file('banner'), User::COLLECTION_BANNER);

        return response()->json([
            'message'    => 'Banner uploaded successfully',
            'banner_url' => $bannerUrl,
        ]);
    }

    public function completeOnboarding(Request $request)
    {
        $user = $this->profileService->updateProfile($request->user(), ['onboarding_completed' => true]);

        return response()->json([
            'message' => 'Onboarding completed',
            'user'    => new UserResource($user),
        ]);
    }

    public function search(SearchRequest $request)
    {
        $users = $this->profileService->search(
            $request->input('query'),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return UserResource::collection($users);
    }

    public function flowScore(Request $request): JsonResponse
    {
        $summary = $this->flowScoreService->summary($request->user());

        return response()->json($summary);
    }

    public function updateNotificationSettings(\App\Http\Requests\Profile\UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $user = $this->profileService->updateNotificationSettings($request->user(), $request->validated('settings'));

        return response()->json([
            'message' => 'Notification preferences updated successfully.',
            'notification_settings' => $user->notification_settings,
        ]);
    }
}
