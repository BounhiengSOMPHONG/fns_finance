@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າພາກສ່ວນ')
@section('page-title', 'Dashboardຫົວໜ້າພາກສ່ວນ')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-600 to-blue-500 p-6 text-white shadow-sm">
        <p class="text-sm text-indigo-100">ຍິນດີຕ້ອນຮັບ</p>
        <h2 class="mt-1 text-2xl font-semibold">{{ auth()->user()->full_name }}</h2>
        <p class="mt-2 text-sm text-indigo-50">ກວດສອບ ແລະ ໃຫ້ຄຳເຫັນຕໍ່ແຜນງົບປະມານທີ່ຖືກມອບໝາຍ.</p>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Assigned Reviews</p>
        <p class="mt-2 text-lg font-semibold text-slate-900">ລາຍການແຜນງົບທີ່ຕ້ອງກວດສອບ</p>
        <p class="mt-2 text-sm text-slate-600">ເຂົ້າໄປກວດສອບຂໍ້ມູນ ແລະ ບັນທຶກຄຳເຫັນໄດ້ທັນທີ.</p>
        <a href="{{ route('head_of_department.annual-budget.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            ເປີດລາຍການກວດສອບ
        </a>
    </section>
</div>
@endsection
