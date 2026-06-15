@extends('layouts.admin')

@section('title', 'Admin')
@section('page-title', 'ຈັດການລະບົບ')

@section('content')
@php
    $cards = [
        [
            'title' => 'ຜູ້ໃຊ້',
            'value' => number_format($userCount),
            'detail' => 'ໃຊ້ງານ ' . number_format($activeUserCount) . ' ບັນຊີ',
            'route' => route('admin.users.index'),
            'action' => 'ຈັດການຜູ້ໃຊ້',
            'tone' => 'blue',
        ],
        [
            'title' => 'ບົດບາດ',
            'value' => number_format($roleCount),
            'detail' => 'ກຳນົດສິດການເຂົ້າໃຊ້',
            'route' => route('admin.roles.index'),
            'action' => 'ຈັດການບົດບາດ',
            'tone' => 'emerald',
        ],
        [
            'title' => 'ພະແນກ',
            'value' => number_format($departmentCount),
            'detail' => 'ຜູກຜູ້ໃຊ້ກັບໜ່ວຍງານ',
            'route' => route('admin.departments.index'),
            'action' => 'ຈັດການພະແນກ',
            'tone' => 'amber',
        ],
        [
            'title' => 'ຜັງບັນຊີ',
            'value' => number_format($accountCount),
            'detail' => 'ລະຫັດບັນຊີທີ່ໃຊ້ໃນແຜນງົບ',
            'route' => route('admin.chart-of-accounts.index'),
            'action' => 'ຈັດການຜັງບັນຊີ',
            'tone' => 'slate',
        ],
    ];

    $toneClasses = [
        'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'slate' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
@endphp

<div class="space-y-6">
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Admin</p>
                <h2 class="mt-1 text-xl font-semibold text-gray-900">
                    {{ auth()->user()->full_name ?? auth()->user()->username }} ເຂົ້າສູ່ໜ້າຈັດການລະບົບ
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-gray-600">
                    ເລືອກວຽກທີ່ຕ້ອງການຈາກບັດດ້ານລຸ່ມ. ໜ້ານີ້ໃຊ້ສຳລັບຈັດການຜູ້ໃຊ້, ບົດບາດ, ພະແນກ ແລະ ຜັງບັນຊີ.
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                ເພີ່ມຜູ້ໃຊ້
            </a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($cards as $card)
            <a href="{{ $card['route'] }}"
               class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $card['title'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-gray-950">{{ $card['value'] }}</p>
                    </div>
                    <span class="rounded-lg border px-3 py-1 text-xs font-semibold {{ $toneClasses[$card['tone']] }}">
                        {{ $card['action'] }}
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-600">{{ $card['detail'] }}</p>
                <p class="mt-5 text-sm font-semibold text-blue-700 group-hover:text-blue-800">
                    ເປີດໜ້ານີ້
                </p>
            </a>
        @endforeach
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">ທາງລັດທີ່ໃຊ້ບ່ອຍ</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                ລາຍຊື່ຜູ້ໃຊ້
            </a>
            <a href="{{ route('admin.roles.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                ສິດ ແລະ ບົດບາດ
            </a>
            <a href="{{ route('admin.departments.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                ຂໍ້ມູນພະແນກ
            </a>
            <a href="{{ route('admin.chart-of-accounts.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                ຜັງບັນຊີ
            </a>
        </div>
    </section>
</div>
@endsection
