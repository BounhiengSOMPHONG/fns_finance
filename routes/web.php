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
            'head_of_department' => redirect()->route('head_of_department.home'),
            default => abort(403, 'ບໍ່ພົບບົດບາດຂອງທ່ານ.'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Notifications ──────────────────────────────────────────────────
    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read-all');

    Route::get('/notifications/data', function () {
        $notifications = auth()->user()->notifications()->latest()->limit(20)->get();
        return response()->json([
            'unread_count' => auth()->user()->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? '#',
                'read' => !is_null($n->read_at),
                'time' => $n->created_at->diffForHumans(),
            ]),
        ]);
    })->name('notifications.data');
});

// ─────────────────────────────────────────────────────────────────
// Role-specific routes (each in their own file)
// ─────────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/head_of_finance.php';
require __DIR__ . '/accountant.php';
require __DIR__ . '/deputy_head_of_faculty.php';
require __DIR__ . '/head_of_faculty.php';
require __DIR__ . '/head_of_department.php';
