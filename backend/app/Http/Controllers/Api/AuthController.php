<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->login($request->email, $request->password);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    public function socialRedirect($provider)
    {
        return \Laravel\Socialite\Facades\Socialite::driver($provider)->stateless()->redirect();
    }

    public function socialCallback($provider)
    {
        $socialUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->stateless()->user();
        $user = $this->authService->handleSocialCallback($provider, $socialUser);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }
}
