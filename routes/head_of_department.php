<?php

use App\Http\Controllers\HeadOfDepartment\HomeController;
use App\Http\Controllers\HeadOfDepartment\BudgetReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.active'])
    ->prefix('head-of-department')
    ->name('head_of_department.')
    ->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('role:head_of_department');
        
        Route::get('/annual-budget', [BudgetReviewController::class, 'index'])->name('annual-budget.index');
        Route::get('/annual-budget/{annualBudget}', [BudgetReviewController::class, 'show'])->name('annual-budget.show');
        Route::post('/annual-budget/{annualBudget}/review', [BudgetReviewController::class, 'review'])->name('annual-budget.review');
    });
