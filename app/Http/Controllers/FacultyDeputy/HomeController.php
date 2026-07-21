<?php

namespace App\Http\Controllers\FacultyDeputy;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return redirect()->route('reviews.planning-years.index');
    }
}
