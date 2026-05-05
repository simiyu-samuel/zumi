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
    require __DIR__ . '/api/modules/payouts.php';
    require __DIR__ . '/api/modules/challenges.php';
    require __DIR__ . '/api/modules/skill_drops.php';
    require __DIR__ . '/api/modules/gated_rooms.php';
    require __DIR__ . '/api/modules/subscriptions.php';
    require __DIR__ . '/api/modules/webhooks.php';
    require __DIR__ . '/api/modules/search.php';
    require __DIR__ . '/api/modules/moderation.php';
    require __DIR__ . '/api/modules/notifications.php';
});
