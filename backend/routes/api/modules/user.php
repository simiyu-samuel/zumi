<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Public Profile
Route::get('/users/{username}', [ProfileController::class, 'show']);

// Protected User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/users/me', [ProfileController::class, 'update']);
    
    // Media & Onboarding
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/user/banner', [ProfileController::class, 'uploadBanner']);
    Route::post('/user/onboarding', [ProfileController::class, 'completeOnboarding']);
    
    Route::get('/users/search', [ProfileController::class, 'search']);
    Route::get('/user/flow-score', [ProfileController::class, 'flowScore']);
});
