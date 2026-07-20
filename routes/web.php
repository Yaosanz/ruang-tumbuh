<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'quiz-index')->name('quizzes.index');
Route::view('/quiz/{quiz:slug}', 'quiz-take')->name('quizzes.take');
Route::view('/result/{submission}', 'result')->name('results.show');
Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::middleware('admin')->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/admin/quizzes/{quiz?}', 'admin.editor')->name('admin.quizzes.editor');
});
