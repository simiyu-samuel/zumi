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
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

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
        $users = $this->profileService->search($request->input('query'));

        return UserResource::collection($users);
    }
}
