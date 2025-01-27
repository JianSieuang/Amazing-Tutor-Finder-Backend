<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TutorController;
use App\Models\Parents;

Route::middleware(['auth:sanctum'])->put('/user', [UserController::class, 'update']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Update Image
Route::middleware(['auth:sanctum'])->post('/users/{user_id}/image', [UserController::class, 'updateImage']);

// Change password
Route::middleware(['auth:sanctum'])->post('/user/changePassword', [UserController::class, 'changePassword']);

// Linked Account
Route::middleware(['auth:sanctum'])->post('/user/linkEmail', [UserController::class, 'linkEmail']);
Route::middleware(['auth:sanctum'])->get('/users/{user_id}/linkAccount', [UserController::class, 'getLinkedAccounts']);
Route::post('/linkAccount/{link_account_id}/status', [UserController::class, 'updateLinkAccountStatus']);
Route::middleware(['auth:sanctum'])->post('/users/{user_id}/unlinkAccount', [UserController::class, 'unlinkAccount']);
Route::middleware(['auth:sanctum'])->post('/tutors/{user_id}/edit', [TutorController::class, 'editTutor']);

// Tutor
Route::get('/tutors', [TutorController::class, 'index']);
Route::get('/tutors/pending', [TutorController::class, 'pendingTutors']);
Route::post('/tutors/register', [TutorController::class, 'register']);
Route::get('/tutors/{tutor_id}', [TutorController::class, 'tutorDetails']);
Route::middleware(['auth:sanctum'])->post('/tutors/{tutor_id}/status', [TutorController::class, 'updateStatus']);