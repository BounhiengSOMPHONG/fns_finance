@extends('layouts.admin')

@section('title', 'ແກ້ໄຂລາຄາຄ່າໜ່ວຍກິດ')
@section('page-title', 'ແກ້ໄຂລາຄາຄ່າໜ່ວຍກິດ')

@section('content')
<div class="fns-card" style="max-width:560px;">
    @php $setting = $creditUnitPrice; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.credit-unit-price.update', $creditUnitPrice) }}">
        @csrf @method('PUT')
        @include('dashboards.finance_head.settings.credit-unit-price._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ອັບເດດ</button>
            <a href="{{ route('head_of_finance.settings.credit-unit-price.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
