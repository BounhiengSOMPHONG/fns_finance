@extends('layouts.admin')

@section('title', $periodTitle . ' - ' . $planningYear->year)
@section('page-title', $periodTitle)

@section('content')
@php
    $periodRows = collect($periodReport['rows'] ?? []);
    $periodTotals = $periodReport['totals'] ?? [
        'yearly_amount' => 0,
        'period_1_amount' => 0,
        'period_2_amount' => 0,
        'first_half_amount' => 0,
        'second_half_amount' => 0,
    ];
    $periodWarnings = $periodReport['warnings'] ?? ['unlinked_expenses' => [], 'reference_fallbacks' => []];
    $canEditPeriod = (bool) ($canEditPeriod ?? false);
    $money = fn ($amount) => number_format((float) $amount, 0);
    $inputValue = fn ($amount) => rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.');
    $saveUrlTemplate = route('head_of_finance.manage-plan.period-1-2.override', [
        'planningYear' => $planningYear,
        'accountCode' => '__ACCOUNT__',
    ]);
@endphp

<div class="period-page">
    <div class="period-toolbar">
        <div>
            <span class="period-eyebrow">ແຜນປີ {{ $planningYear->year }}</span>
            <h2>{{ $periodTitle }}</h2>
            <p>{{ $planningYear->name }}</p>
        </div>
        <div class="period-actions">
            <a href="{{ route('head_of_finance.manage-plan.index') }}" class="period-btn period-btn-secondary">ກັບຄືນ</a>
        </div>
    </div>

    @if($periodKey !== 'period-1-2')
        <section class="period-placeholder" id="{{ $periodKey }}">
            <h3>{{ $periodTitle }}</h3>
            <p>ໜ້ານີ້ຖືກແຍກອອກຈາກໜ້າ Preview ແລ້ວ. ລໍຖ້າກຳນົດວ່າຈະໃຫ້ໜ້ານີ້ເຮັດຫຍັງ.</p>
        </section>
    @else
        @if(! $canEditPeriod)
            <div class="period-lock-note">
                ຕ້ອງບັນທຶກແຜນກ່ອນ ຈຶ່ງຈະປ້ອນຍອດງວດໄດ້. ຖ້າແຜນຢູ່ໃນສະຖານະກວດສອບ ຈະສາມາດເບິ່ງຍອດໄດ້ເທົ່ານັ້ນ.
            </div>
        @endif

        @if(! empty($periodWarnings['unlinked_expenses']))
            <div class="period-warning">
                <strong>ມີລາຍຈ່າຍທີ່ຍັງບໍ່ໄດ້ຜູກບັນຊີ {{ count($periodWarnings['unlinked_expenses']) }} ລາຍການ</strong>
                <span>ກວດແກ້ທີ່ Expense Structure & Account Links ກ່ອນປັບຍອດງວດ.</span>
            </div>
        @endif

        <section class="period-paper" id="{{ $periodKey }}">
            <div class="period-official-header">
                <div>
                    <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                    <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
                </div>
                <div>
                    <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                    <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
                </div>
            </div>

            <div class="period-title-block">
                <p>ແຜນລາຍຈ່າຍງວດ 1-2 ງົບປະມານວິຊາການ ປີ {{ $planningYear->year }}</p>
            </div>

            <div class="period-table-wrap">
                <table class="period-report-table">
                    <colgroup>
                        <col class="period-code-col">
                        <col class="period-code-col">
                        <col class="period-code-col">
                        <col class="period-code-col">
                        <col class="period-title-col">
                        <col class="period-money-col">
                        <col class="period-input-col">
                        <col class="period-input-col">
                        <col class="period-money-col">
                        <col class="period-money-col">
                        <col class="period-status-col">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2">ພາກ</th>
                            <th rowspan="2">ພາກ<br>ສ່ວນ</th>
                            <th rowspan="2">ຮ່ວງ</th>
                            <th rowspan="2">ລູກ<br>ຮ່ວງ</th>
                            <th rowspan="2">ເນື້ອໃນລາຍຈ່າຍ</th>
                            <th rowspan="2">ງົບປີ</th>
                            <th colspan="2">ປັບຍອດງວດ</th>
                            <th rowspan="2">ລວມງວດ 1-2</th>
                            <th rowspan="2">ຍອດຄົງເຫຼືອ</th>
                            <th rowspan="2">ສະຖານະ</th>
                        </tr>
                        <tr>
                            <th>ງວດ 1</th>
                            <th>ງວດ 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periodRows as $row)
                            @php
                                $code = str_pad((string) $row['account_code'], 8, '0', STR_PAD_LEFT);
                                $codeParts = [
                                    substr($code, 0, 2),
                                    substr($code, 2, 2),
                                    substr($code, 4, 2),
                                    substr($code, 6, 2),
                                ];
                                $level = min((int) $row['level'], 3);
                                $rowClass = ((int) $row['level'] === 0 ? ' period-root-row' : '') . (! empty($row['is_group']) ? ' period-group-row' : '');
                                $isEditableRow = $canEditPeriod && empty($row['is_group']);
                            @endphp
                            <tr class="period-data-row{{ $rowClass }}"
                                data-period-row
                                data-account-code="{{ $row['account_code'] }}"
                                data-level="{{ (int) $row['level'] }}"
                                data-is-group="{{ ! empty($row['is_group']) ? '1' : '0' }}"
                                data-yearly-amount="{{ $inputValue($row['yearly_amount']) }}"
                                data-period-1-amount="{{ $inputValue($row['period_1_amount']) }}"
                                data-period-2-amount="{{ $inputValue($row['period_2_amount']) }}"
                                data-save-url="{{ str_replace('__ACCOUNT__', rawurlencode((string) $row['account_code']), $saveUrlTemplate) }}">
                                @foreach($codeParts as $partIndex => $part)
                                    <td class="center period-code-cell {{ $partIndex === $level ? 'period-code-main' : '' }}">
                                        {{ $partIndex < $level ? '' : $part }}
                                    </td>
                                @endforeach
                                <td class="period-row-title">{{ $row['title'] }}</td>
                                <td class="num" data-yearly-display>{{ $money($row['yearly_amount']) }}</td>
                                <td>
                                    @if($isEditableRow)
                                        <input
                                            class="period-money-input"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            value="{{ $inputValue($row['period_1_amount']) }}"
                                            data-period-input="period_1_amount"
                                        >
                                    @else
                                        <span class="period-readonly-amount" data-period-display="period_1_amount">{{ $money($row['period_1_amount']) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isEditableRow)
                                        <input
                                            class="period-money-input"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            value="{{ $inputValue($row['period_2_amount']) }}"
                                            data-period-input="period_2_amount"
                                        >
                                    @else
                                        <span class="period-readonly-amount" data-period-display="period_2_amount">{{ $money($row['period_2_amount']) }}</span>
                                    @endif
                                </td>
                                <td class="num" data-first-half>{{ $money($row['first_half_amount']) }}</td>
                                <td class="num" data-second-half>{{ $money($row['second_half_amount']) }}</td>
                                <td class="period-row-status" data-period-status>{{ ! empty($row['is_group']) ? 'total' : ($row['has_override'] ? 'saved' : 'default') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="center">62</td>
                                <td colspan="10" class="center">ຍັງບໍ່ມີຂໍ້ມູນລາຍຈ່າຍວິຊາການຕາມຜັງບັນຊີ</td>
                            </tr>
                        @endforelse
                        <tr class="period-total-row" data-period-total-row>
                            <td colspan="4"></td>
                            <td class="center">ລວມຍອດ</td>
                            <td class="num" data-total-yearly>{{ $money($periodTotals['yearly_amount']) }}</td>
                            <td class="num" data-total-period-1>{{ $money($periodTotals['period_1_amount']) }}</td>
                            <td class="num" data-total-period-2>{{ $money($periodTotals['period_2_amount']) }}</td>
                            <td class="num" data-total-first-half>{{ $money($periodTotals['first_half_amount']) }}</td>
                            <td class="num" data-total-second-half>{{ $money($periodTotals['second_half_amount']) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>

<style>
    .period-page {
        display: grid;
        gap: 1rem;
    }

    .period-toolbar,
    .period-paper {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .period-toolbar {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1rem;
    }

    .period-eyebrow {
        color: #64748b;
        display: block;
        font-size: .82rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .period-toolbar h2 {
        color: #0f172a;
        font-size: 1.35rem;
        margin: 0;
    }

    .period-toolbar p {
        color: #64748b;
        margin: .15rem 0 0;
    }

    .period-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .period-btn {
        border-radius: 7px;
        font-weight: 700;
        padding: .55rem .85rem;
        text-decoration: none;
    }

    .period-btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .period-placeholder {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
    }

    .period-placeholder h3 {
        color: #0f172a;
        font-size: 1.4rem;
        margin: 0 0 .5rem;
    }

    .period-placeholder p {
        color: #64748b;
        margin: 0 auto;
        max-width: 620px;
    }

    .period-lock-note,
    .period-warning {
        border-radius: 8px;
        padding: .8rem 1rem;
    }

    .period-lock-note {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-weight: 700;
    }

    .period-warning {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        display: grid;
        gap: .2rem;
    }

    .period-paper {
        overflow: hidden;
        padding: 1rem;
    }

    .period-official-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .9rem;
    }

    .period-official-header div {
        display: grid;
        gap: .2rem;
    }

    .period-official-header div:last-child {
        text-align: right;
    }

    .period-official-header strong,
    .period-official-header span {
        color: #111827;
        font-size: .86rem;
    }

    .period-title-block {
        margin: 0 auto 1rem;
        text-align: center;
    }

    .period-title-block p {
        color: #111827;
        font-size: 1.08rem;
        font-weight: 800;
        margin: 0;
    }

    .period-table-wrap {
        overflow-x: auto;
    }

    .period-report-table {
        border-collapse: collapse;
        color: #111827;
        font-size: .78rem;
        min-width: 1180px;
        width: 100%;
    }

    .period-code-col {
        width: 44px;
    }

    .period-title-col {
        width: 290px;
    }

    .period-money-col,
    .period-input-col {
        width: 132px;
    }

    .period-status-col {
        width: 104px;
    }

    .period-report-table th,
    .period-report-table td {
        border: 1px solid #111827;
        padding: .35rem .4rem;
        vertical-align: middle;
    }

    .period-report-table th {
        background: #f8fafc;
        font-weight: 800;
        text-align: center;
    }

    .period-root-row td,
    .period-group-row td,
    .period-total-row td {
        font-weight: 800;
    }

    .period-code-cell {
        color: #1f2937;
        font-weight: 700;
    }

    .period-code-main {
        background: #f9fafb;
    }

    .period-row-title {
        line-height: 1.35;
    }

    .period-money-input {
        background: #fff;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        color: #0f172a;
        font: inherit;
        font-variant-numeric: tabular-nums;
        padding: .35rem .45rem;
        text-align: right;
        width: 100%;
    }

    .period-money-input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 2px rgba(15, 118, 110, .14);
        outline: none;
    }

    .period-money-input:disabled {
        background: #f8fafc;
        color: #64748b;
    }

    .period-readonly-amount {
        display: block;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    .period-data-row.period-invalid {
        background: #fff1f2;
    }

    .period-row-status {
        color: #64748b;
        font-size: .72rem;
        text-align: center;
        white-space: nowrap;
    }

    .period-row-status.is-saving {
        color: #0369a1;
    }

    .period-row-status.is-saved {
        color: #047857;
    }

    .period-row-status.is-error {
        color: #b91c1c;
        white-space: normal;
    }

    .period-total-row td {
        background: #f8fafc;
    }

    .center {
        text-align: center;
    }

    .num {
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    @media (max-width: 720px) {
        .period-toolbar,
        .period-official-header {
            align-items: stretch;
            flex-direction: column;
        }

        .period-actions,
        .period-official-header div:last-child {
            justify-content: flex-start;
            text-align: left;
        }
    }
</style>

@if($periodKey === 'period-1-2')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canEdit = @json($canEditPeriod);
            const csrfToken = @json(csrf_token());
            const rows = Array.from(document.querySelectorAll('[data-period-row]'));
            const formatMoney = (value) => new Intl.NumberFormat('en-US', {
                maximumFractionDigits: 0,
                minimumFractionDigits: 0,
            }).format(Number.isFinite(value) ? value : 0);
            const parseAmount = (value) => {
                const parsed = Number.parseFloat(String(value).replace(/,/g, ''));
                return Number.isFinite(parsed) ? parsed : 0;
            };
            const isGroup = (row) => row.dataset.isGroup === '1';
            const rowAmount = (row, key) => {
                const input = row.querySelector(`[data-period-input="${key}"]`);
                return input ? parseAmount(input.value) : parseAmount(row.dataset[key === 'period_1_amount' ? 'period1Amount' : 'period2Amount']);
            };
            const setRowAmount = (row, key, value) => {
                row.dataset[key === 'period_1_amount' ? 'period1Amount' : 'period2Amount'] = String(value);
                const display = row.querySelector(`[data-period-display="${key}"]`);
                if (display) {
                    display.textContent = formatMoney(value);
                }
            };
            const prefixLength = (row) => Math.min((Number.parseInt(row.dataset.level || '0', 10) + 1) * 2, row.dataset.accountCode.length);
            const isChildOf = (parent, child) => {
                if (parent === child || Number.parseInt(child.dataset.level || '0', 10) <= Number.parseInt(parent.dataset.level || '0', 10)) {
                    return false;
                }

                return child.dataset.accountCode.startsWith(parent.dataset.accountCode.slice(0, prefixLength(parent)));
            };
            const setStatus = (row, message, className = '') => {
                const status = row.querySelector('[data-period-status]');
                status.className = `period-row-status ${className}`.trim();
                status.textContent = message;
            };
            const updateGroupRows = () => {
                rows.filter(isGroup).forEach((row) => {
                    const children = rows.filter((child) => ! isGroup(child) && isChildOf(row, child));
                    if (children.length === 0) {
                        return;
                    }

                    const yearly = parseAmount(row.dataset.yearlyAmount);
                    const p1 = children.reduce((sum, child) => sum + rowAmount(child, 'period_1_amount'), 0);
                    const p2 = children.reduce((sum, child) => sum + rowAmount(child, 'period_2_amount'), 0);
                    const first = p1 + p2;

                    setRowAmount(row, 'period_1_amount', p1);
                    setRowAmount(row, 'period_2_amount', p2);
                    row.querySelector('[data-first-half]').textContent = formatMoney(first);
                    row.querySelector('[data-second-half]').textContent = formatMoney(yearly - first);
                });
            };
            const updateTotals = () => {
                updateGroupRows();

                const totals = rows.reduce((sum, row) => {
                    if (Number.parseInt(row.dataset.level || '0', 10) !== 0) {
                        return sum;
                    }

                    const yearly = parseAmount(row.dataset.yearlyAmount);
                    const p1 = rowAmount(row, 'period_1_amount');
                    const p2 = rowAmount(row, 'period_2_amount');
                    const first = p1 + p2;
                    sum.yearly += yearly;
                    sum.p1 += p1;
                    sum.p2 += p2;
                    sum.first += first;
                    sum.second += yearly - first;
                    return sum;
                }, { yearly: 0, p1: 0, p2: 0, first: 0, second: 0 });

                document.querySelector('[data-total-yearly]').textContent = formatMoney(totals.yearly);
                document.querySelector('[data-total-period-1]').textContent = formatMoney(totals.p1);
                document.querySelector('[data-total-period-2]').textContent = formatMoney(totals.p2);
                document.querySelector('[data-total-first-half]').textContent = formatMoney(totals.first);
                document.querySelector('[data-total-second-half]').textContent = formatMoney(totals.second);
            };
            const recalculate = (row) => {
                if (isGroup(row)) {
                    updateTotals();
                    return null;
                }

                const yearly = parseAmount(row.dataset.yearlyAmount);
                const p1 = rowAmount(row, 'period_1_amount');
                const p2 = rowAmount(row, 'period_2_amount');
                const first = p1 + p2;
                const second = yearly - first;

                setRowAmount(row, 'period_1_amount', p1);
                setRowAmount(row, 'period_2_amount', p2);
                row.querySelector('[data-first-half]').textContent = formatMoney(first);
                row.querySelector('[data-second-half]').textContent = formatMoney(second);
                row.classList.toggle('period-invalid', p1 < 0 || p2 < 0 || first > yearly);
                updateTotals();

                if (p1 < 0 || p2 < 0) {
                    setStatus(row, '>= 0', 'is-error');
                    return null;
                }

                if (first > yearly) {
                    setStatus(row, 'ເກີນງົບປີ', 'is-error');
                    return null;
                }

                return {
                    period_1_amount: p1,
                    period_2_amount: p2,
                };
            };
            const saveRow = async (row) => {
                const payload = recalculate(row);
                if (! payload) {
                    return;
                }

                setStatus(row, 'saving', 'is-saving');

                try {
                    const response = await fetch(row.dataset.saveUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        throw new Error(data.message || 'Save failed');
                    }

                    row.querySelector('[data-first-half]').textContent = formatMoney(data.row.first_half_amount);
                    row.querySelector('[data-second-half]').textContent = formatMoney(data.row.second_half_amount);
                    setStatus(row, 'saved', 'is-saved');
                    updateTotals();
                } catch (error) {
                    setStatus(row, error.message || 'error', 'is-error');
                }
            };

            rows.forEach((row) => {
                recalculate(row);

                if (! canEdit || isGroup(row)) {
                    return;
                }

                let saveTimer = null;
                row.querySelectorAll('[data-period-input]').forEach((input) => {
                    input.addEventListener('input', () => {
                        window.clearTimeout(saveTimer);
                        const payload = recalculate(row);
                        if (! payload) {
                            return;
                        }

                        setStatus(row, 'changed');
                        saveTimer = window.setTimeout(() => saveRow(row), 650);
                    });
                });
            });
        });
    </script>
@endif
@endsection
