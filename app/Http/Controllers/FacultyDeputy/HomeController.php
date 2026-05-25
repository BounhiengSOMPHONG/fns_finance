<?php

namespace App\Http\Controllers\FacultyDeputy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.faculty_deputy.home');
    }
}
