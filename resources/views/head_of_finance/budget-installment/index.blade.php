@extends('layouts.admin')

@section('title', 'ແຜນງວດງົບປະມານ')
@section('page-title', 'ແຜນງວດງົບປະມານ')

@section('content')
    <div class="bg-white rounded-lg shadow-sm w-full">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">ລາຍການແຜນງົບປະມານທີ່ອະນຸມັດແລ້ວ (ສຳລັບຈັດສັນງວດ)</h2>
            <p class="text-sm text-gray-500 mt-1">ເລືອກແຜນງົບປະມານປະຈຳປີເພື່ອແບ່ງງົບປະມານເປັນແຜນງວດ 1 ແລະ ແຜນງວດ 2</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">ສົກປີ</th>
                        <th class="px-6 py-4">ສະຖານະ</th>
                        <th class="px-6 py-4 text-right">ການດຳເນີນງານ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($plans as $plan)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $plan->fiscal_year }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">ອະນຸມັດແລ້ວ</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('head_of_finance.budget-installment.show', $plan) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    ຈັດສັນແຜນງວດ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                ຍັງບໍ່ມີແຜນງົບປະມານທີ່ຖືກອະນຸມັດ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
