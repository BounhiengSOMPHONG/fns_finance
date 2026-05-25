@extends('layouts.admin')

@section('title', 'ເພີ່ມລາຄາຄ່າໜ່ວຍກິດ')
@section('page-title', 'ເພີ່ມລາຄາຄ່າໜ່ວຍກິດໃໝ່')

@section('content')
<div class="fns-card" style="max-width:560px;">
    <form method="POST" action="{{ route('head_of_finance.settings.credit-unit-price.store') }}">
        @csrf
        @include('dashboards.finance_head.settings.credit-unit-price._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            <a href="{{ route('head_of_finance.settings.credit-unit-price.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
