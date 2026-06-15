{{-- One salary entry row. Vars: $e (SalaryEntry|null), $account (array|null) --}}
@php
    $rowAccountId = $e?->chart_of_account_id ?? data_get($account ?? null, 'id');
    $rowAccountCode = $e?->chartOfAccount?->account_code ?? data_get($account ?? null, 'code');
    $rowAccountName = $e?->chartOfAccount?->account_name ?? data_get($account ?? null, 'name');
    $rowTopicName = data_get($account ?? null, 'topic_name');
    $rowGroupCode = data_get($account ?? null, 'group_code');
    $rowGroupKey = $rowGroupCode ? 'coa-' . $rowGroupCode : 'coa-other';
    $isChildAccount = filled(data_get($account ?? null, 'topic_code'))
        && data_get($account ?? null, 'topic_code') !== $rowGroupCode;
    $rowPaymentType = old('payment_type', $e?->payment_type ?? 'transfer');
    $hasAccount = filled($rowAccountId);
@endphp
<tr class="smg-row is-collapsed {{ $isChildAccount ? 'smg-row-child' : '' }}"
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
              title="{{ trim(($rowTopicName ? $rowTopicName . ' / ' : '') . ($rowAccountName ?? '')) }}">
            {{ $rowAccountName ?? 'ເລືອກລະຫັດບັນຊີ...' }}
        </span>
    </td>
    <td class="smg-cell-center smg-editable-cell">
        <input class="smg-input smg-persons" type="number" min="0" step="1"
               value="{{ $e ? (int) $e->person_count : 0 }}" style="text-align:center;">
    </td>
    <td class="smg-editable-cell">
        <select class="smg-input smg-payment-type">
            <option value="transfer" @selected($rowPaymentType === 'transfer')>ໂອນເຂົ້າບັນຊີ</option>
            <option value="cash" @selected($rowPaymentType === 'cash')>ເງິນສົດ</option>
        </select>
    </td>
    <td class="smg-editable-cell"><input class="smg-input smg-amount smg-money-input" type="text" inputmode="numeric" value="{{ number_format($e ? (float) $e->amount : 0, 0) }}"></td>
</tr>
