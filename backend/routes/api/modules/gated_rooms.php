<?php

use App\Http\Controllers\Api\GatedRoomController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rooms', [GatedRoomController::class, 'index']);
    Route::get('/rooms/my-rooms', [GatedRoomController::class, 'myRooms']);
    Route::post('/rooms', [GatedRoomController::class, 'store']);
    Route::get('/rooms/{gatedRoom}', [GatedRoomController::class, 'show']);
    Route::post('/rooms/{gatedRoom}/join', [GatedRoomController::class, 'join']);
    Route::post('/rooms/{gatedRoom}/start', [GatedRoomController::class, 'start']);
    Route::post('/rooms/{gatedRoom}/end', [GatedRoomController::class, 'end']);
});
