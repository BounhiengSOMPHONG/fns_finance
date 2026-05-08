<?php

namespace App\Http\Controllers\HeadOfFinance;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomeItem;
use App\Models\AcademicIncomePlan;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AcademicIncomeController extends Controller
{
    // ─── Plans CRUD ───────────────────────────────────────────────────────────

    public function index()
    {
        $plans = AcademicIncomePlan::with('creator')
            ->orderByDesc('fiscal_year')
            ->get();

        return view('head_of_finance.academic-income.index', compact('plans'));
    }

    public function create()
    {
        return view('head_of_finance.academic-income.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:9999|unique:academic_income_plans,fiscal_year',
        ]);

        $plan = AcademicIncomePlan::create([
            'fiscal_year' => $request->fiscal_year,
            'status'      => 'DRAFT',
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('head_of_finance.academic_income.show', $plan)
            ->with('success', 'ສ້າງແຜນລາຍຮັບວິຊາການສຳເລັດ!');
    }

    public function show(AcademicIncomePlan $plan)
    {
        $plan->load('items');

        $sections = [
            '1.1' => $plan->items->where('section_code', '1.1')->sortBy('sort_order')->values(),
            '1.2' => $plan->items->where('section_code', '1.2')->sortBy('sort_order')->values(),
            '1.3' => $plan->items->where('section_code', '1.3')->sortBy('sort_order')->values(),
            '1.4' => $plan->items->where('section_code', '1.4')->sortBy('sort_order')->values(),
        ];

        $pricePerCredit = (float) AppSetting::get('price_per_credit', 35000);

        return view('head_of_finance.academic-income.show', compact('plan', 'sections', 'pricePerCredit'));
    }

    public function destroy(AcademicIncomePlan $plan)
    {
        $plan->items()->delete();
        $plan->delete();

        return redirect()->route('head_of_finance.academic_income.index')
            ->with('success', 'ລຶບແຜນລາຍຮັບສຳເລັດ!');
    }

    // ─── Item CRUD ────────────────────────────────────────────────────────────

    public function storeItem(Request $request, AcademicIncomePlan $plan)
    {
        $isCredit = in_array($request->section_code, ['1.1', '1.3']);

        $rules = [
            'section_code'    => 'required|in:1.1,1.2,1.3,1.4',
            'item_name'       => 'required|string|max:255',
            'num_persons'     => 'required|integer|min:0',
            'nuol_percentage' => 'required|numeric|min:0|max:1',
        ];

        if ($isCredit) {
            $rules['num_credits']   = 'required|integer|min:0';
            $rules['student_year']  = 'required|in:1,2,3,4,masters_phd';
        } else {
            $rules['rate_per_person'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $maxOrder = $plan->items()->where('section_code', $request->section_code)->max('sort_order') ?? -1;

        AcademicIncomeItem::create([
            'plan_id'         => $plan->id,
            'section_code'    => $request->section_code,
            'sort_order'      => $maxOrder + 1,
            'item_name'       => $request->item_name,
            'num_credits'     => $isCredit ? $request->num_credits : null,
            'rate_per_person' => $isCredit ? null : $request->rate_per_person,
            'num_persons'     => $request->num_persons,
            'nuol_percentage' => $request->nuol_percentage,
            'student_year'    => $isCredit ? $request->student_year : null,
        ]);

        return redirect()->route('head_of_finance.academic_income.show', $plan)
            ->with('success', 'ເພີ່ມລາຍການສຳເລັດ!');
    }

    public function updateItem(Request $request, AcademicIncomePlan $plan, AcademicIncomeItem $item)
    {
        $isCredit = in_array($item->section_code, ['1.1', '1.3']);

        $rules = [
            'item_name'       => 'required|string|max:255',
            'num_persons'     => 'required|integer|min:0',
            'nuol_percentage' => 'required|numeric|min:0|max:1',
        ];

        if ($isCredit) {
            $rules['num_credits']  = 'required|integer|min:0';
            $rules['student_year'] = 'required|in:1,2,3,4,masters_phd';
        } else {
            $rules['rate_per_person'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        $item->update([
            'item_name'       => $request->item_name,
            'num_credits'     => $isCredit ? $request->num_credits : null,
            'rate_per_person' => $isCredit ? null : $request->rate_per_person,
            'num_persons'     => $request->num_persons,
            'nuol_percentage' => $request->nuol_percentage,
            'student_year'    => $isCredit ? $request->student_year : null,
        ]);

        return redirect()->route('head_of_finance.academic_income.show', $plan)
            ->with('success', 'ແກ້ໄຂລາຍການສຳເລັດ!');
    }

    public function destroyItem(AcademicIncomePlan $plan, AcademicIncomeItem $item)
    {
        $item->delete();

        return redirect()->route('head_of_finance.academic_income.show', $plan)
            ->with('success', 'ລຶບລາຍການສຳເລັດ!');
    }

    // ─── Summary / PDF ────────────────────────────────────────────────────────

    public function summary(AcademicIncomePlan $plan)
    {
        $plan->load('items');

        $pricePerCredit       = (float) AppSetting::get('price_per_credit', 35000);
        $teachingRateBsc      = (float) AppSetting::get('teaching_rate_bachelor', 0.40);
        $teachingRateMscPhd   = (float) AppSetting::get('teaching_rate_masters_phd', 0.60);

        $sections = [
            '1.1' => $plan->items->where('section_code', '1.1')->sortBy('sort_order')->values(),
            '1.2' => $plan->items->where('section_code', '1.2')->sortBy('sort_order')->values(),
            '1.3' => $plan->items->where('section_code', '1.3')->sortBy('sort_order')->values(),
            '1.4' => $plan->items->where('section_code', '1.4')->sortBy('sort_order')->values(),
        ];

        // Build summary rows: keyed by student_year for credit sections, by section for registration
        $summaryRows = $this->buildSummaryRows($sections, $pricePerCredit, $teachingRateBsc, $teachingRateMscPhd);

        return view('head_of_finance.academic-income.summary', compact(
            'plan', 'sections', 'summaryRows',
            'pricePerCredit', 'teachingRateBsc', 'teachingRateMscPhd'
        ));
    }

    public function exportPdf(AcademicIncomePlan $plan)
    {
        $plan->load('items');

        $pricePerCredit     = (float) AppSetting::get('price_per_credit', 35000);
        $teachingRateBsc    = (float) AppSetting::get('teaching_rate_bachelor', 0.40);
        $teachingRateMscPhd = (float) AppSetting::get('teaching_rate_masters_phd', 0.60);

        $sections = [
            '1.1' => $plan->items->where('section_code', '1.1')->sortBy('sort_order')->values(),
            '1.2' => $plan->items->where('section_code', '1.2')->sortBy('sort_order')->values(),
            '1.3' => $plan->items->where('section_code', '1.3')->sortBy('sort_order')->values(),
            '1.4' => $plan->items->where('section_code', '1.4')->sortBy('sort_order')->values(),
        ];

        $summaryRows = $this->buildSummaryRows($sections, $pricePerCredit, $teachingRateBsc, $teachingRateMscPhd);

        $html = view('head_of_finance.academic-income.summary', compact(
            'plan', 'sections', 'summaryRows',
            'pricePerCredit', 'teachingRateBsc', 'teachingRateMscPhd'
        ))->render();

        $defaultConfig    = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs         = $defaultConfig['fontDir'];
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData         = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 12,
            'margin_bottom' => 12,
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'notosanslao' => [
                    'R' => 'NotoSansLao-Regular.ttf',
                    'B' => 'NotoSansLao-Bold.ttf',
                ],
            ],
            'default_font' => 'notosanslao',
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="ຮ່າງສັງລວມລາຍຮັບວິຊາການ_' . $plan->fiscal_year . '.pdf"');
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    public function saveSetting(Request $request)
    {
        $request->validate([
            'price_per_credit'         => 'required|numeric|min:1',
            'teaching_rate_bachelor'   => 'required|numeric|min:0|max:1',
            'teaching_rate_masters_phd'=> 'required|numeric|min:0|max:1',
        ]);

        AppSetting::set('price_per_credit',         $request->price_per_credit);
        AppSetting::set('teaching_rate_bachelor',    $request->teaching_rate_bachelor);
        AppSetting::set('teaching_rate_masters_phd', $request->teaching_rate_masters_phd);

        return back()->with('success', 'ບັນທຶກການຕັ້ງຄ່າສຳເລັດ!');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function buildSummaryRows(array $sections, float $ppc, float $rateBsc, float $rateMsc): array
    {
        $rows = [];

        // ─ Registration fees (1.4 = year 1, 1.2 = year 2-4) ─
        $reg1  = $this->aggregateItems($sections['1.4'], $ppc);
        $reg24 = $this->aggregateItems($sections['1.2'], $ppc);

        $rows['reg_year1']  = array_merge($reg1,  ['label' => 'ນັກສຶກສາ ປີທີ 1 (ຄ່າລົງທະບຽນ)',       'type' => 'registration']);
        $rows['reg_year24'] = array_merge($reg24, ['label' => 'ນັກສຶກສາ ປີທີ 2,3,4 (ຄ່າລົງທະບຽນ)',   'type' => 'registration']);

        // ─ Credit fees grouped by student_year ─
        $creditYears = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', 'masters_phd' => 'masters_phd'];
        $allCreditItems = $sections['1.1']->concat($sections['1.3']);

        foreach ($creditYears as $year => $key) {
            $yearItems = $allCreditItems->filter(fn($i) => $i->student_year === $year);
            $agg = $this->aggregateItems($yearItems, $ppc);

            $teachingRate = ($year === 'masters_phd') ? $rateMsc : $rateBsc;
            $teaching     = $agg['kawt_income'] * $teachingRate;
            $remainder    = $agg['kawt_income'] - $teaching;

            $label = match($year) {
                '1'           => 'ນັກສຶກສາ ປີທີ 1 (ຄ່າໜ່ວຍກິດ)',
                '2'           => 'ນັກສຶກສາ ປີທີ 2 (ຄ່າໜ່ວຍກິດ)',
                '3'           => 'ນັກສຶກສາ ປີທີ 3 (ຄ່າໜ່ວຍກິດ)',
                '4'           => 'ນັກສຶກສາ ປີທີ 4 (ຄ່າໜ່ວຍກິດ)',
                'masters_phd' => 'ນັກສຶກສາ ປ.ໂທ + ເອກ (ຄ່າໜ່ວຍກິດ)',
                default       => $year,
            };

            $rows['credit_' . $year] = array_merge($agg, [
                'label'     => $label,
                'type'      => 'credit',
                'teaching'  => $teaching,
                'remainder' => $remainder,
            ]);
        }

        return $rows;
    }

    private function aggregateItems($items, float $ppc): array
    {
        $totalPersons = 0;
        $totalIncome  = 0;
        $nuolObligation = 0;
        $kawtIncome   = 0;

        foreach ($items as $item) {
            $totalPersons   += $item->num_persons;
            $t               = $item->totalIncome($ppc);
            $totalIncome    += $t;
            $nuolObligation += $item->nuolObligation($ppc);
            $kawtIncome     += $item->kawtIncome($ppc);
        }

        return compact('totalPersons', 'totalIncome', 'nuolObligation', 'kawtIncome');
    }
}
