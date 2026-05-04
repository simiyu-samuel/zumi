<?php

use App\Http\Controllers\Api\SkillDropController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/skill-drops', [SkillDropController::class, 'index']);
    Route::get('/skill-drops/library', [SkillDropController::class, 'library']);
    Route::get('/skill-drops/my-drops', [SkillDropController::class, 'myDrops']);
    Route::post('/skill-drops', [SkillDropController::class, 'store']);
    Route::get('/skill-drops/{skillDrop}', [SkillDropController::class, 'show']);
    Route::patch('/skill-drops/{skillDrop}', [SkillDropController::class, 'update']);
    Route::delete('/skill-drops/{skillDrop}', [SkillDropController::class, 'destroy']);
    Route::post('/skill-drops/{skillDrop}/purchase', [SkillDropController::class, 'purchase']);
});
