<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <title>ແຜນງົບປະມານປະຈຳປີ {{ $annualBudget->fiscal_year }}</title>
    <style>
        body {
            font-family: 'notosanslao', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Document Header */
        .doc-header {
            text-align: center;
            margin-bottom: 5px;
        }

        .doc-header p {
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }

        .doc-header .line2 {
            font-size: 12px;
        }

        .header-grid {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 5px;
            border: none;
        }

        .header-grid td {
            vertical-align: top;
            border: none;
            padding: 2px 0;
        }

        .header-grid p {
            margin: 2px 0;
            font-size: 11px;
        }

        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 15px 0;
        }

        /* Main Table */
        table.budget-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.budget-table th,
        table.budget-table td {
            border: 1px solid #000;
            padding: 3px 5px;
        }

        table.budget-table th {
            background-color: #d6e4f0;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        /* Row styles */
        .row-grand-total {
            background-color: #e2efda;
            font-weight: bold;
        }

        .row-main-category {
            background-color: #d9e2f3;
            font-weight: bold;
            color: #0000cc;
        }

        .row-sub-category {
            background-color: #e2d9f3;
            font-weight: bold;
        }

        .row-detail {
            background-color: #ffffff;
        }

        .col-code {
            width: 90px;
            text-align: center;
        }

        .col-name {
            text-align: left;
        }

        .col-number {
            width: 110px;
            text-align: right;
            font-family: 'notosanslao', sans-serif;
        }

        .col-num-header {
            text-align: center;
        }

        .number-row td {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    {{-- ── Document Header ──────────────────────────────────────── --}}
    <div class="doc-header">
        <p>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
        <p class="line2">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
    </div>

    <table class="header-grid">
        <tr>
            <td class="text-left" style="width: 50%;">
                <p><strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong></p>
                <p><strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong></p>
            </td>
            <td class="text-right" style="width: 50%;">
                <p>ເລກທີ ............../ຄວທ</p>
                <p>ວັນທີ........./........./ {{ $annualBudget->fiscal_year - 1 }}</p>
            </td>
        </tr>
    </table>

    <div class="doc-title">ແຜນງົບປະມານປະຈຳປີ {{ $annualBudget->fiscal_year }}</div>

    {{-- ── Calculate Totals ──────────────────────────────────────── --}}
    @php
        $totalRegular = 0;
        $totalOther = 0;

        // Collect subtotals per main category and sub-category
        $categories = [];

        foreach ($annualBudget->lineItems as $item) {
            if (str_ends_with($item->account->formatted_code ?? '', '-00-00-00')) {
                $totalRegular += $item->amount_regular ?? 0;
                $totalOther += $item->amount_academic ?? 0;
            }
        }
        $totalLuam = $totalRegular + $totalOther;

        // Determine row type based on account_code pattern
        // XX-00-00-00 = main category
        // XX-XX-00-00 = sub-category
        // XX-XX-XX-00 = detail item
        function getRowType($code)
        {
            if (!$code)
                return 'detail';
            $parts = explode('-', $code);
            if (count($parts) !== 4)
                return 'detail';
            if ($parts[1] === '00' && $parts[2] === '00' && $parts[3] === '00')
                return 'main';
            if ($parts[2] === '00' && $parts[3] === '00')
                return 'sub';
            return 'detail';
        }

        function formatLaoNumber($number)
        {
            return number_format($number, 2, '.', ',');
        }

        // Calculate subtotals per main category
        $mainCatTotals = [];
        foreach ($annualBudget->lineItems as $item) {
            $code = $item->account->account_code ?? '';
            $parts = explode('-', $code);
            $mainKey = $parts[0] . '-' . ($parts[1] ?? '00') . '-00-00';
            if (getRowType($code) === 'main') {
                // This is the main category header itself
                continue;
            }
        }
    @endphp

    {{-- ── Budget Table ──────────────────────────────────────────── --}}
    <table class="budget-table">
        <thead>
            <tr>
                <th class="col-code" style="width: 90px;">ພາກ.ພາກສ່ວນ.</th>
                <th style="min-width: 200px;">ເນື້ອໃນ</th>
                <th colspan="3">ແຜນປີ {{ $annualBudget->fiscal_year }}</th>
            </tr>
            <tr>
                <th class="col-num-header" style="font-size: 10px;">ຮ່ວງ.ລູກຮ່ວງ</th>
                <th class="col-num-header" style="font-size: 10px;">ລາຍການຈ່າຍ</th>
                <th class="col-num-header" style="width: 110px;">ແຜນລວມ</th>
                <th class="col-num-header" style="width: 110px;">ງົບປະມານປົກກະຕິ</th>
                <th class="col-num-header" style="width: 110px;">ງົບປະມານວິຊາການ</th>
            </tr>
            <tr class="number-row">
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
                <td>8=6-7</td>
            </tr>
        </thead>
        <tbody>
            {{-- Grand Totals Row --}}
            <tr class="row-grand-total">
                <td class="col-code"></td>
                <td class="col-name"></td>
                <td class="col-number">{{ formatLaoNumber($totalLuam) }}</td>
                <td class="col-number">{{ formatLaoNumber($totalRegular) }}</td>
                <td class="col-number">{{ formatLaoNumber($totalOther) }}</td>
            </tr>

            @forelse ($annualBudget->lineItems as $item)
                @php
                    $code = $item->account->formatted_code ?? '';
                    $rowType = getRowType($code);
                    $itemLuam = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                    $rowClass = match ($rowType) {
                        'main' => 'row-main-category',
                        'sub' => 'row-sub-category',
                        default => 'row-detail',
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="col-code">{{ $code ?: '-' }}</td>
                    <td class="col-name">
                        @if(!$item->is_parent)- @endif{{ $item->account->account_name ?? '-' }}
                    </td>
                    <td class="col-number">{{ formatLaoNumber($itemLuam) }}</td>
                    <td class="col-number">{{ formatLaoNumber($item->amount_regular ?? 0) }}</td>
                    <td class="col-number">{{ formatLaoNumber($item->amount_academic ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">ບໍ່ມີລາຍການ</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>