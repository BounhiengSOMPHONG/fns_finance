{{-- One salary entry row. Vars: $e (SalaryEntry|null), $account (array|null) --}}
@php
    $rowAccountId = $e?->chart_of_account_id ?? data_get($account ?? null, 'id');
    $rowAccountCode = $e?->chartOfAccount?->account_code ?? data_get($account ?? null, 'code');
    $rowAccountName = $e?->chartOfAccount?->account_name ?? data_get($account ?? null, 'name');
    $hasAccount = filled($rowAccountId);
@endphp
<tr class="smg-row"
    data-item-id="{{ $e?->id }}"
    data-coa-id="{{ $rowAccountId }}"
    data-default-row="{{ $account ? '1' : '0' }}">
    <td>
        <button type="button"
                class="smg-coa-trigger {{ $hasAccount ? '' : 'is-empty' }}"
                @if($account) disabled @endif>
            <span class="smg-coa-trigger-code">{{ $rowAccountCode ?? 'ເລືອກລະຫັດ' }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>
    </td>
    <td>
        <span class="smg-name {{ $hasAccount ? '' : 'smg-name-empty' }}"
              title="{{ $rowAccountName }}">
            {{ $rowAccountName ?? 'ເລືອກລະຫັດບັນຊີ...' }}
        </span>
    </td>
    <td class="smg-cell-center">
        <input class="smg-input smg-persons" type="number" min="0" step="1"
               value="{{ $e ? (int) $e->person_count : 0 }}" style="text-align:center;">
    </td>
    <td><input class="smg-input smg-atm"  type="number" min="0" step="0.01" value="{{ $e ? (float) $e->atm_amount  : 0 }}"></td>
    <td><input class="smg-input smg-cash" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->cash_amount : 0 }}"></td>
    <td><input class="smg-input smg-remark" type="text" value="{{ $e?->remark }}" placeholder="ໝາຍເຫດ..."></td>
</tr>
