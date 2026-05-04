<?php

use App\Http\Controllers\Api\DropsController;
use App\Http\Controllers\Api\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallet', [DropsController::class, 'index']);
    Route::post('/drops/gift', [DropsController::class, 'gift']);
    Route::post('/drops/purchase', [PurchaseController::class, 'purchaseDrops']);
});
