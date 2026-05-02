<?php

use App\Http\Controllers\Api\WaveController;
use Illuminate\Support\Facades\Route;

// Public Feed
Route::get('/waves', [WaveController::class, 'index']);

// Protected Actions
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/waves', [WaveController::class, 'store']);
    Route::post('/waves/{id}/like', [WaveController::class, 'toggleLike']);
});
