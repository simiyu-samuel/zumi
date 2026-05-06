<?php

use App\Http\Controllers\Api\TrendingController;
use Illuminate\Support\Facades\Route;

// Public trending data
Route::get('/trending', [TrendingController::class, 'index']);
