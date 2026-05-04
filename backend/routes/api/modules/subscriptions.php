<?php

use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscriptions/change', [SubscriptionController::class, 'change']);
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/resume', [SubscriptionController::class, 'resume']);
    Route::get('/subscriptions/portal', [SubscriptionController::class, 'portal']);
});
