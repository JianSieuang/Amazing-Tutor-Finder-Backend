<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TutorController;

Route::middleware(['auth:sanctum'])->put('/user', [UserController::class, 'update']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Update Image
Route::middleware(['auth:sanctum'])->post('/users/{user_id}/image', [UserController::class, 'updateImage']);

// Change password
Route::middleware(['auth:sanctum'])->post('/user/changePassword', [UserController::class, 'changePassword']);

// Link Email
Route::middleware(['auth:sanctum'])->post('/user/linkEmail', [UserController::class, 'linkEmail']);

// Tutor
Route::get('/tutors', [TutorController::class, 'index']);
Route::get('/tutors/pending', [TutorController::class, 'pendingTutors']);
Route::post('/tutors/register', [TutorController::class, 'register']);
Route::middleware(['auth:sanctum'])->post('/tutors/{tutor_id}/status', [TutorController::class, 'updateStatus']);