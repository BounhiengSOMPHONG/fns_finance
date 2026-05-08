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

        $isMasters = $isCredit && $request->student_year === 'masters_phd';

        if ($isCredit) {
            $rules['student_year'] = 'required|in:1,2,3,4,masters_phd';
            if ($isMasters) {
                $rules['rate_per_person'] = 'required|numeric|min:0';
            } else {
                $rules['num_credits'] = 'required|integer|min:0';
            }
        } else {
            $rules['rate_per_person'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        $maxOrder = $plan->items()->where('section_code', $request->section_code)->max('sort_order') ?? -1;

        AcademicIncomeItem::create([
            'plan_id'         => $plan->id,
            'section_code'    => $request->section_code,
            'sort_order'      => $maxOrder + 1,
            'item_name'       => $request->item_name,
            'num_credits'     => ($isCredit && !$isMasters) ? $request->num_credits : null,
            'rate_per_person' => (!$isCredit || $isMasters) ? $request->rate_per_person : null,
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

        $isMasters = $isCredit && $request->student_year === 'masters_phd';

        if ($isCredit) {
            $rules['student_year'] = 'required|in:1,2,3,4,masters_phd';
            if ($isMasters) {
                $rules['rate_per_person'] = 'required|numeric|min:0';
            } else {
                $rules['num_credits'] = 'required|integer|min:0';
            }
        } else {
            $rules['rate_per_person'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        $item->update([
            'item_name'       => $request->item_name,
            'num_credits'     => ($isCredit && !$isMasters) ? $request->num_credits : null,
            'rate_per_person' => (!$isCredit || $isMasters) ? $request->rate_per_person : null,
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

    // ─── Load Defaults ────────────────────────────────────────────────────────

    public function loadDefaults(AcademicIncomePlan $plan)
    {
        $plan->items()->delete();

        $defaults = $this->defaultItems();
        $counters  = [];

        foreach ($defaults as $d) {
            $code = $d['section_code'];
            $counters[$code] = ($counters[$code] ?? -1) + 1;

            AcademicIncomeItem::create([
                'plan_id'         => $plan->id,
                'section_code'    => $code,
                'sort_order'      => $counters[$code],
                'item_name'       => $d['item_name'],
                'num_credits'     => $d['num_credits']    ?? null,
                'rate_per_person' => $d['rate_per_person'] ?? null,
                'num_persons'     => $d['num_persons'],
                'nuol_percentage' => $d['nuol_percentage'],
                'student_year'    => $d['student_year']   ?? null,
            ]);
        }

        return redirect()->route('head_of_finance.academic_income.show', $plan)
            ->with('success', 'ໂຫຼດຂໍ້ມູນເລີ່ມຕົ້ນຈາກ Planning 2026.xls ສຳເລັດ!');
    }

    private function defaultItems(): array
    {
        // Credits = rate / 35,000 for bachelor (all divide exactly).
        // Masters/PhD use rate_per_person directly (does not divide evenly by 35,000).
        return [
            // ─── 1.1 Credit fees year 2-4 + masters/PhD ───────────────────────
            // Year 2
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ວິທະຍາສາດຄອມ',                  'num_credits'=>37,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ພັດທະນາໂປຣແກຣມ',                'num_credits'=>37,'num_persons'=>70, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ພັດທະນາເວບໄຊ້',                 'num_credits'=>37,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຕໍ່ເນື່ອງວິທະຍາສາດຄອມ',          'num_credits'=>27,'num_persons'=>8,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຄະນິດສາດນໍາໃຊ້',                'num_credits'=>37,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ',        'num_credits'=>37,'num_persons'=>6,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຄະນິດສາດສະຖິຕິ',                'num_credits'=>37,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຊີວະທົ່ວໄປ',                     'num_credits'=>31,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ເທັກໂນໂລຍີ່ຊີວະພາບ',             'num_credits'=>31,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ເຄມີທົ່ວໄປ',                     'num_credits'=>35,'num_persons'=>7,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ເຄມີສິ່ງແວດລ້ອມ',                'num_credits'=>35,'num_persons'=>6,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຟີຊິກທົ່ວໄປ',                    'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ທໍລະນີຟີຊິກ',                    'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ວັດສະດຸສາດ',                     'num_credits'=>37,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'2','item_name'=>'ປີ 2 ຟິຊິກນິວເຄຣຍ',                  'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            // Year 3
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ວິທະຍາສາດຄອມ',                  'num_credits'=>33,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ພັດທະນາໂປຣແກຣມ',                'num_credits'=>38,'num_persons'=>35, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ພັດທະນາເວບໄຊ້',                 'num_credits'=>42,'num_persons'=>45, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຄະນິດທົ່ວໄປ',                   'num_credits'=>39,'num_persons'=>1,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ',        'num_credits'=>39,'num_persons'=>12, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຄະນິດສາດສະຖິຕິ',                'num_credits'=>37,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຊີວະວິທະຍາທົ່ວໄປ',               'num_credits'=>36,'num_persons'=>1,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ເທັກໂນໂລຍີ່ຊີວະພາບ',             'num_credits'=>33,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ເຄມີສາດທົ່ວໄປ',                  'num_credits'=>35,'num_persons'=>5,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ເຄມີສິ່ງແວດລ້ອມ',                'num_credits'=>36,'num_persons'=>3,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຟີຊິກສາດທົ່ວໄປ',                 'num_credits'=>34,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ທໍລະນີຟີຊິກ',                    'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ວັດສະດຸສາດ',                     'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'3','item_name'=>'ປີ 3 ຟິຊິກນິວເຄຣຍ',                  'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            // Year 4
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ວິທະຍາສາດຄອມ',                  'num_credits'=>27,'num_persons'=>55, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ພັດທະນາໂປຣແກຣມ',                'num_credits'=>30,'num_persons'=>30, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ພັດທະນາເວບໄຊ້',                 'num_credits'=>27,'num_persons'=>40, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຄະນິດທົ່ວໄປ',                   'num_credits'=>24,'num_persons'=>2,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ',        'num_credits'=>27,'num_persons'=>21, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຄະນິດສາດສະຖິຕິ',                'num_credits'=>27,'num_persons'=>11, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຊີວະວິທະຍາທົ່ວໄປ',               'num_credits'=>25,'num_persons'=>3,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ເທັກໂນໂລຍີ່ຊີວະພາບ',             'num_credits'=>23,'num_persons'=>3,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ເຄມີສາດທົ່ວໄປ',                  'num_credits'=>23,'num_persons'=>16, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ເຄມີສິ່ງແວດລ້ອມ',                'num_credits'=>22,'num_persons'=>5,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຟີຊິກສາດທົ່ວໄປ',                 'num_credits'=>27,'num_persons'=>2,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ທໍລະນີຟີຊິກ',                    'num_credits'=>30,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ວັດສະດຸສາດ',                     'num_credits'=>27,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.1','student_year'=>'4','item_name'=>'ປີ 4 ຟິຊິກນິວເຄຣຍ',                  'num_credits'=>28,'num_persons'=>1,  'nuol_percentage'=>0.17],
            // Masters/PhD — rate_per_person (rate does not divide evenly by 35,000)
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທຟິຊິກນໍາໃຊ້',       'rate_per_person'=>10800000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທຄະນິດສາດ',          'rate_per_person'=>11040000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປິລິນຍາໂທຊີວະວິທະຍາ',         'rate_per_person'=>9840000, 'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທເຄມີ',               'rate_per_person'=>11040000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທວິທະຍາສາດຄອມ',       'rate_per_person'=>11040000,'num_persons'=>3, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ຟີຊິກສາດຮູບແບບຄົ້ນຄວ້າ',       'rate_per_person'=>11040000,'num_persons'=>3, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ເຄມີສາດຮູບແບບຄົ້ນຄວ້າ',        'rate_per_person'=>11040000,'num_persons'=>7, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ຊີວະວິທະຍາຮູບແບບຄົ້ນຄວ້າ',     'rate_per_person'=>11040000,'num_persons'=>3, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາເອກຟິຊິກ',              'rate_per_person'=>22800000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.1','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາເອກຊີວະວິທະຍາ',         'rate_per_person'=>24000000,'num_persons'=>0, 'nuol_percentage'=>0.10],

            // ─── 1.2 Registration fees year 2-4 (705 students) ────────────────
            ['section_code'=>'1.2','item_name'=>'ຄ່າທຳນຽມນັກສຶກສາລົງທະບຽນ',             'rate_per_person'=>10000, 'num_persons'=>705,'nuol_percentage'=>0.25],
            ['section_code'=>'1.2','item_name'=>'ຄ່າອະນາໄມຫ້ອງຮຽນ',                       'rate_per_person'=>25000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບຳລຸງອຸປະກອນການຮຽນ-ການສອນ',              'rate_per_person'=>20000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບຳລຸງກິດຈະກຳນັກສຶກສາ',                   'rate_per_person'=>30000, 'num_persons'=>705,'nuol_percentage'=>0.30],
            ['section_code'=>'1.2','item_name'=>'ບຳລຸງວິທະຍາເຂດ',                          'rate_per_person'=>30000, 'num_persons'=>705,'nuol_percentage'=>0.40],
            ['section_code'=>'1.2','item_name'=>'ອຸດໜູນວຽກປ້ອງກັນ',                        'rate_per_person'=>20000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບຳລຸງຫ້ອງອ່ານ',                           'rate_per_person'=>30000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບຳລຸງຫ້ອງທົດລອງ',                         'rate_per_person'=>15000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບໍລິການສອບເສັງ',                           'rate_per_person'=>25000, 'num_persons'=>705,'nuol_percentage'=>0.00],
            ['section_code'=>'1.2','item_name'=>'ບໍລິການການລົງທະບຽນລາຍວິຊາ',               'rate_per_person'=>5000,  'num_persons'=>705,'nuol_percentage'=>0.00],

            // ─── 1.3 Credit fees year 1 + masters/PhD ─────────────────────────
            // Year 1 bachelor
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ວິທະຍາສາດຄອມພິວເຕີ ປີ 1',          'num_credits'=>37,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ການພັດທະນາໂປຣແກຣມ ປີ 1',            'num_credits'=>38,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ການພັດທະນາເວບໄຊ້ ປີ 1',              'num_credits'=>38,'num_persons'=>60, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມ ປີ 1',          'num_credits'=>37,'num_persons'=>10, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຄະນິດສາດສົດ ປີ 1',                   'num_credits'=>37,'num_persons'=>10, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ ປີ 1',       'num_credits'=>36,'num_persons'=>20, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຄະນິດສາດສະຖິຕິ ປີ 1',                'num_credits'=>36,'num_persons'=>0,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຊີວະສາດທົ່ວໄປ ປີ 1',                 'num_credits'=>37,'num_persons'=>5,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ເທັກໂນໂລຍີ່ຊີວະພາບ ປີ 1',             'num_credits'=>37,'num_persons'=>5,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ເຄມີທົ່ວໄປ ປີ 1',                     'num_credits'=>39,'num_persons'=>10, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ເຄມີສິ່ງແວດລ້ອມ ປີ 1',                'num_credits'=>39,'num_persons'=>10, 'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຟິຊິກທົ່ວໄປ ປີ 1',                    'num_credits'=>35,'num_persons'=>1,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ທໍລະນີຟິຊິກ ປີ 1',                    'num_credits'=>35,'num_persons'=>1,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ວັດສະດຸສາດ ປີ 1',                     'num_credits'=>35,'num_persons'=>1,  'nuol_percentage'=>0.17],
            ['section_code'=>'1.3','student_year'=>'1','item_name'=>'ຟິຊິກນິວເຄຣຍ ປີ 1',                  'num_credits'=>35,'num_persons'=>1,  'nuol_percentage'=>0.17],
            // Masters/PhD year-1 rates
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທຟິຊິກນໍາໃຊ້(ພະລັງງາທົດແທນ)','rate_per_person'=>15840000,'num_persons'=>4, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທຄະນິດສາດ',                    'rate_per_person'=>16560000,'num_persons'=>4, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປິລິນຍາໂທຊີວະວິທະຍາ',                    'rate_per_person'=>14760000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທເຄມີ',                           'rate_per_person'=>16560000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາໂທວິທະຍາສາດຄອມ',                  'rate_per_person'=>16560000,'num_persons'=>5, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ຟີຊິກສາດຮູບແບບຄົ້ນຄວ້າ ປີ 1',            'rate_per_person'=>16560000,'num_persons'=>4, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ເຄມີສາດຮູບແບບຄົ້ນຄວ້າ ປີ 1',             'rate_per_person'=>16560000,'num_persons'=>5, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ຊີວະວິທະຍາຮູບແບບຄົ້ນຄວ້າ ປີ 1',          'rate_per_person'=>16560000,'num_persons'=>4, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາເອກຟິຊິກ',                          'rate_per_person'=>34200000,'num_persons'=>0, 'nuol_percentage'=>0.10],
            ['section_code'=>'1.3','student_year'=>'masters_phd','item_name'=>'ປະລິນຍາເອກຊີວະວິທະຍາ',                     'rate_per_person'=>36000000,'num_persons'=>0, 'nuol_percentage'=>0.10],

            // ─── 1.4 Registration fees year 1 (324 students) ─────────────────
            ['section_code'=>'1.4','item_name'=>'ຄ່າທຳນຽມລົງທະບຽນປະຈໍາປີ',                'rate_per_person'=>15000,'num_persons'=>324,'nuol_percentage'=>1.00],
            ['section_code'=>'1.4','item_name'=>'ຄ່າຊຸດເອກະສານລົງທະບຽນ ນ/ສ ໃໝ່',          'rate_per_person'=>15000,'num_persons'=>324,'nuol_percentage'=>1.00],
            ['section_code'=>'1.4','item_name'=>'ຄ່າອະນາໄມຫ້ອງຮຽນ',                        'rate_per_person'=>25000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບຳລຸງອຸປະກອນການຮຽນ-ການສອນ',               'rate_per_person'=>20000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບຳລຸງກິດຈະກຳນັກສຶກສາ',                    'rate_per_person'=>30000,'num_persons'=>324,'nuol_percentage'=>0.30],
            ['section_code'=>'1.4','item_name'=>'ບຳລຸງວິທະຍາເຂດ',                           'rate_per_person'=>30000,'num_persons'=>324,'nuol_percentage'=>0.40],
            ['section_code'=>'1.4','item_name'=>'ອຸດໜູນວຽກປ້ອງກັນ',                         'rate_per_person'=>20000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບຳລຸງຫ້ອງອ່ານ',                            'rate_per_person'=>10000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບຳລຸງຫ້ອງທົດລອງ',                          'rate_per_person'=>15000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບໍລິການສອບເສັງ',                            'rate_per_person'=>25000,'num_persons'=>324,'nuol_percentage'=>0.00],
            ['section_code'=>'1.4','item_name'=>'ບໍລິການການລົງທະບຽນລາຍວິຊາ',                'rate_per_person'=>5000, 'num_persons'=>324,'nuol_percentage'=>0.00],
        ];
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
