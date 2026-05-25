@extends('layouts.admin')

@section('title', 'ແຜນບັນຊີ')
@section('page-title', 'ຈັດການແຜນບັນຊີ')

@section('content')
<div class="bg-white rounded-lg shadow-sm">
    <!-- Header & Actions -->
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">ລາຍການບັນຊີ</h2>
        <a href="{{ route('admin.chart-of-accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            ເພີ່ມບັນຊີ
        </a>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('admin.chart-of-accounts.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ບັນຊີ..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                ຄົ້ນຫາ
            </button>
            <a href="{{ route('admin.chart-of-accounts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                ຄືນຄ່າເລີ່ມຕົ້ນ
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">ລະຫັດບັນຊີ</th>
                    <th class="px-6 py-4">ຊື່ບັນຊີ</th>
                    <th class="px-6 py-4 text-right">ການດໍາເນີນງານ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($chartOfAccounts as $account)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $account->id }}</td>
                        <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $account->account_code }}</td>
                        <td class="px-6 py-4 text-gray-900">{{ $account->account_name }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.chart-of-accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900" title="ເບິ່ງລາຍລະອຽດ">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.chart-of-accounts.edit', $account) }}" class="text-yellow-600 hover:text-yellow-900" title="ແກ້ໄຂ">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.chart-of-accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບບັນຊີນີ້?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="ລົບ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            ບໍ່ພົບຂໍ້ມູນບັນຊີ
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($chartOfAccounts->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $chartOfAccounts->links() }}
        </div>
    @endif
</div>
@endsection
