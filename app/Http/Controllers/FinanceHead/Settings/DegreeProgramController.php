<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\DegreeProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DegreeProgramController extends Controller
{
    public function index()
    {
        // All programs, ordered; grouping + filtering happens in the view (instant, client-side).
        $programs = DegreeProgram::planningOrder()
            ->get();

        $displayPrograms = $this->displayPrograms($programs);
        $departments = DegreeProgram::departmentOptions();

        return view('dashboards.finance_head.settings.degree-programs.index', compact('programs', 'displayPrograms', 'departments'));
    }

    public function create()
    {
        $departments = DegreeProgram::departmentOptions();

        return view('dashboards.finance_head.settings.degree-programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'       => 'required|string|max:50',
            'name'       => 'required|string|max:255',
            'level'      => 'required|in:bachelor,master,phd',
            'study_year' => 'nullable|integer|min:1|max:4',
            'study_years' => 'nullable|array',
            'study_years.*' => 'integer|min:1|max:4',
            'is_active'  => 'boolean',
            'include_in_planning' => 'boolean',
            'academic_department' => ['required', 'string', Rule::in(array_keys(DegreeProgram::DEPARTMENTS))],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['include_in_planning'] = $request->boolean('include_in_planning', true);
        $validated['department_sort_order'] = DegreeProgram::departmentOrder($validated['academic_department']);

        if ($validated['level'] === 'bachelor') {
            $years = $this->requestedStudyYears($request);
            if ($years->isEmpty()) {
                return back()->withErrors(['study_years' => 'ກະລຸນາເລືອກຊັ້ນປີຢ່າງໜ້ອຍ 1 ປີ'])->withInput();
            }

            $baseCode = $this->baseProgramCode($validated['code']);
            $existingCodes = DegreeProgram::whereIn('code', $years->map(fn (int $year): string => $baseCode.'-Y'.$year))->pluck('code');
            if ($existingCodes->isNotEmpty()) {
                return back()->withErrors(['code' => 'ລະຫັດນີ້ມີແລ້ວ: '.$existingCodes->implode(', ')])->withInput();
            }

            DB::transaction(function () use ($validated, $years, $baseCode): void {
                foreach ($years as $year) {
                    DegreeProgram::create([
                        'code' => $baseCode.'-Y'.$year,
                        'name' => $validated['name'],
                        'level' => 'bachelor',
                        'study_year' => $year,
                        'is_active' => $validated['is_active'],
                        'include_in_planning' => $validated['include_in_planning'],
                        'academic_department' => $validated['academic_department'],
                        'department_sort_order' => $validated['department_sort_order'],
                    ]);
                }
            });
        } else {
            if (DegreeProgram::where('code', $validated['code'])->exists()) {
                return back()->withErrors(['code' => 'ລະຫັດນີ້ມີແລ້ວ'])->withInput();
            }

            DegreeProgram::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'level' => $validated['level'],
                'study_year' => null,
                'is_active' => $validated['is_active'],
                'include_in_planning' => $validated['include_in_planning'],
                'academic_department' => $validated['academic_department'],
                'department_sort_order' => $validated['department_sort_order'],
            ]);
        }

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ສ້າງສາຂາວິຊາສຳເລັດ');
    }

    public function edit(DegreeProgram $degreeProgram)
    {
        $departments = DegreeProgram::departmentOptions();

        return view('dashboards.finance_head.settings.degree-programs.edit', compact('degreeProgram', 'departments'));
    }

    public function update(Request $request, DegreeProgram $degreeProgram)
    {
        $validated = $request->validate([
            'code'       => ['required', 'string', 'max:50'],
            'name'       => 'required|string|max:255',
            'level'      => 'required|in:bachelor,master,phd',
            'study_year' => 'nullable|integer|min:1|max:4',
            'study_years' => 'nullable|array',
            'study_years.*' => 'integer|min:1|max:4',
            'group_ids' => 'nullable|string',
            'is_active'  => 'boolean',
            'include_in_planning' => 'boolean',
            'academic_department' => ['required', 'string', Rule::in(array_keys(DegreeProgram::DEPARTMENTS))],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['include_in_planning'] = $request->boolean('include_in_planning', true);
        $validated['department_sort_order'] = DegreeProgram::departmentOrder($validated['academic_department']);

        if ($validated['level'] === 'bachelor') {
            $years = $this->requestedStudyYears($request);
            if ($years->isEmpty()) {
                return back()->withErrors(['study_years' => 'ກະລຸນາເລືອກຊັ້ນປີຢ່າງໜ້ອຍ 1 ປີ'])->withInput();
            }

            $groupIds = $this->requestedGroupIds($request, $degreeProgram);
            $baseCode = $this->baseProgramCode($validated['code']);
            $targetCodes = $years->map(fn (int $year): string => $baseCode.'-Y'.$year);
            $existingCodes = DegreeProgram::whereIn('code', $targetCodes)
                ->whereNotIn('id', $groupIds)
                ->pluck('code');

            if ($existingCodes->isNotEmpty()) {
                return back()->withErrors(['code' => 'ລະຫັດນີ້ມີແລ້ວ: '.$existingCodes->implode(', ')])->withInput();
            }

            DB::transaction(function () use ($groupIds, $years, $baseCode, $validated): void {
                $programsByYear = DegreeProgram::whereIn('id', $groupIds)
                    ->get()
                    ->keyBy(fn (DegreeProgram $program): int => (int) $program->study_year);

                foreach ($programsByYear as $year => $program) {
                    $program->update([
                        'code' => $baseCode.'-Y'.$year,
                        'name' => $validated['name'],
                        'level' => 'bachelor',
                        'study_year' => $year,
                        'is_active' => $years->contains((int) $year) ? $validated['is_active'] : false,
                        'include_in_planning' => $years->contains((int) $year) ? $validated['include_in_planning'] : false,
                        'academic_department' => $validated['academic_department'],
                        'department_sort_order' => $validated['department_sort_order'],
                    ]);
                }

                foreach ($years as $year) {
                    if ($programsByYear->has($year)) {
                        continue;
                    }

                    DegreeProgram::create([
                        'code' => $baseCode.'-Y'.$year,
                        'name' => $validated['name'],
                        'level' => 'bachelor',
                        'study_year' => $year,
                        'is_active' => $validated['is_active'],
                        'include_in_planning' => $validated['include_in_planning'],
                        'academic_department' => $validated['academic_department'],
                        'department_sort_order' => $validated['department_sort_order'],
                    ]);
                }
            });
        } else {
            $request->validate([
                'code' => ['required', 'string', 'max:50', Rule::unique('degree_programs')->ignore($degreeProgram->id)],
            ]);

            $degreeProgram->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'level' => $validated['level'],
                'study_year' => null,
                'is_active' => $validated['is_active'],
                'include_in_planning' => $validated['include_in_planning'],
                'academic_department' => $validated['academic_department'],
                'department_sort_order' => $validated['department_sort_order'],
            ]);
        }

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ອັບເດດສາຂາວິຊາສຳເລັດ');
    }

    public function destroy(Request $request, DegreeProgram $degreeProgram)
    {
        $groupIds = $this->requestedGroupIds($request, $degreeProgram);
        DegreeProgram::whereIn('id', $groupIds)->delete();

        return redirect()
            ->route('head_of_finance.settings.degree-programs.index')
            ->with('success', 'ລຶບສາຂາວິຊາສຳເລັດ');
    }

    private function displayPrograms($programs)
    {
        return $programs
            ->groupBy(fn (DegreeProgram $program): string => $this->displayGroupKey($program))
            ->map(function ($group) {
                $program = clone $group->sortBy(fn (DegreeProgram $item): int => (int) ($item->study_year ?? 0))->first();
                $years = $group->filter(fn (DegreeProgram $item): bool => $item->is_active)
                    ->pluck('study_year')
                    ->filter()
                    ->map(fn ($year): int => (int) $year)
                    ->unique()
                    ->sort()
                    ->values();

                if ($years->isEmpty()) {
                    $years = $group->pluck('study_year')
                        ->filter()
                        ->map(fn ($year): int => (int) $year)
                        ->unique()
                        ->sort()
                        ->values();
                }

                $program->display_code = $this->baseProgramCode($program->code);
                $program->display_name = $program->name;
                $program->display_codes = $group->pluck('code')->implode(' ');
                $program->display_group_ids = $group->pluck('id')->implode(',');
                $program->display_years = $years;
                $program->display_count = $group->count();
                $program->is_active = $group->contains(fn (DegreeProgram $item): bool => $item->is_active);
                $program->include_in_planning = $group->contains(fn (DegreeProgram $item): bool => $item->include_in_planning);
                $program->academic_department = $program->academic_department ?: 'other';
                $program->department_sort_order = DegreeProgram::departmentOrder($program->academic_department);

                return $program;
            })
            ->sortBy([
                ['department_sort_order', 'asc'],
                ['level', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function displayGroupKey(DegreeProgram $program): string
    {
        if ($program->level === 'bachelor') {
            return implode('|', [$program->level, $program->academic_department, $program->name]);
        }

        return implode('|', [$program->level, $program->id]);
    }

    private function baseProgramCode(string $code): string
    {
        return preg_replace('/-Y\d+$/i', '', trim($code)) ?: trim($code);
    }

    private function requestedStudyYears(Request $request)
    {
        $years = collect($request->input('study_years', []))
            ->filter(fn ($year): bool => is_numeric($year))
            ->map(fn ($year): int => (int) $year);

        if ($years->isEmpty() && $request->filled('study_year')) {
            $years->push((int) $request->input('study_year'));
        }

        return $years
            ->filter(fn (int $year): bool => $year >= 1 && $year <= 4)
            ->unique()
            ->sort()
            ->values();
    }

    private function requestedGroupIds(Request $request, DegreeProgram $degreeProgram)
    {
        return collect(explode(',', (string) $request->input('group_ids', '')))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->map(fn (string $id): int => (int) $id)
            ->push((int) $degreeProgram->id)
            ->unique()
            ->values();
    }
}
