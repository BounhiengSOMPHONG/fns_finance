@extends('layouts.admin')

@section('title', 'Dashboard ຮອງຫົວໜ້າຄະນະ')
@section('page-title', 'Dashboard ຮອງຫົວໜ້າຄະນະ')

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
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
        </div>
    </div>

    @if($hasPendingReview)
        <a href="{{ route('deputy_head_of_faculty.annual-budget.index') }}" class="card" style="max-width:420px;">
            <div class="card-badge orange">BUDGET REVIEW</div>
            <div class="card-title">ເຂົ້າໄປພິຈາລະນາແຜນງົບປະມານ</div>
            <div class="card-desc">ເບິ່ງລາຍການທີ່ລໍຖ້າການກວດສອບ ແລະ ສົ່ງຄວາມຄິດເຫັນ.</div>
            <div class="card-link">ເປີດລາຍການພິຈາລະນາ →</div>
        </a>
    @endif
</div>
@endsection
