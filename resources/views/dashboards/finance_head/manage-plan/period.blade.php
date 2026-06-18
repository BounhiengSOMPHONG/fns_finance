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
        'average_increase_amount' => 0,
        'average_decrease_amount' => 0,
        'requested_decrease_amount' => 0,
        'requested_increase_amount' => 0,
        'adjusted_second_half_amount' => 0,
        'period_3_amount' => 0,
        'period_4_amount' => 0,
        'period_3_4_total_amount' => 0,
    ];
    $periodWarnings = $periodReport['warnings'] ?? ['unlinked_expenses' => [], 'reference_fallbacks' => []];
    $canEditPeriod = (bool) ($canEditPeriod ?? false);
    $money = fn ($amount) => number_format((float) $amount, 0, '.', '.');
    $inputValue = fn ($amount) => rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.');
    $inputMoney = fn ($amount) => number_format((float) $amount, 0, '.', '.');
    $saveUrlTemplate = $periodKey === 'period-3-4'
        ? route('head_of_finance.manage-plan.period-3-4.override', [
            'planningYear' => $planningYear,
            'accountCode' => '__ACCOUNT__',
        ])
        : route('head_of_finance.manage-plan.period-1-2.override', [
            'planningYear' => $planningYear,
            'accountCode' => '__ACCOUNT__',
        ]);
    $savePeriodRoute = $periodKey === 'period-3-4'
        ? route('head_of_finance.manage-plan.period-3-4.save', $planningYear)
        : route('head_of_finance.manage-plan.period-1-2.save', $planningYear);
    $isPeriodThreeFour = $periodKey === 'period-3-4';
@endphp

<div class="period-page">
    <div class="period-toolbar">
        <div>
            <span class="period-eyebrow">ແຜນປີ {{ $planningYear->year }}</span>
            <h2>{{ $periodTitle }}</h2>
            <p>{{ $planningYear->name }}</p>
        </div>
        <div class="period-actions">
            @if(in_array($periodKey, ['period-1-2', 'period-3-4'], true))
                <button type="button" class="period-btn period-btn-primary" data-print-period>ພິມ</button>
                @if($canEditPeriod)
                    <form method="POST" action="{{ $savePeriodRoute }}" data-save-period-form>
                        @csrf
                        <button type="submit" class="period-btn period-btn-success" data-save-period-button>
                            ບັນທຶກ{{ $periodTitle }}
                        </button>
                    </form>
                @endif
            @endif
            <a href="{{ route('head_of_finance.manage-plan.index') }}" class="period-btn period-btn-secondary">ກັບຄືນ</a>
        </div>
    </div>

    @if(in_array($periodKey, ['period-1-2', 'period-3-4'], true))
        @if($periodKey === 'period-1-2' && $planningYear->hasSavedPeriodOneTwo())
            <div class="period-lock-note">
                ງວດ 1-2 ຖືກບັນທຶກແລ້ວ ຈຶ່ງບໍ່ສາມາດແກ້ໄຂຍອດໄດ້.
            </div>
        @elseif($periodKey === 'period-3-4' && $planningYear->hasSavedPeriodThreeFour())
            <div class="period-lock-note">
                ງວດ 3-4 ຖືກບັນທຶກແລ້ວ ຈຶ່ງບໍ່ສາມາດແກ້ໄຂຍອດໄດ້.
            </div>
        @elseif(! $canEditPeriod)
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
            <div class="period-print-header">
                <img src="{{ asset('storage/Emblem_of_Laos.png') }}" alt="Lao emblem">
                <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
                <small>-----xxxxx-----</small>
            </div>

            <div class="period-print-meta">
                <div>
                    <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                    <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
                </div>
                <div>
                    <span>ເລກທີ&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ຄວທ</span>
                    <span>ນະຄອນຫຼວງວຽງຈັນ ວັນທີ {{ now()->format('d/m/Y') }}</span>
                </div>
            </div>

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
                <p>ແຜນລາຍຈ່າຍ{{ $periodTitle }} ງົບປະມານວິຊາການ ປີ {{ $planningYear->year }}</p>
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
                        @if($isPeriodThreeFour)
                            <col class="period-money-col">
                            <col class="period-money-col">
                            <col class="period-money-col">
                            <col class="period-input-col">
                            <col class="period-input-col">
                            <col class="period-money-col">
                            <col class="period-input-col">
                            <col class="period-input-col">
                            <col class="period-money-col">
                            <col class="period-percent-col">
                        @else
                            <col class="period-input-col">
                            <col class="period-input-col">
                            <col class="period-money-col">
                            <col class="period-money-col">
                        @endif
                    </colgroup>
                    <thead>
                        <tr class="period-head-row">
                            <th rowspan="2" class="period-code-head"><span>ພາກ</span></th>
                            <th rowspan="2" class="period-code-head"><span>{{ $isPeriodThreeFour ? 'ພາກສ່ວນ' : 'ພາກ' }}</span>@if(! $isPeriodThreeFour)<span>ສ່ວນ</span>@endif</th>
                            <th rowspan="2" class="period-code-head"><span>ຮ່ວງ</span></th>
                            <th rowspan="2" class="period-code-head"><span>{{ $isPeriodThreeFour ? 'ລູກຮ່ວງ' : 'ລູກ' }}</span>@if(! $isPeriodThreeFour)<span>ຮ່ວງ</span>@endif</th>
                            <th rowspan="2">ເນື້ອໃນລາຍຈ່າຍ</th>
                            <th rowspan="2">ແຜນການ<br>ປີ {{ $planningYear->year }}</th>
                            @if($isPeriodThreeFour)
                                <th rowspan="2">ແຜນ 06 ເດືອນ<br>ທ້າຍປີ {{ $planningYear->year }}</th>
                                <th colspan="2" class="period-adjust-head">ແຜນດັດແກ້ສະເລ່ຍ</th>
                                <th rowspan="2" class="period-adjust-head">ແຜນຂໍຫຼຸດ</th>
                                <th rowspan="2" class="period-adjust-head">ແຜນຂໍເພີ່ມ</th>
                                <th rowspan="2">ແຜນດັດແກ້ 6 ເດືອນ<br>ທ້າຍປີ {{ $planningYear->year }}</th>
                                <th rowspan="2">ແຜນງວດ 3</th>
                                <th rowspan="2">ແຜນງວດ 4</th>
                                <th rowspan="2">ແຜນປະຕິບັດ<br>ໝົດປີ {{ $planningYear->year }}</th>
                                <th rowspan="2">ຫຼຸດ%</th>
                            @else
                                <th colspan="3">ແຜນ 06 ເດືອນຕົ້ນປີ {{ $planningYear->year }}</th>
                                <th rowspan="2">ແຜນ 06 ເດືອນ<br>ທ້າຍປີ {{ $planningYear->year }}</th>
                            @endif
                        </tr>
                        <tr class="period-subhead-row">
                            @if($isPeriodThreeFour)
                                <th class="period-adjust-head">ເພີ່ມ</th>
                                <th class="period-adjust-head">ຫຼຸດ</th>
                            @else
                                <th>ແຜນງວດ1</th>
                                <th>ແຜນງວດ2</th>
                                <th>ແຜນ 06 ເດືອນ</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="period-total-row" data-period-total-row>
                            <td colspan="4"></td>
                            <td class="period-total-label">ລວມຍອດເງິນພາກສ່ວນ</td>
                                <td class="num" data-total-yearly>{{ $money($periodTotals['yearly_amount']) }}</td>
                            @if($isPeriodThreeFour)
                                <td class="num" data-total-second-half>{{ $money($periodTotals['second_half_amount']) }}</td>
                                <td class="num" data-total-average-increase>{{ $money($periodTotals['average_increase_amount']) }}</td>
                                <td class="num" data-total-average-decrease>{{ $money($periodTotals['average_decrease_amount']) }}</td>
                                <td class="num" data-total-requested-decrease>{{ $money($periodTotals['requested_decrease_amount']) }}</td>
                                <td class="num" data-total-requested-increase>{{ $money($periodTotals['requested_increase_amount']) }}</td>
                                <td class="num" data-total-adjusted-second-half>{{ $money($periodTotals['adjusted_second_half_amount']) }}</td>
                                <td class="num" data-total-period-3>{{ $money($periodTotals['period_3_amount']) }}</td>
                                <td class="num" data-total-period-4>{{ $money($periodTotals['period_4_amount']) }}</td>
                                <td class="num" data-total-period-3-4>{{ $money($periodTotals['period_3_4_total_amount']) }}</td>
                                <td class="num" data-total-reduction-percent>{{ number_format((float) ($periodTotals['second_half_amount'] > 0 ? ($periodTotals['requested_decrease_amount'] / $periodTotals['second_half_amount']) * 100 : 0), 2) }}%</td>
                            @else
                                <td class="num" data-total-period-1>{{ $money($periodTotals['period_1_amount']) }}</td>
                                <td class="num" data-total-period-2>{{ $money($periodTotals['period_2_amount']) }}</td>
                                <td class="num" data-total-first-half>{{ $money($periodTotals['first_half_amount']) }}</td>
                                <td class="num" data-total-second-half>{{ $money($periodTotals['second_half_amount']) }}</td>
                            @endif
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
                                data-second-half-amount="{{ $inputValue($row['second_half_amount']) }}"
                                data-average-increase-amount="{{ $inputValue($row['average_increase_amount']) }}"
                                data-average-decrease-amount="{{ $inputValue($row['average_decrease_amount']) }}"
                                data-requested-decrease-amount="{{ $inputValue($row['requested_decrease_amount']) }}"
                                data-requested-increase-amount="{{ $inputValue($row['requested_increase_amount']) }}"
                                data-adjusted-second-half-amount="{{ $inputValue($row['adjusted_second_half_amount']) }}"
                                data-period-3-amount="{{ $inputValue($row['period_3_amount']) }}"
                                data-period-4-amount="{{ $inputValue($row['period_4_amount']) }}"
                                data-save-url="{{ str_replace('__ACCOUNT__', rawurlencode((string) $row['account_code']), $saveUrlTemplate) }}">
                                @foreach($codeParts as $partIndex => $part)
                                    <td class="center period-code-cell {{ $partIndex === $level ? 'period-code-main' : '' }}">
                                        {{ $partIndex < $level ? '' : $part }}
                                    </td>
                                @endforeach
                                <td class="period-row-title">{{ $row['title'] }}</td>
                                <td class="num" data-yearly-display>{{ $money($row['yearly_amount']) }}</td>
                                @if($isPeriodThreeFour)
                                    <td class="num" data-second-half>{{ $money($row['second_half_amount']) }}</td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['average_increase_amount']) }}"
                                                data-period-input="average_increase_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="average_increase_amount">{{ $money($row['average_increase_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['average_decrease_amount']) }}"
                                                data-period-input="average_decrease_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="average_decrease_amount">{{ $money($row['average_decrease_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['requested_decrease_amount']) }}"
                                                data-period-input="requested_decrease_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="requested_decrease_amount">{{ $money($row['requested_decrease_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['requested_increase_amount']) }}"
                                                data-period-input="requested_increase_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="requested_increase_amount">{{ $money($row['requested_increase_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td class="num" data-adjusted-second-half>{{ $money($row['adjusted_second_half_amount']) }}</td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['period_3_amount']) }}"
                                                data-period-input="period_3_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="period_3_amount">{{ $money($row['period_3_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['period_4_amount']) }}"
                                                data-period-input="period_4_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="period_4_amount">{{ $money($row['period_4_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td class="num" data-period-3-4-total>{{ $money($row['period_3_4_total_amount']) }}</td>
                                    <td class="num" data-reduction-percent>{{ number_format((float) $row['reduction_percent'], 2) }}%</td>
                                @else
                                    <td>
                                        @if($isEditableRow)
                                            <input
                                                class="period-money-input"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['period_1_amount']) }}"
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
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9.]*"
                                                value="{{ $inputMoney($row['period_2_amount']) }}"
                                                data-period-input="period_2_amount"
                                            >
                                        @else
                                            <span class="period-readonly-amount" data-period-display="period_2_amount">{{ $money($row['period_2_amount']) }}</span>
                                        @endif
                                    </td>
                                    <td class="num" data-first-half>{{ $money($row['first_half_amount']) }}</td>
                                    <td class="num" data-second-half>{{ $money($row['second_half_amount']) }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="center">62</td>
                                <td colspan="{{ $isPeriodThreeFour ? 15 : 9 }}" class="center">ຍັງບໍ່ມີຂໍ້ມູນລາຍຈ່າຍວິຊາການຕາມຜັງບັນຊີ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="period-print-signatures">
                <div>ຫົວໜ້າຄະນະບໍລິຫານ</div>
                <div>ພະແນກການເງິນ</div>
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

    .period-btn-success {
        background: #0f766e;
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

    .period-print-header,
    .period-print-meta,
    .period-print-signatures {
        display: none;
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
        min-width: {{ $isPeriodThreeFour ? '1720px' : '1180px' }};
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

    .period-percent-col {
        width: 86px;
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

    .period-adjust-head {
        color: #e60000 !important;
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
            padding: 0 0 20px;
        }

        .period-print-header {
            align-items: center;
            color: #000;
            display: flex;
            flex-direction: column;
            line-height: 1.45;
            margin: 0 0 2rem;
            text-align: center;
        }

        .period-print-header img {
            height: 72px;
            margin: 0 0 .25rem;
            object-fit: contain;
            width: 72px;
        }

        .period-print-header strong {
            font-size: 1rem;
            font-weight: 800;
        }

        .period-print-header span {
            font-size: .9rem;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #d9a300;
            text-decoration-thickness: 2px;
            text-underline-offset: 6px;
        }

        .period-print-header small {
            font-size: .72rem;
            margin-top: .35rem;
        }

        .period-print-meta {
            align-items: start;
            color: #000;
            display: grid;
            font-size: .88rem;
            font-weight: 700;
            grid-template-columns: minmax(220px, 1fr) minmax(260px, 1fr);
            line-height: 1.75;
            margin: 0 0 1.35rem;
        }

        .period-print-meta div {
            display: grid;
            gap: .1rem;
        }

        .period-print-meta div:last-child {
            justify-self: end;
            text-align: left;
        }

        .period-print-meta strong,
        .period-print-meta span {
            display: block;
        }

        .period-official-header {
            display: none;
        }

        .period-title-block {
            margin: 0 0 1rem;
        }

        .period-title-block p {
            color: #111;
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.45;
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

        .period-print-signatures {
            color: #000;
            display: grid;
            font-size: .9rem;
            font-weight: 700;
            grid-template-columns: 1fr 1fr;
            margin-top: 2.25rem;
            min-height: 96px;
            text-align: center;
        }
    }
</style>

@if(in_array($periodKey, ['period-1-2', 'period-3-4'], true))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const printButton = document.querySelector('[data-print-period]');
            const saveForm = document.querySelector('[data-save-period-form]');
            const saveButton = document.querySelector('[data-save-period-button]');
            const periodKey = @json($periodKey);
            const canEdit = @json($canEditPeriod);
            const csrfToken = @json(csrf_token());
            const rows = Array.from(document.querySelectorAll('[data-period-row]'));
            const amountDatasetKeys = {
                period_1_amount: 'period1Amount',
                period_2_amount: 'period2Amount',
                average_increase_amount: 'averageIncreaseAmount',
                average_decrease_amount: 'averageDecreaseAmount',
                requested_decrease_amount: 'requestedDecreaseAmount',
                requested_increase_amount: 'requestedIncreaseAmount',
                period_3_amount: 'period3Amount',
                period_4_amount: 'period4Amount',
            };
            const formatMoney = (value) => new Intl.NumberFormat('de-DE', {
                maximumFractionDigits: 0,
                minimumFractionDigits: 0,
            }).format(Number.isFinite(value) ? value : 0);
            const formatInputMoney = (value) => formatMoney(value);
            const formatPercent = (value) => `${(Number.isFinite(value) ? value : 0).toFixed(2)}%`;
            const parseAmount = (value) => {
                let normalized = String(value).trim();

                if (/^\d{1,3}(\.\d{3})+(,\d+)?$/.test(normalized)) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                } else {
                    normalized = normalized.replace(/,/g, '');
                }

                const parsed = Number.parseFloat(normalized);
                return Number.isFinite(parsed) ? parsed : 0;
            };
            const normalizeMoneyInput = (input) => {
                const digits = input.value.replace(/\D/g, '');
                input.value = digits ? formatInputMoney(Number.parseInt(digits, 10)) : '';
            };
            const isGroup = (row) => row.dataset.isGroup === '1';
            const rowAmount = (row, key) => {
                const input = row.querySelector(`[data-period-input="${key}"]`);
                return input ? parseAmount(input.value) : parseAmount(row.dataset[amountDatasetKeys[key]]);
            };
            const setRowAmount = (row, key, value) => {
                row.dataset[amountDatasetKeys[key]] = String(value);
                const input = row.querySelector(`[data-period-input="${key}"]`);
                if (input && document.activeElement !== input) {
                    input.value = formatInputMoney(value);
                }

                const display = row.querySelector(`[data-period-display="${key}"]`);
                if (display) {
                    display.textContent = formatMoney(value);
                }
            };
            const setCellText = (row, selector, value, formatter = formatMoney) => {
                const cell = row.querySelector(selector);
                if (cell) {
                    cell.textContent = formatter(value);
                }
            };
            const setTotalText = (selector, value, formatter = formatMoney) => {
                const cell = document.querySelector(selector);
                if (cell) {
                    cell.textContent = formatter(value);
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
                    const second = yearly - first;
                    const averageIncrease = children.reduce((sum, child) => sum + rowAmount(child, 'average_increase_amount'), 0);
                    const averageDecrease = children.reduce((sum, child) => sum + rowAmount(child, 'average_decrease_amount'), 0);
                    const decrease = children.reduce((sum, child) => sum + rowAmount(child, 'requested_decrease_amount'), 0);
                    const increase = children.reduce((sum, child) => sum + rowAmount(child, 'requested_increase_amount'), 0);
                    const adjusted = second - averageDecrease + averageIncrease - decrease + increase;
                    const p3 = children.reduce((sum, child) => sum + rowAmount(child, 'period_3_amount'), 0);
                    const p4 = children.reduce((sum, child) => sum + rowAmount(child, 'period_4_amount'), 0);
                    const p34 = p3 + p4;
                    const reductionPercent = second > 0 ? (decrease / second) * 100 : 0;

                    setRowAmount(row, 'period_1_amount', p1);
                    setRowAmount(row, 'period_2_amount', p2);
                    setRowAmount(row, 'average_increase_amount', averageIncrease);
                    setRowAmount(row, 'average_decrease_amount', averageDecrease);
                    setRowAmount(row, 'requested_decrease_amount', decrease);
                    setRowAmount(row, 'requested_increase_amount', increase);
                    setRowAmount(row, 'period_3_amount', p3);
                    setRowAmount(row, 'period_4_amount', p4);
                    row.dataset.adjustedSecondHalfAmount = String(adjusted);
                    setCellText(row, '[data-first-half]', first);
                    setCellText(row, '[data-second-half]', second);
                    setCellText(row, '[data-adjusted-second-half]', adjusted);
                    setCellText(row, '[data-period-3-4-total]', p34);
                    setCellText(row, '[data-reduction-percent]', reductionPercent, formatPercent);
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
                    const second = yearly - first;
                    const averageIncrease = rowAmount(row, 'average_increase_amount');
                    const averageDecrease = rowAmount(row, 'average_decrease_amount');
                    const decrease = rowAmount(row, 'requested_decrease_amount');
                    const increase = rowAmount(row, 'requested_increase_amount');
                    const adjusted = second - averageDecrease + averageIncrease - decrease + increase;
                    const p3 = rowAmount(row, 'period_3_amount');
                    const p4 = rowAmount(row, 'period_4_amount');
                    sum.yearly += yearly;
                    sum.p1 += p1;
                    sum.p2 += p2;
                    sum.first += first;
                    sum.second += second;
                    sum.averageIncrease += averageIncrease;
                    sum.averageDecrease += averageDecrease;
                    sum.decrease += decrease;
                    sum.increase += increase;
                    sum.adjusted += adjusted;
                    sum.p3 += p3;
                    sum.p4 += p4;
                    sum.p34 += p3 + p4;
                    return sum;
                }, { yearly: 0, p1: 0, p2: 0, first: 0, second: 0, averageIncrease: 0, averageDecrease: 0, decrease: 0, increase: 0, adjusted: 0, p3: 0, p4: 0, p34: 0 });

                setTotalText('[data-total-yearly]', totals.yearly);
                setTotalText('[data-total-period-1]', totals.p1);
                setTotalText('[data-total-period-2]', totals.p2);
                setTotalText('[data-total-first-half]', totals.first);
                setTotalText('[data-total-second-half]', totals.second);
                setTotalText('[data-total-average-increase]', totals.averageIncrease);
                setTotalText('[data-total-average-decrease]', totals.averageDecrease);
                setTotalText('[data-total-requested-decrease]', totals.decrease);
                setTotalText('[data-total-requested-increase]', totals.increase);
                setTotalText('[data-total-adjusted-second-half]', totals.adjusted);
                setTotalText('[data-total-period-3]', totals.p3);
                setTotalText('[data-total-period-4]', totals.p4);
                setTotalText('[data-total-period-3-4]', totals.p34);
                setTotalText('[data-total-reduction-percent]', totals.second > 0 ? (totals.decrease / totals.second) * 100 : 0, formatPercent);
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
                const averageIncrease = rowAmount(row, 'average_increase_amount');
                const averageDecrease = rowAmount(row, 'average_decrease_amount');
                const decrease = rowAmount(row, 'requested_decrease_amount');
                const increase = rowAmount(row, 'requested_increase_amount');
                const adjusted = second - averageDecrease + averageIncrease - decrease + increase;
                const p3 = rowAmount(row, 'period_3_amount');
                const p4 = rowAmount(row, 'period_4_amount');
                const p34 = p3 + p4;
                const reductionPercent = second > 0 ? (decrease / second) * 100 : 0;

                setRowAmount(row, 'period_1_amount', p1);
                setRowAmount(row, 'period_2_amount', p2);
                setRowAmount(row, 'average_increase_amount', averageIncrease);
                setRowAmount(row, 'average_decrease_amount', averageDecrease);
                setRowAmount(row, 'requested_decrease_amount', decrease);
                setRowAmount(row, 'requested_increase_amount', increase);
                setRowAmount(row, 'period_3_amount', p3);
                setRowAmount(row, 'period_4_amount', p4);
                row.dataset.adjustedSecondHalfAmount = String(adjusted);
                setCellText(row, '[data-first-half]', first);
                setCellText(row, '[data-second-half]', second);
                setCellText(row, '[data-adjusted-second-half]', adjusted);
                setCellText(row, '[data-period-3-4-total]', p34);
                setCellText(row, '[data-reduction-percent]', reductionPercent, formatPercent);

                const invalidFirstHalf = p1 < 0 || p2 < 0 || first > yearly;
                const invalidSecondHalf = averageIncrease < 0 || averageDecrease < 0 || decrease < 0 || increase < 0 || p3 < 0 || p4 < 0 || adjusted < 0 || Math.abs(p34 - adjusted) > 0.01;
                row.classList.toggle('period-invalid', periodKey === 'period-3-4' ? invalidSecondHalf : invalidFirstHalf);
                updateTotals();

                if (periodKey === 'period-3-4') {
                    if (invalidSecondHalf) {
                        return null;
                    }

                    return {
                        average_increase_amount: averageIncrease,
                        average_decrease_amount: averageDecrease,
                        requested_decrease_amount: decrease,
                        requested_increase_amount: increase,
                        period_3_amount: p3,
                        period_4_amount: p4,
                    };
                }

                if (invalidFirstHalf) {
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
                    return false;
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

                    setCellText(row, '[data-first-half]', data.row.first_half_amount);
                    setCellText(row, '[data-second-half]', data.row.second_half_amount);
                    setCellText(row, '[data-adjusted-second-half]', data.row.adjusted_second_half_amount);
                    setCellText(row, '[data-period-3-4-total]', data.row.period_3_4_total_amount);
                    setCellText(row, '[data-reduction-percent]', data.row.reduction_percent, formatPercent);
                    row.classList.remove('period-invalid');
                    updateTotals();
                    return true;
                } catch (error) {
                    row.classList.add('period-invalid');
                    return false;
                }
            };

            rows.forEach((row) => {
                recalculate(row);

                if (! canEdit || isGroup(row)) {
                    return;
                }

                let saveTimer = null;
                row.querySelectorAll('[data-period-input]').forEach((input) => {
                    normalizeMoneyInput(input);

                    input.addEventListener('input', () => {
                        window.clearTimeout(saveTimer);
                        normalizeMoneyInput(input);
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

            if (saveForm) {
                saveForm.addEventListener('submit', async (event) => {
                    if (! canEdit || saveForm.dataset.readyToSubmit === '1') {
                        return;
                    }

                    event.preventDefault();
                    if (saveButton) {
                        saveButton.disabled = true;
                        saveButton.textContent = 'ກຳລັງບັນທຶກ...';
                    }

                    const editableRows = rows.filter((row) => ! isGroup(row) && row.querySelector('[data-period-input]'));
                    const results = await Promise.all(editableRows.map((row) => saveRow(row)));

                    if (results.every(Boolean)) {
                        saveForm.dataset.readyToSubmit = '1';
                        saveForm.submit();
                        return;
                    }

                    if (saveButton) {
                        saveButton.disabled = false;
                        saveButton.textContent = @json('ບັນທຶກ' . $periodTitle);
                    }
                });
            }
        });
    </script>
@endif
@endsection
