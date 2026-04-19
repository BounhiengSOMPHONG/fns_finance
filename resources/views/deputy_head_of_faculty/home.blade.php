@extends('layouts.admin')

@section('title', 'Dashboard ຮອງຫົວໜ້າຄະນະ')
@section('page-title', 'Dashboardຮອງຫົວໜ້າຄະນະ')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-500 to-yellow-500 p-6 text-white shadow-sm">
        <p class="text-sm text-amber-100">ຍິນດີຕ້ອນຮັບ</p>
        <h2 class="mt-1 text-2xl font-semibold">{{ auth()->user()->full_name }}</h2>
        <p class="mt-2 text-sm text-amber-50">ພິຈາລະນາແຜນງົບປະມານ ແລະ ຕັດສິນໃຈ approve/reject ໃນຂັ້ນຕອນທີ່ຮັບຜິດຊອບ.</p>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Budget Review</p>
        <p class="mt-2 text-lg font-semibold text-slate-900">ເຂົ້າໄປພິຈາລະນາແຜນງົບປະມານ</p>
        <p class="mt-2 text-sm text-slate-600">ເບິ່ງລາຍການທີ່ລໍຖ້າການພິຈາລະນາ ແລະ ສົ່ງຜົນການອະນຸມັດ.</p>
        <a href="{{ route('deputy_head_of_faculty.annual-budget.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">
            ເປີດລາຍການພິຈາລະນາ
        </a>
    </section>
</div>
@endsection
