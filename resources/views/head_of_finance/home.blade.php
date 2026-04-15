@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າການເງິນ')
@section('page-title', 'ແດຊບອດຫົວໜ້າການເງິນ')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-600 to-sky-500 p-6 text-white shadow-sm">
        <p class="text-sm text-blue-100">ພ້ອມເຮັດວຽກ</p>
        <h2 class="mt-1 text-2xl font-semibold">{{ auth()->user()->full_name }}</h2>
        <p class="mt-2 text-sm text-blue-50">ຄວບຄຸມແຜນງົບປະມານປະຈຳປີ, ກຳນົດງວດ, ແລະຕິດຕາມການອະນຸມັດ.</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('head_of_finance.annual-budget.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">Annual Budget</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ແຜນງົບປະມານປະຈຳປີ</p>
            <p class="mt-3 text-xs text-blue-600">ເຂົ້າໄປຈັດການແຜນງົບ</p>
        </a>

        <a href="{{ route('head_of_finance.budget-installment.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">Installment</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ແຜນງວດງົບປະມານ</p>
            <p class="mt-3 text-xs text-blue-600">ຄິດໄລ່ ແລະ ປັບແຜນງວດ</p>
        </a>

        <a href="{{ route('head_of_finance.plans') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium text-slate-500">Plans</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">ໜ້າແຜນວຽກເພີ່ມເຕີມ</p>
            <p class="mt-3 text-xs text-blue-600">ເຂົ້າໄປໜ້າ Plans</p>
        </a>
    </section>
</div>
@endsection
