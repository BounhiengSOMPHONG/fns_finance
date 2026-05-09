<?php

// routes/head_of_finance.php
// ─────────────────────────────────────────────────────
// Only users with role_name = 'head_of_finance' can access these.
// ─────────────────────────────────────────────────────

use App\Http\Controllers\HeadOfFinance\AcademicIncomeController;
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

        // ── Academic Income (ຮ່າງສັງລວມລາຍຮັບວິຊາການ) ─────────────────────
        Route::get('academic-income',                                       [AcademicIncomeController::class, 'index'])          ->name('academic_income.index');
        Route::get('academic-income/create',                                [AcademicIncomeController::class, 'create'])         ->name('academic_income.create');
        Route::post('academic-income',                                      [AcademicIncomeController::class, 'store'])          ->name('academic_income.store');
        Route::post('academic-income/settings',                             [AcademicIncomeController::class, 'saveSetting'])   ->name('academic_income.settings');
        // Defaults management — must be before /{plan} to avoid route binding collision
        Route::get('academic-income/defaults',                              [AcademicIncomeController::class, 'defaults'])       ->name('academic_income.defaults');
        Route::post('academic-income/defaults',                             [AcademicIncomeController::class, 'storeDefault'])   ->name('academic_income.defaults.store');
        Route::delete('academic-income/defaults/{default}',                 [AcademicIncomeController::class, 'destroyDefault']) ->name('academic_income.defaults.destroy');
        Route::get('academic-income/{plan}',                                [AcademicIncomeController::class, 'show'])           ->name('academic_income.show');
        Route::delete('academic-income/{plan}',                             [AcademicIncomeController::class, 'destroy'])        ->name('academic_income.destroy');
        Route::get('academic-income/{plan}/summary',                        [AcademicIncomeController::class, 'summary'])        ->name('academic_income.summary');
        Route::get('academic-income/{plan}/pdf',                            [AcademicIncomeController::class, 'exportPdf'])      ->name('academic_income.pdf');
        Route::post('academic-income/{plan}/save-all',                      [AcademicIncomeController::class, 'saveAll'])        ->name('academic_income.save_all');
        Route::post('academic-income/{plan}/items',                         [AcademicIncomeController::class, 'storeItem'])      ->name('academic_income.items.store');
        Route::put('academic-income/{plan}/items/{item}',                   [AcademicIncomeController::class, 'updateItem'])     ->name('academic_income.items.update');
        Route::delete('academic-income/{plan}/items/{item}',                [AcademicIncomeController::class, 'destroyItem'])    ->name('academic_income.items.destroy');

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
        
        // ── Budget Period Installments ──────────────────────────────────
        Route::get('budget-installment', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'index'])->name('budget-installment.index');
        Route::get('budget-installment/{budgetPlan}', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'show'])->name('budget-installment.show');
        Route::get('budget-installment/{budgetPlan}/preview', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'preview'])->name('budget-installment.preview');
        Route::post('budget-installment/{budgetPlan}/save', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'save'])->name('budget-installment.save');
        
        // Budget Period Installments 3-4
        Route::get('budget-installment-34/{budgetPlan}', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'show34'])->name('budget-installment-34.show');
        Route::get('budget-installment-34/{budgetPlan}/preview', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'preview34'])->name('budget-installment-34.preview');
        Route::post('budget-installment-34/{budgetPlan}/save', [\App\Http\Controllers\HeadOfFinance\BudgetInstallmentController::class, 'save34'])->name('budget-installment-34.save');
    });
