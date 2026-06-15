<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.admin.home', [
            'userCount' => User::count(),
            'activeUserCount' => User::where('is_active', true)->count(),
            'roleCount' => Role::count(),
            'departmentCount' => Department::count(),
            'accountCount' => ChartOfAccount::count(),
        ]);
    }
}
