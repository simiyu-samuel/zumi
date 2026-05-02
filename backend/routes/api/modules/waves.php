<?php

use App\Http\Controllers\Api\WaveController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/waves/initialize-upload', [WaveController::class, 'initializeUpload']);
    Route::apiResource('waves', WaveController::class)->except(['index', 'show']);
    Route::post('waves/{wave}/like', [WaveController::class, 'like']);
    Route::post('waves/{wave}/purchase', [WaveController::class, 'purchase']);
});

// Public routes
Route::get('waves', [WaveController::class, 'index']);
Route::get('waves/{id}', [WaveController::class, 'show']);
