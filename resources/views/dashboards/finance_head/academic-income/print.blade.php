<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<title>ຮ່າງສັງລວມລາຍຮັບ {{ $academicIncome->fiscal_year }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;600;700&family=Cinzel:wght@700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Noto Sans Lao','Phetsarath OT',sans-serif;
    font-size:9.5pt;
    color:#111;
    background:#fff;
}

/* ─── Print ─── */
@page{
    size:A4 landscape;
    margin:12mm 10mm 14mm 10mm;
}

/* ─── Screen toolbar ─── */
.toolbar{
    display:flex;align-items:center;gap:8px;
    padding:10px 16px;
    background:#1a2744;
    position:fixed;top:0;left:0;right:0;z-index:200;
}
.toolbar a,.toolbar button{
    font-family:inherit;font-size:13px;cursor:pointer;
    border:none;border-radius:6px;padding:7px 16px;text-decoration:none;
}
.btn-print{background:#c9991a;color:#fff;font-weight:600;}
.btn-back{background:rgba(255,255,255,0.12);color:#fff;}
.toolbar-title{color:rgba(255,255,255,0.55);font-size:12px;margin-left:8px;}

.doc-wrap{
    padding-top:52px; /* offset fixed toolbar */
    padding:72px 0 24px;
}

@media print{
    .toolbar{display:none;}
    .doc-wrap{padding:0;}
}

/* ─── Header ─── */
.doc-header{text-align:center;margin-bottom:10pt;line-height:1.6;}
.motto{font-size:8pt;letter-spacing:0.05em;}
.org-name{font-size:10pt;font-weight:700;margin-top:3pt;}
.doc-title{font-size:14pt;font-weight:700;margin-top:6pt;letter-spacing:0.02em;}
.doc-sub{font-size:11pt;font-weight:600;margin-top:1pt;}
.doc-divider{width:120pt;border:none;border-top:1pt solid #111;margin:5pt auto;}

/* ─── Section heading ─── */
.sec-hd{
    background:#1a2744;color:#fff;
    padding:4pt 8pt;
    font-weight:700;font-size:9.5pt;
    margin-top:10pt;
}
.sec-hd span{color:#f0c040;margin-right:6pt;font-family:'Cinzel',serif;}

/* ─── Tables ─── */
table{width:100%;border-collapse:collapse;font-size:8pt;margin-bottom:0;}

th{
    background:#dde4ef;
    border:0.5pt solid #888;
    padding:3pt 3pt;
    text-align:center;
    font-weight:700;
    font-size:7.5pt;
    line-height:1.3;
}
th.th-gold{background:#1a2744;color:#f0c040;}

td{border:0.5pt solid #bbb;padding:2.5pt 4pt;vertical-align:middle;}
td.c{text-align:center;}
td.r{text-align:right;font-variant-numeric:tabular-nums;}
td.prog{font-size:8pt;}

tr.subtotal td{
    background:#edf1f9;font-weight:700;
    border-top:0.8pt solid #1a2744;
}
tr.subtotal td.r{color:#1a2744;font-size:8.5pt;}

/* ─── Summary / Grand total table ─── */
.summary-wrap{margin-top:10pt;}
.summary-title{
    background:#1a2744;color:#f0c040;
    font-weight:700;font-size:10pt;
    padding:4pt 8pt;
    font-family:'Cinzel',serif;letter-spacing:0.04em;
}

.sum-table th{background:#1a2744;color:#fff;font-size:8.5pt;}
.sum-table td{font-size:9pt;}
.sum-table tr.grand td{
    background:#fdf3d0;font-weight:700;font-size:10pt;color:#1a2744;
    border-top:1.5pt solid #1a2744;
}
.sum-table td.r{text-align:right;font-variant-numeric:tabular-nums;}

/* ─── Footer / Signatures ─── */
.signatures{
    margin-top:18pt;
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:20pt;
    text-align:center;
    font-size:8.5pt;
}
.sig-box{border-top:0.5pt solid #555;margin-top:36pt;padding-top:3pt;}
.sig-label{font-weight:600;}
.sig-line{margin-top:2pt;color:#555;font-size:7.5pt;}

/* ─── Page break helper ─── */
.page-break{page-break-before:always;}
</style>
</head>
<body>

{{-- ═══ Screen toolbar ═══ --}}
<div class="toolbar">
    <button class="btn-print" onclick="window.print()">🖨 ພິມ / PDF</button>
    <a href="{{ route('head_of_finance.academic-income.summary', $academicIncome) }}" class="btn-back">← ກັບໄປ</a>
    <span class="toolbar-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ · ສົກ {{ $academicIncome->fiscal_year }}</span>
</div>

<div class="doc-wrap">

{{-- ═══ Document header ═══ --}}
<div class="doc-header">
    <div class="motto">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</div>
    <div class="motto">ສັນຕິພາບ &nbsp; ເອກະລາດ &nbsp; ປະຊາທິປະໄຕ &nbsp; ເອກະພາບ &nbsp; ວັດທະນະຖາວອນ</div>
    <hr class="doc-divider">
    <div class="org-name">ມະຫາວິທະຍາໄລແຫ່ງຊາດ &nbsp;·&nbsp; ຄະນະວິທະຍາສາດທຳມະຊາດ</div>
    <div class="doc-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ</div>
    <div class="doc-sub">ສົກປີງົບປະມານ &nbsp;{{ $academicIncome->fiscal_year }}</div>
</div>

@php
use Illuminate\Support\Collection;

$sec11 = $grouped->get('1.1', collect());
$sec12 = $grouped->get('1.2', collect());
$sec13 = $grouped->get('1.3', collect());
$sec14 = $grouped->get('1.4', collect());
$sec21 = $grouped->get('2.1', collect());
$sec22 = $grouped->get('2.2', collect());
$sec23 = $grouped->get('2.3', collect());
$sec24 = $grouped->get('2.4', collect());

$calc = function(Collection $items): array {
    $gross = $items->sum(fn($it) =>
        in_array($it->section_code, ['1.2','1.4','2.2','2.3'])
            ? $it->student_count * $it->snap_registration_fee_rate
            : $it->student_count * ($it->snap_credit_unit_price ?? 0)
    );
    $fac   = $items->sum('total_income');
    $nuol  = $gross - $fac;
    $p1    = $items->sum('first_payment_amount');
    $p2    = $items->sum('second_payment_amount');
    return compact('gross', 'fac', 'nuol', 'p1', 'p2');
};

$t11 = $calc($sec11);
$t12 = $calc($sec12);
$t13 = $calc($sec13);
$t14 = $calc($sec14);
$t21 = $calc($sec21);
$t22 = $calc($sec22);
$t23 = $calc($sec23);
$t24 = $calc($sec24);

$grandFac   = $t11['fac']  + $t12['fac']  + $t13['fac']  + $t14['fac']  + $t21['fac']  + $t22['fac']  + $t23['fac']  + $t24['fac'];
$grandGross = $t11['gross'] + $t12['gross'] + $t13['gross'] + $t14['gross'] + $t21['gross'] + $t22['gross'] + $t23['gross'] + $t24['gross'];
$grandNuol  = $t11['nuol'] + $t12['nuol'] + $t13['nuol'] + $t14['nuol'] + $t21['nuol'] + $t22['nuol'] + $t23['nuol'] + $t24['nuol'];
$grandP1    = $t11['p1']   + $t12['p1']   + $t13['p1']   + $t14['p1']   + $t21['p1']   + $t22['p1']   + $t23['p1']   + $t24['p1'];
$grandP2    = $t11['p2']   + $t12['p2']   + $t13['p2']   + $t14['p2']   + $t21['p2']   + $t22['p2']   + $t23['p2']   + $t24['p2'];
@endphp

{{-- ═══════════════════════════════════════════
     SECTION 1.1 — Credit fees, Year 2-4 + Master
     ═══════════════════════════════════════════ --}}
<div class="sec-hd"><span>1.1</span>ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ຊັ້ນປີ 2–4 ລະບົບຈ່າຍເງິນ ແລະ ລະດັບ ປ.ໂທ / ປ.ເອກ</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:3%">#</th>
            <th rowspan="2" style="width:18%">ສາຂາວິຊາ</th>
            <th rowspan="2" style="width:5%">ຊັ້ນ</th>
            <th rowspan="2" style="width:6%">ຈຳ ນ/ສ</th>
            <th rowspan="2" style="width:5%">ໜ່ວຍ<br>ກິດ</th>
            <th rowspan="2" style="width:8%">ລາຄາ/ໜ່ວຍ<br>(ກີບ)</th>
            <th rowspan="2" style="width:8%">ອັດຕາ/ຄົນ<br>(ກີບ)</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th rowspan="2" style="width:5%">%<br>ມຊ</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບ ມຊ<br>(ກີບ)</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
            <th colspan="2" style="width:16%">ການຈ່າຍ ຄວທ (ກີບ)</th>
        </tr>
        <tr>
            <th>ງວດ 1 (60%)</th>
            <th>ງວດ 2 (40%)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec11 as $idx => $item)
        @php
            $feePerPerson = $item->snap_course_credit_unit * $item->snap_credit_unit_price;
            $gross11      = $item->student_count * $feePerPerson;
            $nuolShare11  = $gross11 - $item->total_income;
        @endphp
        <tr>
            <td class="c">{{ $idx + 1 }}</td>
            <td class="prog">{{ $item->degreeProgram?->name ?? '—' }}</td>
            <td class="c">{{ $item->degreeProgram?->study_year ? 'ປີ '.$item->degreeProgram->study_year : '—' }}</td>
            <td class="r">{{ number_format($item->student_count) }}</td>
            <td class="c">{{ $item->snap_course_credit_unit ?? '—' }}</td>
            <td class="r">{{ $item->snap_credit_unit_price ? number_format($item->snap_credit_unit_price, 0) : '—' }}</td>
            <td class="r">{{ $feePerPerson ? number_format($feePerPerson, 0) : '—' }}</td>
            <td class="r">{{ number_format($gross11, 0) }}</td>
            <td class="c">{{ number_format($item->snap_nuol_pct * 100, 0) }}%</td>
            <td class="r">{{ number_format($nuolShare11, 0) }}</td>
            <td class="r">{{ number_format($item->total_income, 0) }}</td>
            <td class="r">{{ number_format($item->first_payment_amount, 0) }}</td>
            <td class="r">{{ number_format($item->second_payment_amount, 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="13" class="c" style="color:#999;padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="7" class="c">ລວມໝວດ 1.1</td>
            <td class="r">{{ number_format($t11['gross'], 0) }}</td>
            <td></td>
            <td class="r">{{ number_format($t11['nuol'], 0) }}</td>
            <td class="r">{{ number_format($t11['fac'], 0) }}</td>
            <td class="r">{{ number_format($t11['p1'], 0) }}</td>
            <td class="r">{{ number_format($t11['p2'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════
     SECTION 1.2 — Registration fee, Year 2-4
     ═══════════════════════════════════════════ --}}
<div class="sec-hd"><span>1.2</span>ລາຍຮັບຄ່າລົງທະບຽນ ນ/ສ ຊັ້ນປີ 2–4 ຂອງ ຄວທ</div>
<table>
    <thead>
        <tr>
            <th style="width:3%">#</th>
            <th style="width:25%">ລາຍການ</th>
            <th style="width:8%">ຈຳ ນ/ສ</th>
            <th style="width:12%">ຄ່າລົງທະບຽນ/ຄົນ (ກີບ)</th>
            <th style="width:12%">ລາຍຮັບລວມ (ກີບ)</th>
            <th style="width:6%">% ມຊ</th>
            <th style="width:12%">ລາຍຮັບ ມຊ (ກີບ)</th>
            <th style="width:12%">ລາຍຮັບ ຄວທ (ກີບ)</th>
            <th style="width:12%">ງວດ 1 (100%) (ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec12 as $idx => $item)
        @php
            $gross12     = $item->student_count * $item->snap_registration_fee_rate;
            $nuolShare12 = $gross12 - $item->total_income;
        @endphp
        <tr>
            <td class="c">{{ $idx + 1 }}</td>
            <td>ຄ່າລົງທະບຽນ (ທຸກສາຂາ)</td>
            <td class="r">{{ number_format($item->student_count) }}</td>
            <td class="r">{{ number_format($item->snap_registration_fee_rate, 0) }}</td>
            <td class="r">{{ number_format($gross12, 0) }}</td>
            <td class="c">{{ number_format($item->snap_nuol_pct * 100, 0) }}%</td>
            <td class="r">{{ number_format($nuolShare12, 0) }}</td>
            <td class="r">{{ number_format($item->total_income, 0) }}</td>
            <td class="r">{{ number_format($item->first_payment_amount, 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="c" style="color:#999;padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="4" class="c">ລວມໝວດ 1.2</td>
            <td class="r">{{ number_format($t12['gross'], 0) }}</td>
            <td></td>
            <td class="r">{{ number_format($t12['nuol'], 0) }}</td>
            <td class="r">{{ number_format($t12['fac'], 0) }}</td>
            <td class="r">{{ number_format($t12['p1'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════
     SECTION 1.3 — Credit fees, Year 1
     ═══════════════════════════════════════════ --}}
<div class="sec-hd"><span>1.3</span>ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ຊັ້ນປີ 1 ລະບົບຈ່າຍເງິນ</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:3%">#</th>
            <th rowspan="2" style="width:18%">ສາຂາວິຊາ</th>
            <th rowspan="2" style="width:5%">ຊັ້ນ</th>
            <th rowspan="2" style="width:6%">ຈຳ ນ/ສ</th>
            <th rowspan="2" style="width:5%">ໜ່ວຍ<br>ກິດ</th>
            <th rowspan="2" style="width:8%">ລາຄາ/ໜ່ວຍ<br>(ກີບ)</th>
            <th rowspan="2" style="width:8%">ອັດຕາ/ຄົນ<br>(ກີບ)</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th rowspan="2" style="width:5%">%<br>ມຊ</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບ ມຊ<br>(ກີບ)</th>
            <th rowspan="2" style="width:9%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
            <th style="width:16%">ການຈ່າຍ ຄວທ (ກີບ)</th>
        </tr>
        <tr>
            <th>ງວດ 1 (60%)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec13 as $idx => $item)
        @php
            $feePerPerson13 = $item->snap_course_credit_unit * $item->snap_credit_unit_price;
            $gross13        = $item->student_count * $feePerPerson13;
            $nuolShare13    = $gross13 - $item->total_income;
        @endphp
        <tr>
            <td class="c">{{ $idx + 1 }}</td>
            <td class="prog">{{ $item->degreeProgram?->name ?? '—' }}</td>
            <td class="c">ປີ 1</td>
            <td class="r">{{ number_format($item->student_count) }}</td>
            <td class="c">{{ $item->snap_course_credit_unit ?? '—' }}</td>
            <td class="r">{{ $item->snap_credit_unit_price ? number_format($item->snap_credit_unit_price, 0) : '—' }}</td>
            <td class="r">{{ $feePerPerson13 ? number_format($feePerPerson13, 0) : '—' }}</td>
            <td class="r">{{ number_format($gross13, 0) }}</td>
            <td class="c">{{ number_format($item->snap_nuol_pct * 100, 0) }}%</td>
            <td class="r">{{ number_format($nuolShare13, 0) }}</td>
            <td class="r">{{ number_format($item->total_income, 0) }}</td>
            <td class="r">{{ number_format($item->first_payment_amount, 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="12" class="c" style="color:#999;padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="7" class="c">ລວມໝວດ 1.3</td>
            <td class="r">{{ number_format($t13['gross'], 0) }}</td>
            <td></td>
            <td class="r">{{ number_format($t13['nuol'], 0) }}</td>
            <td class="r">{{ number_format($t13['fac'], 0) }}</td>
            <td class="r">{{ number_format($t13['p1'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════
     SECTION 1.4 — Registration fee, Year 1
     ═══════════════════════════════════════════ --}}
<div class="sec-hd"><span>1.4</span>ຄ່າລົງທະບຽນ ນ/ສ ຊັ້ນປີ 1 ລະບົບຈ່າຍເງິນ ຂອງ ຄວທ</div>
<table>
    <thead>
        <tr>
            <th style="width:3%">#</th>
            <th style="width:25%">ລາຍການ</th>
            <th style="width:8%">ຈຳ ນ/ສ</th>
            <th style="width:12%">ຄ່າລົງທະບຽນ/ຄົນ (ກີບ)</th>
            <th style="width:12%">ລາຍຮັບລວມ (ກີບ)</th>
            <th style="width:6%">% ມຊ</th>
            <th style="width:12%">ລາຍຮັບ ມຊ (ກີບ)</th>
            <th style="width:12%">ລາຍຮັບ ຄວທ (ກີບ)</th>
            <th style="width:12%">ງວດ 1 (100%) (ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec14 as $idx => $item)
        @php
            $gross14     = $item->student_count * $item->snap_registration_fee_rate;
            $nuolShare14 = $gross14 - $item->total_income;
        @endphp
        <tr>
            <td class="c">{{ $idx + 1 }}</td>
            <td>ຄ່າລົງທະບຽນ (ທຸກສາຂາ)</td>
            <td class="r">{{ number_format($item->student_count) }}</td>
            <td class="r">{{ number_format($item->snap_registration_fee_rate, 0) }}</td>
            <td class="r">{{ number_format($gross14, 0) }}</td>
            <td class="c">{{ number_format($item->snap_nuol_pct * 100, 0) }}%</td>
            <td class="r">{{ number_format($nuolShare14, 0) }}</td>
            <td class="r">{{ number_format($item->total_income, 0) }}</td>
            <td class="r">{{ number_format($item->first_payment_amount, 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="c" style="color:#999;padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="4" class="c">ລວມໝວດ 1.4</td>
            <td class="r">{{ number_format($t14['gross'], 0) }}</td>
            <td></td>
            <td class="r">{{ number_format($t14['nuol'], 0) }}</td>
            <td class="r">{{ number_format($t14['fac'], 0) }}</td>
            <td class="r">{{ number_format($t14['p1'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ==========================================================
     SECTIONS 2.1–2.4 — Income Rate Items 3–6
     ========================================================== --}}
@foreach([
    '2.1' => ['label' => 'Item 3', 't' => $t21, 'sec' => $sec21, 'type' => 'rate'],
    '2.2' => ['label' => 'Item 4 (1.2+1.4 × ອັດຕາ)', 't' => $t22, 'sec' => $sec22, 'type' => 'fee'],
    '2.3' => ['label' => 'Item 5 (1.2+1.4 × ອັດຕາ)', 't' => $t23, 'sec' => $sec23, 'type' => 'fee'],
    '2.4' => ['label' => 'Item 6', 't' => $t24, 'sec' => $sec24, 'type' => 'rate'],
] as $secCode => [$secLabel, $secT, $secItems, $secType])
<div class="sec-hd"><span>{{ $secCode }}</span>{{ $secLabel }}</div>
<table>
    <thead>
        <tr>
            <th style="width:3%">#</th>
            <th style="width:30%">ລາຍການ</th>
            <th style="width:10%">ຈຳ ນ/ສ</th>
            <th style="width:14%">ອັດຕາ/ຄົນ (ກີບ)</th>
            <th style="width:14%">ລາຍຮັບລວມ (ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($secItems as $idx => $item)
        @php
            $rateSnap = $secType === 'fee' ? ($item->snap_registration_fee_rate ?? 0) : ($item->snap_credit_unit_price ?? 0);
            $grossRow = $item->student_count * $rateSnap;
        @endphp
        <tr>
            <td class="c">{{ $idx + 1 }}</td>
            <td>{{ $item->degreeProgram?->name ?? 'ລວມ (ທຸກສາຂາ)' }}</td>
            <td class="r">{{ number_format($item->student_count) }}</td>
            <td class="r">{{ number_format($rateSnap, 0) }}</td>
            <td class="r">{{ number_format($item->total_income, 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="c" style="color:#999;padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="4" class="c">ລວມໝວດ {{ $secCode }}</td>
            <td class="r">{{ number_format($secT['fac'], 0) }}</td>
        </tr>
    </tfoot>
</table>
@endforeach


<div class="summary-wrap">
    <div class="summary-title">ສັງລວມລາຍຮັບວິຊາການທັງໝົດ</div>
    <table class="sum-table">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:30%">ໝວດ</th>
                <th style="width:13%">ລາຍຮັບລວມ (ກີບ)</th>
                <th style="width:13%">ລາຍຮັບ ມຊ (ກີບ)</th>
                <th style="width:13%">ລາຍຮັບ ຄວທ (ກີບ)</th>
                <th style="width:13%">ງວດ 1 (ກີບ)</th>
                <th style="width:13%">ງວດ 2 (ກີບ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach([
                '1' => ['ໝວດ 1.1 — ຄ່າໜ່ວຍກິດ ປີ 2–4 + ປ.ໂທ/ເອກ', $t11],
                '2' => ['ໝວດ 1.2 — ຄ່າລົງທະບຽນ ປີ 2–4',             $t12],
                '3' => ['ໝວດ 1.3 — ຄ່າໜ່ວຍກິດ ປີ 1',                 $t13],
                '4' => ['ໝວດ 1.4 — ຄ່າລົງທະບຽນ ປີ 1',                $t14],
                '5' => ['ໝວດ 2.1 — Item 3',                            $t21],
                '6' => ['ໝວດ 2.2 — Item 4',                            $t22],
                '7' => ['ໝວດ 2.3 — Item 5',                            $t23],
                '8' => ['ໝວດ 2.4 — Item 6',                            $t24],
            ] as $num => [$label, $t])
            <tr>
                <td class="c">{{ $num }}</td>
                <td>{{ $label }}</td>
                <td class="r">{{ number_format($t['gross'], 0) }}</td>
                <td class="r">{{ number_format($t['nuol'], 0) }}</td>
                <td class="r">{{ number_format($t['fac'], 0) }}</td>
                <td class="r">{{ number_format($t['p1'], 0) }}</td>
                <td class="r">{{ number_format($t['p2'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand">
                <td colspan="2" class="c">ລວມທັງໝົດ (1.1 + 1.2 + 1.3 + 1.4)</td>
                <td class="r">{{ number_format($grandGross, 0) }}</td>
                <td class="r">{{ number_format($grandNuol, 0) }}</td>
                <td class="r">{{ number_format($grandFac, 0) }}</td>
                <td class="r">{{ number_format($grandP1, 0) }}</td>
                <td class="r">{{ number_format($grandP2, 0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ═══ Signature block ═══ --}}
<div class="signatures" style="margin-top:22pt;">
    <div>
        <div style="font-size:8pt;color:#555;">ຜູ້ຈັດທຳ</div>
        <div style="height:36pt;"></div>
        <div class="sig-box">
            <div class="sig-label">ຫົວໜ້າພະແນກການເງິນ</div>
            <div class="sig-line">ຊື່ - ນາມສະກຸນ</div>
        </div>
    </div>
    <div>
        <div style="font-size:8pt;color:#555;">ຜູ້ກວດສອບ</div>
        <div style="height:36pt;"></div>
        <div class="sig-box">
            <div class="sig-label">ຮອງຄະນະບໍດີ</div>
            <div class="sig-line">ຊື່ - ນາມສະກຸນ</div>
        </div>
    </div>
    <div>
        <div style="font-size:8pt;color:#555;text-align:right;">ວຽງຈັນ, ວັນທີ.........ເດືອນ.........ປີ {{ $academicIncome->fiscal_year }}</div>
        <div style="height:36pt;"></div>
        <div class="sig-box">
            <div class="sig-label">ຄະນະບໍດີ ຄວທ</div>
            <div class="sig-line">ຊື່ - ນາມສະກຸນ</div>
        </div>
    </div>
</div>

</div>{{-- end doc-wrap --}}
</body>
</html>
