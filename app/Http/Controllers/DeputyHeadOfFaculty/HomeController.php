<?php

namespace App\Http\Controllers\DeputyHeadOfFaculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $hasPendingReview = auth()->user()
            ->reviewerAssignments()
            ->whereHas('budgetPlan', function ($q) {
                $q->where('status', 'PENDING_REVIEW');
            })
            ->exists();

        return view('deputy_head_of_faculty.home', compact('hasPendingReview'));
    }
}
