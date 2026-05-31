{{-- One salary entry row. Vars: $e (SalaryEntry|null) --}}
<tr class="smg-row" data-item-id="{{ $e?->id }}" data-coa-id="{{ $e?->chart_of_account_id }}">
    <td>
        <input class="smg-input smg-code" type="text" list="coa-codes"
               value="{{ $e?->chartOfAccount?->account_code }}" placeholder="ລະຫັດ...">
    </td>
    <td>
        <span class="smg-name {{ $e?->chartOfAccount ? '' : 'smg-name-empty' }}"
              title="{{ $e?->chartOfAccount?->account_name }}">
            {{ $e?->chartOfAccount?->account_name ?? 'ເລືອກລະຫັດບັນຊີ...' }}
        </span>
    </td>
    <td class="smg-cell-center">
        <input class="smg-input smg-persons" type="number" min="0" step="1"
               value="{{ $e ? (int) $e->person_count : 0 }}" style="text-align:center;">
    </td>
    <td><input class="smg-input smg-atm"  type="number" min="0" step="0.01" value="{{ $e ? (float) $e->atm_amount  : 0 }}"></td>
    <td><input class="smg-input smg-cash" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->cash_amount : 0 }}"></td>
    <td><input class="smg-input smg-remark" type="text" value="{{ $e?->remark }}" placeholder="ໝາຍເຫດ..."></td>
    <td style="text-align:center;">
        <button class="smg-btn-del" type="button" title="ລຶບລາຍການ" aria-label="ລຶບລາຍການ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/></svg>
        </button>
    </td>
</tr>
