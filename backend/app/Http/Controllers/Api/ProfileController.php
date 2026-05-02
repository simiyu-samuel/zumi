<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
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

        $user->update($request->only(['name', 'username', 'bio']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh(),
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'], // 2MB Max
        ]);

        $user = $request->user();
        $user->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return response()->json([
            'message'    => 'Avatar uploaded successfully',
            'avatar_url' => $user->getFirstMediaUrl('avatar'),
        ]);
    }

    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner' => ['required', 'image', 'max:5120'], // 5MB Max
        ]);

        $user = $request->user();
        $user->addMediaFromRequest('banner')
            ->toMediaCollection('banner');

        return response()->json([
            'message'    => 'Banner uploaded successfully',
            'banner_url' => $user->getFirstMediaUrl('banner'),
        ]);
    }
}
