<?php

use App\Http\Controllers\Api\ChallengeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('challenges')->group(function () {
    Route::get('/', [ChallengeController::class, 'index']);
    Route::post('/', [ChallengeController::class, 'store']);
    Route::get('/{challenge}', [ChallengeController::class, 'show']);
    Route::post('/{challenge}/join', [ChallengeController::class, 'join']);
    Route::post('/participations/{participationId}/vote', [ChallengeController::class, 'vote']);
    Route::post('/{challenge}/close', [ChallengeController::class, 'close']);
});
