@extends('layouts.admin')

@section('title', 'ປ້ອນຂໍ້ມູນ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ປ້ອນຂໍ້ມູນປະເມີນລາຍຮັບວິຊາການ ປີ ' . $academicIncome->fiscal_year)

@section('content')

<style>
.ai-card { padding:1.1rem 1.25rem !important; margin-bottom:0.9rem; }
.ai-grid   { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:0.7rem; }
.ai-tile   { border:1px solid var(--fns-gray-200); border-radius:10px; padding:0.7rem 0.8rem; background:#fafbfc; display:flex; flex-direction:column; gap:0.5rem; }
.ai-tile-hd { display:flex; align-items:center; gap:0.5rem; }
.ai-tile-num { flex-shrink:0; min-width:1.8rem; height:1.8rem; padding:0 0.35rem; border-radius:8px; background:var(--fns-navy); color:var(--fns-gold-light); display:flex; align-items:center; justify-content:center; font-family:'Cinzel',serif; font-size:0.64rem; font-weight:700; }
.ai-tile-title { font-weight:700; font-size:0.8rem; color:var(--fns-navy); line-height:1.25; }
.ai-tile-rate { font-size:0.72rem; color:var(--fns-gray-500); }
.ai-tile-rate b { color:var(--fns-navy); font-size:0.82rem; }
.ai-sublabel { font-size:0.76rem; font-weight:600; color:var(--fns-gray-500); margin:0.65rem 0 0.4rem; text-transform:uppercase; letter-spacing:0.04em; }
.ai-sublabel:first-child { margin-top:0; }
/* compact program entry grid (fills horizontal whitespace, fewer rows) */
.ai-pg-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:0.5rem; }
.ai-pg { display:flex; align-items:center; justify-content:space-between; gap:0.6rem; border:1px solid var(--fns-gray-200); border-radius:9px; padding:0.4rem 0.55rem; background:#fff; }
.ai-pg-warn { background:rgba(245,158,11,0.06); border-color:rgba(245,158,11,0.35); }
.ai-pg-info { min-width:0; }
.ai-pg-name { font-size:0.79rem; color:#374151; line-height:1.2; }
.ai-pg-sub { display:flex; align-items:center; gap:0.35rem; margin-top:0.2rem; font-size:0.67rem; color:var(--fns-gray-400); }
.ai-pg-input { width:4.4rem; flex-shrink:0; padding:0.3rem 0.4rem; font-size:0.85rem; text-align:right; }
.ai-submit-bar { position:sticky; bottom:0; z-index:5; display:flex; gap:0.5rem; align-items:center; margin-top:0.25rem; padding:0.8rem 0.25rem; background:rgba(255,255,255,0.94); backdrop-filter:blur(4px); border-top:1px solid var(--fns-gray-200); }
</style>

<form method="POST" action="{{ route('head_of_finance.academic-income.saveEvaluate', $academicIncome) }}">
@csrf

{{-- ── Credit-unit income: 1.1 + 1.3 ───────────────────────── --}}
<div class="fns-card ai-card">
    <div class="fns-sec-hd" style="margin-bottom:0.7rem;">
        <div class="fns-sec-num">1.1</div>
        <div>
            <div class="fns-sec-title">ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 2–4 ລະບົບຈ່າຍເງິນ ແລະ ປ.ໂທ</div>
            <div class="fns-sec-desc">ສູດ: ຈຳນວນ ນ/ສ × ໜ່ວຍກິດ × ລາຄາ/ໜ່ວຍ × (1 − % ມຊ)</div>
        </div>
    </div>
    @include('dashboards.finance_head.academic-income._program-grid', [
        'programs'    => $programs11,
        'section'     => '1.1',
        'inputPrefix' => 's11',
    ])
</div>

<div class="fns-card ai-card">
    <div class="fns-sec-hd" style="margin-bottom:0.7rem;">
        <div class="fns-sec-num">1.3</div>
        <div>
            <div class="fns-sec-title">ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 1 ລະບົບຈ່າຍເງິນ</div>
            <div class="fns-sec-desc">ສູດ: ຈຳນວນ ນ/ສ × ໜ່ວຍກິດ × ລາຄາ/ໜ່ວຍ × (1 − % ມຊ)</div>
        </div>
    </div>
    <div class="ai-sublabel">ປ.ຕີ ປີ 1</div>
    @include('dashboards.finance_head.academic-income._program-grid', [
        'programs'    => $programs13_bach,
        'section'     => '1.3',
        'inputPrefix' => 's13',
    ])
    <div class="ai-sublabel">ປ.ໂທ / ປ.ເອກ ປີ 1</div>
    @include('dashboards.finance_head.academic-income._program-grid', [
        'programs'      => $programs13_master,
        'section'       => '1.3',
        'inputPrefix'   => 's13m',
        'useYear1Unit'  => true,
    ])
</div>

{{-- ── Registration fees & flat-rate items (single-count inputs) ─ --}}
<div class="fns-card ai-card">
    <div class="fns-sec-hd" style="margin-bottom:0.7rem;">
        <div class="fns-sec-num" style="font-size:0.6rem;">1.2<br>1.4<br>3–6</div>
        <div>
            <div class="fns-sec-title">ຄ່າລົງທະບຽນ ແລະ ລາຍຮັບ Item 3–6</div>
            <div class="fns-sec-desc">ປ້ອນຈຳນວນ ນ/ສ ຂອງແຕ່ລະລາຍການ</div>
        </div>
    </div>

    <div class="ai-grid">
        {{-- 1.2 — registration year 2-4 --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ × ຄ່າລົງທະບຽນ × (1 − % ມຊ ປ.ຕີ)">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">1.2</span>
                <span class="ai-tile-title">ຄ່າລົງທະບຽນ ນ/ສ ປີ 2–4 (ຄວທ)</span>
            </div>
            @if($feeYear2_4)
                <div class="ai-tile-rate">ຄ່າລົງທະບຽນ: <b>{{ number_format($feeYear2_4->total_rate, 0) }} ກີບ</b> <span style="color:var(--fns-gray-400);">(ປີ {{ $feeYear2_4->start_year }})</span></div>
            @else
                <div class="ai-tile-rate" style="color:#d97706;">⚠ ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 2–4</div>
            @endif
            <input type="number" name="students_1_2" min="0"
                value="{{ old('students_1_2', $existingItems->get('1.2_')?->student_count ?? 0) }}"
                class="fns-input" required style="text-align:right;">
        </div>

        {{-- 1.4 — registration year 1 --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ × ຄ່າລົງທະບຽນ × (1 − % ມຊ ປ.ຕີ)">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">1.4</span>
                <span class="ai-tile-title">ຄ່າລົງທະບຽນ ນ/ສ ປີ 1 (ຄວທ)</span>
            </div>
            @if($feeYear1)
                <div class="ai-tile-rate">ຄ່າລົງທະບຽນ: <b>{{ number_format($feeYear1->total_rate, 0) }} ກີບ</b> <span style="color:var(--fns-gray-400);">(ປີ {{ $feeYear1->start_year }})</span></div>
            @else
                <div class="ai-tile-rate" style="color:#d97706;">⚠ ຍັງບໍ່ໄດ້ຕັ້ງຄ່າລົງທະບຽນ ປີ 1</div>
            @endif
            <input type="number" name="students_1_4" min="0"
                value="{{ old('students_1_4', $existingItems->get('1.4_')?->student_count ?? 0) }}"
                class="fns-input" required style="text-align:right;">
        </div>

        {{-- Item 3 (2.1) --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ × ອັດຕາຕໍ່ໜ່ວຍ">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">3</span>
                <span class="ai-tile-title">{{ $incomeRates->get('item3_rate')?->label ?? 'Item 3' }}</span>
            </div>
            <div class="ai-tile-rate">ອັດຕາ/ນ/ສ: <b>{{ number_format((float)($incomeRates->get('item3_rate')?->rate ?? 0), 0) }} ກີບ</b></div>
            <input type="number" name="students_2_1" id="students_2_1" min="0"
                value="{{ old('students_2_1', $existingItems->get('2.1_')?->student_count ?? 0) }}"
                class="fns-input" required style="text-align:right;">
        </div>

        {{-- Item 4 (2.2) — auto-fill 1.2+1.4 --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4) × ອັດຕາຕໍ່ໜ່ວຍ">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">4</span>
                <span class="ai-tile-title">{{ $incomeRates->get('item4_rate')?->label ?? 'Item 4' }}</span>
            </div>
            <div class="ai-tile-rate">ອັດຕາ/ນ/ສ: <b>{{ number_format((float)($incomeRates->get('item4_rate')?->rate ?? 0), 0) }} ກີບ</b></div>
            <div style="display:flex; gap:0.4rem; align-items:stretch;">
                <input type="number" name="students_2_2" id="students_2_2" min="0"
                    value="{{ old('students_2_2', $existingItems->get('2.2_')?->student_count ?? 0) }}"
                    class="fns-input" required style="flex:1; text-align:right;">
                <button type="button" class="fns-btn fns-btn-secondary" onclick="autoFill('students_2_2')"
                    title="ຄຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4)" style="white-space:nowrap; padding:0 0.6rem;">⚡</button>
            </div>
        </div>

        {{-- Item 5 (2.3) — auto-fill 1.2+1.4 --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4) × ອັດຕາຕໍ່ໜ່ວຍ">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">5</span>
                <span class="ai-tile-title">{{ $incomeRates->get('item5_rate')?->label ?? 'Item 5' }}</span>
            </div>
            <div class="ai-tile-rate">ອັດຕາ/ນ/ສ: <b>{{ number_format((float)($incomeRates->get('item5_rate')?->rate ?? 0), 0) }} ກີບ</b></div>
            <div style="display:flex; gap:0.4rem; align-items:stretch;">
                <input type="number" name="students_2_3" id="students_2_3" min="0"
                    value="{{ old('students_2_3', $existingItems->get('2.3_')?->student_count ?? 0) }}"
                    class="fns-input" required style="flex:1; text-align:right;">
                <button type="button" class="fns-btn fns-btn-secondary" onclick="autoFill('students_2_3')"
                    title="ຄຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4)" style="white-space:nowrap; padding:0 0.6rem;">⚡</button>
            </div>
        </div>

        {{-- Item 6 (2.4) --}}
        <div class="ai-tile" title="ສູດ: ຈຳນວນ ນ/ສ × ອັດຕາຕໍ່ໜ່ວຍ">
            <div class="ai-tile-hd">
                <span class="ai-tile-num">6</span>
                <span class="ai-tile-title">{{ $incomeRates->get('item6_rate')?->label ?? 'Item 6' }}</span>
            </div>
            <div class="ai-tile-rate">ອັດຕາ/ນ/ສ: <b>{{ number_format((float)($incomeRates->get('item6_rate')?->rate ?? 0), 0) }} ກີບ</b></div>
            <input type="number" name="students_2_4" id="students_2_4" min="0"
                value="{{ old('students_2_4', $existingItems->get('2.4_')?->student_count ?? 0) }}"
                class="fns-input" required style="text-align:right;">
        </div>
    </div>
</div>

{{-- Sticky submit bar --}}
<div class="ai-submit-bar">
    <button type="submit" class="fns-btn fns-btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
        ບັນທຶກ
    </button>
    <a href="{{ route('head_of_finance.academic-income.show', $academicIncome) }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
</div>

@push('scripts')
<script>
function autoFill(targetId) {
    const v12 = parseInt(document.querySelector('[name=students_1_2]')?.value || 0, 10);
    const v14 = parseInt(document.querySelector('[name=students_1_4]')?.value || 0, 10);
    document.getElementById(targetId).value = v12 + v14;
}
</script>
@endpush

</form>
@endsection
