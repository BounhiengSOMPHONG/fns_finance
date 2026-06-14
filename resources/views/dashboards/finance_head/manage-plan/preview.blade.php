@extends('layouts.admin')

@section('title', 'Preview plan ' . $planningYear->year)
@section('page-title', 'Preview plan')

@section('content')
@php
    $money = fn ($amount) => number_format((float) $amount, 0) . ' ກີບ';
    $grandTotal = (float) $incomeTotal + (float) $salaryTotal + (float) $expenseTotal;
@endphp

<section class="plan-preview">
    <div class="pp-head">
        <div>
            <span class="pp-kicker">Planning preview</span>
            <h2>ຂຶ້ນແຜນປະຈຳປີ {{ $planningYear->year }}</h2>
            <p>{{ $planningYear->name ?: 'Planning ' . $planningYear->year }}</p>
        </div>
        <a href="{{ route('head_of_finance.manage-plan.index') }}" class="pp-back">ກັບຄືນ</a>
    </div>

    <div class="pp-summary">
        <div>
            <span>ລາຍຮັບ</span>
            <strong>{{ $money($incomeTotal) }}</strong>
        </div>
        <div>
            <span>ເງິນເດືອນ</span>
            <strong>{{ $money($salaryTotal) }}</strong>
        </div>
        <div>
            <span>ລາຍຈ່າຍ</span>
            <strong>{{ $money($expenseTotal) }}</strong>
        </div>
        <div>
            <span>ລວມທັງໝົດ</span>
            <strong>{{ $money($grandTotal) }}</strong>
        </div>
    </div>

    <section class="pp-section">
        <div class="pp-section-head">
            <h3>ລາຍຮັບ</h3>
            <strong>{{ $money($incomeTotal) }}</strong>
        </div>
        <div class="pp-table-wrap">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>ລຳດັບ</th>
                        <th>ພາກ</th>
                        <th>ຫຼັກສູດ</th>
                        <th class="pp-num">ນັກສຶກສາ</th>
                        <th class="pp-num">ລວມ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomeRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['section_code'] ?: '-' }}</td>
                            <td>{{ $row['program'] }}</td>
                            <td class="pp-num">{{ number_format($row['students']) }}</td>
                            <td class="pp-num">{{ $money($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="pp-empty">ຍັງບໍ່ມີຂໍ້ມູນລາຍຮັບ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="pp-section">
        <div class="pp-section-head">
            <h3>ເງິນເດືອນ</h3>
            <strong>{{ $money($salaryTotal) }}</strong>
        </div>
        <div class="pp-table-wrap">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>ລຳດັບ</th>
                        <th>ເດືອນ</th>
                        <th>ລະຫັດບັນຊີ</th>
                        <th>ຊື່ບັນຊີ</th>
                        <th class="pp-num">ຈຳນວນຄົນ</th>
                        <th class="pp-num">ລວມປີ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaryRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['month'] }}</td>
                            <td>{{ $row['account_code'] }}</td>
                            <td>{{ $row['account_name'] }}</td>
                            <td class="pp-num">{{ number_format($row['person_count']) }}</td>
                            <td class="pp-num">{{ $money($row['annual_amount']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="pp-empty">ຍັງບໍ່ມີຂໍ້ມູນເງິນເດືອນ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="pp-section">
        <div class="pp-section-head">
            <h3>ລາຍຈ່າຍ</h3>
            <strong>{{ $money($expenseTotal) }}</strong>
        </div>
        <div class="pp-table-wrap">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>ລຳດັບ</th>
                        <th>ໝວດ</th>
                        <th>ລາຍການຍ່ອຍ</th>
                        <th>ລາຍລະອຽດແຜນ</th>
                        <th>ໝາຍເຫດ</th>
                        <th class="pp-num">ລວມປີ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenseRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $row['section_code'] ?: '-' }}</strong>
                                <span>{{ $row['section_name'] }}</span>
                            </td>
                            <td>
                                <strong>{{ $row['subsection_code'] ?: '-' }}</strong>
                                <span>{{ $row['subsection_name'] }}</span>
                            </td>
                            <td>{{ $row['plan_detail'] }}</td>
                            <td>{{ $row['detail'] ?: '-' }}</td>
                            <td class="pp-num">{{ $money($row['yearly_total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="pp-empty">ຍັງບໍ່ມີຂໍ້ມູນລາຍຈ່າຍ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>

<style>
    .plan-preview { display:flex; flex-direction:column; gap:1rem; }
    .pp-head {
        display:flex; align-items:flex-end; justify-content:space-between; gap:1rem;
        background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px;
        padding:1.1rem 1.2rem; box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .pp-kicker { color:var(--fns-gold); font-size:.72rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .pp-head h2 { margin:.2rem 0; color:var(--fns-navy); font-size:1.35rem; }
    .pp-head p { margin:0; color:var(--fns-gray-500); font-size:.86rem; }
    .pp-back {
        display:inline-flex; align-items:center; justify-content:center; min-height:38px;
        border:1px solid var(--fns-gray-200); border-radius:8px; background:#fff;
        color:var(--fns-navy); font-size:.82rem; font-weight:900; padding:.55rem .8rem;
        text-decoration:none;
    }
    .pp-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem; }
    .pp-summary div, .pp-section {
        background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px;
        box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .pp-summary div { padding:.85rem; }
    .pp-summary span { display:block; color:var(--fns-gray-400); font-size:.72rem; font-weight:900; }
    .pp-summary strong {
        display:block; margin-top:.2rem; color:var(--fns-navy); font-size:.96rem;
        font-variant-numeric:tabular-nums; overflow-wrap:anywhere; line-height:1.25;
    }
    .pp-section { overflow:hidden; }
    .pp-section-head {
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        padding:.9rem 1rem; border-bottom:1px solid var(--fns-gray-200);
    }
    .pp-section-head h3 { margin:0; color:var(--fns-navy); font-size:1rem; }
    .pp-section-head strong { color:#72500b; font-size:.9rem; font-variant-numeric:tabular-nums; }
    .pp-table-wrap { overflow-x:auto; }
    .pp-table { width:100%; border-collapse:collapse; min-width:760px; }
    .pp-table th, .pp-table td {
        border-bottom:1px solid var(--fns-gray-200); padding:.65rem .75rem;
        text-align:left; vertical-align:top; color:var(--fns-navy); font-size:.82rem;
    }
    .pp-table th {
        background:#f7f8fa; color:var(--fns-gray-500); font-size:.72rem;
        font-weight:900; white-space:nowrap;
    }
    .pp-table td span { display:block; margin-top:.16rem; color:var(--fns-gray-500); font-size:.76rem; }
    .pp-table tr:last-child td { border-bottom:0; }
    .pp-num { text-align:right !important; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .pp-empty { color:var(--fns-gray-400) !important; text-align:center !important; padding:1.2rem !important; }
    @media (max-width:760px) {
        .pp-head { align-items:stretch; flex-direction:column; }
        .pp-summary { grid-template-columns:1fr; }
    }
</style>
@endsection
