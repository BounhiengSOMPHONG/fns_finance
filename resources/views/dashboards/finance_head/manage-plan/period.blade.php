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
            @if($periodKey === 'period-1-2')
                <button type="button" class="period-btn period-btn-primary" data-print-period>ພິມ</button>
            @endif
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
                    </colgroup>
                    <thead>
                        <tr class="period-head-row">
                            <th rowspan="2" class="period-code-head"><span>ພາກ</span></th>
                            <th rowspan="2" class="period-code-head"><span>ພາກ</span><span>ສ່ວນ</span></th>
                            <th rowspan="2" class="period-code-head"><span>ຮ່ວງ</span></th>
                            <th rowspan="2" class="period-code-head"><span>ລູກ</span><span>ຮ່ວງ</span></th>
                            <th rowspan="2">ເນື້ອໃນລາຍຈ່າຍ</th>
                            <th rowspan="2">ແຜນການ<br>ປີ {{ $planningYear->year }}</th>
                            <th colspan="3">ແຜນ 06 ເດືອນຕົ້ນປີ {{ $planningYear->year }}</th>
                            <th rowspan="2">ແຜນ 06 ເດືອນ<br>ທ້າຍປີ {{ $planningYear->year }}</th>
                        </tr>
                        <tr class="period-subhead-row">
                            <th>ແຜນງວດ1</th>
                            <th>ແຜນງວດ2</th>
                            <th>ແຜນ 06 ເດືອນ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="period-total-row" data-period-total-row>
                            <td colspan="4"></td>
                            <td class="period-total-label">ລວມຍອດເງິນພາກສ່ວນ</td>
                            <td class="num" data-total-yearly>{{ $money($periodTotals['yearly_amount']) }}</td>
                            <td class="num" data-total-period-1>{{ $money($periodTotals['period_1_amount']) }}</td>
                            <td class="num" data-total-period-2>{{ $money($periodTotals['period_2_amount']) }}</td>
                            <td class="num" data-total-first-half>{{ $money($periodTotals['first_half_amount']) }}</td>
                            <td class="num" data-total-second-half>{{ $money($periodTotals['second_half_amount']) }}</td>
                        </tr>
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
                            </tr>
                        @empty
                            <tr>
                                <td class="center">62</td>
                                <td colspan="9" class="center">ຍັງບໍ່ມີຂໍ້ມູນລາຍຈ່າຍວິຊາການຕາມຜັງບັນຊີ</td>
                            </tr>
                        @endforelse
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
        border: 0;
        border-radius: 7px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        font-weight: 700;
        line-height: 1.2;
        padding: .55rem .85rem;
        text-decoration: none;
    }

    .period-btn-primary {
        background: #0f172a;
        color: #fff;
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

    @media screen {
        .period-official-header,
        .period-title-block {
            display: none;
        }
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
        color: #000;
        font-size: .78rem;
        min-width: 1180px;
        table-layout: fixed;
        width: 100%;
    }

    .period-code-col {
        width: 45px;
    }

    .period-title-col {
        width: 340px;
    }

    .period-money-col,
    .period-input-col {
        width: 142px;
    }

    .period-report-table th,
    .period-report-table td {
        border: 2px solid #000;
        line-height: 1.25;
        padding: .35rem .42rem;
        vertical-align: middle;
    }

    .period-report-table th {
        background: #fff;
        color: #000;
        font-weight: 800;
        text-align: center;
        white-space: normal;
    }

    .period-head-row th {
        height: 52px;
    }

    .period-subhead-row th {
        height: 38px;
    }

    .period-code-head span {
        display: block;
    }

    .period-root-row td,
    .period-group-row td,
    .period-total-row td {
        font-weight: 800;
    }

    .period-code-cell {
        color: #000;
        font-weight: 700;
        white-space: nowrap;
    }

    .period-code-main {
        background: transparent;
        color: #000;
        font-style: italic;
        font-weight: 900;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .period-row-title {
        line-height: 1.25;
        text-align: left;
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

    .period-data-row.period-invalid td {
        background: #fff1f2;
        color: #b91c1c;
    }

    .period-data-row.period-invalid .period-money-input {
        background: #fef2f2;
        border-color: #dc2626;
        color: #b91c1c;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, .12);
    }

    .period-total-row td {
        background: #ccffff;
        font-weight: 900;
    }

    .period-total-label {
        font-weight: 900;
        text-align: center;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .period-root-row td {
        background: #ccffcc;
    }

    .period-root-row .num,
    .period-group-row .num,
    .period-total-row .num,
    .period-root-row .period-readonly-amount,
    .period-group-row .period-readonly-amount {
        text-decoration: underline;
        text-underline-offset: 2px;
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

    @media print {
        @page {
            margin: 8mm;
            size: A4 landscape;

            @bottom-center {
                color: #111;
                content: counter(page);
                font-family: 'Noto Sans Lao', ui-sans-serif, system-ui, sans-serif;
                font-size: 8pt;
            }
        }

        html,
        body {
            background: #fff !important;
            font-family: 'Noto Sans Lao', ui-sans-serif, system-ui, sans-serif !important;
            margin: 0 !important;
        }

        .fns-topnav,
        .fns-alert,
        .fns-page-title,
        .period-toolbar,
        .period-lock-note,
        .period-warning {
            display: none !important;
        }

        .fns-main,
        .fns-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .fns-main > div:has(.fns-page-title) {
            display: none !important;
        }

        .period-page {
            display: block;
            width: 100%;
            zoom: .76;
        }

        .period-paper {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
            padding: clamp(1.5rem, 5vw, 80px) 0 20px;
        }

        .period-official-header {
            align-items: flex-start;
            display: grid;
            grid-template-columns: minmax(180px, 370px) minmax(360px, 1fr) minmax(180px, 370px);
            margin: 0 0 20px;
            min-height: 96px;
        }

        .period-official-header div:first-child {
            grid-column: 1;
            padding-top: 3rem;
        }

        .period-official-header div:last-child {
            grid-column: 2;
            justify-self: center;
            min-width: 360px;
            padding-top: 0;
            text-align: center;
        }

        .period-official-header strong {
            color: #000;
            display: block;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.72;
            white-space: nowrap;
        }

        .period-official-header span {
            color: #000;
            display: block;
            font-size: .86rem;
            font-weight: 700;
            line-height: 1.55;
        }

        .period-title-block {
            margin: 14px 0 28px;
        }

        .period-title-block p {
            color: #111;
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .period-table-wrap {
            overflow: visible;
        }

        .period-report-table {
            color: #111;
            font-size: 8.6pt;
            min-width: 0;
            table-layout: fixed;
            width: 100%;
        }

        .period-code-col {
            width: 36px;
        }

        .period-title-col {
            width: 275px;
        }

        .period-money-col,
        .period-input-col {
            width: 116px;
        }

        .period-report-table th,
        .period-report-table td {
            border-color: #000;
            line-height: 1.2;
            padding: .22rem .28rem;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .period-report-table th {
            background: #fff;
            color: #111;
            font-weight: 800;
            white-space: normal;
        }

        .period-root-row td {
            background: #ccffcc;
            font-weight: 900;
        }

        .period-total-row td {
            background: #ccffff;
            font-weight: 900;
        }

        .period-code-cell {
            color: #111;
            font-weight: 700;
            white-space: nowrap;
        }

        .period-code-main {
            background: transparent;
            color: #111827;
            font-style: italic;
            font-weight: 900;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .period-row-title {
            line-height: 1.2;
            padding-left: 4px !important;
        }

        .period-root-row .num,
        .period-group-row .num,
        .period-total-row .num,
        .period-root-row .period-readonly-amount,
        .period-group-row .period-readonly-amount {
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .period-report-table th,
        .period-code-main,
        .period-total-row td {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .period-money-input {
            appearance: textfield;
            background: transparent;
            border: 0;
            box-shadow: none;
            color: #111827;
            padding: 0;
        }

        .period-money-input::-webkit-inner-spin-button,
        .period-money-input::-webkit-outer-spin-button {
            appearance: none;
            margin: 0;
        }
    }
</style>

@if($periodKey === 'period-1-2')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const printButton = document.querySelector('[data-print-period]');
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
                    return null;
                }

                if (first > yearly) {
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
                    row.classList.remove('period-invalid');
                    updateTotals();
                } catch (error) {
                    row.classList.add('period-invalid');
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

                        saveTimer = window.setTimeout(() => saveRow(row), 650);
                    });
                });
            });

            if (printButton) {
                printButton.addEventListener('click', () => window.print());
            }
        });
    </script>
@endif
@endsection
