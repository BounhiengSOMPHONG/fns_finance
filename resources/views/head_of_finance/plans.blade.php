@extends('layouts.admin')

@section('title', 'Plans ຫົວໜ້າການເງິນ')
@section('page-title', 'Plans ຫົວໜ້າການເງິນ')

@section('content')
<div class="max-w-3xl space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Plans Module</p>
        <h2 class="mt-1 text-2xl font-semibold text-slate-900">ໜ້ານີ້ພ້ອມສຳລັບຕໍ່ຍອດຟີເຈີ</h2>
        <p class="mt-3 text-sm text-slate-600">
            ໂຄງສ້າງໜ້າຖືກປັບໃຫ້ໃຊ້ Tailwind CSS ແບບດຽວກັບສ່ວນອື່ນໃນລະບົບແລ້ວ.
        </p>
        <a href="{{ route('head_of_finance.home') }}" class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            ກັບໄປ Dashboard
        </a>
    </section>
</div>
@endsection
