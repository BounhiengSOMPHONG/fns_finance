<?php

namespace App\Http\Controllers\FacultyHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('dashboards.faculty_head.home');
    }
}
