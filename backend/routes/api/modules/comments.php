<?php

use App\Http\Controllers\Api\CommentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/waves/{wave}/comments', [CommentController::class, 'storeWave']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
});

Route::get('/waves/{wave}/comments', [CommentController::class, 'forWave']);
