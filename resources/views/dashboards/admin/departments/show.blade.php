@extends('layouts.admin')

@section('title', 'ລາຍລະອຽດພະແນກ')
@section('page-title', 'ລາຍລະອຽດພະແນກ')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">ຂໍ້ມູນພະແນກ</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.departments.edit', $department) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    ແກ້ໄຂ
                </a>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Department Name -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-sm font-medium text-gray-500">ຊື່ພະແນກ</div>
                <div class="col-span-2 text-sm text-gray-900">{{ $department->department_name }}</div>
            </div>

            <!-- Department Type -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-sm font-medium text-gray-500">ປະເພດ</div>
                <div class="col-span-2">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        {{ $department->department_type }}
                    </span>
                </div>
            </div>

            <!-- Users Count -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-sm font-medium text-gray-500">ຈຳນວນຜູ້ໃຊ້</div>
                <div class="col-span-2">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ $department->users->count() }} ຄົນ
                    </span>
                </div>
            </div>

            <!-- Users List -->
            @if ($department->users->count() > 0)
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">ຜູ້ໃຊ້ໃນພະແນກນີ້</h3>
                    <div class="space-y-2">
                        @foreach ($department->users as $user)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->username }} - {{ $user->role->role_name }}</p>
                                </div>
                                @if ($user->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">ໃຊ້ງານ</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ບໍ່ໃຊ້ງານ</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Back Button -->
            <div class="pt-6 border-t border-gray-200">
                <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    ກັບໄປລາຍການ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
