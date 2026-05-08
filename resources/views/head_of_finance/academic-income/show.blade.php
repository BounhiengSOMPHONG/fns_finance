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
<div class="space-y-4">

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
            <button type="button" onclick="document.getElementById('settingsModal').style.display='flex'"
                class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                ຕັ້ງຄ່າ
            </button>
            <button type="button" onclick="document.getElementById('loadDefaultsModal').style.display='flex'"
                class="inline-flex items-center px-3 py-2 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-100 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                ໂຫຼດຄ່າເລີ່ມຕົ້ນ
            </button>
            <a href="{{ route('head_of_finance.academic_income.summary', $plan) }}"
                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                ເບິ່ງສັງລວມ
            </a>
            <button type="button"
                onclick="openDeleteModal('{{ route('head_of_finance.academic_income.destroy', $plan) }}', 'ສົກ {{ $plan->fiscal_year }}')"
                class="inline-flex items-center px-3 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                ລຶບ
            </button>
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
        — ປ່ຽນໄດ້ໂດຍກົດ "ຕັ້ງຄ່າ"
    </div>

    {{-- ─── 4 Sections ─── --}}
    @foreach (['1.1','1.2','1.3','1.4'] as $code)
    @php
        $items   = $sections[$code];
        $credit  = $isCredit($code);
        // section totals
        $secTotal       = 0; $secNuol = 0; $secKawt = 0;
        foreach ($items as $it) {
            $rate = $credit ? ($it->num_credits * $pricePerCredit) : (float)$it->rate_per_person;
            $tot  = $rate * $it->num_persons;
            $secTotal += $tot;
            $secNuol  += $tot * $it->nuol_percentage;
            $secKawt  += $tot * (1 - $it->nuol_percentage);
        }
    @endphp
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-slate-700 text-white text-sm font-semibold">
            {{ $sectionTitles[$code] }}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-100 text-gray-600 uppercase">
                    <tr>
                        <th class="px-3 py-2 text-center w-8">#</th>
                        <th class="px-3 py-2 text-left">ລາຍການ / ຫຼັກສູດ</th>
                        @if ($credit)
                            <th class="px-3 py-2 text-right">ນ/ສ</th>
                            <th class="px-3 py-2 text-right">ໜ່ວຍກິດ</th>
                            <th class="px-3 py-2 text-center">ປະເພດ</th>
                            <th class="px-3 py-2 text-right">ອັດຕາ/ຄົນ</th>
                        @else
                            <th class="px-3 py-2 text-right">ອັດຕາ/ຄົນ</th>
                            <th class="px-3 py-2 text-right">ນ/ສ</th>
                        @endif
                        <th class="px-3 py-2 text-right">ລາຍຮັບລວມ</th>
                        <th class="px-3 py-2 text-center">% ມຊ</th>
                        <th class="px-3 py-2 text-right">ພັນທະ ມຊ</th>
                        <th class="px-3 py-2 text-right">ລາຍຮັບ ຄວທ</th>
                        <th class="px-3 py-2 text-center w-20">ດຳເນີນງານ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                    @php
                        $rate = $credit
                            ? ($item->num_credits * $pricePerCredit)
                            : (float)$item->rate_per_person;
                        $total = $rate * $item->num_persons;
                        $nuol  = $total * $item->nuol_percentage;
                        $kawt  = $total * (1 - $item->nuol_percentage);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-center text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 text-gray-800">{{ $item->item_name }}</td>
                        @if ($credit)
                            <td class="px-3 py-2 text-right">{{ fmtNum($item->num_persons) }}</td>
                            <td class="px-3 py-2 text-right">{{ fmtNum($item->num_credits) }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-1.5 py-0.5 rounded text-xs
                                    {{ $item->student_year === 'masters_phd' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $yearLabels[$item->student_year] ?? $item->student_year }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ fmtNum($rate) }}</td>
                        @else
                            <td class="px-3 py-2 text-right text-gray-600">{{ fmtNum($item->rate_per_person) }}</td>
                            <td class="px-3 py-2 text-right">{{ fmtNum($item->num_persons) }}</td>
                        @endif
                        <td class="px-3 py-2 text-right font-medium">{{ fmtNum($total) }}</td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ ($item->nuol_percentage * 100) }}%</td>
                        <td class="px-3 py-2 text-right text-orange-600">{{ fmtNum($nuol) }}</td>
                        <td class="px-3 py-2 text-right text-green-700 font-semibold">{{ fmtNum($kawt) }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    onclick="openEditModal({{ json_encode($item) }}, '{{ $code }}')"
                                    class="text-yellow-600 hover:text-yellow-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button"
                                    onclick="openDeleteItemModal('{{ route('head_of_finance.academic_income.items.destroy', [$plan, $item]) }}')"
                                    class="text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $credit ? 11 : 10 }}"
                                class="px-4 py-4 text-center text-gray-400 italic text-xs">
                                ຍັງບໍ່ມີລາຍການ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($items->count() > 0)
                <tfoot>
                    <tr class="bg-slate-50 font-semibold text-xs">
                        <td colspan="{{ $credit ? 5 : 3 }}" class="px-3 py-2 text-right text-gray-700">ລວມ</td>
                        <td class="px-3 py-2 text-right">{{ fmtNum($secTotal) }}</td>
                        <td class="px-3 py-2"></td>
                        <td class="px-3 py-2 text-right text-orange-600">{{ fmtNum($secNuol) }}</td>
                        <td class="px-3 py-2 text-right text-green-700">{{ fmtNum($secKawt) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Add item row --}}
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50">
            <form action="{{ route('head_of_finance.academic_income.items.store', $plan) }}" method="POST"
                class="flex flex-wrap gap-2 items-end">
                @csrf
                <input type="hidden" name="section_code" value="{{ $code }}">

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500">ລາຍການ / ຫຼັກສູດ *</label>
                    <input type="text" name="item_name" placeholder="ຊື່ລາຍການ"
                        class="px-2 py-1.5 border border-gray-300 rounded text-xs w-48 focus:ring-1 focus:ring-blue-400">
                </div>

                @if ($credit)
                @php $codeId = str_replace('.','_',$code); @endphp
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">ນ/ສ *</label>
                        <input type="number" name="num_persons" min="0" placeholder="0"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1" id="add_credits_{{ $codeId }}">
                        <label class="text-xs text-gray-500">ໜ່ວຍກິດ *</label>
                        <input type="number" name="num_credits" min="0" placeholder="0"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1" id="add_rate_{{ $codeId }}" style="display:none;">
                        <label class="text-xs text-gray-500">ອັດຕາ/ຄົນ (ກີບ) *</label>
                        <input type="number" name="rate_per_person" min="0" placeholder="0"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-28 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">ປະເພດ *</label>
                        <select name="student_year"
                            onchange="toggleAddCreditFields('{{ $codeId }}', this.value)"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-400">
                            @foreach ($yearLabels as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">% ມຊ *</label>
                        <input type="number" name="nuol_percentage" min="0" max="1" step="0.01"
                            placeholder="0.17"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                @else
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">ອັດຕາ/ຄົນ (ກີບ) *</label>
                        <input type="number" name="rate_per_person" min="0" placeholder="0"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-28 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">ນ/ສ *</label>
                        <input type="number" name="num_persons" min="0" placeholder="0"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">% ມຊ *</label>
                        <input type="number" name="nuol_percentage" min="0" max="1" step="0.01"
                            placeholder="0.25"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                @endif

                <div class="flex flex-col justify-end">
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                        + ເພີ່ມ
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</div>{{-- end space-y --}}


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

{{-- ─── Edit Item Modal ─── --}}
<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header" style="padding:20px 24px 16px;">
            <h3 style="font-size:var(--font-size-lg);font-weight:600;">ແກ້ໄຂລາຍການ</h3>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="padding:0 24px 20px; display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ລາຍການ / ຫຼັກສູດ</label>
                    <input type="text" name="item_name" id="edit_item_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div id="edit_credit_fields">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ຈຳນວນ ນ/ສ</label>
                            <input type="number" name="num_persons" id="edit_num_persons" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div id="edit_credits_div">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ໜ່ວຍກິດ</label>
                            <input type="number" name="num_credits" id="edit_num_credits" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div id="edit_rate_div">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ອັດຕາ/ຄົນ (ກີບ)</label>
                            <input type="number" name="rate_per_person" id="edit_rate_per_person" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div id="edit_year_div">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ປະເພດ ນ/ສ</label>
                        <select name="student_year" id="edit_student_year"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach ($yearLabels as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">% ມຊ (0.00 – 1.00)</label>
                        <input type="number" name="nuol_percentage" id="edit_nuol_percentage" min="0" max="1" step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                    class="btn btn-secondary">ຍົກເລີກ</button>
                <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Delete Item Modal ─── --}}
<div id="deleteItemModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:380px;">
        <div class="modal-body" style="text-align:center; padding:28px 24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--color-danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg style="width:24px;height:24px;color:#DC2626" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 style="font-size:var(--font-size-lg);font-weight:600;margin-bottom:8px;">ຢືນຢັນການລຶບ</h3>
            <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລຶບລາຍການນີ້?</p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="document.getElementById('deleteItemModal').style.display='none'"
                class="btn btn-secondary">ຍົກເລີກ</button>
            <form id="deleteItemForm" method="POST" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">ຢືນຢັນລຶບ</button>
            </form>
        </div>
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

{{-- ─── Load Defaults Modal ─── --}}
<div id="loadDefaultsModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:420px;">
        <div class="modal-body" style="text-align:center; padding:28px 24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg style="width:24px;height:24px;color:#4f46e5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <h3 style="font-size:var(--font-size-lg);font-weight:600;margin-bottom:8px;">ໂຫຼດຄ່າເລີ່ມຕົ້ນ</h3>
            <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);">
                ການດຳເນີນງານນີ້ຈະ<strong>ລຶບລາຍການທັງໝົດ</strong>ທີ່ມີຢູ່ ແລະ ໂຫຼດຂໍ້ມູນຈາກ Planning 2026.xls ແທນ. ທ່ານແນ່ໃຈບໍ່?
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="document.getElementById('loadDefaultsModal').style.display='none'"
                class="btn btn-secondary">ຍົກເລີກ</button>
            <form action="{{ route('head_of_finance.academic_income.load_defaults', $plan) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-primary">ໂຫຼດຄ່າເລີ່ມຕົ້ນ</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const YEAR_LABELS = @json($yearLabels);
let editSectionIsCredit = false;

function toggleAddCreditFields(codeId, yearValue) {
    const creditsDiv = document.getElementById('add_credits_' + codeId);
    const rateDiv    = document.getElementById('add_rate_' + codeId);
    if (yearValue === 'masters_phd') {
        creditsDiv.style.display = 'none';
        rateDiv.style.display    = '';
    } else {
        creditsDiv.style.display = '';
        rateDiv.style.display    = 'none';
    }
}

function openEditModal(item, sectionCode) {
    editSectionIsCredit = ['1.1', '1.3'].includes(sectionCode);
    const form = document.getElementById('editForm');

    form.action = `/head-of-finance/academic-income/{{ $plan->id }}/items/${item.id}`;

    document.getElementById('edit_item_name').value       = item.item_name  ?? '';
    document.getElementById('edit_num_persons').value     = item.num_persons ?? 0;
    document.getElementById('edit_nuol_percentage').value = item.nuol_percentage ?? 0.17;

    const creditsDiv = document.getElementById('edit_credits_div');
    const rateDiv    = document.getElementById('edit_rate_div');
    const yearDiv    = document.getElementById('edit_year_div');

    if (editSectionIsCredit) {
        document.getElementById('edit_student_year').value = item.student_year ?? '1';
        yearDiv.style.display = '';
        if (item.student_year === 'masters_phd') {
            document.getElementById('edit_rate_per_person').value = item.rate_per_person ?? 0;
            creditsDiv.style.display = 'none';
            rateDiv.style.display    = '';
        } else {
            document.getElementById('edit_num_credits').value = item.num_credits ?? 0;
            creditsDiv.style.display = '';
            rateDiv.style.display    = 'none';
        }
    } else {
        document.getElementById('edit_rate_per_person').value = item.rate_per_person ?? 0;
        creditsDiv.style.display = 'none';
        rateDiv.style.display    = '';
        yearDiv.style.display    = 'none';
    }

    document.getElementById('editModal').style.display = 'flex';
}

document.getElementById('edit_student_year').addEventListener('change', function() {
    if (!editSectionIsCredit) return;
    const creditsDiv = document.getElementById('edit_credits_div');
    const rateDiv    = document.getElementById('edit_rate_div');
    if (this.value === 'masters_phd') {
        creditsDiv.style.display = 'none';
        rateDiv.style.display    = '';
    } else {
        creditsDiv.style.display = '';
        rateDiv.style.display    = 'none';
    }
});

function openDeleteItemModal(url) {
    document.getElementById('deleteItemForm').action = url;
    document.getElementById('deleteItemModal').style.display = 'flex';
}

function openDeleteModal(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals on backdrop click
['settingsModal','editModal','deleteItemModal','deleteModal','loadDefaultsModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>
@endpush
@endsection
