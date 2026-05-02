<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio'      => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $this->profileService->updateProfile($user, $request->only(['name', 'username', 'bio']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => new UserResource($user),
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $avatarUrl = $this->profileService->uploadMedia($request->user(), $request->file('avatar'), User::COLLECTION_AVATAR);

        return response()->json([
            'message'    => 'Avatar uploaded successfully',
            'avatar_url' => $avatarUrl,
        ]);
    }

    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner' => ['required', 'image', 'max:5120'],
        ]);

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

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $users = $this->profileService->search($request->input('query'));

        return UserResource::collection($users);
    }
}
