@extends('layouts.admin')
@section('title', 'ສະຫຼຸບການປະເມີນລາຍຮັບ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ສະຫຼຸບການປະເມີນລາຍຮັບ ປີ ' . $academicIncome->fiscal_year)

@section('content')
<style>
.rpt-wrap{width:100%;overflow-x:auto;background:#fff;padding:2rem 2.5rem;border-radius:12px;border:1px solid var(--fns-gray-200);box-shadow:0 4px 14px -10px rgba(17,27,51,.18)}
.rpt-actions{display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:1.5rem}
.rpt-header{text-align:center;margin-bottom:1.2rem;line-height:1.7}
.rpt-header .rpt-org{font-size:.88rem;font-weight:700;color:var(--fns-navy-deep)}
.rpt-header .rpt-title{font-size:1.15rem;font-weight:700;color:var(--fns-navy);margin-top:.35rem;display:flex;align-items:center;justify-content:center;gap:.5rem}
.rpt-header .rpt-nation{font-size:.62rem;font-weight:600;color:var(--fns-navy);letter-spacing:.04em;margin-top:.15rem}
.rpt-header .rpt-motto{font-size:.58rem;color:var(--fns-gray-400)}
.rpt-table{width:100%;border-collapse:collapse;font-size:.82rem;color:#374151}
.rpt-table th,.rpt-table td{border:1px solid #9ca3af;padding:.45rem .65rem;vertical-align:middle}
.rpt-table th{background:var(--fns-navy);color:#fff;font-weight:600;text-align:center;white-space:nowrap;font-size:.78rem}
.rpt-table td.n{text-align:right;font-variant-numeric:tabular-nums}
.rpt-table .rpt-grand td{background:rgba(201,153,26,.18);font-weight:800;color:var(--fns-navy-deep);font-size:.88rem}
.rpt-table .rpt-sec td{background:rgba(46,63,110,.06);font-weight:700;color:var(--fns-navy-deep)}
.rpt-table .rpt-sub td{padding-left:1.8rem}
.rpt-table .rpt-sum td{background:rgba(46,63,110,.04);font-weight:700}
.rpt-sig{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-top:2.5rem;text-align:center;font-size:.82rem}
.rpt-sig-item{display:flex;flex-direction:column;gap:.25rem}
.rpt-sig-date{color:var(--fns-gray-500);font-size:.75rem}
.rpt-sig-line{margin-top:3rem;border-bottom:1px dotted var(--fns-gray-400)}
.rpt-sig-title{font-weight:600;color:var(--fns-navy);font-size:.78rem;margin-top:.3rem}
.rpt-year-select{padding:.35rem 1.8rem .35rem .6rem;border-radius:6px;border:1.5px solid var(--fns-navy);background:#fff;font-size:1.1rem;font-weight:700;color:var(--fns-navy);cursor:pointer;outline:none;font-family:'Cinzel',serif;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%232e3f6e' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .5rem center}
.rpt-detail-title{font-size:1rem;font-weight:700;color:var(--fns-navy);margin:2.5rem 0 .8rem;padding-bottom:.4rem;border-bottom:2px solid var(--fns-navy)}
@media print{
@page{size:A4 landscape;margin:12mm}
body{background:#fff!important}
.fns-topnav,.fns-sidebar,.rpt-actions{display:none!important}
.rpt-wrap{box-shadow:none!important;border:none!important;padding:0!important;border-radius:0!important}
.fns-content{padding:0!important;margin:0!important}
.rpt-table{font-size:9pt}
.rpt-table th{background:#d1d5db!important;color:#000!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rpt-table .rpt-grand td{background:#e5e7eb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rpt-table .rpt-sec td,.rpt-table .rpt-sum td{background:#f3f4f6!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rpt-year-select{border:none;appearance:none;background-image:none;padding:0}
.rpt-detail-title{page-break-before:always}
}
</style>

<div class="rpt-wrap">
<div class="rpt-actions">
    <a href="{{ route('head_of_finance.academic-income.index') }}" style="background:#fff;border:1px solid var(--fns-gray-200);padding:.5rem 1rem;border-radius:8px;font-weight:600;color:var(--fns-navy);text-decoration:none;font-size:.85rem">← ກັບຄືນ</a>
    <button type="button" id="btn-print" style="background:var(--fns-navy);border:none;padding:.5rem 1rem;border-radius:8px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;gap:.4rem;font-size:.85rem">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        ພິມ
    </button>
</div>

{{-- ===== Official header ===== --}}
<div class="rpt-header">
    <div class="rpt-org">ມະຫາວິທະຍາໄລແຫ່ງຊາດ</div>
    <div class="rpt-org">ຄະນະວິທະຍາສາດທຳມະຊາດ</div>
    <div class="rpt-title">
        ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ
        <select class="rpt-year-select" onchange="window.location.href=this.value">
            @foreach($allPlans as $p)
                <option value="{{ route('head_of_finance.academic-income.show', $p) }}" {{ $p->id === $academicIncome->id ? 'selected' : '' }}>{{ $p->fiscal_year }}</option>
            @endforeach
        </select>
    </div>
    <div class="rpt-nation">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</div>
    <div class="rpt-motto">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</div>
</div>

{{-- ==================== SUMMARY TABLE ==================== --}}
<table class="rpt-table">
    <thead>
        <tr>
            <th rowspan="2" style="width:40px">ລ/ດ</th>
            <th rowspan="2" style="min-width:220px">ເນື້ອໃນເອກະສານ</th>
            <th rowspan="2" style="width:75px">ຈຳນວນພົນ</th>
            <th rowspan="2" style="width:90px">ອັດຕາຕໍ່ໜ່ວຍ</th>
            <th rowspan="2" style="width:120px">ຈຳນວນເງິນລວມ</th>
            <th rowspan="2" style="width:110px">ມອບພັນທະ ມຊ</th>
            <th colspan="3">ລາຍຮັບຂອງ ຄວທ</th>
        </tr>
        <tr><th style="width:110px">ລວມລາຍຮັບ ຄວທ</th><th style="width:110px">ຄ່າສິດສອນ</th><th style="width:110px">ເຫຼືອຈາກຄ່າສອນ</th></tr>
    </thead>
    <tbody>
        <tr class="rpt-grand">
            <td></td><td style="text-align:center">ລວມລາຍຮັບທັງໝົດ</td><td class="n"></td><td class="n"></td>
            <td class="n">{{ number_format($totals['gross']) }}</td><td class="n">{{ number_format($totals['nuol']) }}</td>
            <td class="n">{{ number_format($totals['fns_income']) }}</td><td class="n">{{ number_format($totals['teaching_fee']) }}</td><td class="n">{{ number_format($totals['remaining']) }}</td>
        </tr>
        {{-- Section 1: Registration fees --}}
        @php $s = $sections['s1']; @endphp
        <tr class="rpt-sec"><td style="text-align:center">1</td><td>{{ $s['title'] }}</td><td class="n"></td><td class="n"></td>
            <td class="n">{{ number_format($s['totals']['gross']) }}</td><td class="n">{{ number_format($s['totals']['nuol']) }}</td>
            <td class="n">{{ number_format($s['totals']['fns_income']) }}</td><td class="n">{{ number_format($s['totals']['teaching_fee']) }}</td><td class="n">{{ number_format($s['totals']['remaining']) }}</td></tr>
        @foreach($s['rows'] as $k => $r)
        <tr class="rpt-sub"><td style="text-align:center">{{ $k }}</td><td>{{ $r['title'] }}</td>
            <td class="n">{{ $r['count'] ?: '' }}</td><td class="n"></td>
            <td class="n">{{ number_format($r['gross']) }}</td><td class="n">{{ number_format($r['nuol']) }}</td>
            <td class="n">{{ number_format($r['fns_income']) }}</td><td class="n">{{ number_format($r['teaching_fee']) }}</td><td class="n">{{ number_format($r['remaining']) }}</td></tr>
        @endforeach
        {{-- Section 2: Credit unit income --}}
        @php $s = $sections['s2']; @endphp
        <tr class="rpt-sec"><td style="text-align:center">2</td><td>{{ $s['title'] }}</td><td class="n"></td><td class="n"></td>
            <td class="n">{{ number_format($s['totals']['gross']) }}</td><td class="n">{{ number_format($s['totals']['nuol']) }}</td>
            <td class="n">{{ number_format($s['totals']['fns_income']) }}</td><td class="n">{{ number_format($s['totals']['teaching_fee']) }}</td><td class="n">{{ number_format($s['totals']['remaining']) }}</td></tr>
        @foreach($s['rows'] as $k => $r)
        <tr class="rpt-sub"><td style="text-align:center">{{ $k }}</td><td>{{ $r['title'] }}</td>
            <td class="n">{{ $r['count'] ?: '' }}</td><td class="n"></td>
            <td class="n">{{ number_format($r['gross']) }}</td><td class="n">{{ number_format($r['nuol']) }}</td>
            <td class="n">{{ number_format($r['fns_income']) }}</td><td class="n">{{ number_format($r['teaching_fee']) }}</td><td class="n">{{ number_format($r['remaining']) }}</td></tr>
        @endforeach
        {{-- Sections 3–6: Flat rate items --}}
        @foreach(['s3' => '3', 's4' => '4', 's5' => '5', 's6' => '6'] as $sk => $num)
        @php $r = $sections[$sk]; @endphp
        <tr class="rpt-sec"><td style="text-align:center">{{ $num }}</td><td>{{ $r['title'] }}</td>
            <td class="n">{{ $r['count'] ?: '' }}</td><td class="n">{{ $r['rate'] ? number_format($r['rate']) : '' }}</td>
            <td class="n">{{ number_format($r['gross']) }}</td><td class="n">{{ number_format($r['nuol']) }}</td>
            <td class="n">{{ number_format($r['fns_income']) }}</td><td class="n">{{ number_format($r['teaching_fee']) }}</td><td class="n">{{ number_format($r['remaining']) }}</td></tr>
        @endforeach
    </tbody>
</table>

{{-- Signature block --}}
<div class="rpt-sig">
    @foreach(['ຄະນະບໍດີ','ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ','ຫົວໜ້າພະແນກວິຊາການ','ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $sig)
    <div class="rpt-sig-item"><span class="rpt-sig-date">ວັນທີ ......./......./....... </span><div class="rpt-sig-line"></div><span class="rpt-sig-title">{{ $sig }}</span></div>
    @endforeach
</div>

{{-- ==================== 1.1 DETAIL ==================== --}}
<h3 class="rpt-detail-title">1.1. ລາຍຮັບຄ່າໜ່ວຍກິດນັກຮຽນແຕ່ປີ 2-4 ລະບົບຈ່າຍເງິນ ແລະ ປະລິນຍາໂທ</h3>
<table class="rpt-table">
    <thead><tr><th>ລ/ດ</th><th>ລາຍການ / ຫຼັກສູດ</th><th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th><th>ຈຳນວນຄົນ</th><th>ລາຍຮັບລວມ</th><th>ຈຳນວນເປີເຊັນ ມຊ</th><th>ພັນທະມຊ</th><th>ຈຳນວນເປີເຊັນ ຄວທ</th><th>ລາຍຮັບຄວທ</th></tr></thead>
    <tbody>
        @php $dTotal = ['gross'=>0,'nuol'=>0,'fns'=>0]; $dN = 0; $dIdx = 0; @endphp
        @foreach($detail_1_1 as $item)
        @php
            $dIdx++;
            $rate = $item->snap_course_credit_unit * $item->snap_credit_unit_price;
            $gross = $item->student_count * $rate;
            $nuolPct = $item->snap_nuol_pct;
            $nuol = $gross * $nuolPct;
            $fns = $gross - $nuol;
            $dTotal['gross'] += $gross; $dTotal['nuol'] += $nuol; $dTotal['fns'] += $fns; $dN += $item->student_count;
            $level = $item->degreeProgram?->level;
            $year  = $item->degreeProgram?->study_year;
            if ($level === 'bachelor') {
                $label = 'ປີ ' . ($year ?? '—') . ' ' . $item->degreeProgram?->name;
            } else {
                $label = ($level === 'master' ? 'ປະລິນຍາໂທ' : 'ປະລິນຍາເອກ') . ' ' . $item->degreeProgram?->name;
            }
        @endphp
        <tr>
            <td style="text-align:center">{{ $dIdx }}</td>
            <td>{{ $label }}</td>
            <td class="n">{{ number_format($rate) }}</td><td class="n">{{ $item->student_count }}</td>
            <td class="n">{{ number_format($gross) }}</td><td class="n">{{ $nuolPct }}</td><td class="n">{{ number_format($nuol) }}</td>
            <td class="n">{{ round(1 - $nuolPct, 2) }}</td><td class="n">{{ number_format($fns) }}</td>
        </tr>
        @endforeach
        <tr class="rpt-sum"><td></td><td>ລວມ</td><td class="n"></td><td class="n">{{ $dN }}</td>
            <td class="n">{{ number_format($dTotal['gross']) }}</td><td class="n"></td><td class="n">{{ number_format($dTotal['nuol']) }}</td>
            <td class="n"></td><td class="n">{{ number_format($dTotal['fns']) }}</td></tr>
    </tbody>
</table>

{{-- ==================== 1.2 DETAIL ==================== --}}
<h3 class="rpt-detail-title">1.2. ລາຍຮັບຄ່າລົງທະບຽນນັກສຶກສາປີທີ 2-4 ຂອງ ຄວທ</h3>
@if($feeYear2_4 && $s1_2)
<table class="rpt-table">
    <thead><tr><th>ລ/ດ</th><th>ລາຍການ</th><th>ອັດຕາຄ່າທະບຽນຕໍ່ຄົນ</th><th>ຈຳນວນຄົນ</th><th>ລາຍຮັບລວມ</th><th>ຈຳນວນເປີເຊັນ ມຊ</th><th>ພັນທະມຊ</th><th>ຈຳນວນເປີເຊັນ ຄວທ</th><th>ລາຍຮັບຄວທ</th></tr></thead>
    <tbody>
        @php $fTotal = ['gross'=>0,'nuol'=>0,'fns'=>0]; $cnt12 = $s1_2->student_count; @endphp
        @foreach($feeYear2_4->items as $fi)
        @php
            $fGross = $cnt12 * $fi->amount;
            $fNuol = $fGross * $fi->nuol_pct;
            $fFns = $fGross - $fNuol;
            $fTotal['gross'] += $fGross; $fTotal['nuol'] += $fNuol; $fTotal['fns'] += $fFns;
        @endphp
        <tr><td style="text-align:center">{{ $loop->iteration }}</td><td>{{ $fi->name }}</td>
            <td class="n">{{ number_format($fi->amount) }}</td><td class="n">{{ $cnt12 }}</td><td class="n">{{ number_format($fGross) }}</td>
            <td class="n">{{ $fi->nuol_pct }}</td><td class="n">{{ number_format($fNuol) }}</td>
            <td class="n">{{ round(1 - $fi->nuol_pct, 2) }}</td><td class="n">{{ number_format($fFns) }}</td></tr>
        @endforeach
        <tr class="rpt-sum"><td></td><td>ລວມ</td><td class="n">{{ number_format($feeYear2_4->total_rate) }}</td><td class="n">{{ $cnt12 }}</td>
            <td class="n">{{ number_format($fTotal['gross']) }}</td><td class="n"></td><td class="n">{{ number_format($fTotal['nuol']) }}</td>
            <td class="n"></td><td class="n">{{ number_format($fTotal['fns']) }}</td></tr>
    </tbody>
</table>
@else <p style="color:var(--fns-gray-400)">ບໍ່ມີຂໍ້ມູນ</p> @endif

{{-- ==================== 1.3 DETAIL ==================== --}}
<h3 class="rpt-detail-title">1.3. ລາຍຮັບຄ່າໜ່ວຍກິດປີ 1 ລະບົບຈ່າຍເງິນ</h3>
<table class="rpt-table">
    <thead><tr><th>ລ/ດ</th><th>ລາຍການ / ຫຼັກສູດ</th><th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th><th>ຈຳນວນຄົນ</th><th>ລາຍຮັບລວມ</th><th>ຈຳນວນເປີເຊັນ ມຊ</th><th>ພັນທະມຊ</th><th>ຈຳນວນເປີເຊັນ ຄວທ</th><th>ລາຍຮັບຄວທ</th></tr></thead>
    <tbody>
        @php
            $dTotal = ['gross'=>0,'nuol'=>0,'fns'=>0]; $dN = 0; $dIdx = 0;
            $detail_1_3_grouped = $detail_1_3->groupBy(fn($i) =>
                $i->degreeProgram?->level === 'bachelor' ? 'A' : 'B');
        @endphp
        @foreach($detail_1_3_grouped as $group => $items)
            @foreach($items as $item)
            @php
                $dIdx++;
                $rate = $item->snap_course_credit_unit * $item->snap_credit_unit_price;
                $gross = $item->student_count * $rate;
                $nuolPct = $item->snap_nuol_pct;
                $nuol = $gross * $nuolPct;
                $fns = $gross - $nuol;
                $dTotal['gross'] += $gross; $dTotal['nuol'] += $nuol; $dTotal['fns'] += $fns; $dN += $item->student_count;
                $level = $item->degreeProgram?->level;
                if ($level === 'bachelor') {
                    $label = 'ນັກສຶກສາ ປີທີ 1 ' . $item->degreeProgram?->name;
                } elseif ($level === 'master') {
                    $label = 'ປະລິນຍາໂທ' . $item->degreeProgram?->name;
                } else {
                    $label = 'ປະລິນຍາເອກ' . $item->degreeProgram?->name;
                }
            @endphp
            <tr>
                <td style="text-align:center">{{ $dIdx }}</td>
                <td>{{ $label }}</td>
                <td class="n">{{ number_format($rate) }}</td><td class="n">{{ $item->student_count }}</td>
                <td class="n">{{ number_format($gross) }}</td><td class="n">{{ $nuolPct }}</td><td class="n">{{ number_format($nuol) }}</td>
                <td class="n">{{ round(1 - $nuolPct, 2) }}</td><td class="n">{{ number_format($fns) }}</td>
            </tr>
            @endforeach
        @endforeach
        <tr class="rpt-sum"><td></td><td>ລວມ</td><td class="n"></td><td class="n">{{ $dN }}</td>
            <td class="n">{{ number_format($dTotal['gross']) }}</td><td class="n"></td><td class="n">{{ number_format($dTotal['nuol']) }}</td>
            <td class="n"></td><td class="n">{{ number_format($dTotal['fns']) }}</td></tr>
    </tbody>
</table>

{{-- ==================== 1.4 DETAIL ==================== --}}
<h3 class="rpt-detail-title">1.4. ຄ່າລົງທະບຽນນັກສຶກສາປີທີ 1 ລະບົບຈ່າຍເງິນຂອງ ຄວທ</h3>
@if($feeYear1 && $s1_1)
<table class="rpt-table">
    <thead><tr><th>ລ/ດ</th><th>ລາຍການ</th><th>ອັດຕາຄ່າທະບຽນຕໍ່ຄົນ</th><th>ຈຳນວນຄົນ</th><th>ລາຍຮັບລວມ</th><th>ຈຳນວນເປີເຊັນ ມຊ</th><th>ພັນທະມຊ</th><th>ຈຳນວນເປີເຊັນ ຄວທ</th><th>ລາຍຮັບຄວທ</th></tr></thead>
    <tbody>
        @php $fTotal = ['gross'=>0,'nuol'=>0,'fns'=>0]; $cnt14 = $s1_1->student_count; @endphp
        @foreach($feeYear1->items as $fi)
        @php
            $fGross = $cnt14 * $fi->amount;
            $fNuol = $fGross * $fi->nuol_pct;
            $fFns = $fGross - $fNuol;
            $fTotal['gross'] += $fGross; $fTotal['nuol'] += $fNuol; $fTotal['fns'] += $fFns;
        @endphp
        <tr><td style="text-align:center">{{ $loop->iteration }}</td><td>{{ $fi->name }}</td>
            <td class="n">{{ number_format($fi->amount) }}</td><td class="n">{{ $cnt14 }}</td><td class="n">{{ number_format($fGross) }}</td>
            <td class="n">{{ $fi->nuol_pct }}</td><td class="n">{{ number_format($fNuol) }}</td>
            <td class="n">{{ round(1 - $fi->nuol_pct, 2) }}</td><td class="n">{{ number_format($fFns) }}</td></tr>
        @endforeach
        <tr class="rpt-sum"><td></td><td>ລວມ</td><td class="n">{{ number_format($feeYear1->total_rate) }}</td><td class="n">{{ $cnt14 }}</td>
            <td class="n">{{ number_format($fTotal['gross']) }}</td><td class="n"></td><td class="n">{{ number_format($fTotal['nuol']) }}</td>
            <td class="n"></td><td class="n">{{ number_format($fTotal['fns']) }}</td></tr>
    </tbody>
</table>
@else <p style="color:var(--fns-gray-400)">ບໍ່ມີຂໍ້ມູນ</p> @endif

</div>

@push('scripts')
<script>document.getElementById('btn-print').addEventListener('click', () => window.print());</script>
@endpush
@endsection
