<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ {{ $plan->fiscal_year }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'NotoSansLao', 'Noto Sans Lao', 'Phetsarath OT', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #f0f0f0;
        }

        /* ── Toolbar (screen only) ── */
        .toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: #1e293b;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-back  { background: #334155; color: #e2e8f0; }
        .btn-back:hover { background: #475569; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-pdf   { background: #16a34a; color: #fff; }
        .btn-pdf:hover { background: #15803d; }
        .toolbar-title { color: #94a3b8; font-size: 13px; margin-left: auto; }

        /* ── Page ── */
        .page {
            background: #fff;
            width: 277mm;
            min-height: 190mm;
            margin: 70px auto 20px;
            padding: 12mm 14mm;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }

        /* ── Header / Letterhead ── */
        .letterhead { text-align: center; margin-bottom: 6px; }
        .letterhead p { font-size: 11px; }
        .letterhead .main-org { font-size: 12px; font-weight: bold; }
        .letterhead .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
        }
        th { font-weight: bold; text-align: center; }

        .bg-header    { background: #fdba74; }
        .bg-subheader { background: #fed7aa; }
        .bg-total     { background: #a5f3fc; }
        .bg-section   { background: #e0e7ff; }
        .bg-group     { background: #f0fdf4; }

        td.num, th.num { text-align: right; white-space: nowrap; }
        td.center       { text-align: center; }

        /* ── Signatures ── */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
        }
        .sig-block { text-align: center; width: 22%; font-size: 10px; }
        .sig-block .sig-date { margin-bottom: 4px; }
        .sig-block .sig-role { font-weight: bold; }
        .sig-space { height: 30px; }

        /* ── Page break ── */
        .page-break { page-break-after: always; }

        /* ── Print overrides ── */
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .page {
                margin: 0;
                padding: 10mm 12mm;
                box-shadow: none;
                width: 100%;
                page-break-after: always;
            }
            .page:last-child { page-break-after: avoid; }
        }

        @page { size: A4 landscape; margin: 10mm 12mm; }
    </style>
</head>
<body>

{{-- ── Toolbar (screen) ── --}}
<div class="toolbar">
    <a href="{{ route('head_of_finance.academic_income.show', $plan) }}" class="btn-back">
        ← ກັບຄືນ
    </a>
    <button onclick="window.print()" class="btn-print">
        🖨 ພິມ
    </button>
    <a href="{{ route('head_of_finance.academic_income.pdf', $plan) }}" class="btn-pdf" target="_blank">
        📄 PDF
    </a>
    <span class="toolbar-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ {{ $plan->fiscal_year }}</span>
</div>

@php
function fmtN($n) { return number_format((float)$n, 0, '.', ','); }

$allRows = $summaryRows;

// Grand totals
$grandTotal    = 0; $grandNuol = 0; $grandKawt = 0;
$grandTeaching = 0; $grandRemainder = 0;
foreach ($allRows as $row) {
    $grandTotal       += $row['totalIncome'];
    $grandNuol        += $row['nuolObligation'];
    $grandKawt        += $row['kawtIncome'];
    $grandTeaching    += $row['teaching']  ?? 0;
    $grandRemainder   += $row['remainder'] ?? 0;
}

// Registration group totals
$regTotal = ($allRows['reg_year1']['totalIncome'] ?? 0) + ($allRows['reg_year24']['totalIncome'] ?? 0);
$regNuol  = ($allRows['reg_year1']['nuolObligation'] ?? 0) + ($allRows['reg_year24']['nuolObligation'] ?? 0);
$regKawt  = ($allRows['reg_year1']['kawtIncome'] ?? 0) + ($allRows['reg_year24']['kawtIncome'] ?? 0);

// Credit group totals
$creditRows = ['credit_1','credit_2','credit_3','credit_4','credit_masters_phd'];
$creditTotal = $creditNuol = $creditKawt = $creditTeaching = $creditRemainder = 0;
foreach ($creditRows as $k) {
    $r = $allRows[$k] ?? [];
    $creditTotal    += $r['totalIncome']    ?? 0;
    $creditNuol     += $r['nuolObligation'] ?? 0;
    $creditKawt     += $r['kawtIncome']     ?? 0;
    $creditTeaching += $r['teaching']       ?? 0;
    $creditRemainder+= $r['remainder']      ?? 0;
}

$sectionTitles = [
    '1.1' => '1.1  ລາຍຮັບຄ່າໜ່ວຍກິດ ປີ 2-4 (ລະບົບຈ່າຍເງິນ) ແລະ ປະລິນຍາໂທ',
    '1.2' => '1.2  ຄ່າລົງທະບຽນ ປີ 2-4 ຂອງ ຄວທ',
    '1.3' => '1.3  ລາຍຮັບຄ່າໜ່ວຍກິດ ປີ 1 ລະບົບຈ່າຍເງິນ',
    '1.4' => '1.4  ຄ່າລົງທະບຽນ ປີ 1 ລະບົບຈ່າຍເງິນ ຂອງ ຄວທ',
];
@endphp


{{-- ════════════════════════════════════════
     PAGE 1 — ຮ່າງສັງລວມ (Aggregate Summary)
     ════════════════════════════════════════ --}}
<div class="page">
    <div class="letterhead">
        <p>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
        <p>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
        <p class="main-org">ມະຫາວິທະຍາໄລແຫ່ງຊາດ</p>
        <p class="main-org">ຄະນະວິທະຍາສາດທຳມະຊາດ</p>
        <p class="doc-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ {{ $plan->fiscal_year }}</p>
    </div>

    <table>
        <thead>
            <tr class="bg-header">
                <th style="width:4%">ລ/ດ</th>
                <th style="width:24%">ເນື້ອໃນເອກະສານ</th>
                <th class="num" style="width:8%">ຈຳນວນພົນ</th>
                <th class="num" style="width:10%">ອັດຕາຕໍ່ໜ່ວຍ</th>
                <th class="num" style="width:12%">ຈຳນວນເງິນລວມ</th>
                <th class="num" style="width:12%">ມອບພັນທະ ມຊ</th>
                <th class="num" style="width:12%">ລາຍຮັບຂອງ ຄວທ</th>
                <th class="num" style="width:10%">ຄ່າສິດສອນ</th>
                <th class="num" style="width:10%">ເຫຼືອຈາກຄ່າສອນ</th>
            </tr>
        </thead>
        <tbody>
            {{-- Grand Total row --}}
            <tr class="bg-total">
                <td class="center"></td>
                <td><strong>ລວມລາຍຮັບທັງໝົດ</strong></td>
                <td class="num"></td>
                <td class="num"></td>
                <td class="num"><strong>{{ fmtN($grandTotal) }}</strong></td>
                <td class="num"><strong>{{ fmtN($grandNuol) }}</strong></td>
                <td class="num"><strong>{{ fmtN($grandKawt) }}</strong></td>
                <td class="num"><strong>{{ fmtN($grandTeaching) }}</strong></td>
                <td class="num"><strong>{{ fmtN($grandRemainder) }}</strong></td>
            </tr>

            {{-- 1. Registration fees group --}}
            <tr class="bg-section">
                <td class="center">1.</td>
                <td colspan="8"><strong>ຄ່າລົງທະບຽນນັກສຶກສາ</strong></td>
            </tr>
            @php $regSeq = 1; @endphp
            @foreach (['reg_year1' => 'ນັກສຶກສາ ປີທີ 1', 'reg_year24' => 'ນັກສຶກສາ ປີທີ 2, 3, 4'] as $key => $lbl)
            @php $r = $allRows[$key] ?? []; @endphp
            <tr>
                <td class="center">1.{{ $regSeq }}</td>
                <td style="padding-left:14px;">{{ $lbl }}</td>
                <td class="num">{{ fmtN($r['totalPersons'] ?? 0) }}</td>
                <td class="num"></td>
                <td class="num">{{ fmtN($r['totalIncome'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['nuolObligation'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['kawtIncome'] ?? 0) }}</td>
                <td class="num"></td>
                <td class="num">{{ fmtN($r['kawtIncome'] ?? 0) }}</td>
            </tr>
            @php $regSeq++; @endphp
            @endforeach
            {{-- Registration subtotal --}}
            <tr class="bg-group">
                <td colspan="4" class="center" style="font-weight:bold;">ລວມຄ່າລົງທະບຽນ</td>
                <td class="num"><strong>{{ fmtN($regTotal) }}</strong></td>
                <td class="num"><strong>{{ fmtN($regNuol) }}</strong></td>
                <td class="num"><strong>{{ fmtN($regKawt) }}</strong></td>
                <td class="num"></td>
                <td class="num"><strong>{{ fmtN($regKawt) }}</strong></td>
            </tr>

            {{-- 2. Credit fees group --}}
            <tr class="bg-section">
                <td class="center">2.</td>
                <td colspan="8"><strong>ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ</strong></td>
            </tr>
            @php
            $creditRowDefs = [
                'credit_1'           => ['seq' => '2.1', 'label' => 'ນັກສຶກສາ ປີທີ 1'],
                'credit_2'           => ['seq' => '2.2', 'label' => 'ນັກສຶກສາ ປີທີ 2'],
                'credit_3'           => ['seq' => '2.3', 'label' => 'ນັກສຶກສາ ປີທີ 3'],
                'credit_4'           => ['seq' => '2.4', 'label' => 'ນັກສຶກສາ ປີທີ 4'],
                'credit_masters_phd' => ['seq' => '2.5', 'label' => 'ນັກສຶກສາ ປ.ໂທ + ເອກ'],
            ];
            @endphp
            @foreach ($creditRowDefs as $key => $def)
            @php $r = $allRows[$key] ?? []; @endphp
            <tr>
                <td class="center">{{ $def['seq'] }}</td>
                <td style="padding-left:14px;">{{ $def['label'] }}</td>
                <td class="num">{{ fmtN($r['totalPersons'] ?? 0) }}</td>
                <td class="num"></td>
                <td class="num">{{ fmtN($r['totalIncome'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['nuolObligation'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['kawtIncome'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['teaching'] ?? 0) }}</td>
                <td class="num">{{ fmtN($r['remainder'] ?? 0) }}</td>
            </tr>
            @endforeach
            {{-- Credit subtotal --}}
            <tr class="bg-group">
                <td colspan="4" class="center" style="font-weight:bold;">ລວມຄ່າໜ່ວຍກິດ</td>
                <td class="num"><strong>{{ fmtN($creditTotal) }}</strong></td>
                <td class="num"><strong>{{ fmtN($creditNuol) }}</strong></td>
                <td class="num"><strong>{{ fmtN($creditKawt) }}</strong></td>
                <td class="num"><strong>{{ fmtN($creditTeaching) }}</strong></td>
                <td class="num"><strong>{{ fmtN($creditRemainder) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Signatures --}}
    <div class="signatures">
        @foreach (['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $role)
        <div class="sig-block">
            <div class="sig-date">ວັນທີ ......../......../{{ $plan->fiscal_year }}</div>
            <div class="sig-space"></div>
            <div class="sig-role">{{ $role }}</div>
        </div>
        @endforeach
    </div>
</div>


{{-- ═══════════════════════════════════
     PAGES 2–5 — Detail Sections 1.1–1.4
     ═══════════════════════════════════ --}}
@foreach (['1.1','1.2','1.3','1.4'] as $code)
@php
    $items   = $sections[$code];
    $isCredit = in_array($code, ['1.1','1.3']);
    $secTotal = $secNuol = $secKawt = 0;
    foreach ($items as $it) {
        $rate = $isCredit
            ? ($it->num_credits * $pricePerCredit)
            : (float)$it->rate_per_person;
        $t = $rate * $it->num_persons;
        $secTotal += $t;
        $secNuol  += $t * $it->nuol_percentage;
        $secKawt  += $t * (1 - $it->nuol_percentage);
    }
@endphp
<div class="page">
    <div class="letterhead">
        <p>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
        <p>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
        <p class="main-org">ມະຫາວິທະຍາໄລແຫ່ງຊາດ — ຄະນະວິທະຍາສາດທຳມະຊາດ</p>
        <p class="doc-title">{{ $sectionTitles[$code] }}</p>
        <p style="font-size:10px;margin-top:2px;">ສົກ {{ $plan->fiscal_year }}</p>
    </div>

    <table>
        <thead>
            <tr class="bg-header">
                <th style="width:4%">ລ/ດ</th>
                <th>ລາຍການ / ຫຼັກສູດ</th>
                <th class="num" style="width:11%">ອັດຕາຕໍ່ຄົນ (ກີບ)</th>
                <th class="num" style="width:7%">ຈຳນວນຄົນ</th>
                <th class="num" style="width:12%">ລາຍຮັບລວມ</th>
                <th class="num" style="width:7%">% ມຊ</th>
                <th class="num" style="width:12%">ພັນທະ ມຊ</th>
                <th class="num" style="width:7%">% ຄວທ</th>
                <th class="num" style="width:12%">ລາຍຮັບ ຄວທ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
            @php
                $rate = $isCredit
                    ? ($item->num_credits * $pricePerCredit)
                    : (float)$item->rate_per_person;
                $tot  = $rate * $item->num_persons;
                $nuol = $tot * $item->nuol_percentage;
                $kawt = $tot * (1 - $item->nuol_percentage);
            @endphp
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="num">{{ fmtN($rate) }}</td>
                <td class="num">{{ fmtN($item->num_persons) }}</td>
                <td class="num">{{ fmtN($tot) }}</td>
                <td class="center">{{ ($item->nuol_percentage * 100) }}%</td>
                <td class="num">{{ fmtN($nuol) }}</td>
                <td class="center">{{ ((1 - $item->nuol_percentage) * 100) }}%</td>
                <td class="num">{{ fmtN($kawt) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:8px;color:#666;">ຍັງບໍ່ມີລາຍການ</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="4" style="text-align:center;font-weight:bold;">ລວມ</td>
                <td class="num"><strong>{{ fmtN($secTotal) }}</strong></td>
                <td></td>
                <td class="num"><strong>{{ fmtN($secNuol) }}</strong></td>
                <td></td>
                <td class="num"><strong>{{ fmtN($secKawt) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    {{-- Signatures --}}
    <div class="signatures">
        @foreach (['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $role)
        <div class="sig-block">
            <div class="sig-date">ວັນທີ ......../......../{{ $plan->fiscal_year }}</div>
            <div class="sig-space"></div>
            <div class="sig-role">{{ $role }}</div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

</body>
</html>
