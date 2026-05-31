{{-- One detail row inside an item-group. Category comes from the group; account is per row. Vars: $e (ExpenseEntry|null) --}}
<tr class="grid-row" data-item-id="{{ $e?->id }}">
    <td>
        <input class="gi gi-acct" type="text" list="coa-codes" value="{{ $e?->chartOfAccount?->account_code }}" placeholder="ລະຫັດ...">
        <input class="gi-acctid" type="hidden" value="{{ $e?->chart_of_account_id }}">
    </td>
    <td><input class="gi gi-sub" type="text" value="{{ $e?->sub_item }}" placeholder="ລາຍການຍ່ອຍ..."></td>
    <td><input class="gi gi-r1" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->rate1 : 0 }}"></td>
    <td><input class="gi gi-r2" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->rate2 : 0 }}"></td>
    <td><input class="gi gi-qty" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->qty : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-period" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->period : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-freq" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->frequency : 1 }}" style="text-align:center;"></td>
    <td><input class="gi gi-addon" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->add_on : 0 }}"></td>
    <td class="cell-total" style="text-align:right;">{{ $e ? number_format($e->total, 0) : 0 }}</td>
    <td><input class="gi gi-note" type="text" value="{{ $e?->note }}" placeholder="ໝາຍເຫດ..."></td>
    <td style="text-align:center;">
        <button class="btn-del-row" type="button" title="ລຶບລາຍການ" aria-label="ລຶບລາຍການ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/></svg>
        </button>
    </td>
</tr>
