<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle.auth');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle.auth');

// Social Login
Route::get('/auth/{provider}/redirect', [AuthController::class, 'socialRedirect'])->middleware('throttle.auth');
Route::get('/auth/{provider}/callback', [AuthController::class, 'socialCallback'])->middleware('throttle.auth');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
