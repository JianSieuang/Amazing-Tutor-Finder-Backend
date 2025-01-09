<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TutorController;

Route::middleware(['auth:sanctum'])->put('/user', [UserController::class, 'update']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tutors', [TutorController::class, 'index']);
Route::get('/tutors/pending', [TutorController::class, 'pendingTutors']);
Route::post('/tutors/register', [TutorController::class, 'register']);