<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\PerformanceItemController;
use App\Http\Controllers\PublicSubmissionController;
use App\Http\Controllers\QuarterlyReportController;
use App\Http\Controllers\RecipientController;
use Illuminate\Support\Facades\Route;

// Public, token-protected submission (linked from emails) - no login needed
Route::get('/submit/{token}', [PublicSubmissionController::class, 'form'])->name('submit.form');
Route::post('/submit/{token}', [PublicSubmissionController::class, 'store'])->name('submit.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('items', PerformanceItemController::class)->except('show');
    Route::resource('campuses', CampusController::class)->only('index', 'store', 'update');
    Route::resource('recipients', RecipientController::class)->only('index', 'store', 'update', 'destroy');
    Route::get('evidence', [EvidenceController::class, 'index'])->name('evidence.index');
    Route::get('evidence/{submission}', [EvidenceController::class, 'show'])->name('evidence.show');
    Route::get('reports', [QuarterlyReportController::class, 'index'])->name('reports.index');
});
