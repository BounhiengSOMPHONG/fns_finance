<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.finance_head.home');
    }
}
