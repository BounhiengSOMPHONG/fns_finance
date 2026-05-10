@extends('layouts.admin')

@section('title', 'ລາຍຮັບວິຊາການ ສົກ ' . $plan->fiscal_year)
@section('page-title', 'ລາຍຮັບວິຊາການ ສົກ ' . $plan->fiscal_year)

@php
function fmtNum($n) {
    return number_format((float)$n, 0, '.', ',');
}

$sectionTitles = [
    '1.1' => '1.1  ລາຍຮັບຄ່າໜ່ວຍກິດ ປີ 2-4 (ລະບົບຈ່າຍເງິນ) ແລະ ປະລິນຍາໂທ',
    '1.2' => '1.2  ຄ່າລົງທະບຽນ ປີ 2-4 ຂອງ ຄວທ',
    '1.3' => '1.3  ລາຍຮັບຄ່າໜ່ວຍກິດ ປີ 1 ລະບົບຈ່າຍເງິນ',
    '1.4' => '1.4  ຄ່າລົງທະບຽນ ປີ 1 ລະບົບຈ່າຍເງິນ ຂອງ ຄວທ',
];
$isCredit = fn($code) => in_array($code, ['1.1', '1.3']);

$yearLabels = [
    '1'           => 'ປີ 1',
    '2'           => 'ປີ 2',
    '3'           => 'ປີ 3',
    '4'           => 'ປີ 4',
    'masters_phd' => 'ປ.ໂທ / ເອກ',
];
@endphp

@section('content')
<div class="space-y-4 pb-24">

    {{-- Top bar --}}
    <div class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('head_of_finance.academic_income.index') }}"
                class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-bold text-gray-900">ສົກ {{ $plan->fiscal_year }}</h2>
                <span class="text-xs px-2 py-0.5 rounded font-semibold
                    {{ $plan->status === 'APPROVED' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $plan->status === 'APPROVED' ? 'ອະນຸມັດແລ້ວ' : 'ຮ່າງ' }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($plan->status === 'DRAFT')
                <button type="button" onclick="document.getElementById('settingsModal').style.display='flex'"
                    class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    ຕັ້ງຄ່າ
                </button>
            @endif
            <a href="{{ route('head_of_finance.academic_income.summary', $plan) }}"
                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                ເບິ່ງສັງລວມ
            </a>
            @if ($plan->status === 'DRAFT')
                <button type="button"
                    onclick="openApproveModal()"
                    class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    ອະນຸມັດ
                </button>
                <button type="button"
                    onclick="openDeleteModal('{{ route('head_of_finance.academic_income.destroy', $plan) }}', 'ສົກ {{ $plan->fiscal_year }}')"
                    class="inline-flex items-center px-3 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    ລຶບ
                </button>
            @else
                <form method="POST" action="{{ route('head_of_finance.academic_income.revert_draft', $plan) }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 bg-orange-50 text-orange-600 text-sm font-medium rounded-lg hover:bg-orange-100 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        ຍ້ອນກັບຮ່າງ
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Price info banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700">
        ລາຄາຕໍ່ໜ່ວຍກິດ: <strong>{{ fmtNum($pricePerCredit) }} ກີບ / ໜ່ວຍ</strong>
        @if ($plan->status === 'DRAFT')— ປ່ຽນໄດ້ໂດຍກົດ "ຕັ້ງຄ່າ"@endif
    </div>

    @if ($plan->status === 'APPROVED')
    <div class="bg-green-50 border border-green-300 rounded-lg px-4 py-3 text-sm text-green-800 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        ແຜນນີ້ຖືກ<strong class="mx-1">ອະນຸມັດ</strong>ແລ້ວ — ຕາຕະລາງຢູ່ໃນໂໝດອ່ານຢ່າງດຽວ. ກົດ "ຍ້ອນກັບຮ່າງ" ເພື່ອແກ້ໄຂ.
    </div>
    @endif

    {{-- ─── Save-All Form wrapping all 4 sections ─── --}}
    <form id="saveAllForm" method="POST"
        action="{{ route('head_of_finance.academic_income.save_all', $plan) }}">
        @csrf

        @foreach (['1.1','1.2','1.3','1.4'] as $code)
        @php
            $codeId  = str_replace('.', '_', $code);
            $items   = $sections[$code];
            $credit  = $isCredit($code);
            $secTotal = 0; $secNuol = 0; $secKawt = 0;
            foreach ($items as $it) {
                $isMst = $credit && $it->student_year === 'masters_phd';
                $r  = $credit ? ($isMst ? (float)$it->rate_per_person : (float)$it->num_credits * $pricePerCredit)
                              : (float)$it->rate_per_person;
                $t  = $r * $it->num_persons;
                $secTotal += $t;
                $secNuol  += $t * $it->nuol_percentage;
                $secKawt  += $t * (1 - $it->nuol_percentage);
            }
        @endphp

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-slate-700 text-white text-sm font-semibold">
                {{ $sectionTitles[$code] }}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs" id="sec_table_{{ $codeId }}">
                    <thead class="bg-slate-100 text-gray-600 uppercase">
                        <tr>
                            <th class="px-3 py-2 text-center w-8">#</th>
                            <th class="px-3 py-2 text-left">ລາຍການ / ຫຼັກສູດ</th>
                            @if ($credit)
                                <th class="px-3 py-2 text-center">ປະເພດ</th>
                            @endif
                            <th class="px-3 py-2 text-right">ນ/ສ</th>
                            @if ($credit)
                                <th class="px-3 py-2 text-right">ໜ່ວຍກິດ</th>
                            @endif
                            <th class="px-3 py-2 text-right">ອັດຕາ/ຄົນ</th>
                            <th class="px-3 py-2 text-right">ລາຍຮັບລວມ</th>
                            <th class="px-3 py-2 text-center">% ມຊ</th>
                            <th class="px-3 py-2 text-right">ພັນທະ ມຊ</th>
                            <th class="px-3 py-2 text-right">ລາຍຮັບ ຄວທ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $item)
                        @php
                            $isMasters = $credit && $item->student_year === 'masters_phd';
                            $rate  = $credit ? ($isMasters ? (float)$item->rate_per_person : (float)$item->num_credits * $pricePerCredit)
                                            : (float)$item->rate_per_person;
                            $total = $rate * $item->num_persons;
                            $nuol  = $total * $item->nuol_percentage;
                            $kawt  = $total * (1 - $item->nuol_percentage);
                        @endphp
                        <tr class="hover:bg-gray-50"
                            data-id="{{ $item->id }}"
                            data-credit="{{ $credit ? 1 : 0 }}"
                            data-masters="{{ $isMasters ? 1 : 0 }}"
                            data-section="{{ $codeId }}">
                            <td class="px-3 py-1.5 text-center text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-1.5 text-gray-800">{{ $item->item_name }}</td>
                            @if ($credit)
                                <td class="px-3 py-1.5 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-xs
                                        {{ $isMasters ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $yearLabels[$item->student_year] ?? $item->student_year }}
                                    </span>
                                </td>
                            @endif

                            {{-- ນ/ສ input --}}
                            <td class="px-2 py-1.5 text-right">
                                <input type="number" min="0"
                                    name="items[{{ $item->id }}][num_persons]"
                                    value="{{ $item->num_persons }}"
                                    oninput="calcRow({{ $item->id }})"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-blue-400">
                            </td>

                            @if ($credit)
                                {{-- ໜ່ວຍກິດ: input for bachelor, em-dash for masters --}}
                                <td class="px-2 py-1.5 text-right">
                                    @if (!$isMasters)
                                        <input type="number" min="0"
                                            name="items[{{ $item->id }}][num_credits]"
                                            value="{{ $item->num_credits }}"
                                            oninput="calcRow({{ $item->id }})"
                                            class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-blue-400">
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                {{-- ອັດຕາ/ຄົນ: computed for bachelor, input for masters --}}
                                <td class="px-2 py-1.5 text-right text-gray-600"
                                    @if (!$isMasters) id="row_rate_{{ $item->id }}" data-v="{{ $rate }}" @endif>
                                    @if (!$isMasters)
                                        {{ fmtNum($rate) }}
                                    @else
                                        <input type="number" min="0"
                                            name="items[{{ $item->id }}][rate_per_person]"
                                            value="{{ $item->rate_per_person }}"
                                            oninput="calcRow({{ $item->id }})"
                                            class="w-28 px-2 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-blue-400">
                                    @endif
                                </td>
                            @else
                                {{-- Registration: ອັດຕາ/ຄົນ input --}}
                                <td class="px-2 py-1.5 text-right">
                                    <input type="number" min="0"
                                        name="items[{{ $item->id }}][rate_per_person]"
                                        value="{{ $item->rate_per_person }}"
                                        oninput="calcRow({{ $item->id }})"
                                        class="w-28 px-2 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-blue-400">
                                </td>
                            @endif

                            <td class="px-3 py-1.5 text-right font-medium"
                                id="row_total_{{ $item->id }}" data-v="{{ $total }}">{{ fmtNum($total) }}</td>

                            <td class="px-2 py-1.5 text-center">
                                <input type="number" min="0" max="1" step="0.01"
                                    name="items[{{ $item->id }}][nuol_percentage]"
                                    value="{{ $item->nuol_percentage }}"
                                    oninput="calcRow({{ $item->id }})"
                                    class="w-16 px-2 py-1 border border-gray-300 rounded text-center text-xs focus:ring-1 focus:ring-blue-400">
                            </td>

                            <td class="px-3 py-1.5 text-right text-orange-600"
                                id="row_nuol_{{ $item->id }}" data-v="{{ $nuol }}">{{ fmtNum($nuol) }}</td>
                            <td class="px-3 py-1.5 text-right text-green-700 font-semibold"
                                id="row_kawt_{{ $item->id }}" data-v="{{ $kawt }}">{{ fmtNum($kawt) }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $credit ? 10 : 8 }}"
                                    class="px-4 py-6 text-center text-gray-400 italic text-xs">
                                    ຍັງບໍ່ມີລາຍການ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($items->count() > 0)
                    <tfoot>
                        <tr class="bg-slate-50 font-semibold text-xs">
                            <td colspan="{{ $credit ? 6 : 4 }}" class="px-3 py-2 text-right text-gray-700">ລວມ</td>
                            <td class="px-3 py-2 text-right"
                                id="sec_total_{{ $codeId }}" data-v="{{ $secTotal }}">{{ fmtNum($secTotal) }}</td>
                            <td class="px-3 py-2"></td>
                            <td class="px-3 py-2 text-right text-orange-600"
                                id="sec_nuol_{{ $codeId }}" data-v="{{ $secNuol }}">{{ fmtNum($secNuol) }}</td>
                            <td class="px-3 py-2 text-right text-green-700"
                                id="sec_kawt_{{ $codeId }}" data-v="{{ $secKawt }}">{{ fmtNum($secKawt) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @endforeach
    </form>

</div>{{-- end space-y / pb-24 --}}


{{-- ─── Floating Save Button (DRAFT only) ─── --}}
@if ($plan->status === 'DRAFT')
<div class="fixed bottom-6 right-6 z-50">
    <button type="submit" form="saveAllForm"
        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:bg-blue-700 active:scale-95 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 13l4 4L19 7" />
        </svg>
        ບັນທຶກ
    </button>
</div>
@endif


{{-- ─── Approve Modal ─── --}}
<div id="approveModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:420px;">
        <div class="modal-body" style="text-align:center; padding:28px 24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg style="width:24px;height:24px;color:#16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 style="font-size:var(--font-size-lg);font-weight:600;color:var(--color-text-primary);margin-bottom:8px;">ຢືນຢັນການອະນຸມັດ</h3>
            <p style="font-size:var(--font-size-base);color:var(--color-text-secondary);margin-bottom:4px;">ອະນຸມັດແຜນລາຍຮັບວິຊາການ ສົກ <strong>{{ $plan->fiscal_year }}</strong>?</p>
            <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);">ຫຼັງຈາກອະນຸມັດ ຕາຕະລາງຈະຖືກລັອກ (ບໍ່ສາມາດແກ້ໄຂໄດ້)</p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="document.getElementById('approveModal').style.display='none'" class="btn btn-secondary">ຍົກເລີກ</button>
            <form method="POST" action="{{ route('head_of_finance.academic_income.approve', $plan) }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-primary" style="background:#16a34a;">ອະນຸມັດ</button>
            </form>
        </div>
    </div>
</div>

{{-- ─── Settings Modal ─── --}}
<div id="settingsModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:460px;">
        <div class="modal-header" style="padding:20px 24px 16px;">
            <h3 style="font-size:var(--font-size-lg);font-weight:600;color:var(--color-text-primary);">ຕັ້ງຄ່າລາຄາ</h3>
        </div>
        <form action="{{ route('head_of_finance.academic_income.settings') }}" method="POST">
            @csrf
            <div class="modal-body" style="padding:0 24px 20px; display:flex; flex-direction:column; gap:16px;">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ລາຄາຕໍ່ໜ່ວຍກິດ (ກີບ) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="price_per_credit"
                        value="{{ \App\Models\AppSetting::get('price_per_credit', 35000) }}"
                        min="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">ໃຊ້ຄຳນວນ: ອັດຕາ/ຄົນ = ໜ່ວຍກິດ × ລາຄານີ້</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ອັດຕາຄ່າສອນ ປ.ຕີ (0.00 – 1.00) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="teaching_rate_bachelor"
                        value="{{ \App\Models\AppSetting::get('teaching_rate_bachelor', 0.40) }}"
                        min="0" max="1" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">ຕົວຢ່າງ: 0.40 = 40% ຂອງ ລາຍຮັບ ຄວທ ຖືກຈ່າຍເປັນຄ່າສອນ</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ອັດຕາຄ່າສອນ ປ.ໂທ / ເອກ (0.00 – 1.00) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="teaching_rate_masters_phd"
                        value="{{ \App\Models\AppSetting::get('teaching_rate_masters_phd', 0.60) }}"
                        min="0" max="1" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('settingsModal').style.display='none'"
                    class="btn btn-secondary">ຍົກເລີກ</button>
                <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Delete Plan Modal ─── --}}
<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="text-align:center; padding:28px 24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--color-danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg style="width:24px;height:24px;color:#DC2626" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 style="font-size:var(--font-size-lg);font-weight:600;margin-bottom:8px;">ຢືນຢັນການລຶບ</h3>
            <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);margin-bottom:8px;">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລຶບແຜນນີ້?</p>
            <span id="deleteItemName" style="display:inline-block;padding:4px 12px;background:var(--color-danger-bg);color:var(--color-danger-text);font-size:var(--font-size-sm);font-weight:500;border-radius:var(--radius-md);border:1px solid var(--color-danger-border);"></span>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">ຍົກເລີກ</button>
            <form id="deleteForm" method="POST" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">ຢືນຢັນລຶບ</button>
            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
const PPC = {{ (int)$pricePerCredit }};

function fmtJS(n) {
    if (isNaN(n) || n === 0) return '0';
    return new Intl.NumberFormat().format(Math.round(n));
}

function calcRow(id) {
    const tr = document.querySelector(`tr[data-id="${id}"]`);
    if (!tr) return;
    const isCredit  = tr.dataset.credit  === '1';
    const isMasters = tr.dataset.masters === '1';
    const section   = tr.dataset.section;

    const numPersons = parseFloat(tr.querySelector(`[name="items[${id}][num_persons]"]`)?.value) || 0;
    const nuolPct    = parseFloat(tr.querySelector(`[name="items[${id}][nuol_percentage]"]`)?.value) || 0;

    let rate = 0;
    if (isCredit && !isMasters) {
        const numCredits = parseFloat(tr.querySelector(`[name="items[${id}][num_credits]"]`)?.value) || 0;
        rate = numCredits * PPC;
        const rateCell = document.getElementById(`row_rate_${id}`);
        if (rateCell) { rateCell.textContent = fmtJS(rate); rateCell.dataset.v = rate; }
    } else {
        rate = parseFloat(tr.querySelector(`[name="items[${id}][rate_per_person]"]`)?.value) || 0;
    }

    const total = rate * numPersons;
    const nuol  = total * nuolPct;
    const kawt  = total * (1 - nuolPct);

    const totalCell = document.getElementById(`row_total_${id}`);
    const nuolCell  = document.getElementById(`row_nuol_${id}`);
    const kawtCell  = document.getElementById(`row_kawt_${id}`);
    if (totalCell) { totalCell.textContent = fmtJS(total); totalCell.dataset.v = total; }
    if (nuolCell)  { nuolCell.textContent  = fmtJS(nuol);  nuolCell.dataset.v  = nuol; }
    if (kawtCell)  { kawtCell.textContent  = fmtJS(kawt);  kawtCell.dataset.v  = kawt; }

    calcSectionTotals(section);
}

function calcSectionTotals(sectionId) {
    let total = 0, nuol = 0, kawt = 0;
    document.querySelectorAll(`tr[data-section="${sectionId}"]`).forEach(tr => {
        const id = tr.dataset.id;
        total += parseFloat(document.getElementById(`row_total_${id}`)?.dataset.v || 0);
        nuol  += parseFloat(document.getElementById(`row_nuol_${id}`)?.dataset.v  || 0);
        kawt  += parseFloat(document.getElementById(`row_kawt_${id}`)?.dataset.v  || 0);
    });
    const totalCell = document.getElementById(`sec_total_${sectionId}`);
    const nuolCell  = document.getElementById(`sec_nuol_${sectionId}`);
    const kawtCell  = document.getElementById(`sec_kawt_${sectionId}`);
    if (totalCell) { totalCell.textContent = fmtJS(total); totalCell.dataset.v = total; }
    if (nuolCell)  { nuolCell.textContent  = fmtJS(nuol);  nuolCell.dataset.v  = nuol; }
    if (kawtCell)  { kawtCell.textContent  = fmtJS(kawt);  kawtCell.dataset.v  = kawt; }
}

function openDeleteModal(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

@if ($plan->status === 'APPROVED')
document.querySelectorAll('#saveAllForm input').forEach(el => {
    el.disabled = true;
    el.classList.add('bg-gray-50', 'cursor-not-allowed');
});
@endif

function openApproveModal() {
    document.getElementById('approveModal').style.display = 'flex';
}

['settingsModal','deleteModal','approveModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>
@endpush
@endsection
