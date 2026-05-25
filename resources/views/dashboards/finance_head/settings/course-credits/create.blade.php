@extends('layouts.admin')

@section('title', 'ເພີ່ມໜ່ວຍກິດຕາມຫຼັກສູດ')
@section('page-title', 'ເພີ່ມໜ່ວຍກິດຕາມຫຼັກສູດ')

@section('content')
<div class="fns-card" style="max-width:560px;">
    @php $setting = null; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.course-credits.store') }}">
        @csrf
        @include('dashboards.finance_head.settings.course-credits._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            <a href="{{ route('head_of_finance.settings.course-credits.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
