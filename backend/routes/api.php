<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\MultiplayerGameController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

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

    // Multiplayer Lobby routes (zaštićene)
    Route::get('/lobbies', [LobbyController::class, 'index']);
    Route::post('/lobbies', [LobbyController::class, 'store']);
    Route::get('/lobbies/{id}', [LobbyController::class, 'show']);
    Route::post('/lobbies/join/{code}', [LobbyController::class, 'join']);
    Route::post('/lobbies/{id}/start', [LobbyController::class, 'start']);
    Route::post('/lobbies/{id}/leave', [LobbyController::class, 'leave']);

    // Multiplayer Game routes (zaštićene)
    Route::get('/lobbies/{lobbyId}/game-state', [MultiplayerGameController::class, 'getGameState']);
    Route::post('/lobbies/{lobbyId}/submit-answer', [MultiplayerGameController::class, 'submitAnswer']);
    Route::post('/lobbies/{lobbyId}/complete', [MultiplayerGameController::class, 'completeGame']);

    // Admin routes (samo za admin korisnike)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('users', AdminUserController::class);
        Route::get('users/{id}/stats', [AdminUserController::class, 'show']);

        // Role & Permission routes
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{id}', [RoleController::class, 'show']);
        Route::put('users/{id}/role', [AdminUserController::class, 'updateRole']);
    });

    // Super Admin only routes
    Route::middleware('super_admin')->prefix('admin')->group(function () {
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{id}', [RoleController::class, 'update']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);
    });
});
