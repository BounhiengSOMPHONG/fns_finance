@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າຄະນະ')
@section('page-title', 'Dashboard ຫົວໜ້າຄະນະ')

@section('content')
@php
    $usedPercent = $totalBudget > 0 ? (($totalCommitted + $totalSpent) / $totalBudget) * 100 : 0;
    $spentPercent = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;
    $committedPercent = $totalBudget > 0 ? ($totalCommitted / $totalBudget) * 100 : 0;

    $spentBar = min($spentPercent, 100);
    $committedBar = min($committedPercent, max(0, 100 - $spentBar));
    $remainingPercent = max(100 - $usedPercent, 0);
@endphp

<div class="space-y-8">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-600 p-6 text-white shadow-lg sm:p-8">
        <div class="absolute -top-14 -right-10 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-12 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">ສະບາຍດີ, {{ auth()->user()->full_name }}</h1>
                <p class="mt-1 text-sm text-white/80">ພາບລວມການນຳໃຊ້ງົບປະມານ ປະຈຳປີ {{ $fiscalYear }}</p>
            </div>

            <div class="hidden items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-sm font-medium backdrop-blur sm:inline-flex">
                <svg class="h-5 w-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </section>

    <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1.5 bg-gradient-to-r from-blue-500 to-sky-400"></div>
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 shadow-md shadow-blue-200">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ຍອດງົບປະມານ</p>
                </div>
                <p class="text-2xl font-bold tabular-nums text-slate-900">{{ number_format($totalBudget, 2) }}</p>
                <p class="mt-2 text-xs text-slate-400">ແຜນງົບປະມານທີ່ອະນຸມັດ</p>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1.5 bg-gradient-to-r from-amber-500 to-yellow-400"></div>
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 shadow-md shadow-amber-200">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ຍອດຜູກພັນ</p>
                </div>
                <p class="text-2xl font-bold tabular-nums text-amber-600">{{ number_format($totalCommitted, 2) }}</p>
                <p class="mt-2 text-xs text-slate-400">ກຳລັງດຳເນີນການ / ຍັງບໍ່ທັນຈ່າຍ</p>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1.5 bg-gradient-to-r from-rose-500 to-red-400"></div>
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-red-700 shadow-md shadow-rose-200">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ຍອດໃຊ້ຈ່າຍຈິງ</p>
                </div>
                <p class="text-2xl font-bold tabular-nums text-red-600">{{ number_format($totalSpent, 2) }}</p>
                <p class="mt-2 text-xs text-slate-400">ທຸລະກຳ + ການເບີກທີ່ cleared ແລ້ວ</p>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-green-400"></div>
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-md shadow-emerald-200">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ຍອດຄົງເຫຼືອ</p>
                </div>
                <p class="text-2xl font-bold tabular-nums {{ $totalRemaining >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ number_format($totalRemaining, 2) }}
                </p>
                <p class="mt-2 text-xs text-slate-400">ງົບປະມານ - ຜູກພັນ - ໃຊ້ຈ່າຍຈິງ</p>
            </div>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-700">
            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            ອັດຕາການນຳໃຊ້ງົບປະມານ
        </h2>

        <div class="relative mb-3 h-5 overflow-hidden rounded-full bg-slate-100">
            <div class="absolute inset-y-0 left-0 rounded-l-full bg-gradient-to-r from-rose-500 to-red-400 transition-all duration-700"
                style="width: {{ $spentBar }}%;"></div>
            <div class="absolute inset-y-0 bg-gradient-to-r from-amber-300 to-amber-500 transition-all duration-700"
                style="left: {{ $spentBar }}%; width: {{ $committedBar }}%;"></div>
        </div>

        <div class="flex flex-wrap gap-6 text-xs text-slate-600">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                ໃຊ້ຈ່າຍຈິງ: {{ number_format($spentPercent, 1) }}%
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                ຜູກພັນ: {{ number_format($committedPercent, 1) }}%
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-slate-300"></span>
                ຄົງເຫຼືອ: {{ number_format($remainingPercent, 1) }}%
            </div>
        </div>
    </section>

    <section class="grid gap-6">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700">
                    <svg class="h-5 w-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    ແຜນງົບລໍຖ້າອະນຸມັດ
                </h3>

                @if ($pendingPlans->isNotEmpty())
                    <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">
                        {{ $pendingPlans->count() }}
                    </span>
                @endif
            </header>

            <div>
                @forelse($pendingPlans as $plan)
                    <a href="{{ route('head_of_faculty.annual-budget.show', $plan) }}"
                        class="flex items-center justify-between border-b border-slate-100 px-6 py-4 transition hover:bg-violet-50 last:border-b-0">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">ແຜນປະຈຳປີ {{ $plan->fiscal_year }}</p>
                            <p class="text-xs text-slate-400">ສະຖານະ: ລໍຖ້າອະນຸມັດຂັ້ນສຸດທ້າຍ</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center">
                        <svg class="mx-auto mb-2 h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-400">ບໍ່ມີແຜນທີ່ລໍຖ້າອະນຸມັດ</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
