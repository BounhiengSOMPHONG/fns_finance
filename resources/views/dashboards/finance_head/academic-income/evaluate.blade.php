@extends('layouts.admin')

@section('title', 'ປ້ອນຂໍ້ມູນ ປີ ' . $academicIncome->fiscal_year)

@section('content')

<style>
.ai-wrap { display:flex; flex-direction:column; gap:.65rem; width:100%; }
.ai-top {
    display:grid; grid-template-columns:auto 1fr; gap:.75rem; align-items:center;
    padding:.62rem .78rem; background:#fff;
    border:1px solid #dbe3ee; border-left:4px solid var(--fns-gold);
    border-radius:8px; box-shadow:0 2px 10px rgba(17,27,51,.045);
}
.ai-back {
    width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
    color:var(--fns-navy); background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px;
    text-decoration:none; transition:background .15s, transform .15s, border-color .15s;
}
.ai-back:hover { background:#f8fafc; border-color:#cbd5e1; transform:translateX(-2px); }
.ai-back svg { width:16px; height:16px; }
.ai-heading { min-width:0; }
.ai-kicker { display:block; color:#9b7410; font-size:.66rem; font-weight:900; }
.ai-title { margin:.08rem 0 0; color:var(--fns-navy); font-size:.98rem; line-height:1.25; font-weight:900; }
.ai-meta { margin-top:.12rem; color:var(--fns-gray-600); font-size:.7rem; }
.ai-steps { display:grid; grid-template-columns:repeat(2, 1fr); gap:.4rem; }
.ai-step-tab {
    display:flex; align-items:center; gap:.5rem; min-height:42px; padding:.45rem .58rem;
    border:1px solid #dbe3ee; background:#fff; color:var(--fns-navy);
    border-radius:8px; cursor:pointer; text-align:left; font-family:inherit;
    transition:background .15s, border-color .15s, box-shadow .15s;
}
.ai-step-tab:hover { border-color:#d7bf73; background:#fffdf4; }
.ai-step-tab.is-active { border-color:#d6ad39; background:#fff9e8; box-shadow:none; }
.ai-step-no {
    width:24px; height:24px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center;
    background:#eef2f7; color:var(--fns-navy); font-weight:900; flex-shrink:0;
}
.ai-step-tab.is-active .ai-step-no { background:var(--fns-gold); color:#111b33; }
.ai-step-copy strong { display:block; font-size:.78rem; line-height:1.2; }
.ai-step-copy span { display:none; }

.ai-step-panel {
    display:none; border:1px solid #dbe3ee; border-radius:8px; background:#fff;
    box-shadow:0 2px 10px rgba(26,39,68,.035); overflow:hidden;
}
.ai-step-panel.is-active { display:block; }
.ai-panel-head {
    display:grid; grid-template-columns:1fr minmax(230px, 360px); gap:1rem; align-items:end;
    padding:.85rem 1rem; background:#fbfdff; border-bottom:1px solid var(--fns-gray-200);
}
.ai-panel-head h2 { margin:0; color:var(--fns-navy); font-size:1rem; line-height:1.25; font-weight:900; }
.ai-panel-head p { margin:.22rem 0 0; color:var(--fns-gray-600); font-size:.78rem; line-height:1.5; }
.ai-search { position:relative; }
.ai-search svg {
    position:absolute; left:.72rem; top:50%; transform:translateY(-50%);
    width:15px; height:15px; color:var(--fns-gray-400); pointer-events:none;
}
.ai-search input {
    width:100%; border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff;
    padding:.58rem .75rem .58rem 2.15rem; color:var(--fns-navy); font-family:inherit; outline:none;
}
.ai-search input:focus { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.14); }
.ai-panel-body { padding:.65rem .7rem .75rem; }
.ai-section-note {
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    margin-bottom:.55rem; color:var(--fns-gray-600); font-size:.75rem; flex-wrap:wrap;
}
.ai-tally { white-space:nowrap; font-weight:900; color:var(--fns-navy); }
.ai-tally b { font-family:'Cinzel', serif; font-size:1rem; }

.ai-filter-btn {
    border:1px solid #dbe3ee; background:#fff; color:#475569;
    border-radius:7px; padding:.34rem .62rem; font-family:inherit; font-size:.72rem; font-weight:900;
    cursor:pointer; transition:background .15s, border-color .15s, color .15s;
}
.ai-filter-btn:hover { border-color:#cbd5e1; background:#f8fafc; }
.ai-filter-btn.is-active { background:#18325c; border-color:#18325c; color:#fff; }
.ai-table-tools {
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    margin-bottom:.55rem; flex-wrap:wrap;
}
.ai-filter-group { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; }
.ai-table-summary { display:none; }
.ai-table-summary b { color:var(--fns-navy); font-family:'Cinzel', serif; font-size:.95rem; }
.ai-student-tables { display:flex; flex-direction:column; gap:.85rem; }
.ai-degree-section {
    border:1px solid #dbe3ee; border-radius:8px; background:#fff; overflow:hidden;
}
.ai-degree-section.is-hidden { display:none; }
.ai-degree-head {
    display:flex; align-items:center; justify-content:space-between; gap:.8rem;
    padding:.48rem .62rem; background:#fbfdff; border-bottom:1px solid #e8edf4;
}
.ai-degree-head h3 { margin:0; color:var(--fns-navy); font-size:.86rem; font-weight:900; }
.ai-degree-head p { margin:.08rem 0 0; color:#64748b; font-size:.68rem; font-weight:700; }
.ai-degree-total { text-align:right; flex-shrink:0; }
.ai-degree-total span { display:block; color:#64748b; font-size:.66rem; font-weight:900; }
.ai-degree-total b { display:block; color:var(--fns-navy); font-family:'Cinzel', serif; font-size:1rem; line-height:1.1; }
.ai-table-scroll {
    width:100%; overflow:visible; background:#fff;
}
.ai-program-table { width:100%; min-width:0; table-layout:fixed; border-collapse:separate; border-spacing:0; }
.ai-program-table th,
.ai-program-table td {
    border-bottom:1px solid #e8edf4;
    border-right:1px solid #eef2f7;
    padding:.36rem .42rem;
    vertical-align:middle;
}
.ai-program-table thead th {
    position:sticky; top:0; z-index:2;
    background:#f8fafc; color:#334155; font-size:.75rem; font-weight:900;
    text-align:right; white-space:nowrap;
}
.ai-program-table thead th:first-child,
.ai-program-table .ai-program-name-col {
    position:sticky; left:0; z-index:3;
    text-align:left; background:#fff;
}
.ai-program-table thead th:first-child { z-index:4; background:#f8fafc; }
.ai-program-table tbody tr:nth-child(even) th,
.ai-program-table tbody tr:nth-child(even) td { background:#fcfdff; }
.ai-program-table tbody tr:hover th,
.ai-program-table tbody tr:hover td,
.ai-program-table tbody tr.is-row-active th,
.ai-program-table tbody tr.is-row-active td { background:#fff8df; }
.ai-program-table tbody tr.is-hidden { display:none; }
.ai-program-table tbody tr.is-dirty th,
.ai-program-table tbody tr.is-dirty td { background:#fbfdff; }
.ai-program-table tbody tr.is-dirty .ai-canonical-name::after {
    content:'ແກ້ໄຂແລ້ວ'; display:inline-flex; margin-left:.45rem; vertical-align:middle;
    border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; border-radius:999px;
    padding:.05rem .38rem; font-size:.58rem; font-weight:900;
}
.ai-program-name-col { width:34%; }
.ai-canonical-name { display:block; color:var(--fns-navy); font-size:.8rem; font-weight:900; line-height:1.25; }
.ai-display-names {
    display:block; margin-top:.08rem; color:#64748b; font-size:.64rem; font-weight:700;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.ai-row {
    display:flex; align-items:center; justify-content:flex-end; gap:.35rem; min-height:38px;
    border:1px solid transparent; border-radius:7px; background:transparent; cursor:text;
    transition:background .14s, border-color .14s, box-shadow .14s;
}
.ai-row:hover { background:#fffdf7; border-color:#ecd58f; }
.ai-row:focus-within { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.12); }
.ai-row.is-active { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.12); }
.ai-row:not(.is-zero) { border-color:#9fb3cc; background:#f8fbff; }
.ai-row.row-saving { opacity:.58; pointer-events:none; }
.ai-row.row-saved { animation:aiFlashGreen .9s ease; }
.ai-row.row-error { animation:aiFlashRed .9s ease; }
.ai-cell-input { width:100%; padding:.08rem; }
.ai-row-name { flex:1; min-width:0; display:flex; align-items:center; gap:.4rem; color:#334155; font-size:.82rem; }
.ai-row-txt { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ai-row.is-zero .ai-row-name { color:var(--fns-gray-500); }
.ai-warn-dot {
    width:7px; height:7px; border-radius:50%; flex-shrink:0;
    background:#d97706; box-shadow:0 0 0 2px rgba(217,119,6,.16);
}
.ai-num {
    width:min(5.6rem, 100%); flex-shrink:1; text-align:right; border:1px solid #cbd5e1; border-radius:7px;
    padding:.42rem .55rem; color:var(--fns-navy); background:#fff; font-family:'Cinzel', serif;
    font-size:.98rem; font-weight:900; outline:none; -moz-appearance:textfield;
}
.ai-num::-webkit-outer-spin-button, .ai-num::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.ai-num:focus { border-color:var(--fns-navy); box-shadow:0 0 0 3px rgba(46,63,110,.12); }
.ai-row.is-zero .ai-num { color:#9ca3af; background:#f8fafc; }
.ai-empty {
    grid-column:1 / -1; padding:1rem; border:1px dashed var(--fns-gray-200); border-radius:8px;
    text-align:center; color:var(--fns-gray-400); font-size:.82rem;
}
.ai-empty-cell { display:block; color:#cbd5e1; text-align:center; font-weight:900; }
.ai-col-total,
.ai-row-total,
.ai-program-table tfoot td,
.ai-program-table tfoot th {
    background:#f8fafc; color:var(--fns-navy); font-weight:900; text-align:right;
    font-variant-numeric:tabular-nums;
}
.ai-program-table tfoot th {
    position:sticky; left:0; z-index:3; text-align:left; background:#f8fafc;
}
.ai-nores {
    display:none; margin-top:.75rem; padding:.8rem; border:1px dashed var(--fns-gray-200);
    border-radius:8px; text-align:center; color:var(--fns-gray-500); font-size:.82rem;
}

.ai-fee-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(285px, 1fr)); gap:.62rem; align-items:stretch; }
.ai-item {
    display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:.65rem; align-items:center;
    min-height:82px; padding:.72rem .8rem; border-color:#c8d6ea; background:#fbfdff;
}
.ai-item:hover { background:#fffdf7; border-color:#d6ad39; }
.ai-item:focus-within { background:#fff; }
.ai-item .ai-row-name { flex-direction:column; align-items:flex-start; gap:.26rem; min-width:0; }
.ai-item-title {
    display:grid; grid-template-columns:auto minmax(0, 1fr); align-items:center; gap:.45rem;
    width:100%; color:#1f355f; font-weight:900;
}
.ai-item-tag {
    display:inline-flex; align-items:center; justify-content:center; min-width:2rem;
    border-radius:7px; background:#fff3c4; color:#73520b; padding:.12rem .35rem;
    font-family:'Cinzel', serif; font-size:.68rem; font-weight:900;
}
.ai-item .ai-row-txt { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ai-item-rate { color:#475569; font-size:.72rem; line-height:1.4; }
.ai-item-rate b { color:var(--fns-navy); }
.ai-item-rate.warn { color:#b45309; }
.ai-item-side { display:flex; align-items:center; justify-content:flex-end; gap:.42rem; min-width:0; }
.ai-item-side .ai-num { width:5.1rem; min-width:5.1rem; padding:.44rem .55rem; }
.ai-eq {
    border:1px solid #e2c66d; background:#fff9e8; color:#73520b; border-radius:8px;
    padding:.38rem .48rem; font-family:inherit; font-size:.68rem; font-weight:900; cursor:pointer;
    white-space:nowrap; line-height:1;
}
.ai-eq:hover { background:#fff3c4; }
.ai-rate-details {
    margin-top:1rem; border:1px solid #f1df9f; border-radius:8px; background:#fffdf4; overflow:hidden;
}
.ai-rate-details summary {
    cursor:pointer; padding:.75rem .9rem; color:#73520b; font-size:.82rem; font-weight:900;
}
.ai-rate-editor {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:.65rem;
    padding:0 .9rem .9rem;
}
.ai-rate-field {
    display:flex; align-items:center; justify-content:space-between; gap:.7rem;
    background:#fff; border:1px solid #f1df9f; border-radius:8px; padding:.55rem .65rem;
}
.ai-rate-field span { color:var(--fns-navy); font-size:.78rem; font-weight:800; }
.ai-rate-field input {
    width:7.6rem; border:1px solid #cbd5e1; border-radius:7px; padding:.4rem .5rem;
    color:var(--fns-navy); font-family:'Cinzel', serif; font-weight:800; text-align:right;
}
.ai-rate-field.row-saving { opacity:.58; pointer-events:none; }
.ai-rate-field.row-saved { animation:aiFlashGreen .9s ease; }
.ai-rate-field.row-error { animation:aiFlashRed .9s ease; }
.ai-toasts {
    position:fixed; right:1.5rem; bottom:1.5rem; z-index:9500;
    display:flex; flex-direction:column; gap:.55rem; pointer-events:none;
}
.ai-toast {
    display:flex; align-items:center; gap:.5rem; padding:.65rem .85rem; border-radius:8px;
    background:var(--fns-navy-deep); color:#fff; box-shadow:0 12px 30px -10px rgba(17,27,51,.48);
    font-size:.8rem; animation:aiToastIn .22s ease-out; pointer-events:auto;
}
.ai-toast.is-success { background:#166534; }
.ai-toast.is-error { background:#991b1b; }
@keyframes aiToastIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
@keyframes aiFlashGreen { 0%,100% { background:inherit; } 25% { background:#bbf7d0; } }
@keyframes aiFlashRed { 0%,100% { background:inherit; } 25% { background:#fecaca; } }

@media (max-width: 920px) {
    .ai-top { grid-template-columns:auto 1fr; }
    .ai-steps { grid-template-columns:1fr; }
    .ai-panel-head { grid-template-columns:1fr; }
    .ai-fee-grid { grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); }
}
@media (max-width: 560px) {
    .ai-top { border-radius:0; margin-left:-1rem; margin-right:-1rem; }
    .ai-fee-grid { grid-template-columns:1fr; }
    .ai-item { grid-template-columns:1fr; align-items:stretch; }
    .ai-item-side { width:100%; justify-content:space-between; }
    .ai-item-side .ai-num { width:6rem; min-width:6rem; }
    .ai-toasts { right:1rem; left:1rem; bottom:1rem; }
}
</style>

@php
    $codeBase = function ($program): string {
        $code = strtoupper((string) ($program->code ?? ''));
        $code = preg_replace('/^MR-/', '', $code);
        $code = preg_replace('/^[BMD]-/', '', $code);
        $code = preg_replace('/-Y\d+$/', '', $code);
        return $code ?: ('P' . $program->id);
    };

    $cleanName = function (string $name): string {
        $name = preg_replace('/ປະລ[ິີ]ນຍາ(ໂທ|ເອກ)/u', '', $name);
        $name = preg_replace('/ປິລິນຍາ(ໂທ|ເອກ)/u', '', $name);
        $name = str_replace(['ຮູບແບບຄົ້ນຄວ້າ', 'ທົ່ວໄປ'], '', $name);
        return trim($name) ?: $name;
    };

    $makeCell = function ($program, string $inputPrefix, string $section, bool $year1Split = false) use ($creditPrices, $existingItems): array {
        $totalCreditUnit = $program->latestCourseCredit?->course_credit_unit;
        $creditUnit = $totalCreditUnit;
        if ($totalCreditUnit && in_array($program->level, ['master', 'phd'], true)) {
            $creditUnit = (float) $totalCreditUnit * ($year1Split
                ? \App\Models\CourseCreditSplitSetting::year1For($program->level)
                : \App\Models\CourseCreditSplitSetting::year2For($program->level));
        }
        $price = $creditPrices[$program->level]?->credit_unit_price ?? null;
        $existing = $existingItems->get($section . '_' . $program->id);
        $value = (int) old($inputPrefix . '.' . $program->id, $existing?->student_count ?? 0);

        return [
            'programId' => $program->id,
            'inputPrefix' => $inputPrefix,
            'section' => $section,
            'name' => $program->name,
            'search' => \Illuminate\Support\Str::lower(($program->code ?? '') . ' ' . $program->name),
            'value' => $value,
            'warn' => ! $creditUnit || ! $price,
        ];
    };

    $bachelorRows = $programs13_bach
        ->concat($programs11->where('level', 'bachelor'))
        ->groupBy(fn ($program) => $codeBase($program))
        ->map(function ($programs, $base) use ($cleanName, $makeCell) {
            $cells = [];
            foreach ($programs as $program) {
                $year = (int) ($program->study_year ?: 1);
                if ($year < 1 || $year > 4) continue;
                $cells['y' . $year] = $makeCell(
                    $program,
                    $year === 1 ? 's13' : 's11',
                    $year === 1 ? '1.3' : '1.1'
                );
            }

            $programNames = $programs->pluck('name')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values();
            $label = $programNames->first() ?: $cleanName((string) $programs->first()->name);

            return [
                'label' => $label,
                'search' => \Illuminate\Support\Str::lower($label . ' ' . $programs->pluck('name')->implode(' ') . ' ' . $base),
                'names' => $programNames->skip(1)->implode(' · '),
                'department' => $programs->first()->academic_department ?: 'other',
                'departmentLabel' => $programs->first()->academic_department_label,
                'departmentOrder' => (int) ($programs->first()->department_sort_order ?? \App\Models\DegreeProgram::departmentOrder($programs->first()->academic_department)),
                'cells' => $cells,
            ];
        })
        ->sortBy([
            ['departmentOrder', 'asc'],
            ['label', 'asc'],
        ])
        ->values();

    $bachelorTables = $bachelorRows
        ->groupBy('department')
        ->map(function ($rows, string $department): array {
            $first = $rows->first();

            return [
                'key' => 'bachelor-' . $department,
                'level' => 'bachelor',
                'label' => 'ປ.ຕີ · ' . ($first['departmentLabel'] ?? \App\Models\DegreeProgram::DEPARTMENTS['other']['label']),
                'columns' => [
                    ['key' => 'y1', 'label' => 'ປີ 1'],
                    ['key' => 'y2', 'label' => 'ປີ 2'],
                    ['key' => 'y3', 'label' => 'ປີ 3'],
                    ['key' => 'y4', 'label' => 'ປີ 4'],
                ],
                'rows' => $rows->values()->all(),
            ];
        })
        ->values()
        ->all();

    $graduateRows = function (string $level) use ($programs13_master, $makeCell) {
        return $programs13_master
            ->where('level', $level)
            ->sortBy('name')
            ->map(function ($program) use ($makeCell) {
                $label = trim((string) $program->name);
                return [
                    'label' => $label,
                    'search' => \Illuminate\Support\Str::lower($label . ' ' . ($program->code ?? '') . ' ' . $program->name),
                    'names' => '',
                    'cells' => [
                        'y1' => $makeCell($program, 's13m', '1.3', true),
                        'y2' => $makeCell($program, 's11', '1.1', false),
                    ],
                ];
            })
            ->values()
            ->all();
    };

    $studentTables = array_merge($bachelorTables, [
        [
            'key' => 'master',
            'level' => 'master',
            'label' => 'ປ.ໂທ',
            'columns' => [
                ['key' => 'y1', 'label' => 'ປີ 1'],
                ['key' => 'y2', 'label' => 'ປີ 2+'],
            ],
            'rows' => $graduateRows('master'),
        ],
        [
            'key' => 'phd',
            'level' => 'phd',
            'label' => 'ປ.ເອກ',
            'columns' => [
                ['key' => 'y1', 'label' => 'ປີ 1'],
                ['key' => 'y2', 'label' => 'ປີ 2+'],
            ],
            'rows' => $graduateRows('phd'),
        ],
    ]);

    $items = [
        ['tag'=>'1.2', 'name'=>'students_1_2', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 2-4 (ຄວທ)', 'key'=>'1.2_',
         'rate'=> $feeYear2_4 ? number_format($feeYear2_4->total_rate,0).' ກີບ'.' (ປີ '.$feeYear2_4->start_year.')' : null,
         'warn'=>'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 2-4', 'eq'=>false],
        ['tag'=>'1.4', 'name'=>'students_1_4', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 1 (ຄວທ)', 'key'=>'1.4_',
         'rate'=> $feeYear1 ? number_format($feeYear1->total_rate,0).' ກີບ'.' (ປີ '.$feeYear1->start_year.')' : null,
         'warn'=>'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 1', 'eq'=>false],
        ['tag'=>'3', 'name'=>'students_2_1', 'title'=>$incomeRates->get('item3_rate')?->label ?? 'ລາຍການ 3', 'key'=>'2.1_',
         'rateField'=>'item3_rate', 'rateVal'=>(float)($incomeRates->get('item3_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
        ['tag'=>'4', 'name'=>'students_2_2', 'title'=>$incomeRates->get('item4_rate')?->label ?? 'ລາຍການ 4', 'key'=>'2.2_',
         'rateField'=>'item4_rate', 'rateVal'=>(float)($incomeRates->get('item4_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'5', 'name'=>'students_2_3', 'title'=>$incomeRates->get('item5_rate')?->label ?? 'ລາຍການ 5', 'key'=>'2.3_',
         'rateField'=>'item5_rate', 'rateVal'=>(float)($incomeRates->get('item5_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'6', 'name'=>'students_2_4', 'title'=>$incomeRates->get('item6_rate')?->label ?? 'ລາຍການ 6', 'key'=>'2.4_',
         'rateField'=>'item6_rate', 'rateVal'=>(float)($incomeRates->get('item6_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
    ];
    $rateItems = collect($items)->filter(fn($it) => !empty($it['rateField']));
@endphp

<form method="POST"
      action="{{ route('head_of_finance.academic-income.saveEvaluate', ['academicIncome' => $academicIncome->getKey()]) }}"
      data-autosave-url="{{ route('head_of_finance.academic-income.saveField', ['academicIncome' => $academicIncome->getKey()]) }}"
      class="ai-wrap">
@csrf
    <div class="ai-top">
        <a href="{{ route('head_of_finance.manage-plan.index') }}" class="ai-back" title="ກັບຄືນ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
        <div class="ai-heading">
            <span class="ai-kicker">ປ້ອນຈຳນວນນັກສຶກສາ</span>
            <h1 class="ai-title">ປະເມີນລາຍຮັບວິຊາການ ສົກປີ {{ $academicIncome->fiscal_year }}</h1>
            @if($academicIncome->creator)
                <div class="ai-meta">ສ້າງໂດຍ {{ $academicIncome->creator->full_name ?? $academicIncome->creator->username }}</div>
            @endif
        </div>
    </div>

    <div class="ai-steps" role="tablist" aria-label="ຂັ້ນຕອນປ້ອນລາຍຮັບ">
        <button type="button" class="ai-step-tab is-active" data-step-target="1">
            <span class="ai-step-no">1</span>
            <span class="ai-step-copy"><strong>ຈຳນວນນັກສຶກສາ</strong><span>ປ້ອນຕາມລະດັບ ແລະ ປີຮຽນ</span></span>
        </button>
        <button type="button" class="ai-step-tab" data-step-target="2">
            <span class="ai-step-no">2</span>
            <span class="ai-step-copy"><strong>ຄ່າລົງທະບຽນ ແລະ ຄ່າທຳນຽມ</strong><span>ລາຍການສຸດທ້າຍ</span></span>
        </button>
    </div>

    <section class="ai-step-panel is-active" data-step-panel="1">
        <div class="ai-panel-body">
            <div class="ai-section-note">
                <span class="ai-tally">ລວມນັກສຶກສາ: <b data-grand-program-total>0</b> ຄົນ</span>
            </div>
            <div class="ai-table-tools">
                <div class="ai-filter-group" aria-label="ຕົວກອງ">
                    <button type="button" class="ai-filter-btn is-active" data-filter-mode="all">ທັງໝົດ</button>
                    <button type="button" class="ai-filter-btn" data-filter-mode="zero">ຍັງບໍ່ກອກ / 0</button>
                    <button type="button" class="ai-filter-btn" data-filter-mode="dirty">ແກ້ໄຂແລ້ວ</button>
                </div>
                <div class="ai-table-summary">ວາງຂໍ້ມູນຈາກ Excel ໄດ້ຫຼາຍຊ່ອງ</div>
            </div>
            <div class="ai-student-tables">
                @foreach($studentTables as $table)
                    @include('dashboards.finance_head.academic-income._program-grid', ['table' => $table])
                @endforeach
            </div>
            <div class="ai-nores" data-nores="1">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບ “<span></span>”</div>
        </div>
    </section>

    <section class="ai-step-panel" data-step-panel="2">
        <div class="ai-panel-body">
            <div class="ai-section-note">
                <span>ປຸ່ມ <b>= 1.2+1.4</b> ຈະໃສ່ຈຳນວນລວມຈາກຄ່າລົງທະບຽນປີ 1 ແລະ ປີ 2-4.</span>
                <span class="ai-tally">ລວມລາຍການນີ້: <b data-tally="items">0</b> ຄົນ</span>
            </div>
            <div class="ai-fee-grid">
                @foreach($items as $it)
                    @php $val = (int) old($it['name'], $existingItems->get($it['key'])?->student_count ?? 0); @endphp
                    <label class="ai-row ai-item @if($val<=0) is-zero @endif"
                           data-name="{{ \Illuminate\Support\Str::lower($it['title']) }}"
                           data-save-kind="count"
                           data-item-name="{{ $it['name'] }}">
                        <span class="ai-row-name">
                            <span class="ai-item-title"><span class="ai-item-tag">{{ $it['tag'] }}</span> <span class="ai-row-txt" title="{{ $it['title'] }}">{{ $it['title'] }}</span></span>
                            @if(!empty($it['rateField']))
                                <span class="ai-item-rate">ອັດຕາປັດຈຸບັນ: <b data-rate-preview="{{ $it['rateField'] }}">{{ number_format((float) old($it['rateField'], $it['rateVal']), 0) }}</b> ກີບ</span>
                            @elseif(!empty($it['rate']))
                                <span class="ai-item-rate">ອັດຕາປັດຈຸບັນ: <b>{{ $it['rate'] }}</b></span>
                            @else
                                <span class="ai-item-rate warn">{{ $it['warn'] }}</span>
                            @endif
                        </span>
                        <span class="ai-item-side">
                            @if($it['eq'])
                                <button type="button" class="ai-eq" data-eq title="ໃສ່ຈຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4)">= 1.2+1.4</button>
                            @endif
                            <input type="number" name="{{ $it['name'] }}" min="0" inputmode="numeric" required
                                value="{{ $val }}" class="ai-num" data-sec="items" data-initial="{{ $val }}">
                        </span>
                    </label>
                @endforeach
            </div>
            <details class="ai-rate-details">
                <summary>ຕັ້ງຄ່າອັດຕາຂັ້ນສູງ</summary>
                <div class="ai-rate-editor">
                    @foreach($rateItems as $it)
                        <label class="ai-rate-field">
                            <span>{{ $it['tag'] }} · {{ $it['title'] }}</span>
                            <input type="number" name="{{ $it['rateField'] }}" min="0" step="1"
                                value="{{ old($it['rateField'], (int) $it['rateVal']) }}"
                                data-rate-input="{{ $it['rateField'] }}"
                                data-save-kind="rate"
                                title="ແກ້ໄຂອັດຕາ (ກີບ)">
                        </label>
                    @endforeach
                </div>
            </details>
            <div class="ai-nores" data-nores="2">ບໍ່ພົບລາຍການທີ່ກົງກັບ “<span></span>”</div>
        </div>
    </section>

</form>

<div id="aiToasts" class="ai-toasts" aria-live="polite"></div>

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const form = document.querySelector('.ai-wrap');
    const AUTOSAVE_URL = form?.dataset.autosaveUrl;
    const nums = Array.from(document.querySelectorAll('.ai-num'));
    const stepTabs = Array.from(document.querySelectorAll('[data-step-target]'));
    const stepPanels = Array.from(document.querySelectorAll('[data-step-panel]'));
    const fmt = new Intl.NumberFormat('en-US');
    const saveTimers = new WeakMap();
    let currentStep = 1;

    let currentFilterMode = 'all';

    const visibleNums = () => nums.filter(input => {
        if (input.offsetParent === null || input.disabled) return false;
        const section = input.closest('.ai-degree-section');
        const row = input.closest('.ai-table-row');
        return (!section || !section.classList.contains('is-hidden'))
            && (!row || !row.classList.contains('is-hidden'));
    });

    function focusNearestNumber(step = currentStep) {
        const panel = document.querySelector(`[data-step-panel="${step}"]`);
        const first = panel?.querySelector('.ai-num:not([disabled])');
        if (first) first.focus({ preventScroll: true });
    }

    function moveFocus(from, direction) {
        const inputs = visibleNums();
        const index = inputs.indexOf(from);
        const next = inputs[index + direction];
        if (next) next.focus();
        else if (direction > 0) from.blur();
    }

    function scheduleCountSave(row, delay = 520) {
        if (!row || row.dataset.saveKind !== 'count') return;
        clearTimeout(saveTimers.get(row));
        saveTimers.set(row, setTimeout(() => saveCountRow(row), delay));
    }

    function flushCountSave(row) {
        if (!row || row.dataset.saveKind !== 'count') return;
        clearTimeout(saveTimers.get(row));
        saveCountRow(row);
    }

    function showStep(step) {
        currentStep = Math.max(1, Math.min(2, step));
        stepTabs.forEach(tab => tab.classList.toggle('is-active', Number(tab.dataset.stepTarget) === currentStep));
        stepPanels.forEach(panel => panel.classList.toggle('is-active', Number(panel.dataset.stepPanel) === currentStep));
        const stepCurrent = document.getElementById('ai-step-current');
        if (stepCurrent) stepCurrent.textContent = currentStep;
        window.scrollTo({ top: 0, behavior: 'auto' });
        setTimeout(() => focusNearestNumber(currentStep), 40);
    }

    nums.forEach(el => {
        el.closest('.ai-row')?.addEventListener('click', event => {
            if (event.target !== el) el.focus();
        });
        el.addEventListener('focus', () => {
            el.select();
            el.closest('.ai-row')?.classList.add('is-active');
            el.closest('.ai-table-row')?.classList.add('is-row-active');
        });
        el.addEventListener('blur', () => {
            el.closest('.ai-row')?.classList.remove('is-active');
            el.closest('.ai-table-row')?.classList.remove('is-row-active');
        });
        el.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                flushCountSave(el.closest('.ai-row'));
                moveFocus(el, e.shiftKey ? -1 : 1);
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                moveFocus(el, 1);
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                moveFocus(el, -1);
            }
        });
        el.addEventListener('paste', e => {
            const pasted = e.clipboardData?.getData('text') || '';
            const matrix = pasted
                .replace(/\r/g, '')
                .split('\n')
                .filter(row => row.length > 0)
                .map(row => row.split('\t'));

            const filledCells = matrix.flat().filter(value => numberFromPaste(value) !== null).length;
            if (filledCells <= 1) return;
            e.preventDefault();

            const result = getTableTargets(el, matrix) || getLinearTargets(el, matrix);
            if (!result) return;

            recalc();
            [...new Set(result.touched)].forEach(row => flushCountSave(row));
            result.nextInput?.focus();
        });
        el.addEventListener('input', () => {
            recalc();
            scheduleCountSave(el.closest('.ai-row'));
        });
        el.addEventListener('blur', () => setTimeout(() => {
            if (el.closest('.ai-row')?.contains(document.activeElement)) return;
            flushCountSave(el.closest('.ai-row'));
        }, 120));
    });

    stepTabs.forEach(tab => tab.addEventListener('click', () => showStep(Number(tab.dataset.stepTarget))));
    document.querySelectorAll('[data-step-next]').forEach(btn => btn.addEventListener('click', () => showStep(currentStep + 1)));

    function numberFromPaste(value) {
        const clean = String(value || '').trim().replace(/,/g, '');
        if (clean === '' || Number.isNaN(Number(clean))) return null;
        return Math.max(0, parseInt(clean, 10) || 0);
    }

    function assignInput(input, value) {
        if (!input) return null;
        input.value = value;
        return input.closest('.ai-row');
    }

    function getTableTargets(startInput, matrix) {
        const table = startInput.closest('.ai-program-table');
        const startTr = startInput.closest('.ai-table-row');
        const startTd = startInput.closest('td');
        if (!table || !startTr || !startTd) return null;

        const visibleRows = Array.from(table.querySelectorAll('tbody .ai-table-row'))
            .filter(row => !row.classList.contains('is-hidden'));
        const startRowIndex = visibleRows.indexOf(startTr);
        const startCellIndex = Array.from(startTr.children).indexOf(startTd);
        if (startRowIndex < 0 || startCellIndex < 1) return null;

        const touched = [];
        matrix.forEach((pasteRow, rowOffset) => {
            const tr = visibleRows[startRowIndex + rowOffset];
            if (!tr) return;
            pasteRow.forEach((rawValue, colOffset) => {
                const value = numberFromPaste(rawValue);
                if (value === null) return;
                const td = tr.children[startCellIndex + colOffset];
                const touchedRow = assignInput(td?.querySelector('.ai-num'), value);
                if (touchedRow) touched.push(touchedRow);
            });
        });

        const widestRow = Math.max(...matrix.map(row => row.length));
        const lastRow = visibleRows[startRowIndex + matrix.length - 1];
        const lastCol = startCellIndex + widestRow - 1;
        const nextInput = lastRow?.children[lastCol + 1]?.querySelector('.ai-num')
            || visibleRows[startRowIndex + matrix.length]?.querySelector('.ai-num');

        return { touched, nextInput };
    }

    function getLinearTargets(startInput, matrix) {
        const values = matrix.flat().map(numberFromPaste).filter(value => value !== null);
        if (values.length <= 1) return null;

        const inputs = visibleNums();
        const start = inputs.indexOf(startInput);
        const touched = [];
        values.forEach((value, offset) => {
            const touchedRow = assignInput(inputs[start + offset], value);
            if (touchedRow) touched.push(touchedRow);
        });

        return { touched, nextInput: inputs[start + touched.length] };
    }

    function recalc() {
        const secSum = {};
        let grand = 0;
        let filled = 0;
        let programGrand = 0;
        nums.forEach(el => {
            const value = parseInt(el.value, 10) || 0;
            const sec = el.dataset.sec;
            secSum[sec] = (secSum[sec] || 0) + value;
            grand += value;
            if (el.dataset.level) {
                programGrand += value;
            }
            if (value > 0) {
                filled++;
                el.closest('.ai-row')?.classList.remove('is-zero');
            } else {
                el.closest('.ai-row')?.classList.add('is-zero');
            }
        });
        document.querySelectorAll('.ai-program-table').forEach(table => {
            const colSum = {};
            let tableTotal = 0;
            table.querySelectorAll('tbody .ai-table-row').forEach(row => {
                let rowTotal = 0;
                let rowDirty = false;
                row.querySelectorAll('.ai-num').forEach(input => {
                    const value = parseInt(input.value, 10) || 0;
                    rowTotal += value;
                    tableTotal += value;
                    colSum[input.dataset.col] = (colSum[input.dataset.col] || 0) + value;
                    if (String(input.value || '0') !== String(input.dataset.initial || '0')) {
                        rowDirty = true;
                    }
                });
                row.classList.toggle('is-dirty', rowDirty);
                row.dataset.filterState = rowDirty ? 'dirty' : (rowTotal <= 0 ? 'zero' : 'filled');
                const totalCell = row.querySelector('[data-row-total]');
                if (totalCell) totalCell.textContent = fmt.format(rowTotal);
            });
            table.querySelectorAll('[data-col-total]').forEach(cell => {
                cell.textContent = fmt.format(colSum[cell.dataset.colTotal] || 0);
            });
            table.querySelector('[data-table-total]').textContent = fmt.format(tableTotal);
            document.querySelector(`[data-degree-total="${table.dataset.programTable}"]`).textContent = fmt.format(tableTotal);
        });
        document.querySelectorAll('[data-tally]').forEach(el => {
            el.textContent = fmt.format(secSum[el.dataset.tally] || 0);
        });
        const grandEl = document.getElementById('ai-grand');
        const filledEl = document.getElementById('ai-filled');
        const totalEl = document.getElementById('ai-total');
        if (grandEl) grandEl.textContent = fmt.format(grand);
        if (filledEl) filledEl.textContent = filled;
        if (totalEl) totalEl.textContent = nums.length;
        document.querySelector('[data-grand-program-total]').textContent = fmt.format(programGrand);
        applyStepFilter(1);
    }

    const valueOf = name => parseInt(document.querySelector(`[name="${name}"]`)?.value || 0, 10);
    document.querySelectorAll('[data-eq]').forEach(btn => btn.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        const input = btn.closest('.ai-item')?.querySelector('.ai-num');
        if (!input) return;
        input.value = valueOf('students_1_2') + valueOf('students_1_4');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    }));

    function rowMatchesMode(row, mode) {
        if (mode === 'zero') return row.dataset.filterState === 'zero';
        if (mode === 'dirty') return row.dataset.filterState === 'dirty';
        return true;
    }

    function applyStepFilter(step) {
        const panel = document.querySelector(`[data-step-panel="${step}"]`);
        const filter = document.querySelector(`[data-step-filter="${step}"]`);
        const nores = document.querySelector(`[data-nores="${step}"]`);
        if (!panel) return;

        const q = filter ? filter.value.trim().toLowerCase() : '';
        let anyVisible = false;

        if (Number(step) === 1) {
            panel.querySelectorAll('.ai-degree-section').forEach(section => {
                section.classList.remove('is-hidden');

                section.querySelectorAll('.ai-table-row').forEach(row => {
                    const searchHit = !q || (row.dataset.name || '').includes(q);
                    const show = searchHit && rowMatchesMode(row, currentFilterMode);
                    row.classList.toggle('is-hidden', !show);
                    if (show) anyVisible = true;
                });
            });
        } else {
            panel.querySelectorAll('.ai-fee-grid > .ai-row[data-name]').forEach(row => {
                const input = row.querySelector('.ai-num');
                const value = parseInt(input?.value || '0', 10) || 0;
                const dirty = input && String(input.value || '0') !== String(input.dataset.initial || '0');
                row.dataset.filterState = dirty ? 'dirty' : (value <= 0 ? 'zero' : 'filled');
                const show = !q || (row.dataset.name || '').includes(q);
                row.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });
        }

        if (nores) {
            nores.querySelector('span').textContent = filter ? filter.value : '';
            nores.style.display = (!anyVisible && (q || currentFilterMode !== 'all')) ? 'block' : 'none';
        }
    }

    document.querySelectorAll('[data-step-filter]').forEach(filter => {
        filter.addEventListener('input', () => applyStepFilter(filter.dataset.stepFilter));
    });

    document.querySelectorAll('[data-filter-mode]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentFilterMode = btn.dataset.filterMode || 'all';
            document.querySelectorAll('[data-filter-mode]').forEach(item => item.classList.toggle('is-active', item === btn));
            applyStepFilter(1);
            focusNearestNumber(1);
        });
    });

    document.querySelectorAll('[data-rate-input]').forEach(input => {
        input.addEventListener('input', () => {
            const preview = document.querySelector(`[data-rate-preview="${input.dataset.rateInput}"]`);
            if (preview) preview.textContent = fmt.format(parseFloat(input.value) || 0);
        });
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveRateInput(input);
                input.blur();
            }
        });
        input.addEventListener('blur', () => saveRateInput(input));
    });

    function showToast(message, kind = 'success') {
        const wrap = document.getElementById('aiToasts');
        if (!wrap) return;
        const toast = document.createElement('div');
        toast.className = `ai-toast is-${kind}`;
        toast.textContent = message;
        wrap.appendChild(toast);
        setTimeout(() => toast.remove(), 2200);
    }

    async function sendAutosave(target, payload) {
        if (!AUTOSAVE_URL || !target) return;
        if (target.dataset.isSaving === '1') {
            target.dataset.pendingSave = JSON.stringify(payload);
            return;
        }

        target.dataset.isSaving = '1';
        target.classList.add('row-saving');
        try {
            const res = await fetch(AUTOSAVE_URL, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            target.classList.remove('row-saving');
            if (!res.ok || !data.success) throw new Error(data.message || 'Error');

            target.classList.add('row-saved');
            setTimeout(() => target.classList.remove('row-saved'), 900);
        } catch {
            target.classList.add('row-error');
            setTimeout(() => target.classList.remove('row-error'), 900);
            showToast('ບໍ່ສາມາດບັນທຶກໄດ້', 'error');
        } finally {
            target.dataset.isSaving = '0';
            target.classList.remove('row-saving');
            if (target.dataset.pendingSave) {
                const nextPayload = JSON.parse(target.dataset.pendingSave);
                target.dataset.pendingSave = '';
                sendAutosave(target, nextPayload);
            }
        }
    }

    function saveCountRow(row) {
        if (!row || row.dataset.saveKind !== 'count') return;
        const input = row.querySelector('.ai-num');
        if (!input) return;

        const payload = {
            type: 'count',
            student_count: parseInt(input.value, 10) || 0,
            input_prefix: row.dataset.inputPrefix || null,
            program_id: row.dataset.programId || null,
            item_name: row.dataset.itemName || null,
        };

        sendAutosave(row, payload);
    }

    function saveRateInput(input) {
        if (!input || input.dataset.saveKind !== 'rate') return;
        const target = input.closest('.ai-rate-field');
        sendAutosave(target, {
            type: 'rate',
            rate_key: input.dataset.rateInput,
            rate: parseFloat(input.value) || 0,
        });
    }

    recalc();
})();
</script>
@endpush

@endsection
