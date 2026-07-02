<?php

namespace App\Http\Controllers\FacultyHead;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.faculty_head.home');
    }
}
