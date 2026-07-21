<?php

use App\Http\Controllers\Api\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/quizzes', [QuizController::class, 'index']);
Route::get('/quizzes/{quiz:slug}', [QuizController::class, 'show']);
Route::post('/quizzes/{quiz:slug}/submit', [QuizController::class, 'submit']);
