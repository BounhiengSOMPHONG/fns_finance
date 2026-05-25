@extends('layouts.admin')

@section('title', 'ເພີ່ມຄ່າລົງທະບຽນ')
@section('page-title', 'ເພີ່ມຄ່າລົງທະບຽນໃໝ່')

@section('content')
<div class="fns-card" style="max-width:680px;">
    @php $fee = null; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.registration-fee.store') }}">
        @csrf
        @include('dashboards.finance_head.settings.registration-fee._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            <a href="{{ route('head_of_finance.settings.registration-fee.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
