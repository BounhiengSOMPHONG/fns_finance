<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $role = auth()->user()->role?->role_name;

        return match ($role) {
            'admin' => redirect()->route('admin.home'),
            'head_of_finance' => redirect()->route('head_of_finance.home'),
            'accountant' => redirect()->route('accountant.home'),
            'deputy_head_of_faculty' => redirect()->route('deputy_head_of_faculty.home'),
            'head_of_faculty' => redirect()->route('head_of_faculty.home'),
            default => abort(403, 'ไม่พบบทบาทของคุณ'),
        };
    }
}
