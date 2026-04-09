<?php

// routes/head_of_finance.php
// ─────────────────────────────────────────────────────
// Only users with role_name = 'head_of_finance' can access these.
// ─────────────────────────────────────────────────────

use App\Http\Controllers\HeadOfFinance\AnnualBudgetPlanController;
use App\Http\Controllers\HeadOfFinance\HomeController;
use App\Http\Controllers\HeadOfFinance\PlansCtrl;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.active', 'role:head_of_finance'])
    ->prefix('head-of-finance')
    ->name('head_of_finance.')
    ->group(function () {

        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/plans', [PlansCtrl::class, 'plans'])->name('plans');

        // ── Annual Budget Plans ──────────────────────────────────────────
        Route::resource('annual-budget', AnnualBudgetPlanController::class);
        Route::get('annual-budget/{annualBudget}/pdf', [AnnualBudgetPlanController::class, 'exportPdf'])->name('annual-budget.pdf');
        Route::get('annual-budget/{annualBudget}/preview', [AnnualBudgetPlanController::class, 'preview'])->name('annual-budget.preview');

        // Workflow actions
        Route::post('annual-budget/{annualBudget}/submit', [AnnualBudgetPlanController::class, 'submit'])->name('annual-budget.submit');
        Route::post('annual-budget/{annualBudget}/unsubmit', [AnnualBudgetPlanController::class, 'unsubmit'])->name('annual-budget.unsubmit');
        Route::post('annual-budget/{annualBudget}/start-modifying', [AnnualBudgetPlanController::class, 'startModifying'])->name('annual-budget.start-modifying');
        Route::post('annual-budget/{annualBudget}/submit-final', [AnnualBudgetPlanController::class, 'submitForFinalApproval'])->name('annual-budget.submit-final');

        // Comments
        Route::post('annual-budget/{annualBudget}/comments/{comment}/mark', [AnnualBudgetPlanController::class, 'markComment'])->name('annual-budget.comments.mark');

        // Line item sub-routes
        Route::post(
            'annual-budget/{annualBudget}/items',
            [AnnualBudgetPlanController::class, 'storeItem']
        )->name('annual-budget.items.store');

        Route::post(
            'annual-budget/{annualBudget}/items/bulk',
            [AnnualBudgetPlanController::class, 'storeBulkItems']
        )->name('annual-budget.items.bulk');

        Route::put(
            'annual-budget/{annualBudget}/items/{item}',
            [AnnualBudgetPlanController::class, 'updateItem']
        )->name('annual-budget.items.update');

        Route::delete(
            'annual-budget/{annualBudget}/items/{item}',
            [AnnualBudgetPlanController::class, 'destroyItem']
        )->name('annual-budget.items.destroy');
    });
