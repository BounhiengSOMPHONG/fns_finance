@extends('layouts.admin')

@section('title', 'ແກ້ໄຂອັດຕາ ມຊ')
@section('page-title', 'ແກ້ໄຂອັດຕາເປີເຊັນ ມຊ')

@section('content')
<div class="fns-card" style="max-width:520px;">
    @php $setting = $nuolPct; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.update', $nuolPct) }}">
        @csrf @method('PUT')
        @include('dashboards.finance_head.settings.nuol-pct._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ອັບເດດ</button>
            <a href="{{ route('head_of_finance.settings.nuol-pct.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
