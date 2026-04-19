@extends('layouts.admin')

@section('title', 'ອະນຸມັດແຜນງົບປະມານ ' . $annualBudget->fiscal_year)
@section('page-title', 'ກວດສອບ ແລະ ອະນຸມັດແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year)

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('head_of_faculty.annual-budget.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                ກັບຄືນ
            </a>
            @php
                $statusMap = [
                    'PENDING_FINAL_APPROVAL' => ['label' => 'ລໍຖ້າການອະນຸມັດ', 'class' => 'bg-purple-100 text-purple-700'],
                    'APPROVED' => ['label' => 'ອະນຸມັດແລ້ວ', 'class' => 'bg-green-100 text-green-700'],
                    'MODIFYING' => ['label' => 'ກຳລັງແກ້ໄຂ', 'class' => 'bg-orange-100 text-orange-700'],
                ];
                $s = $statusMap[$annualBudget->status] ?? ['label' => $annualBudget->status, 'class' => 'bg-gray-100 text-gray-700'];
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
        </div>
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

            if (!function_exists('hofacGetRowType')) {
                function hofacGetRowType($code)
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
            }

            if (!function_exists('hofacFormatNumber')) {
                function hofacFormatNumber($number)
                {
                    return number_format($number, 2, '.', ',');
                }
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
                            {{ hofacFormatNumber($totalLuam) }}</td>
                        <td class="border border-black px-3 py-2 text-right tabular-nums text-700 underline">
                            {{ hofacFormatNumber($totalRegular) }}</td>
                        <td class="border border-black px-3 py-2 text-right tabular-nums text-700 underline">
                            {{ hofacFormatNumber($totalOther) }}</td>
                    </tr>

                    @forelse ($annualBudget->lineItems as $item)
                        @php
                            $code = $item->account->formatted_code ?? '';
                            $rowType = hofacGetRowType($code);
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
                                {{ hofacFormatNumber($itemLuam) }}</td>
                            <td class="border border-black px-3 py-1.5 text-right tabular-nums">
                                {{ hofacFormatNumber($item->amount_regular ?? 0) }}</td>
                            <td class="border border-black px-3 py-1.5 text-right tabular-nums">
                                {{ hofacFormatNumber($item->amount_academic ?? 0) }}</td>
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
                $roundColors = ['bg-blue-600', 'bg-purple-600', 'bg-green-600', 'bg-orange-500', 'bg-red-500', 'bg-teal-600'];
            @endphp
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ປະຫວັດຄວາມຄິດເຫັນ</h3>
                <div class="space-y-6 max-h-[450px] overflow-y-auto pr-2">
                    @foreach($commentsByRound as $round => $roundComments)
                        @php $color = $roundColors[($round - 1) % count($roundColors)] ?? 'bg-gray-600'; @endphp
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-white {{ $color }}">
                                    ຮອບທີ {{ $round > 0 ? $round : '—' }}
                                </span>
                                <div class="flex-1 border-t border-gray-200"></div>
                                <span class="text-xs text-gray-400">{{ $roundComments->count() }} ຄຳເຫັນ</span>
                            </div>
                            <div class="space-y-3 pl-2">
                                @foreach($roundComments as $comment)
                                    <div
                                        class="p-4 rounded-lg {{ $comment->user_id === auth()->id() ? 'bg-blue-50 border border-blue-100' : 'bg-gray-50 border border-gray-100' }}">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="font-semibold text-sm text-gray-700">
                                                {{ $comment->user->full_name ?? 'User' }}
                                                <span
                                                    class="font-normal text-gray-400 text-xs">({{ $comment->user->role->role_name ?? '' }})</span>
                                            </span>
                                            <span
                                                class="text-xs text-gray-400 shrink-0">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 whitespace-pre-line mt-1">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Approval Actions ────────────────────────────────────────── --}}
        <div
            class="p-6 bg-white rounded-xl shadow-md border-t-4 {{ $annualBudget->status === 'PENDING_FINAL_APPROVAL' ? 'border-purple-500' : 'border-gray-300' }}">
            <h3 class="text-lg font-bold text-gray-800 mb-2">🏛️ ການອະນຸມັດ</h3>

            @if($annualBudget->status === 'PENDING_FINAL_APPROVAL')
                <p class="text-sm text-gray-500 mb-4">ກະລຸນາກວດສອບແຜນງົບປະມານ ແລ້ວຕັດສິນໃຈ:</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ຄວາມຄິດເຫັນ (ຈຳເປັນສຳລັບການສົ່ງກັບ):</label>
                    <textarea id="mainComment" rows="3"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 overflow-hidden resize-none"
                        placeholder="ພິມຄວາມຄິດເຫັນຂອງທ່ານ..."
                        oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                </div>
                <div class="flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="openRejectModal()"
                        class="px-5 py-2.5 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 shadow-sm transition">
                        ↩ ປັບປຸງ
                    </button>
                    <button type="button" onclick="openApproveModal()"
                        class="px-5 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm transition">
                        ✅ ອະນຸມັດ
                    </button>
                </div>
            @elseif($annualBudget->status === 'APPROVED')
                <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="text-sm font-semibold text-green-700">ແຜນນີ້ໄດ້ຮັບການອະນຸມັດແລ້ວ</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <span class="text-2xl">✏️</span>
                    <div>
                        <p class="text-sm font-semibold text-orange-700">ແຜນນີ້ກຳລັງຖືກແກ້ໄຂ</p>
                        <p class="text-xs text-orange-500 mt-0.5">ກະລຸນາລໍຖ້າຈົນກວ່າຈະສົ່ງກວດສອບໃໝ່.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Approve Modal ──────────────────────────────────────────────── --}}
    @if($annualBudget->status === 'PENDING_FINAL_APPROVAL')
    <div id="approveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm scale-95 transform transition-transform duration-300" id="approveModalBody">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100/50 rounded-full ring-4 ring-green-50">
                <span class="text-green-600 text-3xl">✅</span>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">ຢືນຢັນການອະນຸມັດ</h3>
            <p class="text-sm text-center text-gray-600 mb-6 leading-relaxed">
                ທ່ານຕ້ອງການອະນຸມັດແຜນງົບປະມານນີ້ແທ້ບໍ່?<br>
                <span class="inline-block mt-2 px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-md border border-green-100">
                    ແຜນຈະຖືກອະນຸມັດ ແລະ ສາມາດນຳໄປໃຊ້ງານໄດ້ທັນທີ.
                </span>
            </p>
            <form action="{{ route('head_of_faculty.annual-budget.review', $annualBudget) }}" method="POST" class="flex gap-3">
                @csrf
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="comment" id="approveCommentField" value="">
                <button type="button" onclick="closeApproveModal()"
                    class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors focus:ring-4 focus:ring-gray-100">
                    ຍົກເລີກ
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 font-semibold shadow-sm transition-colors focus:ring-4 focus:ring-green-200">
                    ຢືນຢັນອະນຸມັດ
                </button>
            </form>
        </div>
    </div>

    {{-- ── Reject Modal ───────────────────────────────────────────────── --}}
    <div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm scale-95 transform transition-transform duration-300" id="rejectModalBody">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-orange-100/50 rounded-full ring-4 ring-orange-50">
                <span class="text-orange-500 text-3xl">↩</span>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">ຢືນຢັນການສົ່ງກັບ</h3>
            <p class="text-sm text-center text-gray-600 mb-6 leading-relaxed">
                ທ່ານຕ້ອງການສົ່ງແຜນກັບໃຫ້ແກ້ໄຂແທ້ບໍ່?<br>
                <span class="inline-block mt-2 px-3 py-1 bg-red-50 text-red-600 text-xs font-medium rounded-md border border-red-100">
                    ໝາຍເຫດ: ແຜນຈະກັບໄປສະຖານະ "ກຳລັງແກ້ໄຂ" ແລະ ຫົວໜ້າການເງິນຈະຕ້ອງແກ້ໄຂ ແລ້ວສົ່ງມາໃໝ່ອີກຄັ້ງ.
                </span>
            </p>
            <form action="{{ route('head_of_faculty.annual-budget.review', $annualBudget) }}" method="POST" class="flex gap-3">
                @csrf
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="comment" id="rejectCommentField" value="">
                <button type="button" onclick="closeRejectModal()"
                    class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors focus:ring-4 focus:ring-gray-100">
                    ຍົກເລີກ
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-orange-500 text-white rounded-xl hover:bg-orange-600 font-semibold shadow-sm transition-colors focus:ring-4 focus:ring-orange-200">
                    ຢືນຢັນສົ່ງກັບ
                </button>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        // ── Approve Modal ─────────────────────────────────────────────
        function openApproveModal() {
            const comment = document.getElementById('mainComment')?.value || '';
            document.getElementById('approveCommentField').value = comment;
            const m = document.getElementById('approveModal');
            const b = document.getElementById('approveModalBody');
            if(!m) return;
            m.style.display = 'flex';
            m.classList.remove('hidden');
            m.classList.add('flex');
            setTimeout(() => { b.classList.remove('scale-95'); b.classList.add('scale-100'); }, 10);
        }

        function closeApproveModal() {
            const m = document.getElementById('approveModal');
            const b = document.getElementById('approveModalBody');
            if(!m) return;
            b.classList.remove('scale-100'); b.classList.add('scale-95');
            setTimeout(() => { m.style.display = 'none'; m.classList.add('hidden'); m.classList.remove('flex'); }, 150);
        }

        document.getElementById('approveModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeApproveModal();
        });

        // ── Reject Modal ──────────────────────────────────────────────
        function openRejectModal() {
            const comment = document.getElementById('mainComment')?.value || '';
            document.getElementById('rejectCommentField').value = comment;
            const m = document.getElementById('rejectModal');
            const b = document.getElementById('rejectModalBody');
            if(!m) return;
            m.style.display = 'flex';
            m.classList.remove('hidden');
            m.classList.add('flex');
            setTimeout(() => { b.classList.remove('scale-95'); b.classList.add('scale-100'); }, 10);
        }

        function closeRejectModal() {
            const m = document.getElementById('rejectModal');
            const b = document.getElementById('rejectModalBody');
            if(!m) return;
            b.classList.remove('scale-100'); b.classList.add('scale-95');
            setTimeout(() => { m.style.display = 'none'; m.classList.add('hidden'); m.classList.remove('flex'); }, 150);
        }

        document.getElementById('rejectModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    </script>
    @endpush

@endsection