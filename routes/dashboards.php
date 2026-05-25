<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────
// Role-based Dashboard Routes
// ─────────────────────────────────────────────────────────────────

// 1. Admin
Route::middleware(['auth', 'check.active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/home', [\App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('chart-of-accounts', \App\Http\Controllers\Admin\ChartOfAccountController::class);
    });

// 2. Finance Head
Route::middleware(['auth', 'check.active', 'role:head_of_finance'])
    ->prefix('head-of-finance')
    ->name('head_of_finance.')
    ->group(function () {
        Route::get('/home', [\App\Http\Controllers\FinanceHead\HomeController::class, 'index'])->name('home');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('degree-programs', \App\Http\Controllers\FinanceHead\Settings\DegreeProgramController::class)->except(['show']);
            Route::resource('credit-unit-price', \App\Http\Controllers\FinanceHead\Settings\CreditUnitPriceController::class, ['parameters' => ['credit-unit-price' => 'creditUnitPrice']])->only(['index', 'edit', 'update']);
            Route::resource('course-credits', \App\Http\Controllers\FinanceHead\Settings\CourseCreditController::class, ['parameters' => ['course-credits' => 'courseCredit']])->except(['show']);
            Route::resource('registration-fee', \App\Http\Controllers\FinanceHead\Settings\RegistrationFeeController::class, ['parameters' => ['registration-fee' => 'registrationFee']])->except(['show']);
            Route::resource('nuol-pct', \App\Http\Controllers\FinanceHead\Settings\NuolPctSettingController::class, ['parameters' => ['nuol-pct' => 'nuolPct']])->except(['show']);
            // Income Rate Settings (single-page: index + one patch to update all 4 keys)
            Route::get('income-rates', [\App\Http\Controllers\FinanceHead\Settings\IncomeRateSettingController::class, 'index'])->name('income-rates.index');
            Route::patch('income-rates', [\App\Http\Controllers\FinanceHead\Settings\IncomeRateSettingController::class, 'update'])->name('income-rates.update');
        });

        // Academic Income
        Route::resource('academic-income', \App\Http\Controllers\FinanceHead\AcademicIncomePlanController::class, ['parameters' => ['academic-income' => 'academicIncome']])->except(['edit', 'update']);
        Route::get('academic-income/{academicIncome}/evaluate', [\App\Http\Controllers\FinanceHead\AcademicIncomeAssessmentController::class, 'evaluate'])->name('academic-income.evaluate');
        Route::post('academic-income/{academicIncome}/evaluate', [\App\Http\Controllers\FinanceHead\AcademicIncomeAssessmentController::class, 'saveEvaluate'])->name('academic-income.saveEvaluate');

        // Expense Plans
        Route::resource('expense', \App\Http\Controllers\FinanceHead\ExpensePlanController::class, [
            'parameters' => ['expense' => 'expensePlan'],
        ])->except(['edit', 'update']);
        Route::get('expense/{expensePlan}/manage', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'manage'])->name('expense.manage');
        Route::post('expense/{expensePlan}/approve', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'approve'])->name('expense.approve');

        Route::post('expense-categories', [\App\Http\Controllers\FinanceHead\ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::patch('expense-categories/{expenseCategory}', [\App\Http\Controllers\FinanceHead\ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::delete('expense-categories/{expenseCategory}', [\App\Http\Controllers\FinanceHead\ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');

        Route::post('expense-items', [\App\Http\Controllers\FinanceHead\ExpenseItemController::class, 'store'])->name('expense-items.store');
        Route::patch('expense-items/{expenseItem}', [\App\Http\Controllers\FinanceHead\ExpenseItemController::class, 'update'])->name('expense-items.update');
        Route::delete('expense-items/{expenseItem}', [\App\Http\Controllers\FinanceHead\ExpenseItemController::class, 'destroy'])->name('expense-items.destroy');

        // Annual Report (multi-module PDF)
        Route::get('reports/{year}', [\App\Http\Controllers\FinanceHead\AnnualReportController::class, 'show'])
             ->name('reports.show')
             ->where('year', '[0-9]{4}');

        // Salary Plans
        Route::resource('salary', \App\Http\Controllers\FinanceHead\SalaryPlanController::class, [
            'parameters' => ['salary' => 'salaryPlan'],
        ])->except(['edit', 'update']);
        Route::get('salary/{salaryPlan}/manage', [\App\Http\Controllers\FinanceHead\SalaryPlanController::class, 'manage'])->name('salary.manage');
        Route::post('salary/{salaryPlan}/approve', [\App\Http\Controllers\FinanceHead\SalaryPlanController::class, 'approve'])->name('salary.approve');

        // Salary Entries (AJAX)
        Route::patch('salary-entries/{salaryEntry}', [\App\Http\Controllers\FinanceHead\SalaryEntryController::class, 'update'])->name('salary-entries.update');
    });

// 3. Faculty Head
Route::middleware(['auth', 'check.active', 'role:head_of_faculty'])
    ->prefix('head-of-faculty')
    ->name('head_of_faculty.')
    ->group(function () {
        Route::get('/home', [\App\Http\Controllers\FacultyHead\HomeController::class, 'index'])->name('home');
    });

// 4. Faculty Deputy
Route::middleware(['auth', 'check.active', 'role:deputy_head_of_faculty'])
    ->prefix('deputy-head-of-faculty')
    ->name('deputy_head_of_faculty.')
    ->group(function () {
        Route::get('/home', [\App\Http\Controllers\FacultyDeputy\HomeController::class, 'index'])->name('home');
    });

// 5. Accountant
Route::middleware(['auth', 'check.active', 'role:accountant'])
    ->prefix('accountant')
    ->name('accountant.')
    ->group(function () {
        Route::get('/home', [\App\Http\Controllers\Accountant\HomeController::class, 'index'])->name('home');
    });
