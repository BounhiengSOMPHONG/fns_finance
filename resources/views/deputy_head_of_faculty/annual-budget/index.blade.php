@extends('layouts.admin')

@section('title', 'ລາຍການແຜນງົບປະມານ')
@section('page-title', 'ລາຍການແຜນງົບປະມານທີ່ລໍຖ້າການກວດສອບ')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">ລາຍການແຜນງົບປະມານປະຈຳປີ</h2>
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
                                        'DRAFT' => ['label' => 'ຮ່າງແຜນ', 'class' => 'bg-gray-100 text-gray-700'],
                                        'PENDING_REVIEW' => ['label' => 'ລໍຖ້າການກວດສອບ (ພາກສ່ວນ)', 'class' => 'bg-yellow-100 text-yellow-700'],
                                        'MODIFYING' => ['label' => 'ກຳລັງແກ້ໄຂ', 'class' => 'bg-orange-100 text-orange-700'],
                                        'PENDING_FINAL_APPROVAL' => ['label' => 'ລໍຖ້າການອະນຸມັດ (ຄະນະບໍດີ)', 'class' => 'bg-blue-100 text-blue-700'],
                                        'APPROVED' => ['label' => 'ອະນຸມັດແລ້ວ', 'class' => 'bg-green-100 text-green-700'],
                                    ];
                                    $s = $statusMap[$plan->status] ?? ['label' => $plan->status, 'class' => 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('deputy_head_of_faculty.annual-budget.show', $plan) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md text-xs font-medium transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    ກວດສອບ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                ຍັງບໍ່ມີແຜນງົບປະມານທີ່ຖືກສົ່ງມາ.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
