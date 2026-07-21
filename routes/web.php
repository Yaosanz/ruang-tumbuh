<?php

use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'quiz-index')->name('quizzes.index');
Route::get('/quiz/{quiz:slug}', fn (Quiz $quiz) => view('quiz-take', compact('quiz')))->name('quizzes.take');
Route::get('/result/{submission}', fn (Submission $submission) => view('result', compact('submission')))->name('results.show');

// Auth routes (user)
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'auth.dashboard')->name('dashboard');
});

// Admin routes
Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::middleware('admin')->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::get('/admin/quizzes/{quiz?}', fn (?Quiz $quiz = null) => view('admin.editor', compact('quiz')))->name('admin.quizzes.editor');
});
