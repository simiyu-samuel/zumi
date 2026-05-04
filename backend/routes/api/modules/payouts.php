<?php

use App\Http\Controllers\Api\PayoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('payouts')->group(function () {
    Route::post('/onboard', [PayoutController::class, 'onboard']);
    Route::post('/withdraw', [PayoutController::class, 'withdraw']);
});
