<?php

// routes/head_of_faculty.php
// ─────────────────────────────────────────────────────
// Only users with role_name = 'head_of_faculty' can access these.
// ─────────────────────────────────────────────────────

use App\Http\Controllers\HeadOfFaculty\HomeController;
use App\Http\Controllers\HeadOfFaculty\BudgetApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.active', 'role:head_of_faculty'])
    ->prefix('head-of-faculty')
    ->name('head_of_faculty.')
    ->group(function () {

        Route::get('/home', [HomeController::class, 'index'])->name('home');

        // ── Annual Budget Approval ──────────────────────────────────────
        Route::get('/annual-budget', [BudgetApprovalController::class, 'index'])->name('annual-budget.index');
        Route::get('/annual-budget/{annualBudget}', [BudgetApprovalController::class, 'show'])->name('annual-budget.show');
        Route::post('/annual-budget/{annualBudget}/review', [BudgetApprovalController::class, 'review'])->name('annual-budget.review');
    });
