@extends('layouts.admin')

@section('title', 'ພາບລວມ ແຜນງວດງົບປະມານ ' . $budgetPlan->fiscal_year)
@section('page-title', 'ພາບລວມ ແຜນງວດງົບປະມານ 1, 2 ຕົ້ນປີ ' . $budgetPlan->fiscal_year)

@section('content')

    {{-- ── Top action bar ──────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 print:hidden">
        <a href="{{ route('head_of_finance.budget-installment.show', $budgetPlan) }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            ກັບຄືນ
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                ພິມ
            </button>
        </div>
    </div>

    {{-- ── Preview Container (mimics the official document layout) ───── --}}
    <div class="bg-white rounded-xl shadow-lg p-8 mx-auto" style="max-width: 1300px;" id="preview-container">

        {{-- Document Header with Logo --}}
        <div class="text-center mb-1">
            <img src="{{ asset('storage/logolao.jpg') }}" alt="ຕາລາເຄື່ອງໝາຍຊາດ" class="mx-auto" style="height: 80px;">
        </div>
        <div class="text-center mb-2">
            <p class="text-sm font-bold text-gray-800">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
            <p class="text-sm font-semibold text-gray-700">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
        </div>

        <div class="flex justify-between items-start mt-4 mb-2 text-sm text-gray-700">
            <div>
                <p class="font-bold">ມະຫາວິທະຍາໄລແຫ່ງຊາດ</p>
                <p class="font-bold">ຄະນະວິທະຍາສາດທຳມະຊາດ</p>
            </div>
            <div class="text-right">
                <p>ເລກທີ ............../ຄວທ</p>
                <p>ນະຄອນຫຼວງວຽງຈັນ ວັນທີ ........./........./ {{ $budgetPlan->fiscal_year - 1 }}</p>
            </div>
        </div>

        <h2 class="text-center text-lg font-bold text-gray-900 my-4 leading-relaxed">
            ແຜນງົບປະມານລາຍຈ່າຍວິຊາການ ປະຈຳງວດ1,2 ແລະ ແຜນງົບປະມານ 06 ເດືອນຕົ້ນປີ {{ $budgetPlan->fiscal_year }}<br>
            ຂອງ ຄະນະວິທະຍາສາດທຳມະຊາດ
        </h2>

        <div class="text-right text-xs text-gray-500 mb-1">(ຫົວໜ່ວຍ: ລ້ານກີບ)</div>

        {{-- Calculate Totals --}}
        @php
            $totalAnnual = 0;
            $totalP1 = 0;
            $totalP2 = 0;

            foreach ($budgetPlan->lineItems as $item) {
                if (str_ends_with($item->account->account_code ?? '', '000000')) {
                    $totalAnnual += ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                    $totalP1 += $item->period_1_amount ?? 0;
                    $totalP2 += $item->period_2_amount ?? 0;
                }
            }

            $total6Months = $totalP1 + $totalP2;
            $total6MonthsEnd = $totalAnnual - $total6Months;

            // Collect root parts for equation label
            $rootParts = [];
            foreach ($budgetPlan->lineItems as $item) {
                if (str_ends_with($item->account->account_code ?? '', '000000')) {
                    $rootParts[] = substr($item->account->account_code, 0, 2);
                }
            }
            $rootEquation = count($rootParts) > 0 ? implode('+', array_unique($rootParts)) : '';

            if (!function_exists('installmentPreviewFormat')) {
                function installmentPreviewFormat($number)
                {
                    if ($number == 0)
                        return '0';
                    return number_format($number, 3, '.', ',');
                }
                function installmentPreviewFormat2($number)
                {
                    if ($number == 0)
                        return '0';
                    return rtrim(rtrim(number_format($number, 3, '.', ','), '0'), '.');
                }
            }
        @endphp

        {{-- Budget installment Table --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse" style="font-size: 10px;" id="preview-installment-table">
                <thead>
                    {{-- Row 1: Main grouped headers --}}
                    <tr class="bg-gray-100 text-gray-800">
                        <th rowspan="2" class="border border-black px-2 py-2 text-center font-bold" style="width: 40px;">
                            ພາກ</th>
                        <th rowspan="2" class="border border-black px-2 py-2 text-center font-bold" style="width: 40px;">
                            ພາກ<br>ສ່ວນ</th>
                        <th rowspan="2" class="border border-black px-2 py-2 text-center font-bold" style="width: 40px;">
                            ຮ່ວງ</th>
                        <th rowspan="2" class="border border-black px-2 py-2 text-center font-bold" style="width: 40px;">
                            ລູກ<br>ຮ່ວງ</th>
                        <th rowspan="2" class="border border-black px-3 py-2 text-center font-bold"
                            style="min-width: 180px;">ເນື້ອໃນລາຍຈ່າຍ</th>
                        <th rowspan="2" class="border border-black px-3 py-2 text-center font-bold"
                            style="width: 120px;">
                            ແຜນການ<br>ປີ {{ $budgetPlan->fiscal_year }}
                        </th>
                        <th colspan="3" class="border border-black px-3 py-2 text-center font-bold">
                            ແຜນ 06 ເດືອນຕົ້ນປີ {{ $budgetPlan->fiscal_year }}
                        </th>
                        <th rowspan="2" class="border border-black px-3 py-2 text-center font-bold"
                            style="width: 120px;">
                            ແຜນ 06 ເດືອນ<br>ທ້າຍປີ {{ $budgetPlan->fiscal_year }}
                        </th>
                    </tr>
                    {{-- Row 2: Sub-headers under "ແຜນ 06 ເດືອນຕົ້ນປີ" --}}
                    <tr class="bg-gray-50 text-gray-700">
                        <th class="border border-black px-2 py-2 text-center font-semibold" style="width: 120px;">ແຜນງວດ1
                        </th>
                        <th class="border border-black px-2 py-2 text-center font-semibold" style="width: 120px;">ແຜນງວດ2
                        </th>
                        <th class="border border-black px-2 py-2 text-center font-semibold" style="width: 120px;">ແຜນ 06
                            ເດືອນ</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Grand Totals Row --}}
                    <tr class="bg-cyan-100 font-bold text-gray-900 border border-black"
                        style="background-color: #e0f2fe;">
                        <td colspan="5" class="border border-black px-2 py-2 text-right font-bold text-blue-900 underline">
                            ລວມຍອດເງິນພາກສ່ວນ({{ $rootEquation }}) =
                        </td>
                        <td class="border border-black px-1 py-2 text-right tabular-nums text-green-900 underline">
                            {{ installmentPreviewFormat2($totalAnnual) }}</td>
                        <td class="border border-black px-1 py-2 text-right tabular-nums underline">
                            {{ installmentPreviewFormat2($totalP1) }}</td>
                        <td class="border border-black px-1 py-2 text-right tabular-nums underline">
                            {{ installmentPreviewFormat2($totalP2) }}</td>
                        <td class="border border-black px-1 py-2 text-right tabular-nums underline">
                            {{ installmentPreviewFormat2($total6Months) }}</td>
                        <td class="border border-black px-1 py-2 text-right tabular-nums underline">
                            {{ installmentPreviewFormat2($total6MonthsEnd) }}</td>
                    </tr>

                    @forelse ($budgetPlan->lineItems as $item)
                        @php
                            $code = $item->account->account_code ?? '';
                            $part1 = substr($code, 0, 2);
                            $part2 = substr($code, 2, 2);
                            $part3 = substr($code, 4, 2);
                            $part4 = substr($code, 6, 2);

                            $isParent = $item->is_parent ?? false;

                            $annualAmount = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                            $p1Amount = $item->period_1_amount ?? 0;
                            $p2Amount = $item->period_2_amount ?? 0;
                            $m6Amount = $p1Amount + $p2Amount;
                            $m6endAmount = $annualAmount - $m6Amount;

                            // Determine row type for coloring
                            $isRoot = $part2 === '00' && $part3 === '00' && $part4 === '00'; // XX-00-00-00
                            $isSub = $part2 !== '00' && $part3 === '00' && $part4 === '00';  // XX-XX-00-00

                            $trClass = $isRoot
                                ? 'bg-green-100 font-bold border border-black underline'
                                : ($isSub
                                    ? 'bg-gray-50'
                                    : 'bg-white text-gray-800');

                            if ($isRoot) {
                                $trStyle = "background-color: #dcfce7;"; // light green
                            } else {
                                $trStyle = "";
                            }
                        @endphp
                        <tr class="{{ $trClass }} border border-black" style="{{ $trStyle }}">
                            <td class="border border-black px-1 py-1.5 text-center font-mono">{{ $part1 }}</td>
                            <td class="border border-black px-1 py-1.5 text-center font-mono">{{ $part2 }}</td>
                            <td class="border border-black px-1 py-1.5 text-center font-mono">{{ $part3 }}</td>
                            <td class="border border-black px-1 py-1.5 text-center font-mono">{{ $part4 }}</td>
                            <td class="border border-black px-2 py-1.5 {{ $isRoot ? 'underline' : '' }}">
                                @if(!$isParent && !$isRoot && !$isSub)- @endif{{ $item->account->account_name ?? '-' }}
                            </td>
                            <td class="border border-black px-1 py-1.5 text-right tabular-nums">
                                {{ installmentPreviewFormat2($annualAmount) }}</td>
                            <td class="border border-black px-1 py-1.5 text-right tabular-nums">
                                {{ installmentPreviewFormat2($p1Amount) }}</td>
                            <td class="border border-black px-1 py-1.5 text-right tabular-nums">
                                {{ installmentPreviewFormat2($p2Amount) }}</td>
                            <td class="border border-black px-1 py-1.5 text-right tabular-nums">
                                {{ installmentPreviewFormat2($m6Amount) }}</td>
                            <td class="border border-black px-1 py-1.5 text-right tabular-nums">
                                @if($m6endAmount < 0)
                                    <span class="text-red-600">{{ installmentPreviewFormat2($m6endAmount) }}</span>
                                @else
                                    {{ installmentPreviewFormat2($m6endAmount) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="border border-black px-6 py-10 text-center text-gray-400">ບໍ່ມີລາຍການ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Signatures Footer --}}
        <div class="flex justify-between mt-12 mb-8 px-4 sm:px-12 text-gray-800" style="font-size: 14px;">
            <div class="text-center font-bold">
                <p>ຫົວໜ້າຄະນະວິຊາ</p>
                <br><br><br>
            </div>
            <div class="text-center font-bold">
                <p>ຜູ້ສ້າງແຜນ</p>
                <br><br><br>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            @media print {
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                html,
                body {
                    background-color: #fff !important;
                }

                main {
                    padding: 0 !important;
                    margin: 0 !important;
                }

                #preview-container {
                    box-shadow: none !important;
                    border-radius: 0 !important;
                    padding: 8mm !important;
                    max-width: 100% !important;
                }

                #preview-installment-table {
                    font-size: 8px !important;
                }

                #preview-installment-table th,
                #preview-installment-table td {
                    padding: 4px 2px !important;
                }

                @page {
                    size: auto;
                    /* Uses printer settings which we expect to be A4 landscape */
                    margin: 0;
                }
            }
        </style>
    @endpush
@endsection