@extends('layouts.admin')

@section('title', 'Dashboard ນັກບັນຊີ')
@section('page-title', 'Dashboard ນັກບັນຊີ')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-label">ຍິນດີຕ້ອນຮັບ</div>
            <div class="welcome-name">{{ auth()->user()->full_name }}</div>
            <div class="welcome-desc">ສ່ວນງານນີ້ເຕີມໂຕໄດ້ຕໍ່ໃນຂັ້ນຕໍ່ໄປສຳລັບທຸລະກຳ ແລະ ບັນຊີລາຍວັນ.</div>
        </div>
        <div class="welcome-icon">
            <svg style="width:24px;height:24px;color:#2D55C8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </div>
    </div>

    {{-- Coming Soon --}}
    <div class="card" style="max-width:420px; border-style:dashed; cursor:default;">
        <div class="card-badge gray">COMING SOON</div>
        <div class="card-title">ຟີເຈີ Accountant ກຳລັງພັດທະນາ</div>
        <div class="card-desc">ໃນຮອບຕໍ່ໄປຈະເພີ່ມລະບົບບັນທຶກທຸລະກຳ, ການແນບເອກະສານ, ແລະ ການກະທົບຍອດເງິນ.</div>
    </div>
</div>
@endsection
