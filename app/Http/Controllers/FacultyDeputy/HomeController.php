<?php

namespace App\Http\Controllers\FacultyDeputy;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.faculty_deputy.home');
    }
}
