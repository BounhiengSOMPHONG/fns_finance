<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use Illuminate\Http\Request;

class AcademicIncomePlanController extends Controller
{
    public function index()
    {
        $plans = AcademicIncomePlan::with('creator')
            ->orderByDesc('fiscal_year')
            ->paginate(15);

        return view('dashboards.finance_head.academic-income.index', compact('plans'));
    }

    public function create()
    {
        return view('dashboards.finance_head.academic-income.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100|unique:academic_income_plans',
            'notes'       => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $plan = AcademicIncomePlan::create($validated);

        return redirect()
            ->route('head_of_finance.academic-income.show', $plan)
            ->with('success', 'ສ້າງແຜນລາຍຮັບວິຊາການສຳເລັດ');
    }

    public function show(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->load(['items.degreeProgram', 'creator']);

        return view('dashboards.finance_head.academic-income.show', compact('academicIncome'));
    }

    public function destroy(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->delete();

        return redirect()
            ->route('head_of_finance.academic-income.index')
            ->with('success', 'ລຶບແຜນລາຍຮັບວິຊາການສຳເລັດ');
    }
}
