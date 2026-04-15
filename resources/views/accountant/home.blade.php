@extends('layouts.admin')

@section('title', 'Dashboard ນັກບັນຊີ')
@section('page-title', 'ແດຊບອດນັກບັນຊີ')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-600 to-green-500 p-6 text-white shadow-sm">
        <p class="text-sm text-emerald-100">ຍິນດີຕ້ອນຮັບ</p>
        <h2 class="mt-1 text-2xl font-semibold">{{ auth()->user()->full_name }}</h2>
        <p class="mt-2 text-sm text-emerald-50">ສ່ວນງານນີ້ເຕີມໂຕໄດ້ຕໍ່ໃນຂັ້ນຕໍ່ໄປສຳລັບທຸລະກຳ ແລະ ບັນຊີລາຍວັນ.</p>
    </section>

    <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Coming Soon</p>
        <h3 class="mt-1 text-lg font-semibold text-slate-900">ຟີເຈີ Accountant ກຳລັງພັດທະນາ</h3>
        <p class="mt-2 text-sm text-slate-600">ໃນຮອບຕໍ່ໄປຈະເພີ່ມລະບົບບັນທຶກທຸລະກຳ, ການແນບເອກະສານ, ແລະ ການກະທົບຍອດເງິນ.</p>
    </section>
</div>
@endsection
