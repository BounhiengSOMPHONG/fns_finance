@extends('layouts.admin')

@section('title', 'ສ້າງແຜນງົບປະມານ')
@section('page-title', 'ສ້າງແຜນງົບປະມານໃໝ່')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">ຂໍ້ມູນແຜນງົບປະມານ</h2>

        <form action="{{ route('head_of_finance.annual-budget.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ສົກປີ (ຕົວຢ່າງ: 2026) <span
                        class="text-red-500">*</span></label>
                <input type="number" name="fiscal_year" value="{{ old('fiscal_year', now()->year + 1) }}" min="2000"
                    max="9999"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('fiscal_year') border-red-400 @enderror">
                @error('fiscal_year')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    ສ້າງແຜນ
                </button>
                <a href="{{ route('head_of_finance.annual-budget.index') }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                    ຍົກເລີກ
                </a>
            </div>
        </form>
    </div>
@endsection