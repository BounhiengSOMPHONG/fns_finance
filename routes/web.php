<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// ─────────────────────────────────────────────────────────────────
// Authenticated routes (all roles)
// ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.active'])->group(function () {

    // Smart redirect: after login, send each user to their own home page
    Route::get('/dashboard', function () {
        $role = auth()->user()->role?->role_name;

        return match ($role) {
            'admin' => redirect()->route('admin.home'),
            'head_of_finance' => redirect()->route('head_of_finance.home'),
            'accountant' => redirect()->route('accountant.home'),
            'deputy_head_of_faculty' => redirect()->route('deputy_head_of_faculty.home'),
            'head_of_faculty' => redirect()->route('head_of_faculty.home'),
            default => abort(403, 'ไม่พบบทบาทของคุณ'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─────────────────────────────────────────────────────────────────
// Role-specific routes (each in their own file)
// ─────────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
require __DIR__ . '/dashboards.php';
