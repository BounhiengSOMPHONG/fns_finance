@extends('layouts.admin')

@section('title', 'ລາຍຮັບວິຊາການ')
@section('page-title', 'ຮ່າງສັງລວມລາຍຮັບວິຊາການ')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-lg font-semibold text-gray-800">ລາຍການແຜນລາຍຮັບວິຊາການ</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('head_of_finance.academic_income.defaults') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    ຈັດການ Default
                </a>
                <a href="{{ route('head_of_finance.academic_income.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    ສ້າງແຜນໃໝ່
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">ສົກງົບປະມານ</th>
                        <th class="px-6 py-4">ຈຳນວນລາຍການ</th>
                        <th class="px-6 py-4">ສ້າງໂດຍ</th>
                        <th class="px-6 py-4">ສະຖານະ</th>
                        <th class="px-6 py-4 text-right">ການດຳເນີນງານ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($plans as $plan)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900">ສົກ {{ $plan->fiscal_year }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $plan->items->count() }} ລາຍການ</td>
                            <td class="px-6 py-4 text-gray-600">{{ $plan->creator?->full_name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($plan->status === 'APPROVED')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">ອະນຸມັດແລ້ວ</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">ຮ່າງ</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('head_of_finance.academic_income.summary', $plan) }}"
                                        class="text-blue-600 hover:text-blue-900" title="ເບິ່ງສັງລວມ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('head_of_finance.academic_income.show', $plan) }}"
                                        class="text-yellow-600 hover:text-yellow-900" title="ແກ້ໄຂ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button"
                                        onclick="openDeleteModal('{{ route('head_of_finance.academic_income.destroy', $plan) }}', 'ສົກ {{ $plan->fiscal_year }}')"
                                        class="text-red-600 hover:text-red-900" title="ລຶບ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                ຍັງບໍ່ມີແຜນລາຍຮັບວິຊາການ —
                                <a href="{{ route('head_of_finance.academic_income.create') }}"
                                    class="text-blue-600 underline">ສ້າງໃໝ່</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="modal-overlay" style="display:none;">
        <div class="modal" style="max-width:400px;">
            <div class="modal-body" style="text-align:center; padding:28px 24px;">
                <div style="width:48px;height:48px;border-radius:50%;background:var(--color-danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg style="width:24px;height:24px;color:#DC2626" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 style="font-size:var(--font-size-lg);font-weight:600;color:var(--color-text-primary);margin-bottom:8px;">ຢືນຢັນການລຶບ</h3>
                <p style="font-size:var(--font-size-base);color:var(--color-text-secondary);margin-bottom:8px;">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລຶບແຜນລາຍຮັບນີ້?</p>
                <span id="deleteItemName"
                    style="display:inline-block;padding:4px 12px;background:var(--color-danger-bg);color:var(--color-danger-text);font-size:var(--font-size-sm);font-weight:500;border-radius:var(--radius-md);border:1px solid var(--color-danger-border);"></span>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">ຍົກເລີກ</button>
                <form id="deleteForm" method="POST" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">ຢືນຢັນລຶບ</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openDeleteModal(url, name) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>
    @endpush
@endsection
