@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'ແດຊບອດຜູ້ດູແລລະບົບ')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-500">ຍິນດີຕ້ອນຮັບ</p>
        <h2 class="mt-1 text-2xl font-semibold text-slate-900">{{ auth()->user()->full_name }}</h2>
        <p class="mt-2 text-sm text-slate-600">ໜ້ານີ້ໃຊ້ສຳລັບຈັດການຂໍ້ມູນພື້ນຖານຂອງລະບົບການເງິນ.</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">ຜູ້ໃຊ້</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ຈັດການບັນຊີຜູ້ໃຊ້</p>
            <p class="mt-3 text-xs text-blue-600">ເປີດໜ້າ Users</p>
        </a>

        <a href="{{ route('admin.roles.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">ບົດບາດ</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ກຳນົດສິດທິໃນລະບົບ</p>
            <p class="mt-3 text-xs text-blue-600">ເປີດໜ້າ Roles</p>
        </a>

        <a href="{{ route('admin.departments.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">ພະແນກ</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ຂໍ້ມູນໜ່ວຍງານ</p>
            <p class="mt-3 text-xs text-blue-600">ເປີດໜ້າ Departments</p>
        </a>

        <a href="{{ route('admin.chart-of-accounts.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">ແຜນບັນຊີ</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ໂຄງສ້າງລະຫັດບັນຊີ</p>
            <p class="mt-3 text-xs text-blue-600">ເປີດໜ້າ Chart of Accounts</p>
        </a>
    </section>
</div>
@endsection
