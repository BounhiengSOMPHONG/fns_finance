<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\RegistrationFeeSetting;
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
            ->route('head_of_finance.academic-income.evaluate', $plan)
            ->with('success', 'ສ້າງແຜນລາຍຮັບວິຊາການສຳເລັດ');
    }

    public function show(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->load('items.degreeProgram', 'creator');
        
        $allPlans = AcademicIncomePlan::orderByDesc('fiscal_year')->get();

        $items = $academicIncome->items;

        $s1_1 = $items->where('section_code', '1.4')->first();
        $s1_2 = $items->where('section_code', '1.2')->first();

        $s2_1_items = $items->filter(fn($i) => $i->section_code === '1.3' && $i->degreeProgram?->level === 'bachelor');
        $s2_2_items = $items->filter(fn($i) => $i->section_code === '1.1' && $i->degreeProgram?->study_year === 2);
        $s2_3_items = $items->filter(fn($i) => $i->section_code === '1.1' && $i->degreeProgram?->study_year === 3);
        $s2_4_items = $items->filter(fn($i) => $i->section_code === '1.1' && $i->degreeProgram?->study_year >= 4);
        $s2_5_items = $items->filter(fn($i) => in_array($i->section_code, ['1.1', '1.3']) && in_array($i->degreeProgram?->level, ['master', 'phd']));

        $s3 = $items->where('section_code', '2.1')->first();
        $s4 = $items->where('section_code', '2.2')->first();
        $s5 = $items->where('section_code', '2.3')->first();
        $s6 = $items->where('section_code', '2.4')->first();

        $buildRow = function($title, $rowItems, $rate = null) {
            $rowItems = $rowItems instanceof \Illuminate\Support\Collection ? $rowItems : collect([$rowItems])->filter();

            $count = $rowItems->sum('student_count');
            $fnsIncome = $rowItems->sum('total_income');
            $teachingFee = $rowItems->sum('second_payment_amount');
            $remaining = $fnsIncome - $teachingFee;

            $gross = $rowItems->reduce(function($carry, $item) {
                if ($item->snap_course_credit_unit) {
                    return $carry + ($item->student_count * $item->snap_course_credit_unit * $item->snap_credit_unit_price);
                } elseif ($item->snap_registration_fee_rate) {
                    return $carry + ($item->student_count * $item->snap_registration_fee_rate);
                } else {
                    return $carry + ($item->student_count * $item->snap_credit_unit_price);
                }
            }, 0);

            $nuol = $gross - $fnsIncome;

            return [
                'title' => $title,
                'count' => $count,
                'rate' => $rate,
                'gross' => $gross,
                'nuol' => $nuol,
                'fns_income' => $fnsIncome,
                'teaching_fee' => $teachingFee,
                'remaining' => $remaining
            ];
        };

        $sections = [
            's1' => [
                'title' => 'ຄ່າລົງທະບຽນນັກສຶກສາ',
                'rows' => [
                    '1.1' => $buildRow('ນັກສຶກສາ ປີທີ 1', $s1_1, $s1_1?->snap_registration_fee_rate),
                    '1.2' => $buildRow('ນັກສຶກສາ ປີທີ 2,3,4', $s1_2, $s1_2?->snap_registration_fee_rate),
                ]
            ],
            's2' => [
                'title' => 'ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ',
                'rows' => [
                    '2.1' => $buildRow('ນັກສຶກສາ ປີທີ 1', $s2_1_items),
                    '2.2' => $buildRow('ນັກສຶກສາ ປີທີ 2', $s2_2_items),
                    '2.3' => $buildRow('ນັກສຶກສາ ປີທີ 3', $s2_3_items),
                    '2.4' => $buildRow('ນັກສຶກສາ ປີທີ 4', $s2_4_items),
                    '2.5' => $buildRow('ນັກສຶກສາ ປ.ໂທ + ເອກ', $s2_5_items),
                ]
            ],
            's3' => $buildRow('ຄ່າລົງທະບຽນເທີມສາມ', $s3, $s3?->snap_credit_unit_price),
            's4' => $buildRow('ຄ່າບູລະນະຫ້ອງທົດລອງຄອມພິວເຕີ', $s4, $s4?->snap_registration_fee_rate),
            's5' => $buildRow('ຄ່າບຳລຸງອຸປະກອນຫ້ອງທົດລອງ', $s5, $s5?->snap_registration_fee_rate),
            's6' => $buildRow('ຄ່າບໍລິການວິຊາການ ແລະ ຄ່າບໍລິການອື່ນໆ', $s6, $s6?->snap_credit_unit_price),
        ];

        $totals = [
            'gross' => 0, 'nuol' => 0, 'fns_income' => 0, 'teaching_fee' => 0, 'remaining' => 0
        ];

        foreach ($sections as $key => &$sec) {
            if (isset($sec['rows'])) {
                $secGross = 0; $secNuol = 0; $secFns = 0; $secTeach = 0; $secRem = 0;
                foreach ($sec['rows'] as $row) {
                    $secGross += $row['gross'];
                    $secNuol += $row['nuol'];
                    $secFns += $row['fns_income'];
                    $secTeach += $row['teaching_fee'];
                    $secRem += $row['remaining'];

                    $totals['gross'] += $row['gross'];
                    $totals['nuol'] += $row['nuol'];
                    $totals['fns_income'] += $row['fns_income'];
                    $totals['teaching_fee'] += $row['teaching_fee'];
                    $totals['remaining'] += $row['remaining'];
                }
                $sec['totals'] = [
                    'gross' => $secGross, 'nuol' => $secNuol, 'fns_income' => $secFns,
                    'teaching_fee' => $secTeach, 'remaining' => $secRem
                ];
            } else {
                $totals['gross'] += $sec['gross'];
                $totals['nuol'] += $sec['nuol'];
                $totals['fns_income'] += $sec['fns_income'];
                $totals['teaching_fee'] += $sec['teaching_fee'];
                $totals['remaining'] += $sec['remaining'];
            }
        }

        // Detail data for sub-report tables (pages 2–5 of the PDF)
        $detail_1_1 = $items->where('section_code', '1.1')
            ->sortBy(fn($i) => ($i->degreeProgram?->study_year ?? 99) . $i->degreeProgram?->name);

        $detail_1_3 = $items->where('section_code', '1.3')
            ->sortBy(fn($i) => ($i->degreeProgram?->level === 'bachelor' ? 'A' : 'B') . $i->degreeProgram?->name);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        return view('dashboards.finance_head.academic-income.show', compact(
            'academicIncome', 'sections', 'totals', 'allPlans',
            'detail_1_1', 'detail_1_3', 'feeYear2_4', 'feeYear1',
            's1_2', 's1_1'
        ));
    }

    public function destroy(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->delete();

        return redirect()
            ->route('head_of_finance.academic-income.index')
            ->with('success', 'ລຶບແຜນລາຍຮັບວິຊາການສຳເລັດ');
    }
}
