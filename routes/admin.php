<?php

use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\AttemptController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionExportController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\RankController;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'active', 'admin'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('questions/export', QuestionExportController::class)->name('questions.export');
        Route::get('questions/import', [QuestionImportController::class, 'index'])->name('question-import.index');
        Route::post('questions/import/preview', [QuestionImportController::class, 'preview'])->name('question-import.preview');
        Route::post('questions/import', [QuestionImportController::class, 'store'])->name('question-import.store');
        Route::resource('questions', QuestionController::class)->except('show');

        Route::get('players', [PlayerController::class, 'index'])->name('players.index');
        Route::get('players/{player}', [PlayerController::class, 'show'])->name('players.show');
        Route::get('players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
        Route::put('players/{player}', [PlayerController::class, 'update'])->name('players.update');
        Route::post('players/{player}/suspension', [PlayerController::class, 'suspend'])->name('players.suspend');
        Route::delete('players/{player}/suspension', [PlayerController::class, 'reactivate'])->name('players.reactivate');
        Route::post('players/{player}/verification', [PlayerController::class, 'verify'])->name('players.verify');

        Route::get('attempts', [AttemptController::class, 'index'])->name('attempts.index');
        Route::get('attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
        Route::resource('teams', TeamController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('ranks', RankController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('audit-logs', AuditLogController::class)->name('audit-logs.index');

        Route::middleware('superadmin')->group(function (): void {
            Route::get('administrators', [AdministratorController::class, 'index'])->name('administrators.index');
            Route::put('administrators/{administrator}', [AdministratorController::class, 'update'])->name('administrators.update');
        });
    });
