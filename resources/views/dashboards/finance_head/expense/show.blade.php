@extends('layouts.admin')

@section('title', 'ງົບປະມານ ສົກ ' . $expensePlan->fiscal_year)
@section('page-title', 'ສັງລວມປະເມີນລາຍຈ່າຍ ສົກ ' . $expensePlan->fiscal_year)

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.2rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <a href="{{ route('head_of_finance.expense.manage', $expensePlan) }}" class="fns-btn fns-btn-secondary fns-btn-sm">ແກ້ໄຂ</a>
    <span style="font-size:1rem;font-weight:700;color:var(--fns-navy);">ສົກ {{ $expensePlan->fiscal_year }}</span>
    @if($expensePlan->isApproved())
        <span class="fns-badge fns-badge-success" style="font-size:0.8rem;">ອະນຸມັດແລ້ວ</span>
    @else
        <span class="fns-badge fns-badge-warning" style="font-size:0.8rem;">ຮ່າງ</span>
    @endif
    <span style="margin-left:auto;font-size:0.85rem;color:#64748b;">
        ງົບລວມ: <strong>{{ number_format($expensePlan->grandTotal(), 0) }}</strong> ກີບ
    </span>
</div>

@php
    $byCat = $expensePlan->entries->groupBy(fn($e) => $e->main_cat ?: 'ບໍ່ໄດ້ກຳນົດ');
    $qfmt = fn($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@forelse($byCat as $catName => $catEntries)
<div style="margin-bottom:1.2rem;">
    {{-- Main category header --}}
    <div style="display:flex;align-items:center;gap:8px;background:var(--fns-navy);color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;">
        <span style="font-weight:700;flex:1;">{{ $catName }}</span>
        <span style="font-weight:700;white-space:nowrap;">{{ number_format($catEntries->sum('total'), 0) }} ກີບ</span>
    </div>

    @foreach($catEntries->groupBy(fn($e) => $e->main_item ?: '—') as $itemName => $itemEntries)
    <div style="border-left:3px solid var(--fns-navy);margin-left:8px;">
        <div style="display:flex;align-items:center;gap:8px;background:#f1f5f9;padding:5px 12px;border-bottom:1px solid #e2e8f0;">
            <span style="font-weight:600;flex:1;color:var(--fns-navy);font-size:0.85rem;">{{ $itemName }}</span>
            <span style="font-weight:600;color:var(--fns-navy);white-space:nowrap;font-size:0.85rem;">{{ number_format($itemEntries->sum('total'), 0) }} ກີບ</span>
        </div>
        <div style="overflow-x:auto;">
        <table class="fns-table" style="margin:0;font-size:0.76rem;min-width:1000px;">
            <thead>
                <tr style="font-size:0.68rem;">
                    <th style="width:80px;">ອ້າງອີງ</th>
                    <th style="width:100px;">ລະຫັດບັນຊີ</th>
                    <th>ລາຍການຍ່ອຍ</th>
                    <th style="text-align:right;">ອັດຕາ 1</th>
                    <th style="text-align:right;">ອັດຕາ 2</th>
                    <th style="text-align:center;">ຈຳນວນ</th>
                    <th style="text-align:center;">ໄລຍະ</th>
                    <th style="text-align:center;">ຄວາມຖີ່</th>
                    <th style="text-align:right;">ບວກເພີ່ມ</th>
                    <th style="text-align:right;">ຍອດລວມ</th>
                    <th>ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemEntries as $e)
                <tr>
                    <td>{{ $e->ref_code }}</td>
                    <td style="font-family:monospace;">{{ $e->chartOfAccount?->account_code }}</td>
                    <td>{{ $e->sub_item }}</td>
                    <td style="text-align:right;">{{ number_format($e->rate1, 0) }}</td>
                    <td style="text-align:right;">{{ number_format($e->rate2, 0) }}</td>
                    <td style="text-align:center;">{{ $qfmt($e->qty) }}</td>
                    <td style="text-align:center;">{{ $qfmt($e->period) }}</td>
                    <td style="text-align:center;">{{ $qfmt($e->frequency) }}</td>
                    <td style="text-align:right;">{{ number_format($e->add_on, 0) }}</td>
                    <td style="text-align:right;font-weight:600;">{{ number_format($e->total, 0) }}</td>
                    <td style="color:#64748b;">{{ $e->note }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endforeach
</div>
@empty
<div class="fns-card" style="padding:2rem;text-align:center;color:#94a3b8;">
    ຍັງບໍ່ມີລາຍການ — ກົດ "ແກ້ໄຂ" ເພື່ອປ້ອນຂໍ້ມູນ
</div>
@endforelse

@endsection
