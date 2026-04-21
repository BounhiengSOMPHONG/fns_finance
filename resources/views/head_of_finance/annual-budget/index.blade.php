@extends('layouts.admin')

@section('title', 'ແຜນງົບປະມານປະຈຳປີ')
@section('page-title', 'ແຜນງົບປະມານປະຈຳປີ')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-lg font-semibold text-gray-800">ລາຍການແຜນງົບປະມານ</h2>
            <a href="{{ route('head_of_finance.annual-budget.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ສ້າງແຜນໃໝ່
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">ສົກປີ</th>
                        <th class="px-6 py-4">ຈຳນວນລາຍການ</th>
                        <th class="px-6 py-4">ສະຖານະ</th>
                        <th class="px-6 py-4 text-right">ການດຳເນີນງານ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($plans as $plan)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $plan->fiscal_year }}</td>
                            <td class="px-6 py-4">{{ $plan->lineItems->count() }} ລາຍການ</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusMap = [
                                        'draft' => ['label' => 'ຮ່າງ', 'class' => 'bg-gray-100 text-gray-700'],
                                        'DRAFT' => ['label' => 'ຮ່າງ', 'class' => 'bg-gray-100 text-gray-700'],
                                        'PENDING_REVIEW' => ['label' => 'ລໍຖ້າຫົວໜ້າພາກສ່ວນ', 'class' => 'bg-yellow-100 text-yellow-700'],
                                        'PENDING_FINAL_APPROVAL' => ['label' => 'ລໍຖ້າຄະນະບໍດີ', 'class' => 'bg-blue-100 text-blue-700'],
                                        'APPROVED' => ['label' => 'ອະນຸມັດແລ້ວ', 'class' => 'bg-green-100 text-green-700'],
                                        'MODIFYING' => ['label' => 'ປັບປຸງ', 'class' => 'bg-red-100 text-red-700'],
                                    ];
                                    $s = $statusMap[$plan->status] ?? ['label' => $plan->status, 'class' => 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('head_of_finance.annual-budget.preview', $plan) }}"
                                        class="text-blue-600 hover:text-blue-900" title="ພາບລວມ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('head_of_finance.annual-budget.show', $plan) }}"
                                        class="text-yellow-600 hover:text-yellow-900" title="ແກ້ໄຂ">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button type="button" onclick="openDeleteModal('{{ route('head_of_finance.annual-budget.destroy', $plan) }}', 'ສົກປີ {{ $plan->fiscal_year }}')" 
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
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                ຍັງບໍ່ມີແຜນງົບປະມານ — <a href="{{ route('head_of_finance.annual-budget.create') }}"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 style="font-size:var(--font-size-lg);font-weight:600;color:var(--color-text-primary);margin-bottom:8px;">ຢືນຢັນການລຶບ</h3>
                <p style="font-size:var(--font-size-base);color:var(--color-text-secondary);margin-bottom:8px;">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລຶບແຜນນີ້?</p>
                <span id="deleteItemName" style="display:inline-block;padding:4px 12px;background:var(--color-danger-bg);color:var(--color-danger-text);font-size:var(--font-size-sm);font-weight:500;border-radius:var(--radius-md);border:1px solid var(--color-danger-border);"></span>
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
    function openDeleteModal(actionUrl, itemName) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteItemName').textContent = itemName;
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