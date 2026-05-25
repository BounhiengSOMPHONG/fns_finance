@extends('layouts.admin')

@section('title', 'ແກ້ໄຂພະແນກ')
@section('page-title', 'ແກ້ໄຂຂໍ້ມູນພະແນກ')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">ຂໍ້ມູນພະແນກ</h2>
        </div>

        <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Department Name -->
            <div>
                <label for="department_name" class="block text-sm font-medium text-gray-700 mb-2">
                    ຊື່ພະແນກ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="department_name" id="department_name" value="{{ old('department_name', $department->department_name) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_name') border-red-500 @enderror"
                    placeholder="ປ້ອນຊື່ພະແນກ">
                @error('department_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department Type -->
            <div>
                <label for="department_type" class="block text-sm font-medium text-gray-700 mb-2">
                    ປະເພດພະແນກ <span class="text-red-500">*</span>
                </label>
                <input type="text" name="department_type" id="department_type" value="{{ old('department_type', $department->department_type) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_type') border-red-500 @enderror"
                    placeholder="ປ້ອນປະເພດພະແນກ">
                @error('department_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    ຍົກເລີກ
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    ບັນທຶກ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
