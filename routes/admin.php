<?php

// routes/admin.php
// ─────────────────────────────────────────────────────
// Only users with role_name = 'admin' can access these.
// Your friend working on admin features edits THIS file.
// ─────────────────────────────────────────────────────

use App\Http\Controllers\Admin\ChartOfAccountController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/home', [HomeController::class, 'index'])->name('home');

        // Bulk Delete Routes
        Route::delete('users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk_destroy');
        Route::delete('roles/bulk-delete', [RoleController::class, 'bulkDestroy'])->name('roles.bulk_destroy');
        Route::delete('departments/bulk-delete', [DepartmentController::class, 'bulkDestroy'])->name('departments.bulk_destroy');
        Route::delete('chart-of-accounts/bulk-delete', [ChartOfAccountController::class, 'bulkDestroy'])->name('chart-of-accounts.bulk_destroy');

        // CRUD Resources
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('chart-of-accounts', ChartOfAccountController::class);
    });
