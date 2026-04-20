@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າພາກສ່ວນ')
@section('page-title', 'Dashboard ຫົວໜ້າພາກສ່ວນ')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-label">ຍິນດີຕ້ອນຮັບ</div>
            <div class="welcome-name">{{ auth()->user()->full_name }}</div>
            <div class="welcome-desc">ກວດສອບ ແລະ ໃຫ້ຄຳເຫັນຕໍ່ແຜນງົບປະມານທີ່ຖືກມອບໝາຍ.</div>
        </div>
        <div class="welcome-icon">
            <svg style="width:24px;height:24px;color:#2D55C8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
    </div>

    {{-- Action Card --}}
    <a href="{{ route('head_of_department.annual-budget.index') }}" class="card" style="max-width:420px;">
        <div class="card-badge blue">ASSIGNED REVIEWS</div>
        <div class="card-title">ລາຍການແຜນງົບທີ່ຕ້ອງກວດສອບ</div>
        <div class="card-desc">ເຂົ້າໄປກວດສອບຂໍ້ມູນ ແລະ ບັນທຶກຄຳເຫັນໄດ້ທັນທີ.</div>
        <div class="card-link">ເປີດລາຍການກວດສອບ →</div>
    </a>
</div>
@endsection
