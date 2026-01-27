<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// External API proxy routes (public)
Route::get('/external/premade-quizzes', [ApiController::class, 'premadeQuizzes']);
Route::get('/external/premade-quizzes/{quizId}', [ApiController::class, 'getPremadeQuizQuestions']);

// Public quiz routes (može se vidjeti lista kvizova bez prijave)
Route::get('/quizzes', [QuizController::class, 'index']);
Route::get('/quizzes/{id}', [QuizController::class, 'show']);

// Public question routes
Route::get('/questions', [QuestionController::class, 'index']);
Route::get('/questions/{id}', [QuestionController::class, 'show']);

// Public option routes
Route::get('/options', [OptionController::class, 'index']);
Route::get('/options/{id}', [OptionController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Quiz CRUD routes (zaštićene)
    Route::post('/quizzes', [QuizController::class, 'store']);
    Route::put('/quizzes/{id}', [QuizController::class, 'update']);
    Route::delete('/quizzes/{id}', [QuizController::class, 'destroy']);

    // Question CRUD routes (zaštićene)
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
    Route::post('/quizzes/{id}/questions/reorder', [QuestionController::class, 'reorder']);

    // Option CRUD routes (zaštićene)
    Route::post('/options', [OptionController::class, 'store']);
    Route::put('/options/{id}', [OptionController::class, 'update']);
    Route::delete('/options/{id}', [OptionController::class, 'destroy']);

    // Quiz Attempt routes (zaštićene)
    Route::post('/quizzes/{id}/start', [AttemptController::class, 'start']);
    Route::post('/attempts/{id}/submit', [AttemptController::class, 'submit']);
    Route::get('/attempts/{id}/results', [AttemptController::class, 'results']);
    Route::get('/quizzes/{id}/attempts', [AttemptController::class, 'userAttempts']);
    Route::post('/attempts/answer', [AttemptController::class, 'submitAnswer']);

    // File Upload routes (zaštićene)
    Route::post('/upload/image', [FileUploadController::class, 'uploadImage']);
    Route::post('/upload/pdf', [FileUploadController::class, 'uploadPdf']);

    // Admin routes (samo za admin korisnike)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('users', AdminUserController::class);
        Route::get('users/{id}/stats', [AdminUserController::class, 'show']);
    });
});
