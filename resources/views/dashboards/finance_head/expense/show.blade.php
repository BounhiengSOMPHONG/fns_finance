@extends('layouts.admin')

@section('title', 'ງົບປະມານ ສົກ ' . $expensePlan->fiscal_year)
@section('page-title', 'ສັງລວມປະເມີນລາຍຈ່າຍ ສົກ ' . $expensePlan->fiscal_year)

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.2rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <span style="font-size:1rem;font-weight:700;color:var(--fns-navy);">ສົກ {{ $expensePlan->fiscal_year }}</span>
    @if($expensePlan->isApproved())
        <span class="fns-badge fns-badge-success" style="font-size:0.8rem;">ອະນຸມັດແລ້ວ</span>
    @else
        <span class="fns-badge fns-badge-warning" style="font-size:0.8rem;">ຮ່າງ</span>
    @endif
    <span style="margin-left:auto;font-size:0.85rem;color:#64748b;">
        ງົບລວມ: <strong>{{ number_format($expensePlan->grandTotal(), 0) }} ກີບ</strong>
    </span>
</div>

@include('dashboards.finance_head.expense._tree', [
    'topCategories'  => $expensePlan->topCategories,
    'plan'           => $expensePlan,
    'editable'       => false,
    'chartOfAccounts'=> collect(),
])

@endsection
