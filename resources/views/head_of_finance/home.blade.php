@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າການເງິນ')
@section('page-title', 'Dashboard ຫົວໜ້າການເງິນ')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-label">ພ້ອມເຮັດວຽກ</div>
            <div class="welcome-name">{{ auth()->user()->full_name }}</div>
            <div class="welcome-desc">ຄວບຄຸມແຜນງົບປະມານປະຈຳປີ, ກຳນົດງວດ, ແລະຕິດຕາມການອະນຸມັດ.</div>
        </div>
        <div class="welcome-icon">
            <svg style="width:24px;height:24px;color:#2D55C8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    {{-- Navigation Cards --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:14px;">
        <a href="{{ route('head_of_finance.annual-budget.index') }}" class="card">
            <div class="card-badge blue">ANNUAL BUDGET</div>
            <div class="card-title">ແຜນງົບປະມານປະຈຳປີ</div>
            <div class="card-desc">ເຂົ້າໄປຈັດການແຜນງົບ</div>
            <div class="card-link">ເປີດໜ້າ Annual Budget →</div>
        </a>

        <a href="{{ route('head_of_finance.budget-installment.index') }}" class="card">
            <div class="card-badge orange">INSTALLMENT</div>
            <div class="card-title">ແຜນງວດງົບປະມານ</div>
            <div class="card-desc">ຄິດໄລ່ ແລະ ປັບແຜນງວດ</div>
            <div class="card-link">ເປີດໜ້າ Installments →</div>
        </a>
    </div>
</div>
@endsection
