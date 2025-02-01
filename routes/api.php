<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\StripeController;

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
Route::middleware(['auth:sanctum'])->post('/tutors/{tutor_id}/add_session', [TutorController::class, 'addSession']);
Route::get('/tutors/{tutor_id}/sessions', [TutorController::class, 'getSessions']);
Route::get('/tutors/{tutor_id}/dashboard', [TutorController::class, 'getDashboard']);
Route::get('/tutors/{tutor_id}/schedule', [TutorController::class, 'getSchedule']);

// Admin Dashboard
Route::get('/admin/dashboard', [UserController::class, 'getAdminDashboard']);
Route::middleware(['auth:sanctum'])->post('/admin/social_media', [UserController::class, 'updateSocialMedia']);
Route::get('/social_media', [UserController::class, 'getSocialMedia']);

// Stripe Payment Gateway
Route::post('/payment', [StripeController::class, 'checkout']);
Route::get('/success', [StripeController::class, 'success'])->name('success');
Route::get('/cancel', [StripeController::class, 'cancel'])->name('cancel');

// Parent Purchase History
Route::get('/parent/{id}/purchaseHistory', [UserController::class, 'getPurchaseHistory']);

// Rating Tutor
Route::post('/rating', [UserController::class, 'ratingTutor']);
Route::get('/tutors/{id}/rating', [UserController::class, 'getRating']);

// Enrolled Students
Route::get('/tutors/{id}/enrolled_students', [TutorController::class, 'getEnrolledStudents']);

// Enrolled Tutors 
Route::get('/student/{id}/enrolled_tutors', [TutorController::class, 'getEnrolledTutors']);

// Search Tutor
Route::get('/search', [TutorController::class, 'searchTutor']);

// Report Tutor 
Route::post('/report/tutor', [UserController::class, 'reportTutor']);
Route::get('/report/tutor', [UserController::class, 'getReport']);
Route::get('/report/tutor/{id}', [UserController::class, 'getReportById']);
Route::post('/report/{id}/submit', [UserController::class, 'submitReport']);

