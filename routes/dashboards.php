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
            Route::resource('credit-unit-price', \App\Http\Controllers\FinanceHead\Settings\CreditUnitPriceController::class, ['parameters' => ['credit-unit-price' => 'creditUnitPrice']])->only(['index', 'update']);
            Route::resource('course-credits', \App\Http\Controllers\FinanceHead\Settings\CourseCreditController::class, ['parameters' => ['course-credits' => 'courseCredit']])->except(['show']);
            Route::resource('registration-fee', \App\Http\Controllers\FinanceHead\Settings\RegistrationFeeController::class, ['parameters' => ['registration-fee' => 'registrationFee']])->only(['index', 'edit', 'update']);
            Route::resource('nuol-pct', \App\Http\Controllers\FinanceHead\Settings\NuolPctSettingController::class, ['parameters' => ['nuol-pct' => 'nuolPct']])->only(['index', 'update']);
            // Income rates (items 3–6) are now edited inline on the academic-income entry page.
        });

        // Academic Income
        Route::resource('academic-income', \App\Http\Controllers\FinanceHead\AcademicIncomePlanController::class, ['parameters' => ['academic-income' => 'academicIncome']])->except(['edit', 'update']);
        Route::get('academic-income/{academicIncome}/evaluate', [\App\Http\Controllers\FinanceHead\AcademicIncomeAssessmentController::class, 'evaluate'])->name('academic-income.evaluate');
        Route::post('academic-income/{academicIncome}/evaluate', [\App\Http\Controllers\FinanceHead\AcademicIncomeAssessmentController::class, 'saveEvaluate'])->name('academic-income.saveEvaluate');

        // Expense Plans
        Route::resource('expense', \App\Http\Controllers\FinanceHead\ExpensePlanController::class, [
            'parameters' => ['expense' => 'expensePlan'],
        ])->except(['edit', 'update', 'show']);
        Route::get('expense/{expensePlan}/manage', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'manage'])->name('expense.manage');
        Route::post('expense/{expensePlan}/approve', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'approve'])->name('expense.approve');

        // Flat expense entries (AJAX inline grid)
        Route::post('expense-entries', [\App\Http\Controllers\FinanceHead\ExpenseEntryController::class, 'store'])->name('expense-entries.store');
        Route::patch('expense-entries/{expenseEntry}', [\App\Http\Controllers\FinanceHead\ExpenseEntryController::class, 'update'])->name('expense-entries.update');
        Route::delete('expense-entries/{expenseEntry}', [\App\Http\Controllers\FinanceHead\ExpenseEntryController::class, 'destroy'])->name('expense-entries.destroy');

        // Ref-code configured list (managed via modal on the manage page)
        Route::post('expense-ref-codes', [\App\Http\Controllers\FinanceHead\ExpenseRefCodeController::class, 'store'])->name('expense-ref-codes.store');
        Route::patch('expense-ref-codes/{expenseRefCode}', [\App\Http\Controllers\FinanceHead\ExpenseRefCodeController::class, 'update'])->name('expense-ref-codes.update');
        Route::delete('expense-ref-codes/{expenseRefCode}', [\App\Http\Controllers\FinanceHead\ExpenseRefCodeController::class, 'destroy'])->name('expense-ref-codes.destroy');

        // Salary Plans
        Route::resource('salary', \App\Http\Controllers\FinanceHead\SalaryPlanController::class, [
            'parameters' => ['salary' => 'salaryPlan'],
        ])->except(['edit', 'update']);
        Route::get('salary/{salaryPlan}/manage', [\App\Http\Controllers\FinanceHead\SalaryPlanController::class, 'manage'])->name('salary.manage');

        // Salary Entries (AJAX)
        Route::post('salary-entries',                [\App\Http\Controllers\FinanceHead\SalaryEntryController::class, 'store'])->name('salary-entries.store');
        Route::patch('salary-entries/{salaryEntry}', [\App\Http\Controllers\FinanceHead\SalaryEntryController::class, 'update'])->name('salary-entries.update');
        Route::delete('salary-entries/{salaryEntry}',[\App\Http\Controllers\FinanceHead\SalaryEntryController::class, 'destroy'])->name('salary-entries.destroy');
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
