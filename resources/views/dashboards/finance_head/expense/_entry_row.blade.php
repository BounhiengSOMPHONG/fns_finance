{{-- One detail row inside an item-group. Category comes from the group; account is per row. Vars: $e (ExpenseEntry|null) --}}
<tr class="grid-row" data-item-id="{{ $e?->id }}">
    <td>
        <input class="gi gi-acct" type="text" list="coa-codes" value="{{ $e?->chartOfAccount?->account_code }}" placeholder="ລະຫັດ">
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
