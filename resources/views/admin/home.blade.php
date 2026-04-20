@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard ຜູ້ດູແລລະບົບ')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-label">ຍິນດີຕ້ອນຮັບ</div>
            <div class="welcome-name">{{ auth()->user()->full_name }}</div>
            <div class="welcome-desc">ໜ້ານີ້ໃຊ້ສຳລັບຈັດການຂໍ້ມູນພື້ນຖານຂອງລະບົບການເງິນ.</div>
        </div>
        <div class="welcome-icon">
            <svg style="width:24px;height:24px;color:#2D55C8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
            </svg>
        </div>
    </div>

    {{-- Navigation Cards --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
        <a href="{{ route('admin.users.index') }}" class="card">
            <div class="card-badge blue">CRUD</div>
            <div class="card-title">ຜູ້ໃຊ້</div>
            <div class="card-desc">ຈັດການບັນຊີຜູ້ໃຊ້</div>
            <div class="card-link">ເປີດໜ້າ Users →</div>
        </a>

        <a href="{{ route('admin.roles.index') }}" class="card">
            <div class="card-badge green">CRUD</div>
            <div class="card-title">ບົດບາດ</div>
            <div class="card-desc">ກຳນົດສິດທິໃນລະບົບ</div>
            <div class="card-link">ເປີດໜ້າ Roles →</div>
        </a>

        <a href="{{ route('admin.departments.index') }}" class="card">
            <div class="card-badge orange">CRUD</div>
            <div class="card-title">ພະແນກ</div>
            <div class="card-desc">ຂໍ້ມູນໜ່ວຍງານ</div>
            <div class="card-link">ເປີດໜ້າ Departments →</div>
        </a>

        <a href="{{ route('admin.chart-of-accounts.index') }}" class="card">
            <div class="card-badge gray">CRUD</div>
            <div class="card-title">ແຜນບັນຊີ</div>
            <div class="card-desc">ໂຄງສ້າງລະຫັດບັນຊີ</div>
            <div class="card-link">ເປີດໜ້າ Chart of Accounts →</div>
        </a>
    </div>
</div>
@endsection
