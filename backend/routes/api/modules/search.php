<?php

use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SearchController::class, 'global']);
    Route::get('/users', [SearchController::class, 'users']);
    Route::get('/suggested', [SearchController::class, 'suggestedUsers']);
    Route::get('/waves', [SearchController::class, 'waves']);
    Route::get('/circles', [SearchController::class, 'circles']);
});
