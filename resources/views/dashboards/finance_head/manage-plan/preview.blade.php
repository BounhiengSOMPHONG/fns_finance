@extends('layouts.admin')

@section('title', 'Preview plan ' . $planningYear->year)
@section('page-title', 'Preview plan')

@section('content')
@php
    $sections = $report['sections'];
    $totals = $report['totals'];
    $summaryRows = $report['summaryRows'];
    $detail_1_1 = $report['detail_1_1'];
    $detail_1_3 = $report['detail_1_3'];
    $feeYear2_4 = $report['feeYear2_4'];
    $feeYear1 = $report['feeYear1'];
    $s1_2 = $report['s1_2'];
    $s1_1 = $report['s1_1'];

    $money = fn ($amount) => number_format((float) $amount, 0);
    $blankMoney = fn ($amount) => (float) $amount === 0.0 ? '0' : number_format((float) $amount, 0);
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    $grossIncome = function ($item): float {
        if (! $item) {
            return 0.0;
        }

        if ($item->snap_course_credit_unit !== null) {
            return (float) $item->student_count * (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
        }

        if ($item->snap_registration_fee_rate !== null) {
            return (float) $item->student_count * (float) $item->snap_registration_fee_rate;
        }

        return (float) $item->student_count * (float) $item->snap_credit_unit_price;
    };
    $programLabel = function ($item, bool $yearFirst = true): string {
        $program = $item->degreeProgram;
        $name = $program?->name ?? '-';
        $level = $program?->level;
        $year = $program?->study_year;

        if ($level === 'bachelor') {
            return $yearFirst ? 'ປີ ' . ($year ?: '-') . ' ' . $name : $name . ' ປີທີ ' . ($year ?: '-');
        }

        $prefix = $level === 'phd' ? 'ປະລິນຍາເອກ' : 'ປະລິນຍາໂທ';

        return $prefix . ' ' . $name;
    };
@endphp

<div class="income-preview">
    <section class="paper paper-summary">
        <div class="report-top">
            <div>
                <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
            </div>
        </div>

        <table class="report-table plan-table">
            <thead>
                <tr>
                    <th style="width:44px">ລ/ດ</th>
                    <th>ລາຍການ</th>
                    <th style="width:150px">ຈຳນວນເງິນຕາມແຜນ</th>
                    <th style="width:150px">ຈຳນວນເງິນຮັບຕົວຈິງ</th>
                    <th style="width:120px">ດຸນດ່ຽງ</th>
                    <th style="width:120px">ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryRows as $row)
                    <tr>
                        <td class="center">{{ $row['number'] }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td class="num">{{ $blankMoney($row['planned']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td class="center">ລວມ</td>
                    <td class="num">{{ $money($report['summaryPlanTotal']) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <h2 class="summary-caption">1. ແຜນງົບປະມານລາຍຮັບວິຊາການຂອງ ຄວທ ສົກ {{ $planningYear->year }}</h2>
    </section>

    <section class="paper">
        <div class="official-header">
            <div class="org-left">
                <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
            </div>
            <div class="nation-right">
                <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
            </div>
        </div>
        <h1 class="report-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ {{ $planningYear->year }}</h1>

        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:42px">ລ/ດ</th>
                    <th rowspan="2">ເນື້ອໃນເອກະສານ</th>
                    <th rowspan="2" style="width:76px">ຈຳນວນພົນ</th>
                    <th rowspan="2" style="width:96px">ອັດຕາຕໍ່ໜ່ວຍ</th>
                    <th rowspan="2" style="width:118px">ຈຳນວນເງິນລວມ</th>
                    <th rowspan="2" style="width:110px">ມອບພັນທະ ມຊ</th>
                    <th colspan="3">ລາຍຮັບຂອງ ຄວທ</th>
                </tr>
                <tr>
                    <th style="width:112px">ລວມລາຍຮັບ ຄວທ</th>
                    <th style="width:108px">ຄ່າສິດສອນ</th>
                    <th style="width:112px">ເຫຼືອຈາກຄ່າສອນ</th>
                </tr>
            </thead>
            <tbody>
                <tr class="grand-row">
                    <td></td>
                    <td class="center">ລວມລາຍຮັບທັງໝົດ</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($totals['gross']) }}</td>
                    <td class="num">{{ $money($totals['nuol']) }}</td>
                    <td class="num">{{ $money($totals['fns_income']) }}</td>
                    <td class="num">{{ $money($totals['teaching_fee']) }}</td>
                    <td class="num">{{ $money($totals['remaining']) }}</td>
                </tr>

                @php $s = $sections['s1']; @endphp
                <tr class="section-row">
                    <td class="center">1</td>
                    <td>{{ $s['title'] }}</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($s['totals']['gross']) }}</td>
                    <td class="num">{{ $money($s['totals']['nuol']) }}</td>
                    <td class="num">{{ $money($s['totals']['fns_income']) }}</td>
                    <td class="num">{{ $money($s['totals']['teaching_fee']) }}</td>
                    <td class="num">{{ $money($s['totals']['remaining']) }}</td>
                </tr>
                @foreach($s['rows'] as $key => $row)
                    <tr>
                        <td class="center">{{ $key }}</td>
                        <td class="indent">{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] ?: '' }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach

                @php $s = $sections['s2']; @endphp
                <tr class="section-row">
                    <td class="center">2</td>
                    <td>{{ $s['title'] }}</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($s['totals']['gross']) }}</td>
                    <td class="num">{{ $money($s['totals']['nuol']) }}</td>
                    <td class="num">{{ $money($s['totals']['fns_income']) }}</td>
                    <td class="num">{{ $money($s['totals']['teaching_fee']) }}</td>
                    <td class="num">{{ $money($s['totals']['remaining']) }}</td>
                </tr>
                @foreach($s['rows'] as $key => $row)
                    <tr>
                        <td class="center">{{ $key }}</td>
                        <td class="indent">{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] ?: '' }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach

                @foreach(['s3' => '3', 's4' => '4', 's5' => '5', 's6' => '6'] as $key => $number)
                    @php $row = $sections[$key]; @endphp
                    <tr class="section-row">
                        <td class="center">{{ $number }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] ?: '' }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-grid">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.1. ລາຍຮັບຄ່າໜ່ວຍກິດນັກຮຽນແຕ່ປີ 2-4 ລະບົບຈ່າຍເງິນ ແລະ ປະລິນຍາໂທ</h2>
        <table class="report-table">
            <thead>
                <tr>
                    <th>ລ/ດ</th>
                    <th>ລາຍການ / ຫຼັກສູດ</th>
                    <th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th>
                    <th>ຈຳນວນຄົນ</th>
                    <th>ລາຍຮັບລວມ</th>
                    <th>ຈຳນວນເປີເຊັນ ມຊ</th>
                    <th>ພັນທະມຊ</th>
                    <th>ຈຳນວນເປີເຊັນ ຄວທ</th>
                    <th>ລາຍຮັບຄວທ</th>
                </tr>
            </thead>
            <tbody>
                @php $dTotal = ['count' => 0, 'gross' => 0.0, 'nuol' => 0.0, 'fns' => 0.0]; @endphp
                @foreach($detail_1_1 as $item)
                    @php
                        $rate = (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
                        $gross = $grossIncome($item);
                        $nuolPct = (float) $item->snap_nuol_pct;
                        $nuol = $gross * $nuolPct;
                        $fns = $gross - $nuol;
                        $dTotal['count'] += (int) $item->student_count;
                        $dTotal['gross'] += $gross;
                        $dTotal['nuol'] += $nuol;
                        $dTotal['fns'] += $fns;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $programLabel($item) }}</td>
                        <td class="num">{{ $money($rate) }}</td>
                        <td class="num">{{ (int) $item->student_count }}</td>
                        <td class="num">{{ $money($gross) }}</td>
                        <td class="num">{{ $pct($nuolPct) }}</td>
                        <td class="num">{{ $money($nuol) }}</td>
                        <td class="num">{{ $pct(1 - $nuolPct) }}</td>
                        <td class="num">{{ $money($fns) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="num">{{ $dTotal['count'] }}</td>
                    <td class="num">{{ $money($dTotal['gross']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['nuol']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['fns']) }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.2. ລາຍຮັບຄ່າລົງທະບຽນນັກສຶກສາປີທີ 2-4 ຂອງ ຄວທ</h2>
        @include('dashboards.finance_head.manage-plan._registration-fee-table', [
            'feeSetting' => $feeYear2_4,
            'studentCount' => (int) ($s1_2?->student_count ?? 0),
            'money' => $money,
            'pct' => $pct,
        ])
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.3. ລາຍຮັບຄ່າໜ່ວຍກິດປີ 1 ລະບົບຈ່າຍເງິນ</h2>
        <table class="report-table">
            <thead>
                <tr>
                    <th>ລ/ດ</th>
                    <th>ລາຍການ / ຫຼັກສູດ</th>
                    <th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th>
                    <th>ຈຳນວນຄົນ</th>
                    <th>ລາຍຮັບລວມ</th>
                    <th>ຈຳນວນເປີເຊັນ ມຊ</th>
                    <th>ພັນທະມຊ</th>
                    <th>ຈຳນວນເປີເຊັນ ຄວທ</th>
                    <th>ລາຍຮັບຄວທ</th>
                </tr>
            </thead>
            <tbody>
                @php $dTotal = ['count' => 0, 'gross' => 0.0, 'nuol' => 0.0, 'fns' => 0.0]; @endphp
                @foreach($detail_1_3 as $item)
                    @php
                        $rate = (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
                        $gross = $grossIncome($item);
                        $nuolPct = (float) $item->snap_nuol_pct;
                        $nuol = $gross * $nuolPct;
                        $fns = $gross - $nuol;
                        $dTotal['count'] += (int) $item->student_count;
                        $dTotal['gross'] += $gross;
                        $dTotal['nuol'] += $nuol;
                        $dTotal['fns'] += $fns;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $programLabel($item, false) }}</td>
                        <td class="num">{{ $money($rate) }}</td>
                        <td class="num">{{ (int) $item->student_count }}</td>
                        <td class="num">{{ $money($gross) }}</td>
                        <td class="num">{{ $pct($nuolPct) }}</td>
                        <td class="num">{{ $money($nuol) }}</td>
                        <td class="num">{{ $pct(1 - $nuolPct) }}</td>
                        <td class="num">{{ $money($fns) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="num">{{ $dTotal['count'] }}</td>
                    <td class="num">{{ $money($dTotal['gross']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['nuol']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['fns']) }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.4. ຄ່າລົງທະບຽນນັກສຶກສາປີທີ 1 ລະບົບຈ່າຍເງິນຂອງ ຄວທ</h2>
        @include('dashboards.finance_head.manage-plan._registration-fee-table', [
            'feeSetting' => $feeYear1,
            'studentCount' => (int) ($s1_1?->student_count ?? 0),
            'money' => $money,
            'pct' => $pct,
        ])
    </section>
</div>

<style>
    .income-preview {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        color: #111827;
    }

    .paper {
        background: #fff;
        border: 1px solid #d8dce3;
        border-radius: 8px;
        box-shadow: 0 3px 14px rgba(17, 24, 39, .06);
        overflow-x: auto;
        padding: 1.2rem;
    }

    .report-top,
    .official-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .8rem;
    }

    .report-top strong,
    .official-header strong,
    .official-header span {
        display: block;
        line-height: 1.55;
    }

    .nation-right {
        text-align: center;
        min-width: 360px;
    }

    .nation-right span {
        font-size: .72rem;
    }

    .report-title,
    .summary-caption,
    .detail-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.45;
        margin: .9rem 0 .7rem;
        text-align: center;
    }

    .summary-caption {
        margin-top: 1rem;
        text-align: right;
    }

    .detail-title {
        text-align: left;
    }

    .report-table {
        border-collapse: collapse;
        font-size: .78rem;
        min-width: 1080px;
        width: 100%;
    }

    .plan-table {
        min-width: 920px;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #9ca3af;
        line-height: 1.35;
        padding: .42rem .5rem;
        vertical-align: middle;
    }

    .report-table th {
        background: #f3f4f6;
        color: #111827;
        font-weight: 800;
        text-align: center;
        white-space: nowrap;
    }

    .num {
        font-variant-numeric: tabular-nums;
        text-align: right;
        white-space: nowrap;
    }

    .center {
        text-align: center;
    }

    .indent {
        padding-left: 1.35rem !important;
    }

    .grand-row td,
    .total-row td {
        background: #eef2f7;
        font-weight: 800;
    }

    .section-row td {
        background: #f8fafc;
        font-weight: 700;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 2.1rem;
        text-align: center;
    }

    .signature {
        font-size: .76rem;
    }

    .signature div {
        border-bottom: 1px dotted #6b7280;
        height: 3rem;
        margin-bottom: .35rem;
    }

    @media print {
        @page {
            margin: 10mm;
            size: A4 landscape;
        }

        body {
            background: #fff !important;
        }

        .fns-topnav,
        .fns-sidebar {
            display: none !important;
        }

        .fns-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .income-preview {
            gap: 0;
        }

        .paper {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            min-height: 185mm;
            overflow: visible;
            padding: 0;
            page-break-after: always;
        }

        .paper:last-child {
            page-break-after: auto;
        }

        .report-table {
            font-size: 8.2pt;
            min-width: 0;
        }

        .report-table th,
        .report-table td {
            padding: 3.2pt 4pt;
        }

        .report-table th,
        .grand-row td,
        .total-row td,
        .section-row td {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endsection
