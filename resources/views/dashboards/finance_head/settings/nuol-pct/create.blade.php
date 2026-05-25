@extends('layouts.admin')

@section('title', 'ເພີ່ມອັດຕາ ມຊ')
@section('page-title', 'ເພີ່ມອັດຕາເປີເຊັນ ມຊ ໃໝ່')

@section('content')
<div class="fns-card" style="max-width:520px;">
    @php $setting = null; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.store') }}">
        @csrf
        @include('dashboards.finance_head.settings.nuol-pct._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            <a href="{{ route('head_of_finance.settings.nuol-pct.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
