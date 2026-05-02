<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/user/banner', [ProfileController::class, 'uploadBanner']);
    Route::post('/user/onboarding', [ProfileController::class, 'completeOnboarding']);
    Route::get('/users/search', [ProfileController::class, 'search']);
});
