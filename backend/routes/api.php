<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/modules/auth.php';
    require __DIR__ . '/api/modules/user.php';
    require __DIR__ . '/api/modules/waves.php';
    require __DIR__ . '/api/modules/drops.php';
    require __DIR__ . '/api/modules/follow.php';
    require __DIR__ . '/api/modules/comments.php';
    require __DIR__ . '/api/modules/circles.php';
    require __DIR__ . '/api/modules/webhooks.php';
});
