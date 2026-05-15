<?php

use App\Http\Controllers\Api\CircleController;
use Illuminate\Support\Facades\Route;

// Public discovery endpoints (optional auth for is_member flag)
Route::prefix('circles')->group(function () {
    Route::get('/', [CircleController::class, 'index']);
    Route::get('/{circle}', [CircleController::class, 'show']);
});

// Authenticated circle actions
Route::middleware('auth:sanctum')->prefix('circles')->group(function () {
    Route::post('/', [CircleController::class, 'store']);
    Route::get('/my', [CircleController::class, 'myCircles']);
    Route::post('/{circle}/join', [CircleController::class, 'join']);
    Route::post('/{circle}/leave', [CircleController::class, 'leave']);
    Route::get('/{circle}/insights', [CircleController::class, 'insights']);
    
    // Join Requests
    Route::get('/{circle}/requests', [CircleController::class, 'requests']);
    Route::post('/requests/{requestId}/approve', [CircleController::class, 'approveRequest']);
    Route::post('/requests/{requestId}/decline', [CircleController::class, 'declineRequest']);

    // Chat & Feed
    Route::get('/{circle}/feed', [CircleController::class, 'feed']);
    Route::get('/{circle}/messages', [CircleController::class, 'messages']);
    Route::post('/{circle}/messages', [CircleController::class, 'sendMessage']);
});
