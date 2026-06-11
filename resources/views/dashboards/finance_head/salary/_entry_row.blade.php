{{-- One salary entry row. Vars: $e (SalaryEntry|null), $account (array|null) --}}
@php
    $rowAccountId = $e?->chart_of_account_id ?? data_get($account ?? null, 'id');
    $rowAccountCode = $e?->chartOfAccount?->account_code ?? data_get($account ?? null, 'code');
    $rowAccountName = $e?->chartOfAccount?->account_name ?? data_get($account ?? null, 'name');
    $rowGroupCode = data_get($account ?? null, 'group_code');
    $rowGroupKey = $rowGroupCode ? 'coa-' . $rowGroupCode : 'coa-other';
    $hasAccount = filled($rowAccountId);
@endphp
<tr class="smg-row"
    data-item-id="{{ $e?->id }}"
    data-coa-id="{{ $rowAccountId }}"
    data-group="{{ $rowGroupKey }}"
    data-default-row="{{ $account ? '1' : '0' }}">
    <td>
        <span class="smg-coa-code {{ $hasAccount ? '' : 'is-empty' }}">
            <span class="smg-coa-trigger-code">{{ $rowAccountCode ?? 'ເລືອກລະຫັດ' }}</span>
        </span>
    </td>
    <td>
        <span class="smg-name {{ $hasAccount ? '' : 'smg-name-empty' }}"
              title="{{ $rowAccountName }}">
            {{ $rowAccountName ?? 'ເລືອກລະຫັດບັນຊີ...' }}
        </span>
    </td>
    <td class="smg-cell-center smg-editable-cell">
        <input class="smg-input smg-persons" type="number" min="0" step="1"
               value="{{ $e ? (int) $e->person_count : 0 }}" style="text-align:center;">
    </td>
    <td class="smg-editable-cell"><input class="smg-input smg-atm"  type="number" min="0" step="0.01" value="{{ $e ? (float) $e->atm_amount  : 0 }}"></td>
    <td class="smg-editable-cell"><input class="smg-input smg-cash" type="number" min="0" step="0.01" value="{{ $e ? (float) $e->cash_amount : 0 }}"></td>
    <td class="smg-editable-cell"><input class="smg-input smg-remark" type="text" value="{{ $e?->remark }}" placeholder="ພິມໝາຍເຫດ..."></td>
</tr>
