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
        Route::get('manage-plan', [\App\Http\Controllers\FinanceHead\ManagePlanController::class, 'index'])->name('manage-plan.index');
        Route::post('manage-plan', [\App\Http\Controllers\FinanceHead\ManagePlanController::class, 'store'])->name('manage-plan.store');
        Route::post('manage-plan/{planningYear}/sync', [\App\Http\Controllers\FinanceHead\ManagePlanController::class, 'sync'])->name('manage-plan.sync');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('degree-programs', \App\Http\Controllers\FinanceHead\Settings\DegreeProgramController::class)->except(['show']);
            Route::resource('credit-unit-price', \App\Http\Controllers\FinanceHead\Settings\CreditUnitPriceController::class, ['parameters' => ['credit-unit-price' => 'creditUnitPrice']])->only(['index', 'update']);
            Route::resource('course-credits', \App\Http\Controllers\FinanceHead\Settings\CourseCreditController::class, ['parameters' => ['course-credits' => 'courseCredit']])->except(['show']);
            Route::resource('registration-fee', \App\Http\Controllers\FinanceHead\Settings\RegistrationFeeController::class, ['parameters' => ['registration-fee' => 'registrationFee']])->only(['index', 'edit', 'update']);
            Route::resource('nuol-pct', \App\Http\Controllers\FinanceHead\Settings\NuolPctSettingController::class, ['parameters' => ['nuol-pct' => 'nuolPct']])->only(['index', 'update']);
            Route::resource('expense-patterns', \App\Http\Controllers\FinanceHead\Settings\ExpensePatternController::class, ['parameters' => ['expense-patterns' => 'expensePattern']])->only(['index', 'store', 'update']);
            Route::post('expense-patterns/{expensePattern}/fields', [\App\Http\Controllers\FinanceHead\Settings\ExpensePatternController::class, 'storeField'])->name('expense-patterns.fields.store');
            Route::patch('expense-pattern-fields/{expensePatternField}', [\App\Http\Controllers\FinanceHead\Settings\ExpensePatternController::class, 'updateField'])->name('expense-pattern-fields.update');
            Route::delete('expense-pattern-fields/{expensePatternField}', [\App\Http\Controllers\FinanceHead\Settings\ExpensePatternController::class, 'destroyField'])->name('expense-pattern-fields.destroy');
            Route::get('expense-structure', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'index'])->name('expense-structure.index');
            Route::get('expense-default-rows/accounts', [\App\Http\Controllers\FinanceHead\Settings\ExpenseDefaultRowAccountController::class, 'index'])->name('expense-default-rows.accounts.index');
            Route::post('expense-default-rows', [\App\Http\Controllers\FinanceHead\Settings\ExpenseDefaultRowAccountController::class, 'store'])->name('expense-default-rows.store');
            Route::patch('expense-default-rows/{expenseSubsectionDefaultRow}/account', [\App\Http\Controllers\FinanceHead\Settings\ExpenseDefaultRowAccountController::class, 'update'])->name('expense-default-rows.account.update');
            Route::post('expense-structure/sections', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'storeSection'])->name('expense-structure.sections.store');
            Route::patch('expense-structure/sections/{expenseSection}', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'updateSection'])->name('expense-structure.sections.update');
            Route::delete('expense-structure/sections/{expenseSection}', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'destroySection'])->name('expense-structure.sections.destroy');
            Route::post('expense-structure/sections/{expenseSection}/subsections', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'storeSubsection'])->name('expense-structure.subsections.store');
            Route::patch('expense-structure/subsections/{expenseSubsection}', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'updateSubsection'])->name('expense-structure.subsections.update');
            Route::delete('expense-structure/subsections/{expenseSubsection}', [\App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController::class, 'destroySubsection'])->name('expense-structure.subsections.destroy');
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
        Route::patch('expense/{expensePlan}/sections/{expenseSection}/summary-settings', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'updateSectionSummarySettings'])->name('expense.section-summary-settings.update');
        Route::patch('expense/{expensePlan}/subsections/{expenseSubsection}/summary-settings', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'updateSubsectionSummarySettings'])->name('expense.subsection-summary-settings.update');
        Route::patch('expense/{expensePlan}/subsections/{expenseSubsection}/field-settings', [\App\Http\Controllers\FinanceHead\ExpensePlanController::class, 'updateSubsectionFieldSettings'])->name('expense.subsection-field-settings.update');

        // Expense plan rows (AJAX dynamic fields)
        Route::post('expense-plan-rows', [\App\Http\Controllers\FinanceHead\ExpensePlanRowController::class, 'store'])->name('expense-plan-rows.store');
        Route::patch('expense-plan-rows/{expensePlanRow}', [\App\Http\Controllers\FinanceHead\ExpensePlanRowController::class, 'update'])->name('expense-plan-rows.update');
        Route::delete('expense-plan-rows/{expensePlanRow}', [\App\Http\Controllers\FinanceHead\ExpensePlanRowController::class, 'destroy'])->name('expense-plan-rows.destroy');

        // Salary Plans
        Route::resource('salary', \App\Http\Controllers\FinanceHead\SalaryPlanController::class, [
            'parameters' => ['salary' => 'salaryPlan'],
        ])->except(['edit', 'update', 'destroy']);
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
