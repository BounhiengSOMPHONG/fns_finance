@extends('layouts.admin')

@section('title', 'ປ້ອນຂໍ້ມູນ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ປ້ອນຂໍ້ມູນປະເມີນລາຍຮັບ ປີ ' . $academicIncome->fiscal_year)

@section('content')

<style>
/* ── Evaluate page — calm, scannable data-entry ─────────────────── */
.ai-wrap { width: 100%; }

/* sticky search / orientation bar */
.ai-bar {
    position: sticky; top: 0; z-index: 20;
    display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
    padding: 0.7rem 0.9rem; margin-bottom: 1.1rem;
    background: rgba(248,247,244,0.92); backdrop-filter: blur(8px);
    border: 1px solid var(--fns-gray-200); border-radius: 12px;
    box-shadow: 0 4px 14px -10px rgba(17,27,51,0.18);
}
.ai-back {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; flex-shrink: 0;
    background: var(--fns-gray-100); border-radius: 8px;
    color: var(--fns-navy); text-decoration: none;
    transition: background .15s, transform .12s;
}
.ai-back:hover { background: var(--fns-gray-200); transform: translateX(-2px); }
.ai-back svg { width: 15px; height: 15px; }
.ai-fy-chip {
    display:inline-flex; flex-direction: column; line-height: 1; padding: 0 .25rem 0 .15rem;
}
.ai-fy-chip-k {
    font-size: .55rem; letter-spacing: .22em; text-transform: uppercase;
    color: var(--fns-gray-400); font-weight: 700;
}
.ai-fy-chip-v {
    font-family: 'Cinzel', serif; font-size: 1.1rem; font-weight: 700;
    color: var(--fns-navy); margin-top: .12rem; letter-spacing: .03em;
}
.ai-meta-strip {
    display:inline-flex; align-items: center; gap: .55rem;
    font-size: .72rem; color: var(--fns-gray-600);
    padding-left: .55rem; border-left: 1px solid var(--fns-gray-200);
}
.ai-meta-strip b { color: var(--fns-navy); font-weight: 600; }
.ai-search { position: relative; flex: 1; min-width: 220px; }
.ai-search svg { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--fns-gray-400); pointer-events: none; }
.ai-search input {
    width: 100%; padding: 0.5rem 0.8rem 0.5rem 2.1rem;
    border: 1px solid var(--fns-gray-200); border-radius: 9px; background: #fff;
    font-family: inherit; font-size: 0.85rem; color: #111827; outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.ai-search input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }
.ai-progress { display: flex; align-items: baseline; gap: 0.4rem; font-size: 0.78rem; color: var(--fns-gray-600); white-space: nowrap; }
.ai-progress b { font-family: 'Cinzel', serif; font-size: 1rem; color: var(--fns-navy); }

/* card */
.ai-card { padding: 1.25rem 1.4rem !important; margin-bottom: 1.1rem; }
.ai-card .fns-sec-hd { margin-bottom: 0.4rem; }
.ai-sec-meta { display: flex; align-items: center; gap: 0.5rem; margin-left: auto; flex-shrink: 0; }
.ai-tally { font-size: 0.72rem; color: var(--fns-gray-500, #6b7280); white-space: nowrap; }
.ai-tally b { font-family: 'Cinzel', serif; color: var(--fns-navy); font-size: 0.86rem; }

/* year / level sub-group */
.ai-group { margin-top: 1.05rem; }
.ai-group:first-of-type { margin-top: 0.6rem; }
.ai-glabel {
    display: flex; align-items: center; gap: 0.55rem;
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--fns-navy); padding-bottom: 0.35rem;
    border-bottom: 2px solid var(--fns-gold-pale);
}
.ai-glabel .ai-gcount { font-weight: 500; color: var(--fns-gray-400); letter-spacing: 0; text-transform: none; }

/* clean divided rows that flow into responsive columns */
.ai-rows { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); column-gap: 1.6rem; }
.ai-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.3rem 0.45rem; border-bottom: 1px solid var(--fns-gray-200);
    border-radius: 6px; cursor: text; transition: background .12s;
}
.ai-row:hover { background: rgba(26,39,68,0.035); }
.ai-row.is-active { background: rgba(201,153,26,0.09); }
.ai-row-name { flex: 1; min-width: 0; display: flex; align-items: center; gap: 0.4rem; font-size: 0.84rem; color: #374151; }
.ai-row-txt { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ai-row.is-zero .ai-row-name { color: var(--fns-gray-400); }
.ai-warn-dot { flex-shrink: 0; width: 7px; height: 7px; border-radius: 50%; background: #e0a93b; box-shadow: 0 0 0 2px rgba(224,169,59,0.18); }

/* the number field */
.ai-num {
    width: 5.4rem; flex-shrink: 0; text-align: right;
    padding: 0.34rem 0.55rem; border: 1px solid var(--fns-gray-200); border-radius: 8px;
    font-family: 'Cinzel', serif; font-size: 0.92rem; font-weight: 600; color: #111827;
    background: #fff; outline: none; transition: border-color .15s, box-shadow .15s, color .15s;
    -moz-appearance: textfield;
}
.ai-num::-webkit-outer-spin-button, .ai-num::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.ai-row.is-zero .ai-num { color: var(--fns-gray-400); }
.ai-num:focus { border-color: var(--fns-navy); box-shadow: 0 0 0 3px rgba(46,63,110,0.12); color: #111827; }
.ai-num:not(:placeholder-shown) { color: #111827; }

.ai-empty { grid-column: 1 / -1; text-align: center; padding: 1.1rem; color: var(--fns-gray-400); border: 1px dashed var(--fns-gray-200); border-radius: 8px; font-size: 0.82rem; }
.ai-nores { display: none; text-align: center; padding: 0.6rem; color: var(--fns-gray-400); font-size: 0.8rem; }

/* flat-rate item rows (1.2/1.4/3-6) — same row system, with rate + action */
.ai-item { align-items: flex-start; padding: 0.55rem 0.45rem; }
.ai-item .ai-row-name { flex-direction: column; align-items: flex-start; gap: 0.15rem; }
.ai-item-title { font-weight: 600; font-size: 0.84rem; color: #374151; display: flex; align-items: center; gap: 0.45rem; }
.ai-item-tag { font-family: 'Cinzel', serif; font-size: 0.62rem; font-weight: 700; color: var(--fns-gold); background: rgba(201,153,26,0.1); border-radius: 5px; padding: 0.05rem 0.35rem; }
.ai-item-rate { font-size: 0.72rem; color: var(--fns-gray-400); }
.ai-item-rate b { color: var(--fns-navy); }
.ai-item-rate.warn { color: #b45309; }
.ai-item-side { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; }
.ai-eq { font-size: 0.66rem; color: var(--fns-gold); background: none; border: 1px solid rgba(201,153,26,0.4); border-radius: 7px; padding: 0.2rem 0.45rem; cursor: pointer; white-space: nowrap; transition: background .15s; font-family: inherit; }
.ai-eq:hover { background: rgba(201,153,26,0.12); }

/* sticky submit */
.ai-submit-bar {
    position: sticky; bottom: 0; z-index: 15;
    display: flex; gap: 0.6rem; align-items: center;
    margin-top: 0.5rem; padding: 0.85rem 1.1rem;
    background: rgba(248,247,244,0.94); backdrop-filter: blur(8px);
    border: 1px solid var(--fns-gray-200); border-radius: 12px;
    box-shadow: 0 -4px 14px -10px rgba(17,27,51,0.18);
}
.ai-submit-bar .fns-btn-primary { padding: .6rem 1.3rem; font-size: .85rem; }
.ai-submit-note {
    margin-left: auto;
    display: inline-flex; flex-direction: column; align-items: flex-end; line-height: 1;
}
.ai-submit-note-k {
    font-size: .62rem; letter-spacing: .18em; text-transform: uppercase;
    color: var(--fns-gray-400); font-weight: 700;
}
.ai-submit-note-v { margin-top: .3rem; }
.ai-submit-note-v b { font-family: 'Cinzel', serif; color: var(--fns-navy); font-size: 1.25rem; font-weight: 700; }
.ai-submit-note-v span { font-size: .68rem; color: var(--fns-gray-400); margin-left: .3rem; font-weight: 500; }
</style>

@php
    $groups11 = $programs11->groupBy(fn($p) =>
        $p->level === 'bachelor' ? 'ປ.ຕີ ປີ ' . ($p->study_year ?? '—')
        : ($p->level === 'master' ? 'ປ.ໂທ' : 'ປ.ເອກ'));
@endphp

<form method="POST" action="{{ route('head_of_finance.academic-income.saveEvaluate', $academicIncome) }}" class="ai-wrap">
@csrf

{{-- ── Sticky context + search bar ─────────────────────────── --}}
<div class="ai-bar">
    <a href="{{ route('head_of_finance.manage-plan.index') }}" class="ai-back" title="ກັບຄືນ">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="ai-fy-chip">
        <span class="ai-fy-chip-k">ສົກປີ</span>
        <span class="ai-fy-chip-v">{{ $academicIncome->fiscal_year }}</span>
    </div>
    @if($academicIncome->creator)
        <div class="ai-meta-strip">
            <span>ສ້າງໂດຍ <b>{{ $academicIncome->creator->full_name ?? $academicIncome->creator->username }}</b></span>
            <span>· {{ $academicIncome->created_at->format('d/m/Y') }}</span>
        </div>
    @endif
    <div class="ai-search">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
        <input type="text" id="ai-filter" placeholder="ຄົ້ນຫາສາຂາວິຊາ…" autocomplete="off">
    </div>
    <div class="ai-progress">
        ປ້ອນແລ້ວ <b id="ai-filled">0</b> / <span id="ai-total">0</span> ລາຍການ
    </div>
</div>

{{-- ── 1.1 — credit-unit income, year 2–4 + master/phd ──────────── --}}
<div class="fns-card ai-card">
    <div class="fns-sec-hd">
        <div class="fns-sec-num">1.1</div>
        <div style="flex:1;">
            <div class="fns-sec-title">ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 2–4 (ລະບົບຈ່າຍເງິນ) ແລະ ປ.ໂທ</div>
            <div class="fns-sec-desc">ສູດ: ຈຳນວນ ນ/ສ × ໜ່ວຍກິດ × ລາຄາ/ໜ່ວຍ × (1 − % ມຊ)</div>
        </div>
        <div class="ai-sec-meta"><span class="ai-tally">ລວມ ນ/ສ: <b data-tally="1.1">0</b></span></div>
    </div>
    @foreach($groups11 as $label => $progs)
        <div class="ai-group">
            <div class="ai-glabel">{{ $label }} <span class="ai-gcount">· {{ $progs->count() }} ສາຂາ</span></div>
            @include('dashboards.finance_head.academic-income._program-grid', [
                'programs' => $progs, 'section' => '1.1', 'inputPrefix' => 's11',
            ])
        </div>
    @endforeach
</div>

{{-- ── 1.3 — credit-unit income, year 1 ─────────────────────────── --}}
<div class="fns-card ai-card">
    <div class="fns-sec-hd">
        <div class="fns-sec-num">1.3</div>
        <div style="flex:1;">
            <div class="fns-sec-title">ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 1 (ລະບົບຈ່າຍເງິນ)</div>
            <div class="fns-sec-desc">ສູດ: ຈຳນວນ ນ/ສ × ໜ່ວຍກິດ × ລາຄາ/ໜ່ວຍ × (1 − % ມຊ)</div>
        </div>
        <div class="ai-sec-meta"><span class="ai-tally">ລວມ ນ/ສ: <b data-tally="1.3">0</b></span></div>
    </div>
    <div class="ai-group">
        <div class="ai-glabel">ປ.ຕີ ປີ 1 <span class="ai-gcount">· {{ $programs13_bach->count() }} ສາຂາ</span></div>
        @include('dashboards.finance_head.academic-income._program-grid', [
            'programs' => $programs13_bach, 'section' => '1.3', 'inputPrefix' => 's13',
        ])
    </div>
    @php
        $groups13_master_phd = $programs13_master->groupBy(fn($p) =>
            $p->level === 'master' ? 'ປ.ໂທ' : 'ປ.ເອກ');
    @endphp
    @foreach($groups13_master_phd as $label => $progs)
        <div class="ai-group">
            <div class="ai-glabel">{{ $label }} ປີ 1 <span class="ai-gcount">· {{ $progs->count() }} ສາຂາ</span></div>
            @include('dashboards.finance_head.academic-income._program-grid', [
                'programs' => $progs, 'section' => '1.3', 'inputPrefix' => 's13m', 'useYear1Unit' => true,
            ])
        </div>
    @endforeach
</div>

{{-- ── 1.2 / 1.4 / 3–6 — flat-rate items (single counts) ────────── --}}
@php
    $items = [
        ['tag'=>'1.2', 'name'=>'students_1_2', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 2–4 (ຄວທ)', 'key'=>'1.2_',
         'rate'=> $feeYear2_4 ? number_format($feeYear2_4->total_rate,0).' ກີບ'.' (ປີ '.$feeYear2_4->start_year.')' : null,
         'warn'=>'⚠ ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 2–4', 'eq'=>false],
        ['tag'=>'1.4', 'name'=>'students_1_4', 'title'=>'ຄ່າລົງທະບຽນ ນ/ສ ປີ 1 (ຄວທ)', 'key'=>'1.4_',
         'rate'=> $feeYear1 ? number_format($feeYear1->total_rate,0).' ກີບ'.' (ປີ '.$feeYear1->start_year.')' : null,
         'warn'=>'⚠ ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 1', 'eq'=>false],
        ['tag'=>'3', 'name'=>'students_2_1', 'title'=>$incomeRates->get('item3_rate')?->label ?? 'Item 3', 'key'=>'2.1_',
         'rateField'=>'item3_rate', 'rateVal'=>(float)($incomeRates->get('item3_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
        ['tag'=>'4', 'name'=>'students_2_2', 'title'=>$incomeRates->get('item4_rate')?->label ?? 'Item 4', 'key'=>'2.2_',
         'rateField'=>'item4_rate', 'rateVal'=>(float)($incomeRates->get('item4_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'5', 'name'=>'students_2_3', 'title'=>$incomeRates->get('item5_rate')?->label ?? 'Item 5', 'key'=>'2.3_',
         'rateField'=>'item5_rate', 'rateVal'=>(float)($incomeRates->get('item5_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>true],
        ['tag'=>'6', 'name'=>'students_2_4', 'title'=>$incomeRates->get('item6_rate')?->label ?? 'Item 6', 'key'=>'2.4_',
         'rateField'=>'item6_rate', 'rateVal'=>(float)($incomeRates->get('item6_rate')?->rate ?? 0), 'warn'=>null, 'eq'=>false],
    ];
@endphp
<div class="fns-card ai-card">
    <div class="fns-sec-hd">
        <div class="fns-sec-num" style="font-size:0.58rem; line-height:1.15;">1.2<br>1.4<br>3–6</div>
        <div style="flex:1;">
            <div class="fns-sec-title">ຄ່າລົງທະບຽນ ແລະ ຄ່າທຳນຽມ (3-6)</div>
            <div class="fns-sec-desc">ປ້ອນຈຳນວນ ນ/ສ ຂອງແຕ່ລະລາຍການ</div>
        </div>
    </div>
    <div class="ai-rows">
        @foreach($items as $it)
            @php $val = (int) old($it['name'], $existingItems->get($it['key'])?->student_count ?? 0); @endphp
            <label class="ai-row ai-item @if($val<=0) is-zero @endif" data-name="{{ \Illuminate\Support\Str::lower($it['title']) }}">
                <span class="ai-row-name">
                    <span class="ai-item-title"><span class="ai-item-tag">{{ $it['tag'] }}</span> <span class="ai-row-txt" title="{{ $it['title'] }}">{{ $it['title'] }}</span></span>
                    @if(!empty($it['rateField']))
                        <span class="ai-item-rate">ອັດຕາ:
                            <input type="number" name="{{ $it['rateField'] }}" min="0" step="1"
                                value="{{ old($it['rateField'], (int) $it['rateVal']) }}"
                                title="ແກ້ໄຂອັດຕາ (ກີບ)"
                                style="width:96px;padding:2px 6px;font-size:0.78rem;border:1px solid #cbd5e1;border-radius:5px;font-weight:700;color:var(--fns-navy);"> ກີບ
                        </span>
                    @elseif(!empty($it['rate']))
                        <span class="ai-item-rate">ອັດຕາ: <b>{{ $it['rate'] }}</b></span>
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
</div>

<div class="ai-nores" id="ai-nores">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບ “<span></span>”</div>

{{-- Sticky submit --}}
<div class="ai-submit-bar">
    <button type="submit" class="fns-btn fns-btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
        ບັນທຶກ
    </button>
    <a href="{{ route('head_of_finance.manage-plan.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
    <span class="ai-submit-note">
        <span class="ai-submit-note-k">ລວມ ນ/ສ ຄ່າໜ່ວຍກິດ</span>
        <span class="ai-submit-note-v"><b id="ai-grand">0</b><span>ຄົນ</span></span>
    </span>
</div>

@push('scripts')
<script>
(function () {
    const nums = Array.from(document.querySelectorAll('.ai-num'));

    // tab/click into a field selects its value so you just type over the 0
    nums.forEach(el => {
        el.addEventListener('focus', () => { el.select(); el.closest('.ai-row')?.classList.add('is-active'); });
        el.addEventListener('blur',  () => el.closest('.ai-row')?.classList.remove('is-active'));
        // Enter advances to the next field instead of submitting the form
        el.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault();
                const next = nums[nums.indexOf(el) + 1]; if (next) { next.focus(); } else { el.blur(); }
            }
        });
        el.addEventListener('input', recalc);
    });

    // live tallies: per-section sum, grand credit-unit sum, filled-count progress
    const fmt = new Intl.NumberFormat('en-US');
    function recalc() {
        const secSum = {}; let grand = 0, filled = 0;
        nums.forEach(el => {
            const v = parseInt(el.value, 10) || 0;
            const sec = el.dataset.sec;
            secSum[sec] = (secSum[sec] || 0) + v;
            if (sec !== 'items') grand += v;
            const row = el.closest('.ai-row');
            if (v > 0) { filled++; row?.classList.remove('is-zero'); } else { row?.classList.add('is-zero'); }
        });
        document.querySelectorAll('[data-tally]').forEach(s => s.textContent = fmt.format(secSum[s.dataset.tally] || 0));
        document.getElementById('ai-grand').textContent = fmt.format(grand);
        document.getElementById('ai-filled').textContent = filled;
        document.getElementById('ai-total').textContent = nums.length;
    }

    // "= 1.2+1.4" quick-fill for items 4 & 5
    const v = name => parseInt(document.querySelector(`[name="${name}"]`)?.value || 0, 10);
    document.querySelectorAll('[data-eq]').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.closest('.ai-item').querySelector('.ai-num');
        input.value = v('students_1_2') + v('students_1_4'); recalc();
    }));

    // live filter across program / item rows
    const filter = document.getElementById('ai-filter');
    const nores = document.getElementById('ai-nores');
    filter.addEventListener('input', () => {
        const q = filter.value.trim().toLowerCase();
        let anyVisible = false;
        document.querySelectorAll('.ai-row[data-name]').forEach(r => {
            const hit = !q || r.dataset.name.includes(q);
            r.style.display = hit ? '' : 'none';
            if (hit) anyVisible = true;
        });
        // hide empty groups + section cards while filtering
        document.querySelectorAll('.ai-group').forEach(g => {
            const vis = g.querySelector('.ai-row:not([style*="display: none"])');
            g.style.display = vis ? '' : 'none';
        });
        document.querySelectorAll('.fns-card.ai-card').forEach(c => {
            const vis = c.querySelector('.ai-row:not([style*="display: none"])');
            c.style.display = (q && !vis) ? 'none' : '';
        });
        nores.querySelector('span').textContent = filter.value;
        nores.style.display = (q && !anyVisible) ? 'block' : 'none';
    });

    recalc();
})();
</script>
@endpush

</form>
@endsection
