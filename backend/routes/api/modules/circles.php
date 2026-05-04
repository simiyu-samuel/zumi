<?php

use App\Http\Controllers\Api\CircleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('circles')->group(function () {
    Route::get('/', [CircleController::class, 'index']);
    Route::post('/', [CircleController::class, 'store']);
    Route::get('/my', [CircleController::class, 'myCircles']);
    Route::get('/{circle}', [CircleController::class, 'show']);
    Route::post('/{circle}/join', [CircleController::class, 'join']);
    Route::post('/{circle}/leave', [CircleController::class, 'leave']);
    Route::get('/{circle}/insights', [CircleController::class, 'insights']);
});
