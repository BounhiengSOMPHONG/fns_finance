<?php

use App\Http\Controllers\Admin\ChartOfAccountController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FinanceHead\AcademicIncomeAssessmentController;
use App\Http\Controllers\FinanceHead\AcademicIncomePlanController;
use App\Http\Controllers\FinanceHead\ExpensePlanController;
use App\Http\Controllers\FinanceHead\ExpensePlanRowController;
use App\Http\Controllers\FinanceHead\ManagePlanController;
use App\Http\Controllers\FinanceHead\SalaryEntryController;
use App\Http\Controllers\FinanceHead\SalaryPlanController;
use App\Http\Controllers\FinanceHead\Settings\CourseCreditController;
use App\Http\Controllers\FinanceHead\Settings\CreditUnitPriceController;
use App\Http\Controllers\FinanceHead\Settings\DegreeProgramController;
use App\Http\Controllers\FinanceHead\Settings\ExpenseDefaultRowAccountController;
use App\Http\Controllers\FinanceHead\Settings\ExpensePatternController;
use App\Http\Controllers\FinanceHead\Settings\ExpenseStructureController;
use App\Http\Controllers\FinanceHead\Settings\NuolPctSettingController;
use App\Http\Controllers\FinanceHead\Settings\RegistrationFeeController;
use App\Http\Controllers\Review\PlanningYearReviewController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────
// Role-based Dashboard Routes
// ─────────────────────────────────────────────────────────────────

// 1. Admin
Route::middleware(['auth', 'check.active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('chart-of-accounts', ChartOfAccountController::class);
    });

// 2. Finance Head
Route::middleware(['auth', 'check.active', 'role:head_of_finance'])
    ->prefix('head-of-finance')
    ->name('head_of_finance.')
    ->group(function () {
        Route::get('/home', [App\Http\Controllers\FinanceHead\HomeController::class, 'index'])->name('home');
        Route::get('manage-plan', [ManagePlanController::class, 'index'])->name('manage-plan.index');
        Route::post('manage-plan', [ManagePlanController::class, 'store'])->name('manage-plan.store');
        Route::get('manage-plan/{planningYear}/preview', [ManagePlanController::class, 'preview'])->name('manage-plan.preview');
        Route::get('manage-plan/{planningYear}/previewview', [ManagePlanController::class, 'previewView'])->name('manage-plan.previewview');
        Route::get('manage-plan/{planningYear}/period-1-2', [ManagePlanController::class, 'periodOneTwo'])->name('manage-plan.period-1-2');
        Route::post('manage-plan/{planningYear}/period-1-2/save', [ManagePlanController::class, 'savePeriodOneTwo'])->name('manage-plan.period-1-2.save');
        Route::patch('manage-plan/{planningYear}/period-1-2/overrides/{accountCode}', [ManagePlanController::class, 'updatePeriodOneTwoOverride'])->name('manage-plan.period-1-2.override');
        Route::get('manage-plan/{planningYear}/period-3-4', [ManagePlanController::class, 'periodThreeFour'])->name('manage-plan.period-3-4');
        Route::post('manage-plan/{planningYear}/period-3-4/save', [ManagePlanController::class, 'savePeriodThreeFour'])->name('manage-plan.period-3-4.save');
        Route::patch('manage-plan/{planningYear}/period-3-4/overrides/{accountCode}', [ManagePlanController::class, 'updatePeriodThreeFourOverride'])->name('manage-plan.period-3-4.override');
        Route::delete('manage-plan/{planningYear}', [ManagePlanController::class, 'destroy'])->name('manage-plan.destroy');
        Route::post('manage-plan/{planningYear}/sync', [ManagePlanController::class, 'sync'])->name('manage-plan.sync');
        Route::post('manage-plan/{planningYear}/save', [ManagePlanController::class, 'savePlan'])->name('manage-plan.save');
        Route::post('manage-plan/{planningYear}/request-review', [ManagePlanController::class, 'requestReview'])->name('manage-plan.request-review');
        Route::post('manage-plan/{planningYear}/close-review', [ManagePlanController::class, 'closeReview'])->name('manage-plan.close-review');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('degree-programs', DegreeProgramController::class)->except(['show']);
            Route::resource('credit-unit-price', CreditUnitPriceController::class, ['parameters' => ['credit-unit-price' => 'creditUnitPrice']])->only(['index', 'update']);
            Route::resource('course-credits', CourseCreditController::class, ['parameters' => ['course-credits' => 'courseCredit']])->except(['show']);
            Route::patch('course-credit-splits/{level}', [CourseCreditController::class, 'updateSplit'])->name('course-credit-splits.update');
            Route::post('course-credit-splits/reset-defaults', [CourseCreditController::class, 'resetSplitDefaults'])->name('course-credit-splits.reset-defaults');
            Route::resource('registration-fee', RegistrationFeeController::class, ['parameters' => ['registration-fee' => 'registrationFee']])->only(['index', 'edit', 'update']);
            Route::resource('nuol-pct', NuolPctSettingController::class, ['parameters' => ['nuol-pct' => 'nuolPct']])->only(['index', 'update']);
            Route::resource('expense-patterns', ExpensePatternController::class, ['parameters' => ['expense-patterns' => 'expensePattern']])->only(['index', 'store', 'update']);
            Route::post('expense-patterns/{expensePattern}/fields', [ExpensePatternController::class, 'storeField'])->name('expense-patterns.fields.store');
            Route::patch('expense-patterns/{expensePattern}/fields/{fieldKey}', [ExpensePatternController::class, 'updateField'])->name('expense-patterns.fields.update');
            Route::delete('expense-patterns/{expensePattern}/fields/{fieldKey}', [ExpensePatternController::class, 'destroyField'])->name('expense-patterns.fields.destroy');
            Route::get('expense-setup', [ExpenseStructureController::class, 'overview'])->name('expense-setup.index');
            Route::get('expense-structure', [ExpenseStructureController::class, 'index'])->name('expense-structure.index');
            Route::get('expense-default-rows/accounts', [ExpenseDefaultRowAccountController::class, 'index'])->name('expense-default-rows.accounts.index');
            Route::post('expense-default-rows', [ExpenseDefaultRowAccountController::class, 'store'])->name('expense-default-rows.store');
            Route::patch('expense-default-rows/{expenseCatalogItem}', [ExpenseDefaultRowAccountController::class, 'update'])->name('expense-default-rows.update');
            Route::delete('expense-default-rows/{expenseCatalogItem}', [ExpenseDefaultRowAccountController::class, 'destroy'])->name('expense-default-rows.destroy');
            Route::patch('expense-default-rows/{expenseCatalogItem}/account', [ExpenseDefaultRowAccountController::class, 'update'])->name('expense-default-rows.account.update');
            Route::post('expense-structure/sections', [ExpenseStructureController::class, 'storeSection'])->name('expense-structure.sections.store');
            Route::patch('expense-structure/sections/{expenseSection}', [ExpenseStructureController::class, 'updateSection'])->name('expense-structure.sections.update');
            Route::delete('expense-structure/sections/{expenseSection}', [ExpenseStructureController::class, 'destroySection'])->name('expense-structure.sections.destroy');
            Route::post('expense-structure/sections/{expenseSection}/subsections', [ExpenseStructureController::class, 'storeSubsection'])->name('expense-structure.subsections.store');
            Route::patch('expense-structure/subsections/{expenseSubsection}', [ExpenseStructureController::class, 'updateSubsection'])->name('expense-structure.subsections.update');
            Route::delete('expense-structure/subsections/{expenseSubsection}', [ExpenseStructureController::class, 'destroySubsection'])->name('expense-structure.subsections.destroy');
            // Income rates (items 3–6) are now edited inline on the academic-income entry page.
        });

        // Academic Income
        Route::get('academic-income', [ManagePlanController::class, 'redirectAcademicIncomeIndex']);
        Route::resource('academic-income', AcademicIncomePlanController::class, ['parameters' => ['academic-income' => 'academicIncome']])->except(['index', 'edit', 'update']);
        Route::get('academic-income/{academicIncome}/evaluate', [AcademicIncomeAssessmentController::class, 'evaluate'])->name('academic-income.evaluate');
        Route::patch('academic-income/{academicIncome}/evaluate-field', [AcademicIncomeAssessmentController::class, 'saveField'])->name('academic-income.saveField');
        Route::post('academic-income/{academicIncome}/evaluate', [AcademicIncomeAssessmentController::class, 'saveEvaluate'])->name('academic-income.saveEvaluate');

        // Expense Plans
        Route::resource('expense', ExpensePlanController::class, [
            'parameters' => ['expense' => 'expensePlan'],
        ])->except(['edit', 'update', 'show']);
        Route::get('expense/{expensePlan}/manage', [ExpensePlanController::class, 'manage'])->name('expense.manage');
        Route::post('expense/{expensePlan}/approve', [ExpensePlanController::class, 'approve'])->name('expense.approve');
        Route::patch('expense/{expensePlan}/sections/{expenseSection}/summary-settings', [ExpensePlanController::class, 'updateSectionSummarySettings'])->name('expense.section-summary-settings.update');
        Route::patch('expense/{expensePlan}/subsections/{expenseSubsection}/summary-settings', [ExpensePlanController::class, 'updateSubsectionSummarySettings'])->name('expense.subsection-summary-settings.update');

        // Expense plan rows (AJAX dynamic fields)
        Route::post('expense-plan-rows', [ExpensePlanRowController::class, 'store'])->name('expense-plan-rows.store');
        Route::patch('expense-plan-rows/{expensePlanRow}', [ExpensePlanRowController::class, 'update'])->name('expense-plan-rows.update');
        Route::delete('expense-plan-rows/{expensePlanRow}', [ExpensePlanRowController::class, 'destroy'])->name('expense-plan-rows.destroy');

        // Salary Plans
        Route::resource('salary', SalaryPlanController::class, [
            'parameters' => ['salary' => 'salaryPlan'],
        ])->except(['edit', 'update', 'destroy']);
        Route::get('salary/{salaryPlan}/manage', [SalaryPlanController::class, 'manage'])->name('salary.manage');

        // Salary Entries (AJAX)
        Route::post('salary-entries', [SalaryEntryController::class, 'store'])->name('salary-entries.store');
        Route::patch('salary-entries/{salaryEntry}', [SalaryEntryController::class, 'update'])->name('salary-entries.update');
        Route::delete('salary-entries/{salaryEntry}', [SalaryEntryController::class, 'destroy'])->name('salary-entries.destroy');
    });

// 3. Faculty Head
Route::middleware(['auth', 'check.active', 'role:head_of_faculty'])
    ->prefix('head-of-faculty')
    ->name('head_of_faculty.')
    ->group(function () {
        Route::get('/home', [App\Http\Controllers\FacultyHead\HomeController::class, 'index'])->name('home');
    });

// 4. Faculty Deputy
Route::middleware(['auth', 'check.active', 'role:deputy_head_of_faculty'])
    ->prefix('deputy-head-of-faculty')
    ->name('deputy_head_of_faculty.')
    ->group(function () {
        Route::get('/home', [App\Http\Controllers\FacultyDeputy\HomeController::class, 'index'])->name('home');
    });

// 5. Accountant
Route::middleware(['auth', 'check.active', 'role:accountant'])
    ->prefix('accountant')
    ->name('accountant.')
    ->group(function () {
        Route::get('/home', [App\Http\Controllers\Accountant\HomeController::class, 'index'])->name('home');
    });

Route::middleware(['auth', 'check.active'])
    ->prefix('reviews')
    ->name('reviews.')
    ->group(function () {
        Route::get('planning-years', [PlanningYearReviewController::class, 'index'])->name('planning-years.index');
        Route::get('planning-years/{planningYear}', [PlanningYearReviewController::class, 'show'])->name('planning-years.show');
        Route::post('planning-years/{planningYear}/comments', [PlanningYearReviewController::class, 'storeComment'])->name('planning-years.comments.store');
        Route::post('planning-years/{planningYear}/comments/{comment}/agreement', [PlanningYearReviewController::class, 'toggleAgreement'])->name('planning-years.comments.agreement');
    });
