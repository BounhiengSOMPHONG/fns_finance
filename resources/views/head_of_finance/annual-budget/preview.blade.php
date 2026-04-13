@extends('layouts.admin')

@section('title', 'ພາບລວມ ແຜນງົບປະມານ ' . $annualBudget->fiscal_year)
@section('page-title', 'ພາບລວມ ແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year)

@section('content')

    {{-- ── Top action bar ──────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 print:hidden">
        <a href="{{ route('head_of_finance.annual-budget.show', $annualBudget) }}"
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

    {{-- ── Preview Container (mimics the PDF layout) ───────────────── --}}
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-5xl mx-auto" id="preview-container">

        {{-- Document Header --}}
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
                <p>ວັນທີ........./........./ {{ $annualBudget->fiscal_year - 1 }}</p>
            </div>
        </div>

        <h2 class="text-center text-xl font-bold text-gray-900 my-5">
            ແຜນງົບປະມານປະຈຳປີ {{ $annualBudget->fiscal_year }}
        </h2>

        {{-- Calculate Totals --}}
        @php
            $totalRegular = 0;
            $totalOther = 0;
            foreach ($annualBudget->lineItems as $item) {
                if (str_ends_with($item->account->formatted_code ?? '', '-00-00-00')) {
                    $totalRegular += $item->amount_regular ?? 0;
                    $totalOther += $item->amount_academic ?? 0;
                }
            }
            $totalLuam = $totalRegular + $totalOther;

            function previewGetRowType($code)
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

            function previewFormatNumber($number)
            {
                return number_format($number, 2, '.', ',');
            }
        @endphp

        {{-- Budget Table --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm" id="preview-budget-table">
                <thead>
                    {{-- Row 1: Main headers --}}
                    <tr class="bg-blue-100 text-gray-800">
                        <th class="border border-gray-400 px-3 py-2 w-28 text-center font-bold">ພາກ.ພາກສ່ວນ.</th>
                        <th class="border border-gray-400 px-3 py-2 font-bold text-center">ເນື້ອໃນ</th>
                        <th colspan="3" class="border border-gray-400 px-3 py-2 text-center font-bold">
                            ແຜນປີ {{ $annualBudget->fiscal_year }}
                        </th>
                    </tr>
                    {{-- Row 2: Sub headers --}}
                    <tr class="bg-blue-50 text-gray-700">
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-xs">ຮ່ວງ.ລູກຮ່ວງ</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-xs">ລາຍການຈ່າຍ</th>
                        <th class="border border-gray-400 px-3 py-2 w-32 text-center font-semibold text-xs">ແຜນລວມ</th>
                        <th class="border border-gray-400 px-3 py-2 w-32 text-center font-semibold text-xs">ງົບປະມານປົກກະຕິ
                        </th>
                        <th class="border border-gray-400 px-3 py-2 w-32 text-center font-semibold text-xs">ງົບປະມານວິຊາການ
                        </th>
                    </tr>
                    {{-- Row 3: Column numbers --}}
                    <tr class="bg-gray-100 text-gray-500 text-xs font-bold text-center">
                        <td class="border border-gray-400 px-2 py-1">4</td>
                        <td class="border border-gray-400 px-2 py-1">5</td>
                        <td class="border border-gray-400 px-2 py-1">6</td>
                        <td class="border border-gray-400 px-2 py-1">7</td>
                        <td class="border border-gray-400 px-2 py-1">8=6-7</td>
                    </tr>
                </thead>
                <tbody>
                    {{-- Grand Totals Row --}}
                    <tr class="bg-green-50 font-bold text-gray-900">
                        <td class="border border-gray-400 px-3 py-2 text-center"></td>
                        <td class="border border-gray-400 px-3 py-2"></td>
                        <td class="border border-gray-400 px-3 py-2 text-right tabular-nums">
                            {{ previewFormatNumber($totalLuam) }}</td>
                        <td class="border border-gray-400 px-3 py-2 text-right tabular-nums">
                            {{ previewFormatNumber($totalRegular) }}</td>
                        <td class="border border-gray-400 px-3 py-2 text-right tabular-nums">
                            {{ previewFormatNumber($totalOther) }}</td>
                    </tr>

                    @forelse ($annualBudget->lineItems as $item)
                        @php
                            $code = $item->account->formatted_code ?? '';
                            $rowType = previewGetRowType($code);
                            $itemLuam = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);

                            $trClass = match ($rowType) {
                                'main' => 'bg-blue-100 font-bold text-blue-800',
                                'sub' => 'bg-purple-50 font-semibold text-gray-800',
                                default => 'bg-white text-gray-700 hover:bg-gray-50',
                            };
                        @endphp
                        <tr class="{{ $trClass }}">
                            <td class="border border-gray-400 px-3 py-1.5 text-center font-mono text-xs">{{ $code ?: '-' }}</td>
                            <td class="border border-gray-400 px-3 py-1.5">
                                @if(!$item->is_parent) - @endif{{ $item->account->account_name ?? '-' }}
                            </td>
                            <td class="border border-gray-400 px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($itemLuam) }}</td>
                            <td class="border border-gray-400 px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($item->amount_regular ?? 0) }}</td>
                            <td class="border border-gray-400 px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($item->amount_academic ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border border-gray-400 px-6 py-10 text-center text-gray-400">ບໍ່ມີລາຍການ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Signatures Footer --}}
        <div class="flex justify-between mt-12 mb-8 px-4 sm:px-12 text-gray-800">
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
                    padding: 15mm !important;
                    max-width: 100% !important;
                }

                #preview-budget-table {
                    font-size: 10px !important;
                }

                @page {
                    size: A4 portrait;
                    margin: 0;
                    /* Set to 0 to hide browser headers/footers */
                }
            }
        </style>
    @endpush
@endsection