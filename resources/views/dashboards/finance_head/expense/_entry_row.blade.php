{{-- One flat expense entry row. Vars: $e (ExpenseEntry|null), $i (int index), $refCodes --}}
@php
    $isNew    = ! $e;
    $acctCode = $e?->chartOfAccount?->account_code ?? '';
    // Level-1 = codes with one dot (2.1); level-2 = two dots (2.1.1).
    $level1   = $refCodes->filter(fn ($rc) => substr_count($rc->code, '.') === 1)->values();
    $selItem  = $e && $e->main_item_code ? $refCodes->firstWhere('code', $e->main_item_code) : null;
@endphp
<tr class="grid-row {{ $isNew ? 'row-new' : '' }}" data-item-id="{{ $e?->id }}">
    <td class="row-num" style="text-align:center;font-size:0.7rem;">{{ $isNew ? '+' : $i + 1 }}</td>
    {{-- ໝວດຫຼັກ (Level 1) --}}
    <td>
        <select class="gi gi-maincat-code">
            <option value="">—</option>
            @foreach($level1 as $rc)
                <option value="{{ $rc->code }}" data-label="{{ $rc->label }}" @selected($e && $e->main_cat_code === $rc->code)>{{ $rc->code }}{{ $rc->label ? ' · '.$rc->label : '' }}</option>
            @endforeach
        </select>
    </td>
    {{-- ລາຍການຫຼັກ (Level 2 — options filled by JS based on the chosen Level 1) --}}
    <td>
        <select class="gi gi-mainitem-code">
            <option value="">—</option>
            @if($selItem)
                <option value="{{ $selItem->code }}" data-label="{{ $selItem->label }}" data-acct="{{ $selItem->account_code }}" selected>{{ $selItem->code }}{{ $selItem->label ? ' · '.$selItem->label : '' }}</option>
            @endif
        </select>
    </td>
    {{-- ລະຫັດບັນຊີ (auto-filled from the Level 2 ref code, still editable) --}}
    <td>
        <input class="gi gi-acct" type="text" list="coa-codes" value="{{ $acctCode }}" placeholder="ລະຫັດ">
        <input class="gi-acctid" type="hidden" value="{{ $e?->chart_of_account_id }}">
    </td>
    <td><input class="gi gi-sub" type="text" value="{{ $e?->sub_item }}" placeholder="ລາຍການຍ່ອຍ..."></td>
    <td><input class="gi gi-r1" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->rate1 : 0 }}"></td>
    <td><input class="gi gi-r2" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->rate2 : 0 }}"></td>
    <td><input class="gi gi-qty" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->qty : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-period" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->period : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-freq" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->frequency : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-addon" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->add_on : 0 }}"></td>
    <td class="cell-total" style="text-align:right;font-weight:600;font-size:0.78rem;">{{ $e ? number_format($e->total, 0) : 0 }}</td>
    <td><input class="gi gi-note" type="text" value="{{ $e?->note }}" placeholder="ໝາຍເຫດ"></td>
    <td style="text-align:center;">
        <button class="btn-del-row" type="button" title="ລຶບ" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;padding:2px 4px;">✕</button>
    </td>
</tr>
