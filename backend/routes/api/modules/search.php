<?php

use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

// Public search endpoints for discover page
Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'global']);
    Route::get('/suggested', [SearchController::class, 'suggestedUsers']);
});

// Authenticated search endpoints
Route::prefix('search')->middleware('auth:sanctum')->group(function () {
    Route::get('/users', [SearchController::class, 'users']);
    Route::get('/waves', [SearchController::class, 'waves']);
    Route::get('/circles', [SearchController::class, 'circles']);
});
