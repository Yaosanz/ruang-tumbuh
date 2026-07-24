<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OptionsController;
use App\Http\Controllers\Api\QuestionsController;
use App\Http\Controllers\Api\QuizzesController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SubmissionsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/quizzes', [QuizController::class, 'index']);
Route::get('/quizzes/{quiz:slug}', [QuizController::class, 'show']);
Route::post('/quizzes/{quiz:slug}/submit', [QuizController::class, 'submit']);

// Authenticated endpoints
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'me']);

    // CRUD Users
    Route::apiResource('users', UsersController::class);

    // CRUD Quizzes
    Route::apiResource('quizzes-crud', QuizzesController::class)->parameters(['quizzes-crud' => 'quiz']);

    // Nested CRUD: Questions under Quizzes
    Route::apiResource('quizzes-crud.questions', QuestionsController::class)
        ->parameters(['quizzes-crud' => 'quiz', 'questions' => 'question']);

    // Nested CRUD: Options under Questions under Quizzes
    Route::apiResource('quizzes-crud.questions.options', OptionsController::class)
        ->parameters(['quizzes-crud' => 'quiz', 'questions' => 'question', 'options' => 'option']);

    // CRUD Submissions
    Route::apiResource('submissions', SubmissionsController::class)->only(['index', 'show', 'destroy']);
});
