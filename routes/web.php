<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'player'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::inertia('quiz', 'quiz')->name('quiz');
    Route::post('quiz/attempts', [QuizAttemptController::class, 'store'])->name('quiz.attempts.store');
    Route::post('quiz/attempts/{attempt}/answers', [QuizAttemptController::class, 'answer'])->name('quiz.attempts.answer');
    Route::post('quiz/attempts/{attempt}/complete', [QuizAttemptController::class, 'complete'])->name('quiz.attempts.complete');
});

require __DIR__.'/settings.php';
