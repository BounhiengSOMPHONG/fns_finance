@extends('layouts.admin')

@section('title', 'ແຜນງົບປະມານ ' . $annualBudget->fiscal_year)
@section('page-title', 'ແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year)

@section('content')

    {{-- ── Top action bar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('head_of_finance.annual-budget.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                ກັບຄືນ
            </a>
            @php
                $statusMap = [
                    'DRAFT' => ['label' => 'ຮ່າງ', 'class' => 'bg-gray-100 text-gray-700'],
                    'PENDING_REVIEW' => ['label' => 'ລໍຖ້າຫົວໜ້າພາກສ່ວນ', 'class' => 'bg-yellow-100 text-yellow-700'],
                    'PENDING_FINAL_APPROVAL' => ['label' => 'ລໍຖ້າຄະນະບໍດີ', 'class' => 'bg-blue-100 text-blue-700'],
                    'APPROVED' => ['label' => 'ອະນຸມັດ', 'class' => 'bg-green-100 text-green-700'],
                    'MODIFYING' => ['label' => 'ກຳລັງແກ້ໄຂ', 'class' => 'bg-orange-100 text-orange-700'],
                ];
                $s = $statusMap[$annualBudget->status] ?? ['label' => $annualBudget->status, 'class' => 'bg-gray-100 text-gray-700'];
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('head_of_finance.annual-budget.preview', $annualBudget) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                ພາບລວມ
            </a>

            {{-- ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ — DRAFT / MODIFYING --}}
            @if(in_array($annualBudget->status, ['DRAFT', 'MODIFYING']))
            <button type="button" onclick="openReviewerModal()"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                📤 ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ
            </button>
            @endif

            {{-- ເລີ່ມແກ້ໄຂ — PENDING_REVIEW --}}
            @if($annualBudget->status === 'PENDING_REVIEW')
            <form action="{{ route('head_of_finance.annual-budget.start-modifying', $annualBudget) }}" method="POST" class="inline"
                onsubmit="return confirm('ເລີ່ມແກ້ໄຂແຜນ? ຜູ້ກວດສອບຈະບໍ່ສາມາດ comment ໄດ້ຈົນກວ່າທ່ານຈະສົ່ງກັບ.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600">
                    ✏️ ເລີ່ມແກ້ໄຂ
                </button>
            </form>
            @endif

            {{-- ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ — MODIFYING --}}
            @if($annualBudget->status === 'MODIFYING')
            <form action="{{ route('head_of_finance.annual-budget.submit-final', $annualBudget) }}" method="POST" class="inline"
                onsubmit="return confirm('ສົ່ງແຜນເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ?')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                    🏛️ ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ── Reviewer info banner ── --}}
    @if($annualBudget->reviewers->count() > 0 && in_array($annualBudget->status, ['PENDING_REVIEW', 'MODIFYING', 'PENDING_FINAL_APPROVAL']))
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm font-semibold text-blue-700 mb-1">👥 ຜູ້ກວດສອບທີ່ຖືກມອບໝາຍ:</p>
        <div class="flex flex-wrap gap-2">
            @foreach($annualBudget->reviewers as $reviewer)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ $reviewer->user->full_name ?? 'User #' . $reviewer->user_id }}
                @if($reviewer->user->role)
                <span class="text-blue-500 ml-1">({{ $reviewer->user->role->role_name }})</span>
                @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── MODIFYING warning banner ── --}}
    @if($annualBudget->status === 'MODIFYING')
    <div class="mb-4 p-4 bg-orange-50 border border-orange-300 rounded-lg flex items-start gap-3">
        <span class="text-orange-500 text-xl">⚠️</span>
        <div>
            <p class="font-semibold text-orange-700">ແຜນນີ້ຢູ່ໃນສະຖານະກຳລັງແກ້ໄຂ</p>
            <p class="text-sm text-orange-600 mt-0.5">ທ່ານສາມາດແກ້ໄຂລາຍການ, ສົ່ງກັບໃຫ້ reviewer ຂໍຄວາມຄິດເຫັນ, ຫຼື ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ.</p>
        </div>
    </div>
    @endif

    {{-- ── Budget Table (matches image layout) ────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden" id="budget-table-wrapper">

        {{-- Document Header --}}
        <div class="text-center py-4 border-b border-gray-200 relative">
            <p class="text-xs text-gray-500">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
            <p class="text-xs text-gray-500">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
            <div class="flex items-center justify-center gap-2 mt-2">
                <p class="text-sm font-bold">ແຜນງົບປະມານປະຈຳປີ {{ $annualBudget->fiscal_year }}</p>
                @if(in_array($annualBudget->status, ['DRAFT', 'MODIFYING']))
                <button type="button" onclick="openEditYearModal({{ $annualBudget->fiscal_year }})" class="text-yellow-600 hover:text-yellow-800" title="ແກ້ໄຂສົກປີ">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                @endif
            </div>
        </div>

        @php
            $totalRegular = 0;
            $totalAcademic = 0;
            foreach ($annualBudget->lineItems as $item) {
                if (str_ends_with($item->account->formatted_code ?? '', '-00-00-00')) {
                    $totalRegular += $item->amount_regular ?? 0;
                    $totalAcademic += $item->amount_academic ?? 0;
                }
            }
            $totalLuam = $totalRegular + $totalAcademic; // ແຜນລວມ = ປົກກະຕິ + ວິຊາການ
        @endphp

        {{-- Main Budget Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-orange-400 text-white text-center">
                        <th class="border border-orange-300 px-3 py-2 w-32">ພາກ.ພາກສ່ວນ.</th>
                        <th class="border border-orange-300 px-3 py-2">ເນື້ອໃນ</th>
                        <th colspan="3" class="border border-orange-300 px-3 py-2">ແຜນປີ {{ $annualBudget->fiscal_year }}
                        </th>
                        <th rowspan="2" class="border border-orange-300 px-3 py-2 w-24">ການດໍາ<br>ເນີນງານ</th>
                    </tr>
                    <tr class="bg-orange-400 text-white text-center">
                        <th class="border border-orange-300 px-3 py-2 text-xs">ຮ່ວງ.ລູກຮ່ວງ</th>
                        <th class="border border-orange-300 px-3 py-2">ລາຍການຈ່າຍ</th>
                        <th class="border border-orange-300 px-3 py-2 text-xs font-bold">ແຜນລວມ<br><span
                                class="font-normal">(6)</span></th>
                        <th class="border border-orange-300 px-3 py-2 text-xs">ງົບປະມານ<br>ປົກກະຕິ (7)</th>
                        <th class="border border-orange-300 px-3 py-2 text-xs">ງົບປະມານ<br>ວິຊາການ (8=6-7)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($annualBudget->lineItems as $item)
                        @php
                            $isHeader = str_ends_with($item->account->formatted_code ?? '', '-00-00');
                            $itemLuam = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                        @endphp
                        <tr class="{{ $isHeader ? 'bg-blue-50 font-semibold' : 'bg-white hover:bg-gray-50' }}">
                            <td
                                class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600 whitespace-nowrap text-center">
                                {{ $item->account->formatted_code ?? '-' }}
                            </td>
                            <td class="border border-gray-200 px-4 py-2 text-gray-800">
                                @if(!($item->is_parent ?? false))
                                    - 
                                @endif
                                {{ $item->account->account_name ?? '-' }}
                            </td>
                            {{-- ຄໍ 6: ແຜນລວມ = ປົກກະຕິ + ວິຊາການ --}}
                            <td class="border border-gray-200 px-3 py-2 text-right tabular-nums font-semibold">
                                {{ number_format($itemLuam, 2) }}
                            </td>
                            {{-- ຄໍ 7: ງົບປະມານປົກກະຕິ --}}
                            <td class="border border-gray-200 px-3 py-2 text-right tabular-nums">
                                {{ number_format($item->amount_regular ?? 0, 2) }}
                            </td>
                            {{-- ຄໍ 8=6-7: ງົບປະມານວິຊາການ --}}
                            <td class="border border-gray-200 px-3 py-2 text-right tabular-nums">
                                {{ number_format($item->amount_academic ?? 0, 2) }}
                            </td>
                            <td class="border border-gray-200 px-3 py-2 text-center">
                                @if(!($item->is_parent ?? false))
                                    @if(in_array($annualBudget->status, ['DRAFT', 'MODIFYING']))
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Edit inline via modal --}}
                                        <button type="button"
                                            onclick="openEditModal({{ $item->id }}, {{ $item->amount_regular ?? 0 }}, {{ $item->amount_academic ?? 0 }})"
                                            class="text-yellow-600 hover:text-yellow-800" title="ແກ້ໄຂ">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form
                                            action="{{ route('head_of_finance.annual-budget.items.destroy', [$annualBudget, $item]) }}"
                                            method="POST" class="inline" onsubmit="return confirm('ລຶບລາຍການນີ້?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="ລຶບ">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">ຍັງບໍ່ມີລາຍການ — ເພີ່ມໂດຍໃຊ້ຟອມລຸ່ມນີ້
                            </td>
                        </tr>
                    @endforelse

                    {{-- Totals row --}}
                    @if ($annualBudget->lineItems->count() > 0)
                        <tr class="bg-orange-100 font-bold">
                            <td colspan="2" class="border border-orange-200 px-4 py-2 text-right">ລວມທັງໝົດ</td>
                            {{-- ຄໍ 6: ແຜນລວມ --}}
                            <td class="border border-orange-200 px-3 py-2 text-right tabular-nums text-blue-800">
                                {{ number_format($totalLuam, 2) }}
                            </td>
                            {{-- ຄໍ 7: ງົບປົກກະຕິ --}}
                            <td class="border border-orange-200 px-3 py-2 text-right tabular-nums text-orange-800">
                                {{ number_format($totalRegular, 2) }}
                            </td>
                            {{-- ຄໍ 8: ງົບວິຊາການ --}}
                            <td class="border border-orange-200 px-3 py-2 text-right tabular-nums text-orange-800">
                                {{ number_format($totalAcademic, 2) }}
                            </td>
                            <td class="border border-orange-200"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ── Bulk Add Items Form ─────────────────────────────────────────── --}}
        @if(in_array($annualBudget->status, ['DRAFT', 'MODIFYING']))
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">➕ ເພີ່ມລາຍການງົບປະມານ</h3>
                <button type="button" id="addRowBtn"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    ເພີ່ມແຖວໃໝ່
                </button>
            </div>

            <form action="{{ route('head_of_finance.annual-budget.items.bulk', $annualBudget) }}" method="POST"
                id="bulkForm">
                @csrf

                {{-- Table header --}}
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm" id="bulkTable">
                        <thead class="bg-gray-100 text-xs text-gray-600 uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left w-8">#</th>
                                <th class="px-3 py-2 text-left">ບັນຊີ (Chart of Account)</th>
                                <th class="px-3 py-2 text-left w-48">ງົບປະມານປົກກະຕິ (ຄໍ 7)</th>
                                <th class="px-3 py-2 text-left w-48">ງົບປະມານວິຊາການ (ຄໍ 8=6-7)</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="bulkRows">
                            {{-- JS will populate rows --}}
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center gap-3 mt-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        ບັນທຶກທັງໝົດ
                    </button>
                    <span class="text-xs text-gray-400">ສາມາດເພີ່ມຫຼາຍແຖວ ແລ້ວ Submit ເທື່ອດຽວ</span>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- ── Comments Section ─────────────────────────────────────────────── --}}
    @if($annualBudget->comments->count() > 0)
    @php
        $commentsByRound = $annualBudget->comments->groupBy('submission_round')->sortKeysDesc();
        $roundColors = ['bg-blue-600','bg-purple-600','bg-green-600','bg-orange-500','bg-red-500','bg-teal-600'];
    @endphp
    <div class="mt-8 p-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">ຄວາມຄິດເຫັນ / ການຕອບກັບ</h3>
        <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2">
            @foreach($commentsByRound as $round => $roundComments)
            @php $color = $roundColors[($round - 1) % count($roundColors)] ?? 'bg-gray-600'; @endphp
            <div>
                {{-- Round header --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-white {{ $color }}">
                        ຮອບທີ {{ $round > 0 ? $round : '—' }}
                    </span>
                    <div class="flex-1 border-t border-gray-200"></div>
                    <span class="text-xs text-gray-400">{{ $roundComments->count() }} ຄຳເຫັນ</span>
                </div>
                {{-- Comments in this round --}}
                <div class="space-y-3 pl-2">
                    @foreach($roundComments as $comment)
                    @php $isMarked = $comment->isMarked(); @endphp
                    <div id="comment-card-{{ $comment->id }}"
                        data-marked="{{ $isMarked ? 'true' : 'false' }}"
                        class="comment-card p-4 rounded-lg transition-colors duration-300
                        {{ $isMarked ? 'bg-green-50 border border-green-200' : ($comment->user_id === auth()->id() ? 'bg-blue-50 border border-blue-100 ml-6' : 'bg-gray-50 border border-gray-100 mr-6') }}">
                        <div class="flex justify-between items-start mb-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-sm text-gray-700">
                                    {{ $comment->user->full_name ?? 'User' }}
                                    <span class="font-normal text-gray-400 text-xs">({{ $comment->user->role->role_name ?? '' }})</span>
                                </span>
                                {{-- Acknowledged badge (toggles via JS) --}}
                                <span id="badge-{{ $comment->id }}"
                                    class="ack-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 transition-all duration-200
                                    {{ $isMarked ? '' : 'hidden' }}">
                                    ✓ ຮັບຮູ້ແລ້ວ
                                </span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-2">
                                <span class="text-xs text-gray-400">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                {{-- AJAX mark button --}}
                                <button type="button"
                                    id="mark-btn-{{ $comment->id }}"
                                    data-comment-id="{{ $comment->id }}"
                                    data-url="{{ route('head_of_finance.annual-budget.comments.mark', [$annualBudget, $comment]) }}"
                                    data-csrf="{{ csrf_token() }}"
                                    onclick="toggleMark(this)"
                                    class="mark-btn inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition-all duration-200
                                    {{ $isMarked ? 'bg-green-100 text-green-700 hover:bg-red-50 hover:text-red-500' : 'bg-gray-100 text-gray-500 hover:bg-green-100 hover:text-green-700' }}">
                                    <span class="mark-icon">{{ $isMarked ? '✓' : '○' }}</span>
                                    <span class="mark-label">{{ $isMarked ? 'ໝາຍແລ້ວ' : 'ໝາຍ' }}</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 whitespace-pre-line mt-1">{{ $comment->comment }}</p>
                        {{-- Footer ack line (toggles via JS) --}}
                        <p id="ack-footer-{{ $comment->id }}"
                            class="ack-footer text-xs text-green-600 mt-2 transition-all duration-200
                            {{ $isMarked ? '' : 'hidden' }}">
                            ✓ ຮັບຮູ້ໂດຍ
                            <span class="ack-by">{{ $isMarked ? ($comment->markedBy->full_name ?? 'HoF') : '' }}</span>
                            ·
                            <span class="ack-at">{{ $isMarked ? $comment->marked_at->format('d/m/Y H:i') : '' }}</span>
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- ── Edit Item Modal ────────────────────────────────────────────────── --}}
    <div id="editItemModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
            <h3 class="text-base font-semibold text-gray-800 mb-4">ແກ້ໄຂຈຳນວນເງິນ</h3>
            <form id="editItemForm" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">ງົບທຳມະດາ</label>
                        <input type="number" name="amount_regular" id="edit_regular" min="0" step="0.01"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">ງົບວິຊາການ</label>
                        <input type="number" name="amount_academic" id="edit_academic" min="0" step="0.01"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit"
                        class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        ບັນທຶກ
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        ຍົກເລີກ
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Year Modal ────────────────────────────────────────────────── --}}
    <div id="editYearModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
            <h3 class="text-base font-semibold text-gray-800 mb-4">ແກ້ໄຂສົກປີແຜນງົບປະມານ</h3>
            <form id="editYearForm" action="{{ route('head_of_finance.annual-budget.update', $annualBudget) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">ສົກປີ</label>
                        <input type="number" name="fiscal_year" id="edit_fiscal_year" min="2000" max="2100" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit"
                        class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        ບັນທຶກ
                    </button>
                    <button type="button" onclick="closeEditYearModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        ຍົກເລີກ
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reviewer Selection Modal ──────────────────────────────────────── --}}
    <div id="reviewerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40" style="display:none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md flex flex-col" style="max-height: 80vh;">
            {{-- Header (fixed) --}}
            <div class="p-6 pb-2">
                <h3 class="text-base font-semibold text-gray-800 mb-2">📤 ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ</h3>
                <p class="text-sm text-gray-500">ເລືອກຜູ້ທີ່ຕ້ອງການໃຫ້ກວດສອບແຜນນີ້:</p>
            </div>

            {{-- Scrollable user list --}}
            <form action="{{ route('head_of_finance.annual-budget.submit', $annualBudget) }}" method="POST"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 py-2 space-y-2">
                    @foreach($availableReviewers as $reviewer)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="checkbox" name="reviewer_ids[]" value="{{ $reviewer->id }}"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{ $annualBudget->reviewers->contains('user_id', $reviewer->id) ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $reviewer->full_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $reviewer->role->role_name_lao ?? '' }}
                                @if($reviewer->department)
                                    · {{ $reviewer->department->department_name }}
                                @endif
                            </p>
                        </div>
                    </label>
                    @endforeach
                    @if($availableReviewers->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">ບໍ່ມີຜູ້ໃຊ້ທີ່ active ໃນລະບົບ</p>
                    @endif
                </div>

                {{-- Buttons (always visible at bottom) --}}
                <div class="flex gap-3 p-6 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium"
                        {{ $availableReviewers->isEmpty() ? 'disabled' : '' }}>
                        ສົ່ງ
                    </button>
                    <button type="button" onclick="closeReviewerModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        ຍົກເລີກ
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <style>
            /* Customizing TomSelect to blend better with Tailwind forms */
            .ts-control { border-radius: 0.375rem; border-color: #d1d5db; padding: 0.375rem 0.5rem; font-size: 0.875rem; }
            .ts-control.focus { box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.5); border-color: #60a5fa; }
        </style>
        <script>
            // ── Edit-item modal ────────────────────────────────────────────────────
            const BASE_URL = '{{ url("head-of-finance/annual-budget/" . $annualBudget->id . "/items") }}';

            function openEditModal(itemId, regular, academic) {
                document.getElementById('edit_regular').value = regular;
                document.getElementById('edit_academic').value = academic;
                document.getElementById('editItemForm').action = BASE_URL + '/' + itemId;
                document.getElementById('editItemModal').classList.remove('hidden');
                document.getElementById('editItemModal').classList.add('flex');
            }

            function closeEditModal() {
                document.getElementById('editItemModal').classList.add('hidden');
                document.getElementById('editItemModal').classList.remove('flex');
            }

            document.getElementById('editItemModal').addEventListener('click', function (e) {
                if (e.target === this) closeEditModal();
            });

            // ── Edit-year modal ────────────────────────────────────────────────────
            function openEditYearModal(year) {
                document.getElementById('edit_fiscal_year').value = year;
                document.getElementById('editYearModal').classList.remove('hidden');
                document.getElementById('editYearModal').classList.add('flex');
            }

            function closeEditYearModal() {
                document.getElementById('editYearModal').classList.add('hidden');
                document.getElementById('editYearModal').classList.remove('flex');
            }

            document.getElementById('editYearModal').addEventListener('click', function (e) {
                if (e.target === this) closeEditYearModal();
            });

            // ── Reviewer modal ──────────────────────────────────────────────────
            function openReviewerModal() {
                const m = document.getElementById('reviewerModal');
                m.style.display = 'flex';
                m.classList.remove('hidden');
                m.classList.add('flex');
            }

            function closeReviewerModal() {
                const m = document.getElementById('reviewerModal');
                m.style.display = 'none';
                m.classList.add('hidden');
                m.classList.remove('flex');
            }

            document.getElementById('reviewerModal').addEventListener('click', function (e) {
                if (e.target === this) closeReviewerModal();
            });

            // ── Bulk-add dynamic rows ──────────────────────────────────────────────
            // Build the accounts option list once from PHP data
            @php
                $accounts->loadCount('children');
                $accountData = $accounts->map(function($a) {
                    return [
                        'id' => $a->id,
                        'code' => $a->formatted_code,
                        'raw' => $a->account_code,
                        'name' => $a->account_name,
                        'parent_id' => $a->parent_id,
                        'is_parent' => $a->children_count > 0
                    ];
                });
                $usedData = $annualBudget->lineItems->pluck('account_id');
            @endphp
            const allAccounts = @json($accountData);
            const usedIds = new Set(@json($usedData));

            let rowCount = 0;

            function addRow() {
                const idx = rowCount++;
                const tbody = document.getElementById('bulkRows');
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-100 hover:bg-gray-50';
                tr.setAttribute('data-row', idx);
                const selectId = `account_select_${idx}`;
                tr.innerHTML = `
                                <td class="px-3 py-2 text-center text-gray-400 text-xs">${idx + 1}</td>
                                <td class="px-3 py-2">
                                    <select id="${selectId}" name="items[${idx}][account_id]"
                                            class="w-full text-sm"
                                            placeholder="-- ພິມຊອກຫາບັນຊີ (ຕົວຢ່າງ: 60100100) --">
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[${idx}][amount_regular]"
                                           min="0" step="0.01" placeholder="0.00" oninput="validateRows()"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-400 text-right">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[${idx}][amount_academic]"
                                           min="0" step="0.01" placeholder="0.00" oninput="validateRows()"
                                           class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-400 text-right">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" onclick="removeRow(this)"
                                            class="text-red-400 hover:text-red-600" title="ລຶບແຖວ">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>`;
                tbody.appendChild(tr);
                
                const tsOptions = allAccounts
                    .filter(acc => !acc.is_parent && !usedIds.has(acc.id))
                    .map(acc => ({
                        value: String(acc.id),
                        text: acc.code + ' — ' + acc.name,
                        raw: acc.raw
                    }));

                // Initialize TomSelect on the new select element
                const ts = new TomSelect(`#${selectId}`, {
                    create: false,
                    maxOptions: null,
                    dropdownParent: "body",
                    options: tsOptions,
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text', 'raw'],
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    onChange: function() {
                        validateRows();
                    }
                });

                // Force dropdown to open UPWARDS instead of downwards
                const origPosition = ts.positionDropdown;
                ts.positionDropdown = function() {
                    origPosition.apply(ts, arguments);
                    const controlRect = ts.control.getBoundingClientRect();
                    // When dropdown parent is 'body', TomSelect sets absolute position relative to document.
                    // We just need to subtract the dropdown height and the control height to flip it upwards.
                    // TomSelect sets top = controlRect.top + window.scrollY + controlRect.height
                    // We change it to top = controlRect.top + window.scrollY - dropdownHeight
                    const dropdownHeight = ts.dropdown.offsetHeight;
                    ts.dropdown.style.top = (controlRect.top + window.scrollY - dropdownHeight - 4) + 'px';
                };
            }

            function removeRow(btn) {
                btn.closest('tr').remove();
                validateRows();
            }

            // Client-side visual validation
            function validateRows() {
                const rows = document.querySelectorAll('#bulkRows tr');
                
                // Reset styling
                rows.forEach(tr => {
                    tr.classList.remove('bg-red-50');
                    const tsControl = tr.querySelector('.ts-control');
                    if (tsControl) {
                        tsControl.style.borderColor = '';
                        tsControl.style.backgroundColor = '';
                    }
                    const inputs = tr.querySelectorAll('input[type="number"]');
                    inputs.forEach(inp => inp.classList.remove('border-red-500', 'bg-red-50', 'text-red-700'));
                });

                // 1. Duplicate check
                const selectedAccounts = new Map();
                const formValues = new Map();
                
                rows.forEach(tr => {
                    const sel = tr.querySelector('select');
                    const val = sel ? sel.value : '';
                    if (val) {
                        const accId = parseInt(val);
                        if (!selectedAccounts.has(accId)) selectedAccounts.set(accId, []);
                        selectedAccounts.get(accId).push(tr);
                        
                        const reg = tr.querySelector('input[name*="amount_regular"]');
                        const acad = tr.querySelector('input[name*="amount_academic"]');
                        formValues.set(accId, {
                            reg: parseFloat(reg.value) || 0,
                            acad: parseFloat(acad.value) || 0,
                            row: tr
                        });
                    }
                });

                let hasDuplicates = false;
                selectedAccounts.forEach((trArray) => {
                    if (trArray.length > 1) {
                        hasDuplicates = true;
                        trArray.forEach(tr => {
                            tr.classList.add('bg-red-50');
                            const tsControl = tr.querySelector('.ts-control');
                            if (tsControl) {
                                tsControl.style.borderColor = '#ef4444';
                                tsControl.style.backgroundColor = '#fef2f2';
                            }
                        });
                    }
                });
                
                // Allow Submit only if no errors
                const btnSubmit = document.querySelector('#bulkForm button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = hasDuplicates;
                    btnSubmit.classList.toggle('opacity-50', hasDuplicates);
                    btnSubmit.classList.toggle('cursor-not-allowed', hasDuplicates);
                }
            }

            // Wire up the "ເພີ່ມແຖວໃໝ່" button
            document.getElementById('addRowBtn').addEventListener('click', addRow);

            // Start with one empty row
            addRow();

            // Prevent submitting if no rows have an account selected
            document.getElementById('bulkForm').addEventListener('submit', function (e) {
                const rows = document.querySelectorAll('#bulkRows tr');
                const hasValid = Array.from(rows).some(tr => {
                    const sel = tr.querySelector('select');
                    return sel && sel.value;
                });
                if (!hasValid) {
                    e.preventDefault();
                    alert('ກະລຸນາເລືອກບັນຊີຢ່າງໜ້ອຍ 1 ລາຍການ!');
                }
            });
        </script>

        <style>
            @media print {

                #admin-sidebar,
                x-admin-header,
                .no-print,
                form {
                    display: none !important;
                }

                #budget-table-wrapper {
                    box-shadow: none;
                }
            }
        </style>

<script>
async function toggleMark(btn) {
    const id       = btn.dataset.commentId;
    const url      = btn.dataset.url;
    const csrf     = btn.dataset.csrf;
    const card     = document.getElementById('comment-card-' + id);
    const badge    = document.getElementById('badge-'        + id);
    const footer   = document.getElementById('ack-footer-'  + id);
    const icon     = btn.querySelector('.mark-icon');
    const label    = btn.querySelector('.mark-label');

    // Disable & show loading
    btn.disabled = true;
    const origIcon = icon.textContent;
    icon.textContent = '⏳';

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.marked) {
            // ── Mark ON ────────────────────────────────────────
            card.classList.remove('bg-blue-50','border-blue-100','bg-gray-50','border-gray-100','ml-6','mr-6');
            card.classList.add('bg-green-50','border-green-200');

            badge.classList.remove('hidden');

            icon.textContent  = '✓';
            label.textContent = 'ໝາຍແລ້ວ';
            btn.classList.remove('bg-gray-100','text-gray-500','hover:bg-green-100','hover:text-green-700');
            btn.classList.add('bg-green-100','text-green-700','hover:bg-red-50','hover:text-red-500');

            footer.querySelector('.ack-by').textContent = data.markedBy;
            footer.querySelector('.ack-at').textContent = data.markedAt;
            footer.classList.remove('hidden');
        } else {
            // ── Mark OFF ───────────────────────────────────────
            card.classList.remove('bg-green-50','border-green-200');
            card.classList.add('bg-gray-50','border-gray-100');

            badge.classList.add('hidden');

            icon.textContent  = '○';
            label.textContent = 'ໝາຍ';
            btn.classList.remove('bg-green-100','text-green-700','hover:bg-red-50','hover:text-red-500');
            btn.classList.add('bg-gray-100','text-gray-500','hover:bg-green-100','hover:text-green-700');

            footer.classList.add('hidden');
        }
    } catch (e) {
        icon.textContent = origIcon;
        alert('ເກີດຂໍ້ຜິດພາດ, ກະລຸນາລອງໃໝ່.');
    }

    btn.disabled = false;
}
</script>
    @endpush
@endsection
