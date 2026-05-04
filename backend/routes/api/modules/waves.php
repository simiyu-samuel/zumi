<?php

use App\Http\Controllers\Api\WaveController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/waves/followed', [WaveController::class, 'followedFeed']);
    Route::post('/waves/initialize-upload', [WaveController::class, 'initializeUpload']);
    Route::apiResource('waves', WaveController::class)->except(['index', 'show']);
    Route::post('waves/{wave}/like', [WaveController::class, 'like']);
    Route::post('waves/{wave}/purchase', [WaveController::class, 'purchase']);
    Route::post('waves/{wave}/view', [WaveController::class, 'recordView']);
    Route::post('waves/{wave}/share', [WaveController::class, 'share']);
    Route::post('waves/{wave}/bookmark', [WaveController::class, 'toggleBookmark']);
    Route::get('waves/bookmarks', [WaveController::class, 'bookmarks']);
});

// Public routes
Route::get('waves', [WaveController::class, 'index']);
Route::get('waves/{id}', [WaveController::class, 'show']);
