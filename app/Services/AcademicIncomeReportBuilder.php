<?php

namespace App\Services;

use App\Models\AcademicIncomeItem;
use App\Models\AcademicIncomePlan;
use App\Models\DegreeProgram;
use App\Models\RegistrationFeeSetting;
use Illuminate\Support\Collection;

class AcademicIncomeReportBuilder
{
    public function buildForPlans(Collection $plans): array
    {
        $plans->loadMissing('items.degreeProgram.latestCourseCredit');

        $persistedItems = $plans
            ->flatMap(fn (AcademicIncomePlan $plan) => $plan->items)
            ->values();
        $items = $this->withZeroPlaceholders($plans, $persistedItems);

        $s1_1 = $items->where('section_code', '1.4')->first();
        $s1_2 = $items->where('section_code', '1.2')->first();

        $s2_1Items = $items->filter(fn (AcademicIncomeItem $item) => $item->section_code === '1.3' && $item->degreeProgram?->level === 'bachelor');
        $s2_2Items = $items->filter(fn (AcademicIncomeItem $item) => $item->section_code === '1.1' && (int) $item->degreeProgram?->study_year === 2);
        $s2_3Items = $items->filter(fn (AcademicIncomeItem $item) => $item->section_code === '1.1' && (int) $item->degreeProgram?->study_year === 3);
        $s2_4Items = $items->filter(fn (AcademicIncomeItem $item) => $item->section_code === '1.1' && (int) $item->degreeProgram?->study_year >= 4);
        $s2_5Items = $items->filter(fn (AcademicIncomeItem $item) => in_array($item->section_code, ['1.1', '1.3'], true)
            && in_array($item->degreeProgram?->level, ['master', 'phd'], true));

        $s3 = $items->where('section_code', '2.1')->first();
        $s4 = $items->where('section_code', '2.2')->first();
        $s5 = $items->where('section_code', '2.3')->first();
        $s6 = $items->where('section_code', '2.4')->first();

        $sections = [
            's1' => [
                'title' => 'ຄ່າລົງທະບຽນນັກສຶກສາ',
                'rows' => [
                    '1.1' => $this->buildRow('ນັກສຶກສາ ປີທີ 1', $s1_1, $s1_1?->snap_registration_fee_rate),
                    '1.2' => $this->buildRow('ນັກສຶກສາ ປີທີ 2,3,4', $s1_2, $s1_2?->snap_registration_fee_rate),
                ],
            ],
            's2' => [
                'title' => 'ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ',
                'rows' => [
                    '2.1' => $this->buildRow('ນັກສຶກສາ ປີທີ 1', $s2_1Items),
                    '2.2' => $this->buildRow('ນັກສຶກສາ ປີທີ 2', $s2_2Items),
                    '2.3' => $this->buildRow('ນັກສຶກສາ ປີທີ 3', $s2_3Items),
                    '2.4' => $this->buildRow('ນັກສຶກສາ ປີທີ 4', $s2_4Items),
                    '2.5' => $this->buildRow('ນັກສຶກສາ ປ.ໂທ + ເອກ', $s2_5Items),
                ],
            ],
            's3' => $this->buildRow('ຄ່າລົງທະບຽນເທີມສາມ', $s3, $s3?->snap_credit_unit_price),
            's4' => $this->buildRow('ຄ່າບູລະນະຫ້ອງທົດລອງຄອມພິວເຕີ', $s4, $s4?->snap_registration_fee_rate),
            's5' => $this->buildRow('ຄ່າບຳລຸງອຸປະກອນຫ້ອງທົດລອງ', $s5, $s5?->snap_registration_fee_rate),
            's6' => $this->buildRow('ຄ່າບໍລິການວິຊາການ ແລະ ຄ່າບໍລິການອື່ນໆ', $s6, $s6?->snap_credit_unit_price),
        ];

        $totals = $this->attachSectionTotals($sections);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')
            ->orderByDesc('start_year')
            ->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')
            ->orderByDesc('start_year')
            ->first();

        return [
            'academicIncome' => $plans->first(),
            'items' => $items,
            'sections' => $sections,
            'totals' => $totals,
            'summaryRows' => $this->buildSummaryRows($sections),
            'summaryPlanTotal' => $this->summaryPlanTotal($sections),
            'detail_1_1' => $items->where('section_code', '1.1')
                ->sortBy(fn (AcademicIncomeItem $item): string => $this->degreeProgramSortKey($item))
                ->values(),
            'detail_1_3' => $items->where('section_code', '1.3')
                ->sortBy(fn (AcademicIncomeItem $item): string => $this->degreeProgramSortKey($item))
                ->values(),
            'feeYear2_4' => $feeYear2_4,
            'feeYear1' => $feeYear1,
            's1_2' => $s1_2,
            's1_1' => $s1_1,
        ];
    }

    private function buildRow(string $title, AcademicIncomeItem|Collection|null $rowItems, mixed $rate = null): array
    {
        $rowItems = $rowItems instanceof Collection ? $rowItems : collect([$rowItems])->filter();

        $count = (int) $rowItems->sum('student_count');
        $fnsIncome = (float) $rowItems->sum('total_income');
        $teachingFee = (float) $rowItems->sum('teaching_fee_amount');
        $gross = (float) $rowItems->sum(fn (AcademicIncomeItem $item) => $this->grossIncome($item));
        $nuol = $gross - $fnsIncome;

        return [
            'title' => $title,
            'count' => $count,
            'rate' => $rate !== null ? (float) $rate : null,
            'gross' => $gross,
            'nuol' => $nuol,
            'fns_income' => $fnsIncome,
            'teaching_fee' => $teachingFee,
            'remaining' => $fnsIncome - $teachingFee,
        ];
    }

    private function grossIncome(AcademicIncomeItem $item): float
    {
        if ($item->snap_course_credit_unit !== null) {
            return (float) $item->student_count * (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
        }

        if ($item->snap_registration_fee_rate !== null) {
            return (float) $item->student_count * (float) $item->snap_registration_fee_rate;
        }

        return (float) $item->student_count * (float) $item->snap_credit_unit_price;
    }

    private function attachSectionTotals(array &$sections): array
    {
        $totals = ['gross' => 0.0, 'nuol' => 0.0, 'fns_income' => 0.0, 'teaching_fee' => 0.0, 'remaining' => 0.0];

        foreach ($sections as &$section) {
            if (! isset($section['rows'])) {
                foreach ($totals as $key => $value) {
                    $totals[$key] = $value + (float) $section[$key];
                }

                continue;
            }

            $sectionTotals = ['gross' => 0.0, 'nuol' => 0.0, 'fns_income' => 0.0, 'teaching_fee' => 0.0, 'remaining' => 0.0];
            foreach ($section['rows'] as $row) {
                foreach ($sectionTotals as $key => $value) {
                    $sectionTotals[$key] = $value + (float) $row[$key];
                    $totals[$key] += (float) $row[$key];
                }
            }

            $section['totals'] = $sectionTotals;
        }

        return $totals;
    }

    private function buildSummaryRows(array $sections): array
    {
        $creditYear2Plus = $this->combineRows(
            $sections['s2']['rows']['2.2'],
            $sections['s2']['rows']['2.3'],
            $sections['s2']['rows']['2.4'],
            $sections['s2']['rows']['2.5'],
        );

        return [
            ['number' => 1, 'title' => 'ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ ປີທີ 1', 'planned' => $sections['s2']['rows']['2.1']['fns_income']],
            ['number' => 2, 'title' => 'ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ ປີທີ 2-4 (ປ.ຕີ, ປ.ໂທ)', 'planned' => $creditYear2Plus['fns_income']],
            ['number' => 3, 'title' => 'ຄ່າທະບຽນນັກສຶກສາ ປີທີ 1 ລະບົບຈ່າຍເງິນ', 'planned' => $sections['s1']['rows']['1.1']['fns_income']],
            ['number' => 4, 'title' => 'ຄ່າທະບຽນນັກສຶກສາ ປີທີ 2-4', 'planned' => $sections['s1']['rows']['1.2']['fns_income']],
            ['number' => 5, 'title' => 'ຄ່າໜ່ວຍກິດຮຽນເທີມ', 'planned' => $sections['s3']['fns_income']],
            ['number' => 6, 'title' => 'ຄ່າບຳລຸງຮັກສາຄອມພິວເຕີ', 'planned' => $sections['s4']['fns_income']],
            ['number' => 7, 'title' => 'ບຳລຸງຫ້ອງທົດລອງ', 'planned' => $sections['s5']['fns_income']],
            ['number' => 8, 'title' => 'ຄ່າບໍລິການວິຊາການ ແລະ ຄ່າບໍລິການອື່ນໆ', 'planned' => $sections['s6']['fns_income']],
        ];
    }

    private function summaryPlanTotal(array $sections): float
    {
        return collect($this->buildSummaryRows($sections))->sum('planned');
    }

    private function combineRows(array ...$rows): array
    {
        return [
            'fns_income' => collect($rows)->sum('fns_income'),
        ];
    }

    private function withZeroPlaceholders(Collection $plans, Collection $items): Collection
    {
        $plan = $plans->first();
        if (! $plan) {
            return $items;
        }

        $itemsByKey = $items->keyBy(fn (AcademicIncomeItem $item): string => $this->itemKey($item->section_code, $item->degree_program_id));
        $programs = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->planningOrder()
            ->get();

        $placeholders = collect();

        $programs11 = $programs->filter(fn (DegreeProgram $program): bool => (
            $program->level === 'bachelor' && (int) $program->study_year >= 2
        ) || in_array($program->level, ['master', 'phd'], true));

        foreach ($programs11 as $program) {
            $this->pushPlaceholderIfMissing($placeholders, $itemsByKey, $plan, '1.1', $program);
        }

        $programs13 = $programs->filter(fn (DegreeProgram $program): bool => (
            $program->level === 'bachelor' && ((int) $program->study_year === 1 || $program->study_year === null)
        ) || in_array($program->level, ['master', 'phd'], true));

        foreach ($programs13 as $program) {
            $this->pushPlaceholderIfMissing($placeholders, $itemsByKey, $plan, '1.3', $program);
        }

        foreach (['1.2', '1.4', '2.1', '2.2', '2.3', '2.4'] as $section) {
            $this->pushPlaceholderIfMissing($placeholders, $itemsByKey, $plan, $section);
        }

        return $items->concat($placeholders)->values();
    }

    private function degreeProgramSortKey(AcademicIncomeItem $item): string
    {
        $program = $item->degreeProgram;
        $levelOrder = match ($program?->level) {
            'bachelor' => 10,
            'master' => 20,
            'phd' => 30,
            default => 90,
        };

        return sprintf(
            '%03d|%03d|%03d|%s|%010d',
            (int) ($program?->study_year ?? 99),
            (int) ($program?->department_sort_order ?? DegreeProgram::departmentOrder($program?->academic_department)),
            $levelOrder,
            $program?->name ?? '',
            (int) ($program?->id ?? 0),
        );
    }

    private function pushPlaceholderIfMissing(
        Collection $placeholders,
        Collection $itemsByKey,
        AcademicIncomePlan $plan,
        string $section,
        ?DegreeProgram $program = null
    ): void {
        if ($itemsByKey->has($this->itemKey($section, $program?->id))) {
            return;
        }

        $item = new AcademicIncomeItem([
            'plan_id' => $plan->id,
            'section_code' => $section,
            'degree_program_id' => $program?->id,
            'student_count' => 0,
            'total_income' => 0,
        ]);

        $item->exists = false;
        $item->setRelation('plan', $plan);
        if ($program) {
            $item->setRelation('degreeProgram', $program);
        }

        $placeholders->push($item);
    }

    private function itemKey(string $section, ?int $programId): string
    {
        return $section.'_'.($programId ?? 'none');
    }
}
