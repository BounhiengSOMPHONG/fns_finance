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
        <form id="filter-form" method="GET" action="{{ route('admin.chart-of-accounts.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ບັນຊີ..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onkeydown="if(event.key==='Enter'){this.form.submit();}">
            </div>
            <div class="w-64">
                <select name="parent_id" onchange="this.form.submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- ທຸກບັນຊີຫຼັກ --</option>
                    @foreach($parentAccounts as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent_id') != '' && request('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->account_code }} - {{ $parent->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
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
                    <th class="px-6 py-4">ບັນຊີຫຼັກ</th>
                    <th class="px-6 py-4 text-right">ການດໍາເນີນງານ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($chartOfAccounts as $account)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $account->id }}</td>
                        <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $account->account_code }}</td>
                        <td class="px-6 py-4 text-gray-900">{{ $account->account_name }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            @if($account->parent)
                                {{ $account->parent->account_code }} - {{ $account->parent->account_name }}
                            @else
                                -
                            @endif
                        </td>
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
                                <button type="button" onclick="openDeleteModal('{{ route('admin.chart-of-accounts.destroy', $account) }}', '{{ $account->account_code }} - {{ $account->account_name }}')" class="text-red-600 hover:text-red-900" title="ລົບ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
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

{{-- ── Delete Confirmation Modal ──────────────────────────────────── --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm scale-95 transform transition-transform duration-300" id="deleteModalBody">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100/50 rounded-full ring-4 ring-red-50">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">ຢືນຢັນການລົບ</h3>
        <p class="text-sm text-center text-gray-600 mb-2">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບບັນຊີນີ້?</p>
        <p class="text-center mb-6">
            <span id="deleteItemName" class="inline-block px-3 py-1 bg-red-50 text-red-700 text-sm font-semibold rounded-md border border-red-100"></span>
        </p>
        <form id="deleteForm" method="POST" class="flex gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors focus:ring-4 focus:ring-gray-100">
                ຍົກເລີກ
            </button>
            <button type="submit"
                class="flex-1 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 font-semibold shadow-sm transition-colors focus:ring-4 focus:ring-red-200">
                ຢືນຢັນລົບ
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openDeleteModal(actionUrl, itemName) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteItemName').textContent = itemName;
        const m = document.getElementById('deleteModal');
        const b = document.getElementById('deleteModalBody');
        m.style.display = 'flex';
        m.classList.remove('hidden');
        m.classList.add('flex');
        setTimeout(() => { b.classList.remove('scale-95'); b.classList.add('scale-100'); }, 10);
    }

    function closeDeleteModal() {
        const m = document.getElementById('deleteModal');
        const b = document.getElementById('deleteModalBody');
        b.classList.remove('scale-100'); b.classList.add('scale-95');
        setTimeout(() => { m.style.display = 'none'; m.classList.add('hidden'); m.classList.remove('flex'); }, 150);
    }

    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endpush
@endsection
