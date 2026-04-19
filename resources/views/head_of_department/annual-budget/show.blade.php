@extends('layouts.admin')

@section('title', 'ກວດສອບແຜນງົບປະມານ ' . $annualBudget->fiscal_year)
@section('page-title', 'ກວດສອບແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year)

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 print:hidden">
        <a href="{{ route('head_of_department.annual-budget.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            ກັບຄືນ
        </a>
    </div>

    {{-- ── Preview Container ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-5xl mx-auto mb-8" id="preview-container">
        {{-- Document Header --}}
        <div class="text-center mb-2">
            <p class="text-sm font-bold text-gray-800">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
            <p class="text-sm font-semibold text-gray-700">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
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
            <table class="w-full border-collapse text-sm border-black" id="preview-budget-table">
                <thead>
                    {{-- Row 1: Main headers --}}
                    <tr class="bg-orange-300 text-gray-900 border-black" style="background-color: #fca5a5;">
                        <th class="border border-black px-3 py-2 w-28 text-center font-bold" style="background-color: #fdba74;">ພາກ.ພາກສ່ວນ.</th>
                        <th class="border border-black px-3 py-2 font-bold text-center" style="background-color: #fdba74;">ເນື້ອໃນ</th>
                        <th colspan="3" class="border border-black px-3 py-2 text-center font-bold" style="background-color: #fdba74;">
                            ແຜນປີ {{ $annualBudget->fiscal_year }}
                        </th>
                    </tr>
                    {{-- Row 2: Sub headers --}}
                    <tr class="bg-orange-200 text-gray-900">
                        <th class="border border-black px-2 py-2 text-center font-bold text-xs" style="background-color: #fed7aa;">ຮ່ວງ.ລູກຮ່ວງ</th>
                        <th class="border border-black px-2 py-2 text-center font-bold text-xs" style="background-color: #fed7aa;">ລາຍການຈ່າຍ</th>
                        <th class="border border-black px-3 py-2 w-32 text-center font-bold text-xs" style="background-color: #fed7aa;">ແຜນລວມ</th>
                        <th class="border border-black px-3 py-2 w-32 text-center font-bold text-xs" style="background-color: #fed7aa;">ງົບປະມານປົກກະຕິ
                        </th>
                        <th class="border border-black px-3 py-2 w-32 text-center font-bold text-xs" style="background-color: #fed7aa;">ງົບປະມານວິຊາການ
                        </th>
                    </tr>
                    {{-- Row 3: Column numbers --}}
                    <tr class="bg-indigo-300 text-gray-900 text-sm font-bold text-center">
                        <td class="border border-black px-2 py-1" style="background-color: #c4b5fd;">4</td>
                        <td class="border border-black px-2 py-1" style="background-color: #c4b5fd;">5</td>
                        <td class="border border-black px-2 py-1" style="background-color: #c4b5fd;">6</td>
                        <td class="border border-black px-2 py-1" style="background-color: #c4b5fd;">7</td>
                        <td class="border border-black px-2 py-1" style="background-color: #c4b5fd;">8=6-7</td>
                    </tr>
                </thead>
                <tbody>
                    {{-- Grand Totals Row --}}
                    <tr class="bg-cyan-100 font-bold text-gray-900 border border-black" style="background-color: #a5f3fc;">
                        <td class="border border-black px-3 py-2 text-center"></td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-right tabular-nums text-700 underline">
                            {{ previewFormatNumber($totalLuam) }}
                        </td>
                        <td class="border border-black px-3 py-2 text-right tabular-nums text-700 underline">
                            {{ previewFormatNumber($totalRegular) }}
                        </td>
                        <td class="border border-black px-3 py-2 text-right tabular-nums text-700 underline">
                            {{ previewFormatNumber($totalOther) }}
                        </td>
                    </tr>

                    @forelse ($annualBudget->lineItems as $item)
                        @php
                            $code = $item->account->formatted_code ?? '';
                            $rowType = previewGetRowType($code);
                            $itemLuam = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);

                            $trClass = match ($rowType) {
                                'main' => 'bg-cyan-100 font-bold text-gray-900 border border-black',
                                'sub' => 'bg-white font-bold text-gray-900 border border-black',
                                default => 'bg-white text-gray-800 hover:bg-gray-50 border border-black',
                            };
                            
                            $trStyle = ($rowType === 'main') ? 'background-color: #a5f3fc;' : '';
                        @endphp
                        <tr class="{{ $trClass }}" style="{{ $trStyle }}">
                            <td class="border border-black px-3 py-1.5 text-center font-mono text-xs">{{ $code ?: '-' }}</td>
                            <td class="border border-black px-3 py-1.5">
                                @if(!$item->is_parent) - @endif{{ $item->account->account_name ?? '-' }}
                            </td>
                            <td class="border border-black px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($itemLuam) }}
                            </td>
                            <td class="border border-black px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($item->amount_regular ?? 0) }}
                            </td>
                            <td class="border border-black px-3 py-1.5 text-right tabular-nums">
                                {{ previewFormatNumber($item->amount_academic ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border border-gray-400 px-6 py-10 text-center text-gray-400">ບໍ່ມີລາຍການ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- ── Previous Comments ─────────────────────────────────────── --}}
        @if($annualBudget->comments->count() > 0)
        @php
            $commentsByRound = $annualBudget->comments->groupBy('submission_round')->sortKeysDesc();
            $roundColors = ['bg-blue-600','bg-purple-600','bg-green-600','bg-orange-500','bg-red-500','bg-teal-600'];
        @endphp
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ປະຫວັດຄວາມຄິດເຫັນ</h3>
                <div class="space-y-6 max-h-[450px] overflow-y-auto pr-2">
                    @foreach($commentsByRound as $round => $roundComments)
                    @php $color = $roundColors[($round - 1) % count($roundColors)] ?? 'bg-gray-600'; @endphp
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-white {{ $color }}">
                                ຮອບທີ {{ $round > 0 ? $round : '—' }}
                            </span>
                            <div class="flex-1 border-t border-gray-200"></div>
                            <span class="text-xs text-gray-400">{{ $roundComments->count() }} ຄຳເຫັນ</span>
                        </div>
                        <div class="space-y-3 pl-2">
                            @foreach($roundComments as $comment)
                            <div class="p-4 rounded-lg {{ $comment->isMarked() ? 'bg-green-50 border border-green-200' : ($comment->user_id === auth()->id() ? 'bg-blue-50 border border-blue-100' : 'bg-gray-50 border border-gray-100') }}">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-sm text-gray-700">
                                            {{ $comment->user->full_name ?? 'User' }}
                                        </span>
                                        @if($comment->isMarked())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            ✓ ຮັບຮູ້ແລ້ວ
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 shrink-0">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 whitespace-pre-line mt-1">{{ $comment->comment }}</p>
                                @if($comment->isMarked())
                                <p class="text-xs text-green-600 mt-2">✓ ຮັບຮູ້ໂດຍ {{ $comment->markedBy->full_name ?? 'HoF' }} · {{ $comment->marked_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Review Actions ────────────────────────────────────────── --}}
        <div class="p-6 bg-white rounded-xl shadow-md border-t-4 {{ $annualBudget->status === 'PENDING_REVIEW' ? 'border-blue-500' : 'border-gray-300' }}">
            <h3 class="text-lg font-bold text-gray-800 mb-2">📋 ການກວດສອບ</h3>

            @if($annualBudget->status === 'PENDING_REVIEW')
                <p class="text-sm text-gray-500 mb-4">ກະນຸນາໃຫ້ຄຳເຫັນໃສ່ແຜນງົບປະມານລຸ່ມນີ້:</p>
                <form action="{{ route('head_of_department.annual-budget.review', $annualBudget) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ຄວາມຄິດເຫັນ :</label>
                        <textarea name="comment" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 overflow-hidden resize-none"
                            placeholder="ພິມຄວາມຄິດເຫັນຂອງທ່ານ..."
                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="action" value="comment"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            💬 ສົ່ງຄວາມຄິດເຫັນ
                        </button>
                    </div>
                </form>
            @else
                <div class="flex items-center gap-3 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <span class="text-2xl">✏️</span>
                    <div>
                        <p class="text-sm font-semibold text-orange-700">ແຜນນີ້ກຳລັງຖືກແກ້ໄຂ</p>
                        <p class="text-xs text-orange-500 mt-0.5">ບໍ່ສາມາດໃຫ້ຄຳເຫັນໄດ້ໃນຂະນະນີ້ — ກະລຸນາລໍຖ້າຈົນກວ່າຈະສົ່ງກວດສອບໃໝ່.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
