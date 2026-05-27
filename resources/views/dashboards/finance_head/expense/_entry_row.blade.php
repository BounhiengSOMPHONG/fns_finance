{{-- One flat expense entry row. Vars: $e (ExpenseEntry|null), $i (int index), $refCodes --}}
@php
    $isNew = ! $e;
    $acctCode = $e?->chartOfAccount?->account_code ?? '';
@endphp
<tr class="grid-row {{ $isNew ? 'row-new' : '' }}" data-item-id="{{ $e?->id }}">
    <td class="row-num" style="text-align:center;font-size:0.7rem;">{{ $isNew ? '+' : $i + 1 }}</td>
    <td><input class="gi gi-date" type="date" value="{{ $e && $e->entry_date ? $e->entry_date->format('Y-m-d') : '' }}"></td>
    <td>
        <select class="gi gi-ref">
            <option value="">—</option>
            @foreach($refCodes as $rc)
                <option value="{{ $rc->code }}" @selected($e && $e->ref_code === $rc->code)>{{ $rc->code }}{{ $rc->label ? ' · '.$rc->label : '' }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input class="gi gi-acct" type="text" list="coa-codes" value="{{ $acctCode }}" placeholder="ລະຫັດ">
        <input class="gi-acctid" type="hidden" value="{{ $e?->chart_of_account_id }}">
    </td>
    <td><input class="gi gi-maincat" type="text" value="{{ $e?->main_cat }}" placeholder="ໝວດຫຼັກ"></td>
    <td><input class="gi gi-mainitem" type="text" value="{{ $e?->main_item }}" placeholder="ລາຍການຫຼັກ"></td>
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
