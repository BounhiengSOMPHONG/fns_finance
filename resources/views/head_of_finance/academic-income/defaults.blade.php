@extends('layouts.admin')

@section('title', 'ຈັດການ Default ລາຍຮັບວິຊາການ')
@section('page-title', 'ຈັດການ Default ລາຍຮັບວິຊາການ')

@section('content')
<div class="space-y-4">

    {{-- Top bar --}}
    <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('head_of_finance.academic_income.index') }}"
                class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-bold text-gray-900">ຈັດການ Default ລາຍການ</h2>
                <p class="text-xs text-gray-500 mt-0.5">ລາຍການເຫຼົ່ານີ້ຈະຖືກໂຫຼດອັດຕະໂນມັດເມື່ອສ້າງແຜນໃໝ່ — ລາກແຖວເພື່ອຈັດລຳດັບ</p>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="reorder-toast" class="hidden fixed top-4 right-4 z-50 px-4 py-2 bg-green-600 text-white text-sm rounded-lg shadow-lg transition-opacity">
        ບັນທຶກລຳດັບສຳເລັດ
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

    @foreach (['1.1','1.2','1.3','1.4'] as $code)
    @php
        $codeId   = str_replace('.', '_', $code);
        $items    = $grouped[$code] ?? collect();
        $isCredit = in_array($code, ['1.1', '1.3']);
    @endphp

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-slate-700 text-white text-sm font-semibold">
            {{ $sectionTitles[$code] }}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-100 text-gray-600 uppercase">
                    <tr>
                        <th class="px-2 py-2 w-8"></th>{{-- drag handle --}}
                        <th class="px-3 py-2 text-center w-8">#</th>
                        <th class="px-3 py-2 text-left">ລາຍການ / ຫຼັກສູດ</th>
                        @if ($isCredit)
                            <th class="px-3 py-2 text-center">ປະເພດ</th>
                            <th class="px-3 py-2 text-right">ໜ່ວຍກິດ</th>
                        @endif
                        <th class="px-3 py-2 text-center">% ມຊ</th>
                        <th class="px-3 py-2 text-center w-16">ລຶບ</th>
                    </tr>
                </thead>
                <tbody id="sortable_{{ $codeId }}"
                    class="divide-y divide-gray-100"
                    data-reorder-url="{{ route('head_of_finance.academic_income.defaults.reorder') }}"
                    data-csrf="{{ csrf_token() }}">
                    @forelse ($items as $item)
                    <tr class="hover:bg-gray-50 select-none" data-id="{{ $item->id }}">
                        <td class="px-2 py-2 text-center">
                            <span class="drag-handle cursor-grab text-gray-300 hover:text-gray-500 active:cursor-grabbing">
                                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="9"  cy="6"  r="1.5"/><circle cx="15" cy="6"  r="1.5"/>
                                    <circle cx="9"  cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                    <circle cx="9"  cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                                </svg>
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-gray-400 row-num">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 text-gray-800">{{ $item->item_name }}</td>
                        @if ($isCredit)
                            <td class="px-3 py-2 text-center">
                                @if ($item->student_year)
                                    <span class="px-1.5 py-0.5 rounded text-xs
                                        {{ $item->student_year === 'masters_phd' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $yearLabels[$item->student_year] ?? $item->student_year }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">
                                {{ $item->num_credits !== null ? $item->num_credits : '—' }}
                            </td>
                        @endif
                        <td class="px-3 py-2 text-center text-gray-600">
                            {{ ($item->nuol_percentage * 100) }}%
                        </td>
                        <td class="px-3 py-2 text-center">
                            <form method="POST"
                                action="{{ route('head_of_finance.academic_income.defaults.destroy', $item) }}"
                                onsubmit="return confirm('ຢືນຢັນລຶບ?')"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="{{ $isCredit ? 7 : 5 }}"
                                class="px-4 py-4 text-center text-gray-400 italic text-xs">
                                ຍັງບໍ່ມີ default ລາຍການ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add new default --}}
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50">
            <form method="POST" action="{{ route('head_of_finance.academic_income.defaults.store') }}"
                class="flex flex-wrap gap-2 items-end">
                @csrf
                <input type="hidden" name="section_code" value="{{ $code }}">

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500">ລາຍການ / ຫຼັກສູດ *</label>
                    <input type="text" name="item_name" required placeholder="ຊື່ລາຍການ"
                        class="px-2 py-1.5 border border-gray-300 rounded text-xs w-56 focus:ring-1 focus:ring-blue-400">
                </div>

                @if ($isCredit)
                    <div class="flex flex-col gap-1" id="credits_wrap_{{ $codeId }}">
                        <label class="text-xs text-gray-500">ໜ່ວຍກິດ</label>
                        <input type="number" name="num_credits" min="0" placeholder="0"
                            id="credits_input_{{ $codeId }}"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500">ປະເພດ *</label>
                        <select name="student_year" required
                            onchange="toggleCreditsField('{{ $codeId }}', this.value)"
                            class="px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-400">
                            @foreach ($yearLabels as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500">% ມຊ (0–1)</label>
                    <input type="number" name="nuol_percentage" min="0" max="1" step="0.01"
                        placeholder="{{ $isCredit ? '0.17' : '0.25' }}"
                        value="{{ $isCredit ? '0.17' : '0.25' }}"
                        class="px-2 py-1.5 border border-gray-300 rounded text-xs w-20 focus:ring-1 focus:ring-blue-400">
                </div>

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

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
function toggleCreditsField(codeId, yearValue) {
    const wrap = document.getElementById('credits_wrap_' + codeId);
    const inp  = document.getElementById('credits_input_' + codeId);
    if (!wrap) return;
    if (yearValue === 'masters_phd') {
        wrap.style.opacity = '0.4';
        wrap.style.pointerEvents = 'none';
        if (inp) inp.value = '';
    } else {
        wrap.style.opacity = '1';
        wrap.style.pointerEvents = '';
    }
}

let toastTimer;
function showToast(msg, ok = true) {
    const t = document.getElementById('reorder-toast');
    t.textContent = msg;
    t.className = `fixed top-4 right-4 z-50 px-4 py-2 text-white text-sm rounded-lg shadow-lg transition-opacity ${ok ? 'bg-green-600' : 'bg-red-500'}`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.className += ' opacity-0'; }, 2000);
}

function renumberRows(tbody) {
    let i = 1;
    tbody.querySelectorAll('tr:not(.empty-row) .row-num').forEach(cell => {
        cell.textContent = i++;
    });
}

document.querySelectorAll('[id^="sortable_"]').forEach(tbody => {
    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'bg-blue-50',
        onEnd() {
            renumberRows(tbody);
            const ids = [...tbody.querySelectorAll('tr[data-id]')].map(tr => tr.dataset.id);
            fetch(tbody.dataset.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tbody.dataset.csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items: ids }),
            })
            .then(r => r.ok ? showToast('ບັນທຶກລຳດັບສຳເລັດ') : showToast('ເກີດຂໍ້ຜິດພາດ', false))
            .catch(() => showToast('ເກີດຂໍ້ຜິດພາດ', false));
        },
    });
});
</script>
@endpush
@endsection
