@extends('layouts.admin')

@section('title', 'ແກ້ໄຂຄ່າລົງທະບຽນ')
@section('page-title', 'ແກ້ໄຂຄ່າລົງທະບຽນ')

@section('content')
<div class="fns-card" style="max-width:680px;">
    @php $fee = $registrationFee; @endphp
    <form method="POST" action="{{ route('head_of_finance.settings.registration-fee.update', $registrationFee) }}">
        @csrf @method('PUT')
        @include('dashboards.finance_head.settings.registration-fee._form')
        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ອັບເດດ</button>
            <a href="{{ route('head_of_finance.settings.registration-fee.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
