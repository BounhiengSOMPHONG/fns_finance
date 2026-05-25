@extends('layouts.admin')

@section('title', 'ລາຍລະອຽດບັນຊີ')
@section('page-title', 'ລາຍລະອຽດບັນຊີ')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">ຂໍ້ມູນບັນຊີ</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.chart-of-accounts.edit', $chartOfAccount) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    ແກ້ໄຂ
                </a>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Account Code -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-sm font-medium text-gray-500">ລະຫັດບັນຊີ</div>
                <div class="col-span-2 text-sm font-mono font-medium text-gray-900">{{ $chartOfAccount->account_code }}</div>
            </div>

            <!-- Account Name -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-sm font-medium text-gray-500">ຊື່ບັນຊີ</div>
                <div class="col-span-2 text-sm text-gray-900">{{ $chartOfAccount->account_name }}</div>
            </div>

            <!-- Back Button -->
            <div class="pt-6 border-t border-gray-200">
                <a href="{{ route('admin.chart-of-accounts.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    ກັບໄປຫາລາຍການ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
