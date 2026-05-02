<?php

use App\Http\Controllers\Api\FollowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/{user}/follow', [FollowController::class, 'follow']);
    Route::post('/users/{user}/unfollow', [FollowController::class, 'unfollow']);
});

Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
Route::get('/users/{user}/following', [FollowController::class, 'following']);
