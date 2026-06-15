@extends('layouts.admin')

@section('title', 'ປ້ອນຂໍ້ມູນ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ປ້ອນຂໍ້ມູນປະເມີນລາຍຮັບ ປີ ' . $academicIncome->fiscal_year)

@section('content')

<style>
.ai-wrap { display:flex; flex-direction:column; gap:1rem; width:100%; }
.ai-top {
    position:sticky; top:0; z-index:24;
    display:grid; grid-template-columns:auto 1fr auto; gap:.85rem; align-items:center;
    padding:.85rem 1rem; background:rgba(248,247,244,.96); border:1px solid var(--fns-gray-200);
    border-radius:8px; box-shadow:0 10px 28px rgba(17,27,51,.08); backdrop-filter:blur(10px);
}
.ai-back {
    width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center;
    color:var(--fns-navy); background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px;
    text-decoration:none; transition:background .15s, transform .15s, border-color .15s;
}
.ai-back:hover { background:#f8fafc; border-color:#cbd5e1; transform:translateX(-2px); }
.ai-back svg { width:16px; height:16px; }
.ai-heading { min-width:0; }
.ai-kicker { display:block; color:var(--fns-gold); font-size:.68rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
.ai-title { margin:.12rem 0 0; color:var(--fns-navy); font-size:1.05rem; line-height:1.25; }
.ai-meta { margin-top:.2rem; color:var(--fns-gray-500); font-size:.76rem; }
.ai-stats { display:grid; grid-template-columns:repeat(3, minmax(92px, 1fr)); gap:.45rem; }
.ai-stat {
    background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px;
    padding:.48rem .58rem; min-width:0;
}
.ai-stat span { display:block; color:var(--fns-gray-400); font-size:.65rem; font-weight:900; white-space:nowrap; }
.ai-stat b { display:block; margin-top:.08rem; color:var(--fns-navy); font-family:'Cinzel', serif; font-size:1rem; line-height:1.1; }

.ai-steps { display:grid; grid-template-columns:repeat(3, 1fr); gap:.55rem; }
.ai-step-tab {
    display:flex; align-items:center; gap:.65rem; min-height:64px; padding:.65rem .75rem;
    border:1px solid var(--fns-gray-200); background:#fff; color:var(--fns-navy);
    border-radius:8px; cursor:pointer; text-align:left; font-family:inherit;
    transition:background .15s, border-color .15s, box-shadow .15s;
}
.ai-step-tab:hover { border-color:#d7bf73; background:#fffdf4; }
.ai-step-tab.is-active { border-color:var(--fns-gold); background:#fff8df; box-shadow:0 8px 20px rgba(201,153,26,.14); }
.ai-step-no {
    width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;
    background:#eef2f7; color:var(--fns-navy); font-weight:900; flex-shrink:0;
}
.ai-step-tab.is-active .ai-step-no { background:var(--fns-gold); color:#111b33; }
.ai-step-copy strong { display:block; font-size:.83rem; line-height:1.25; }
.ai-step-copy span { display:block; margin-top:.15rem; color:var(--fns-gray-500); font-size:.72rem; line-height:1.25; }

.ai-step-panel {
    display:none; border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff;
    box-shadow:0 2px 12px rgba(26,39,68,.05); overflow:hidden;
}
.ai-step-panel.is-active { display:block; }
.ai-panel-head {
    display:grid; grid-template-columns:1fr minmax(230px, 360px); gap:1rem; align-items:end;
    padding:1rem 1.1rem; background:#fbfbfc; border-bottom:1px solid var(--fns-gray-200);
}
.ai-panel-head h2 { margin:0; color:var(--fns-navy); font-size:1.08rem; line-height:1.25; }
.ai-panel-head p { margin:.28rem 0 0; color:var(--fns-gray-500); font-size:.82rem; line-height:1.55; }
.ai-search { position:relative; }
.ai-search svg {
    position:absolute; left:.72rem; top:50%; transform:translateY(-50%);
    width:15px; height:15px; color:var(--fns-gray-400); pointer-events:none;
}
.ai-search input {
    width:100%; border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff;
    padding:.62rem .75rem .62rem 2.15rem; color:var(--fns-navy); font-family:inherit; outline:none;
}
.ai-search input:focus { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.14); }
.ai-panel-body { padding:1rem 1.1rem 1.1rem; }
.ai-section-note {
    display:flex; align-items:center; justify-content:space-between; gap:.8rem;
    margin-bottom:.7rem; color:var(--fns-gray-500); font-size:.78rem;
}
.ai-tally { white-space:nowrap; font-weight:900; color:var(--fns-navy); }
.ai-tally b { font-family:'Cinzel', serif; font-size:1rem; }

.ai-group { margin-top:1rem; }
.ai-group:first-of-type { margin-top:0; }
.ai-glabel {
    display:flex; align-items:center; gap:.55rem; margin-bottom:.45rem;
    color:var(--fns-navy); font-size:.76rem; font-weight:900;
}
.ai-glabel::before { content:''; width:6px; height:18px; border-radius:999px; background:var(--fns-gold); }
.ai-gcount { color:var(--fns-gray-400); font-size:.72rem; font-weight:700; }
.ai-rows { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:.45rem .7rem; }
.ai-row {
    display:flex; align-items:center; gap:.75rem; min-height:46px; padding:.42rem .5rem .42rem .65rem;
    border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff; cursor:text;
    transition:background .14s, border-color .14s, box-shadow .14s;
}
.ai-row:hover { background:#fffdf7; border-color:#ecd58f; }
.ai-row.is-active { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.12); }
.ai-row:not(.is-zero) { border-color:#cbd5e1; background:#fbfdff; }
.ai-row.row-saving { opacity:.58; pointer-events:none; }
.ai-row.row-saved { animation:aiFlashGreen .9s ease; }
.ai-row.row-error { animation:aiFlashRed .9s ease; }
.ai-row-name { flex:1; min-width:0; display:flex; align-items:center; gap:.4rem; color:#334155; font-size:.84rem; }
.ai-row-txt { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ai-row.is-zero .ai-row-name { color:var(--fns-gray-500); }
.ai-warn-dot {
    width:7px; height:7px; border-radius:50%; flex-shrink:0;
    background:#d97706; box-shadow:0 0 0 2px rgba(217,119,6,.16);
}
.ai-num {
    width:5.7rem; flex-shrink:0; text-align:right; border:1px solid #cbd5e1; border-radius:8px;
    padding:.44rem .58rem; color:var(--fns-navy); background:#fff; font-family:'Cinzel', serif;
    font-size:.95rem; font-weight:800; outline:none; -moz-appearance:textfield;
}
.ai-num::-webkit-outer-spin-button, .ai-num::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.ai-num:focus { border-color:var(--fns-navy); box-shadow:0 0 0 3px rgba(46,63,110,.12); }
.ai-row.is-zero .ai-num { color:var(--fns-gray-400); }
.ai-empty {
    grid-column:1 / -1; padding:1rem; border:1px dashed var(--fns-gray-200); border-radius:8px;
    text-align:center; color:var(--fns-gray-400); font-size:.82rem;
}
.ai-nores {
    display:none; margin-top:.75rem; padding:.8rem; border:1px dashed var(--fns-gray-200);
    border-radius:8px; text-align:center; color:var(--fns-gray-500); font-size:.82rem;
}

.ai-fee-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:.65rem; }
.ai-item { min-height:72px; align-items:flex-start; padding:.7rem; }
.ai-item .ai-row-name { flex-direction:column; align-items:flex-start; gap:.2rem; }
.ai-item-title { display:flex; align-items:center; gap:.45rem; color:#334155; font-weight:900; }
.ai-item-tag {
    display:inline-flex; align-items:center; justify-content:center; min-width:2rem;
    border-radius:7px; background:#fff3c4; color:#73520b; padding:.12rem .35rem;
    font-family:'Cinzel', serif; font-size:.68rem; font-weight:900;
}
.ai-item-rate { color:var(--fns-gray-500); font-size:.73rem; line-height:1.35; }
.ai-item-rate b { color:var(--fns-navy); }
.ai-item-rate.warn { color:#b45309; }
.ai-item-side { display:flex; align-items:center; gap:.45rem; flex-shrink:0; }
.ai-eq {
    border:1px solid #e2c66d; background:#fff9e8; color:#73520b; border-radius:8px;
    padding:.35rem .5rem; font-family:inherit; font-size:.72rem; font-weight:900; cursor:pointer;
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
.ai-panel-actions {
    display:flex; justify-content:space-between; align-items:center; gap:.7rem;
    padding:.9rem 1.1rem; border-top:1px solid var(--fns-gray-200); background:#fbfbfc;
}
.ai-action-group { display:flex; gap:.55rem; flex-wrap:wrap; }
.ai-nav-btn {
    border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff; color:var(--fns-navy);
    padding:.62rem .9rem; font-family:inherit; font-size:.82rem; font-weight:900; cursor:pointer;
}
.ai-nav-btn:hover { background:#f8fafc; }
.ai-nav-btn-primary { border-color:var(--fns-gold); background:var(--fns-gold); color:#111b33; box-shadow:0 8px 18px rgba(201,153,26,.2); }
.ai-submit-bar {
    position:sticky; bottom:0; z-index:20; display:flex; align-items:center; gap:.65rem;
    padding:.85rem 1rem; border:1px solid var(--fns-gray-200); border-radius:8px;
    background:rgba(248,247,244,.96); box-shadow:0 -10px 28px rgba(17,27,51,.08); backdrop-filter:blur(10px);
}
.ai-submit-bar .fns-btn-primary { padding:.65rem 1.15rem; font-size:.86rem; }
.ai-submit-note { margin-left:auto; color:var(--fns-gray-500); font-size:.78rem; text-align:right; }
.ai-submit-note b { display:block; color:var(--fns-navy); font-size:.86rem; }
.ai-toasts {
    position:fixed; right:1.2rem; bottom:5.5rem; z-index:9500;
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
    .ai-stats { grid-column:1 / -1; grid-template-columns:repeat(3, 1fr); }
    .ai-steps { grid-template-columns:1fr; }
    .ai-panel-head { grid-template-columns:1fr; }
}
@media (max-width: 560px) {
    .ai-top, .ai-submit-bar { border-radius:0; margin-left:-1rem; margin-right:-1rem; }
    .ai-stats { grid-template-columns:1fr; }
    .ai-rows, .ai-fee-grid { grid-template-columns:1fr; }
    .ai-item { flex-direction:column; }
    .ai-item-side { width:100%; justify-content:space-between; }
    .ai-panel-actions, .ai-submit-bar { align-items:stretch; flex-direction:column; }
    .ai-action-group, .ai-submit-bar .fns-btn { width:100%; }
    .ai-action-group .ai-nav-btn { flex:1; }
    .ai-submit-note { margin-left:0; text-align:left; }
}
</style>

@php
    $groups11 = $programs11->groupBy(fn($p) =>
        $p->level === 'bachelor' ? 'ປ.ຕີ ປີ ' . ($p->study_year ?? '—')
        : ($p->level === 'master' ? 'ປ.ໂທ' : 'ປ.ເອກ'));
    $groups13MasterPhd = $programs13_master->groupBy(fn($p) =>
        $p->level === 'master' ? 'ປ.ໂທ' : 'ປ.ເອກ');
    $items = [
        ['tag'=>'1.2', 'name'=>'students_1_2', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 2-4 (ຄວທ)', 'key'=>'1.2_',
         'rate'=> $feeYear2_4 ? number_format($feeYear2_4->total_rate,0).' ກີບ'.' (ປີ '.$feeYear2_4->start_year.')' : null,
         'warn'=>'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 2-4', 'eq'=>false],
        ['tag'=>'1.4', 'name'=>'students_1_4', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 1 (ຄວທ)', 'key'=>'1.4_',
         'rate'=> $feeYear1 ? number_format($feeYear1->total_rate,0).' ກີບ'.' (ປີ '.$feeYear1->start_year.')' : null,
         'warn'=>'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 1', 'eq'=>false],
        ['tag'=>'3', 'name'=>'students_2_1', 'title'=>$incomeRates->get('item3_rate')?->label ?? 'Item 3', 'key'=>'2.1_',
         'rateField'=>'item3_rate', 'rateVal'=>(float)($incomeRates->get('item3_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
        ['tag'=>'4', 'name'=>'students_2_2', 'title'=>$incomeRates->get('item4_rate')?->label ?? 'Item 4', 'key'=>'2.2_',
         'rateField'=>'item4_rate', 'rateVal'=>(float)($incomeRates->get('item4_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'5', 'name'=>'students_2_3', 'title'=>$incomeRates->get('item5_rate')?->label ?? 'Item 5', 'key'=>'2.3_',
         'rateField'=>'item5_rate', 'rateVal'=>(float)($incomeRates->get('item5_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'6', 'name'=>'students_2_4', 'title'=>$incomeRates->get('item6_rate')?->label ?? 'Item 6', 'key'=>'2.4_',
         'rateField'=>'item6_rate', 'rateVal'=>(float)($incomeRates->get('item6_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
    ];
    $rateItems = collect($items)->filter(fn($it) => !empty($it['rateField']));
@endphp

<form method="POST"
      action="{{ route('head_of_finance.academic-income.saveEvaluate', $academicIncome) }}"
      data-autosave-url="{{ route('head_of_finance.academic-income.saveField', $academicIncome) }}"
      class="ai-wrap">
@csrf
    <div class="ai-top">
        <a href="{{ route('head_of_finance.manage-plan.index') }}" class="ai-back" title="ກັບຄືນ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
        <div class="ai-heading">
            <span class="ai-kicker">ປ້ອນຈຳນວນນັກສຶກສາ</span>
            <h1 class="ai-title">ປະເມີນລາຍຮັບວິຊາການ ສົກປີ {{ $academicIncome->fiscal_year }}</h1>
            <div class="ai-meta">
                @if($academicIncome->creator)
                    ສ້າງໂດຍ {{ $academicIncome->creator->full_name ?? $academicIncome->creator->username }} ·
                @endif
                ກອກຈຳນວນຄົນ ແລ້ວລະບົບຈະຄຳນວນລາຍຮັບໃຫ້
            </div>
        </div>
        <div class="ai-stats" aria-live="polite">
            <div class="ai-stat"><span>ຂັ້ນຕອນ</span><b><span id="ai-step-current">1</span>/3</b></div>
            <div class="ai-stat"><span>ປ້ອນແລ້ວ</span><b><span id="ai-filled">0</span>/<span id="ai-total">0</span></b></div>
            <div class="ai-stat"><span>ນ/ສ ລວມ</span><b id="ai-grand">0</b></div>
        </div>
    </div>

    <div class="ai-steps" role="tablist" aria-label="ຂັ້ນຕອນປ້ອນລາຍຮັບ">
        <button type="button" class="ai-step-tab is-active" data-step-target="1">
            <span class="ai-step-no">1</span>
            <span class="ai-step-copy"><strong>ນັກສຶກສາ ປີ 1</strong><span>ປ້ອນປີ 1 ກ່ອນ</span></span>
        </button>
        <button type="button" class="ai-step-tab" data-step-target="2">
            <span class="ai-step-no">2</span>
            <span class="ai-step-copy"><strong>ປີ 2-4 / ປ.ໂທ / ປ.ເອກ</strong><span>ກຸ່ມຄ່າໜ່ວຍກິດ</span></span>
        </button>
        <button type="button" class="ai-step-tab" data-step-target="3">
            <span class="ai-step-no">3</span>
            <span class="ai-step-copy"><strong>ຄ່າລົງທະບຽນ ແລະ ຄ່າທຳນຽມ</strong><span>ລາຍການສຸດທ້າຍ</span></span>
        </button>
    </div>

    <section class="ai-step-panel is-active" data-step-panel="1">
        <div class="ai-panel-head">
            <div>
                <h2>1. ນັກສຶກສາ ປີ 1</h2>
                <p>ກອກຈຳນວນນັກສຶກສາປີ 1 ຂອງແຕ່ລະສາຂາ. ບ່ອນໃດບໍ່ມີໃຫ້ໃສ່ 0.</p>
            </div>
            <label class="ai-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                <input type="text" data-step-filter="1" placeholder="ຄົ້ນຫາສາຂາປີ 1..." autocomplete="off">
            </label>
        </div>
        <div class="ai-panel-body">
            <div class="ai-section-note">
                <span>ສູດຈະຖືກຄຳນວນອັດຕະໂນມັດຕາມຄ່າໜ່ວຍກິດທີ່ຕັ້ງໄວ້.</span>
                <span class="ai-tally">ລວມປີ 1: <b data-tally="1.3">0</b> ຄົນ</span>
            </div>
            <div class="ai-group">
                <div class="ai-glabel">ປ.ຕີ ປີ 1 <span class="ai-gcount">{{ $programs13_bach->count() }} ສາຂາ</span></div>
                @include('dashboards.finance_head.academic-income._program-grid', [
                    'programs' => $programs13_bach, 'section' => '1.3', 'inputPrefix' => 's13',
                ])
            </div>
            @foreach($groups13MasterPhd as $label => $progs)
                <div class="ai-group">
                    <div class="ai-glabel">{{ $label }} ປີ 1 <span class="ai-gcount">{{ $progs->count() }} ສາຂາ</span></div>
                    @include('dashboards.finance_head.academic-income._program-grid', [
                        'programs' => $progs, 'section' => '1.3', 'inputPrefix' => 's13m', 'useYear1Unit' => true,
                    ])
                </div>
            @endforeach
            <div class="ai-nores" data-nores="1">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບ “<span></span>”</div>
        </div>
        <div class="ai-panel-actions">
            <span class="ai-meta">ຂັ້ນຕອນ 1 ຈາກ 3</span>
            <div class="ai-action-group">
                <button type="button" class="ai-nav-btn ai-nav-btn-primary" data-step-next>ຖັດໄປ</button>
            </div>
        </div>
    </section>

    <section class="ai-step-panel" data-step-panel="2">
        <div class="ai-panel-head">
            <div>
                <h2>2. ນັກສຶກສາ ປີ 2-4 / ປ.ໂທ / ປ.ເອກ</h2>
                <p>ກອກຈຳນວນນັກສຶກສາກຸ່ມຄ່າໜ່ວຍກິດ. ແບ່ງຕາມປີຮຽນ ແລະ ລະດັບການສຶກສາ.</p>
            </div>
            <label class="ai-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                <input type="text" data-step-filter="2" placeholder="ຄົ້ນຫາສາຂາປີ 2-4..." autocomplete="off">
            </label>
        </div>
        <div class="ai-panel-body">
            <div class="ai-section-note">
                <span>ລະບົບຈະນຳຈຳນວນຄົນໄປຄູນກັບໜ່ວຍກິດ ແລະ ລາຄາທີ່ຕັ້ງໄວ້.</span>
                <span class="ai-tally">ລວມກຸ່ມນີ້: <b data-tally="1.1">0</b> ຄົນ</span>
            </div>
            @foreach($groups11 as $label => $progs)
                <div class="ai-group">
                    <div class="ai-glabel">{{ $label }} <span class="ai-gcount">{{ $progs->count() }} ສາຂາ</span></div>
                    @include('dashboards.finance_head.academic-income._program-grid', [
                        'programs' => $progs, 'section' => '1.1', 'inputPrefix' => 's11',
                    ])
                </div>
            @endforeach
            <div class="ai-nores" data-nores="2">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບ “<span></span>”</div>
        </div>
        <div class="ai-panel-actions">
            <span class="ai-meta">ຂັ້ນຕອນ 2 ຈາກ 3</span>
            <div class="ai-action-group">
                <button type="button" class="ai-nav-btn" data-step-prev>ກັບຄືນ</button>
                <button type="button" class="ai-nav-btn ai-nav-btn-primary" data-step-next>ຖັດໄປ</button>
            </div>
        </div>
    </section>

    <section class="ai-step-panel" data-step-panel="3">
        <div class="ai-panel-head">
            <div>
                <h2>3. ຄ່າລົງທະບຽນ ແລະ ຄ່າທຳນຽມ</h2>
                <p>ກອກຈຳນວນຄົນຂອງລາຍການທີ່ເຫຼືອ. ອັດຕາລາຄາຈະສະແດງເພື່ອອ້າງອີງ ແລະ ສາມາດເປີດແກ້ໄດ້ໃນຂັ້ນສູງ.</p>
            </div>
            <label class="ai-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                <input type="text" data-step-filter="3" placeholder="ຄົ້ນຫາລາຍການ..." autocomplete="off">
            </label>
        </div>
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
                                value="{{ $val }}" class="ai-num" data-sec="items">
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
            <div class="ai-nores" data-nores="3">ບໍ່ພົບລາຍການທີ່ກົງກັບ “<span></span>”</div>
        </div>
        <div class="ai-panel-actions">
            <span class="ai-meta">ຂັ້ນຕອນ 3 ຈາກ 3</span>
            <div class="ai-action-group">
                <button type="button" class="ai-nav-btn" data-step-prev>ກັບຄືນ</button>
            </div>
        </div>
    </section>

    <div class="ai-submit-bar">
        <button type="submit" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            ບັນທຶກ
        </button>
        <a href="{{ route('head_of_finance.manage-plan.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        <span class="ai-submit-note">
            <b>ລະບົບຈະຄຳນວນລາຍຮັບໃຫ້ອັດຕະໂນມັດ</b>
            ກວດຈຳນວນຄົນໃຫ້ຄົບກ່ອນບັນທຶກ
        </span>
    </div>
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
    let currentStep = 1;

    function showStep(step) {
        currentStep = Math.max(1, Math.min(3, step));
        stepTabs.forEach(tab => tab.classList.toggle('is-active', Number(tab.dataset.stepTarget) === currentStep));
        stepPanels.forEach(panel => panel.classList.toggle('is-active', Number(panel.dataset.stepPanel) === currentStep));
        document.getElementById('ai-step-current').textContent = currentStep;
        const activeSearch = document.querySelector(`[data-step-filter="${currentStep}"]`);
        if (activeSearch) activeSearch.focus({ preventScroll: true });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    nums.forEach(el => {
        el.addEventListener('focus', () => {
            el.select();
            el.closest('.ai-row')?.classList.add('is-active');
        });
        el.addEventListener('blur', () => el.closest('.ai-row')?.classList.remove('is-active'));
        el.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveCountRow(el.closest('.ai-row'));
                const visibleNums = nums.filter(input => input.offsetParent !== null);
                const next = visibleNums[visibleNums.indexOf(el) + 1];
                if (next) next.focus(); else el.blur();
            }
        });
        el.addEventListener('input', recalc);
        el.addEventListener('blur', () => setTimeout(() => {
            if (el.closest('.ai-row')?.contains(document.activeElement)) return;
            saveCountRow(el.closest('.ai-row'));
        }, 120));
    });

    stepTabs.forEach(tab => tab.addEventListener('click', () => showStep(Number(tab.dataset.stepTarget))));
    document.querySelectorAll('[data-step-next]').forEach(btn => btn.addEventListener('click', () => showStep(currentStep + 1)));
    document.querySelectorAll('[data-step-prev]').forEach(btn => btn.addEventListener('click', () => showStep(currentStep - 1)));

    function recalc() {
        const secSum = {};
        let grand = 0;
        let filled = 0;
        nums.forEach(el => {
            const value = parseInt(el.value, 10) || 0;
            const sec = el.dataset.sec;
            secSum[sec] = (secSum[sec] || 0) + value;
            grand += value;
            if (value > 0) {
                filled++;
                el.closest('.ai-row')?.classList.remove('is-zero');
            } else {
                el.closest('.ai-row')?.classList.add('is-zero');
            }
        });
        document.querySelectorAll('[data-tally]').forEach(el => {
            el.textContent = fmt.format(secSum[el.dataset.tally] || 0);
        });
        document.getElementById('ai-grand').textContent = fmt.format(grand);
        document.getElementById('ai-filled').textContent = filled;
        document.getElementById('ai-total').textContent = nums.length;
    }

    const valueOf = name => parseInt(document.querySelector(`[name="${name}"]`)?.value || 0, 10);
    document.querySelectorAll('[data-eq]').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.closest('.ai-item')?.querySelector('.ai-num');
        if (!input) return;
        input.value = valueOf('students_1_2') + valueOf('students_1_4');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        saveCountRow(input.closest('.ai-row'));
    }));

    document.querySelectorAll('[data-step-filter]').forEach(filter => {
        filter.addEventListener('input', () => {
            const step = filter.dataset.stepFilter;
            const panel = document.querySelector(`[data-step-panel="${step}"]`);
            const nores = document.querySelector(`[data-nores="${step}"]`);
            const q = filter.value.trim().toLowerCase();
            let anyVisible = false;

            panel.querySelectorAll('.ai-row[data-name]').forEach(row => {
                const hit = !q || row.dataset.name.includes(q);
                row.style.display = hit ? '' : 'none';
                if (hit) anyVisible = true;
            });
            panel.querySelectorAll('.ai-group').forEach(group => {
                group.style.display = group.querySelector('.ai-row:not([style*="display: none"])') ? '' : 'none';
            });
            if (nores) {
                nores.querySelector('span').textContent = filter.value;
                nores.style.display = (q && !anyVisible) ? 'block' : 'none';
            }
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
        };
        if (row.dataset.programId) {
            payload.input_prefix = row.dataset.inputPrefix;
            payload.program_id = row.dataset.programId;
        } else {
            payload.item_name = row.dataset.itemName;
        }

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
