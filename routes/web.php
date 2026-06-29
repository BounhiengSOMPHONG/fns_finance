<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::redirect('/', '/login');

// ─────────────────────────────────────────────────────────────────
// Authenticated routes (all roles)
// ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.active'])->group(function () {

    // Smart redirect: after login, send each user to their own home page
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─────────────────────────────────────────────────────────────────
// Role-specific routes (each in their own file)
// ─────────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
require __DIR__ . '/dashboards.php';
