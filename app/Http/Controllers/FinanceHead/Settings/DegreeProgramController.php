<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\DegreeProgram;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DegreeProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = DegreeProgram::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $programs = $query->orderBy('level')->orderByRaw('study_year IS NULL')->orderBy('study_year')->orderBy('name')->paginate(20)->withQueryString();
        $grouped = DegreeProgram::orderByRaw('study_year IS NULL')->orderBy('study_year')->orderBy('name')->get()->groupBy('level');

        return view('dashboards.finance_head.settings.degree-programs.index', compact('programs', 'grouped'));
    }

    public function create()
    {
        return view('dashboards.finance_head.settings.degree-programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:50|unique:degree_programs',
            'name'       => 'required|string|max:255',
            'level'      => 'required|in:bachelor,master,phd',
            'study_year' => 'nullable|integer|min:1|max:6',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['study_year'] = $validated['study_year'] ?: null;

        DegreeProgram::create($validated);

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ສ້າງສາຂາວິຊາສຳເລັດ');
    }

    public function edit(DegreeProgram $degreeProgram)
    {
        return view('dashboards.finance_head.settings.degree-programs.edit', compact('degreeProgram'));
    }

    public function update(Request $request, DegreeProgram $degreeProgram)
    {
        $validated = $request->validate([
            'code'       => ['required', 'string', 'max:50', Rule::unique('degree_programs')->ignore($degreeProgram->id)],
            'name'       => 'required|string|max:255',
            'level'      => 'required|in:bachelor,master,phd',
            'study_year' => 'nullable|integer|min:1|max:6',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['study_year'] = $validated['study_year'] ?: null;

        $degreeProgram->update($validated);

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ອັບເດດສາຂາວິຊາສຳເລັດ');
    }

    public function destroy(DegreeProgram $degreeProgram)
    {
        $degreeProgram->delete();

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ລຶບສາຂາວິຊາສຳເລັດ');
    }
}
