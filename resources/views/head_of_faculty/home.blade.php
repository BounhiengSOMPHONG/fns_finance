@extends('layouts.admin')

@section('title', 'Dashboard ຫົວໜ້າຄະນະ')
@section('page-title', 'Dashboard ຫົວໜ້າຄະນະ')

@section('content')

    {{-- ── Welcome Banner ──────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl text-white p-8 mb-8"
        style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 50%, #2563eb 100%);">
        <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full" style="background: rgba(255,255,255,0.08); filter: blur(40px);"></div>
        <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full" style="background: rgba(255,255,255,0.05); filter: blur(60px);"></div>
        <div class="relative flex items-center justify-between" style="z-index: 2;">
            <div>
                <h1 class="text-2xl font-bold mb-1">🎓 ສະບາຍດີ, {{ auth()->user()->full_name }}</h1>
                <p style="color: rgba(255,255,255,0.8);" class="text-sm">ພາບລວມການນຳໃຊ້ງົບປະມານ ປະຈຳປີ {{ $fiscalYear }}</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                <svg class="w-5 h-5" style="color: rgba(255,255,255,0.8);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ── 4 Metric Cards ──────────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">

        {{-- 1. ຍອດງົບປະມານ --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div style="height: 4px; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 12px rgba(59,130,246,0.3);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400" style="letter-spacing: 0.05em; text-transform: uppercase;">ຍອດງົບປະມານ</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900" style="font-variant-numeric: tabular-nums;">{{ number_format($totalBudget, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2">ແຜນງົບປະມານທີ່ອະນຸມັດ (ລ້ານກີບ)</p>
            </div>
        </div>

        {{-- 2. ຍອດຜູກພັນ --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div style="height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400" style="letter-spacing: 0.05em; text-transform: uppercase;">ຍອດຜູກພັນ</p>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: #d97706; font-variant-numeric: tabular-nums;">{{ number_format($totalCommitted, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2">ກຳລັງດຳເນີນການ / ຍັງບໍ່ທັນຈ່າຍ</p>
            </div>
        </div>

        {{-- 3. ຍອດໃຊ້ຈ່າຍຈິງ --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div style="height: 4px; background: linear-gradient(90deg, #ef4444, #f87171);"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(239,68,68,0.3);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400" style="letter-spacing: 0.05em; text-transform: uppercase;">ຍອດໃຊ້ຈ່າຍຈິງ</p>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: #dc2626; font-variant-numeric: tabular-nums;">{{ number_format($totalSpent, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2">ທຸລະກຳ + ການເບີກທີ່ cleared ແລ້ວ</p>
            </div>
        </div>

        {{-- 4. ຍອດຄົງເຫຼືອ --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div style="height: 4px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400" style="letter-spacing: 0.05em; text-transform: uppercase;">ຍອດຄົງເຫຼືອ</p>
                    </div>
                </div>
                <p class="text-2xl font-bold" style="color: {{ $totalRemaining >= 0 ? '#059669' : '#dc2626' }}; font-variant-numeric: tabular-nums;">{{ number_format($totalRemaining, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2">ງົບປະມານ - ຜູກພັນ - ໃຊ້ຈ່າຍຈິງ</p>
            </div>
        </div>
    </div>

    {{-- ── Budget Utilization Progress ──────────────────────────────── --}}
    @php
        $usedPercent = $totalBudget > 0 ? (($totalCommitted + $totalSpent) / $totalBudget) * 100 : 0;
        $spentPercent = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;
        $committedPercent = $totalBudget > 0 ? ($totalCommitted / $totalBudget) * 100 : 0;
    @endphp
    <div class="bg-white rounded-2xl p-6 mb-8" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" style="color: #6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            ອັດຕາການນຳໃຊ້ງົບປະມານ
        </h2>

        {{-- Stacked progress bar --}}
        <div class="relative rounded-full overflow-hidden mb-3" style="height: 20px; background: #f3f4f6;">
            <div class="absolute top-0 left-0 h-full rounded-l-full" style="width: {{ min($spentPercent, 100) }}%; background: linear-gradient(90deg, #ef4444, #f87171); transition: width 0.7s;"></div>
            <div class="absolute top-0 h-full" style="left: {{ min($spentPercent, 100) }}%; width: {{ min($committedPercent, 100 - min($spentPercent, 100)) }}%; background: linear-gradient(90deg, #fbbf24, #f59e0b); transition: width 0.7s;"></div>
        </div>

        <div class="flex flex-wrap gap-6 text-xs text-gray-600">
            <div class="flex items-center gap-2">
                <span class="inline-block rounded-full" style="width: 12px; height: 12px; background: #ef4444;"></span>
                ໃຊ້ຈ່າຍຈິງ: {{ number_format($spentPercent, 1) }}%
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block rounded-full" style="width: 12px; height: 12px; background: #f59e0b;"></span>
                ຜູກພັນ: {{ number_format($committedPercent, 1) }}%
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block rounded-full" style="width: 12px; height: 12px; background: #e5e7eb;"></span>
                ຄົງເຫຼືອ: {{ number_format(max(100 - $usedPercent, 0), 1) }}%
            </div>
        </div>
    </div>

    {{-- ── Bottom Grid: Pending Plans + Recent Activity ─────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">

        {{-- Pending Approval --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid #f3f4f6;">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    ແຜນງົບລໍຖ້າອະນຸມັດ
                </h3>
                @if($pendingPlans->count() > 0)
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background: #f3e8ff; color: #7c3aed;">{{ $pendingPlans->count() }}</span>
                @endif
            </div>
            <div>
                @forelse($pendingPlans as $plan)
                    <a href="{{ route('head_of_faculty.annual-budget.show', $plan) }}"
                        class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid #fafafa; transition: background 0.15s;"
                        onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='transparent'">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">ແຜນປະຈຳປີ {{ $plan->fiscal_year }}</p>
                            <p class="text-xs text-gray-400">ສະຖານະ: ລໍຖ້າອະນຸມັດຂັ້ນສຸດທ້າຍ</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center">
                        <svg class="w-10 h-10 mx-auto mb-2" style="color: #e5e7eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-400">ບໍ່ມີແຜນທີ່ລໍຖ້າອະນຸມັດ</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Advance Requests --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f4f6;">
            <div class="px-6 py-4" style="border-bottom: 1px solid #f3f4f6;">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ການເຄື່ອນໄຫວຫຼ້າສຸດ (ເບີກເງິນ)
                </h3>
            </div>
            <div>
                @forelse($recentAdvances as $adv)
                    @php
                        $statusStyle = match($adv->status) {
                            'pending'  => 'background: #fef3c7; color: #92400e;',
                            'approved' => 'background: #dbeafe; color: #1e40af;',
                            'cleared'  => 'background: #d1fae5; color: #065f46;',
                            'rejected' => 'background: #fee2e2; color: #991b1b;',
                            default    => 'background: #f3f4f6; color: #374151;',
                        };
                        $statusLabel = match($adv->status) {
                            'pending'  => 'ລໍຖ້າ',
                            'approved' => 'ອະນຸມັດ',
                            'cleared'  => 'ສຳເລັດ',
                            'rejected' => 'ປະຕິເສດ',
                            default    => $adv->status,
                        };
                    @endphp
                    <div class="px-6 py-4" style="border-bottom: 1px solid #fafafa; transition: background 0.15s;"
                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-800">{{ $adv->description ?: 'ການເບີກເງິນ #' . $adv->id }}</p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span>{{ $adv->requester_name ?? '-' }} · {{ $adv->department_name ?? '-' }}</span>
                            <span class="text-gray-600" style="font-variant-numeric: tabular-nums; font-family: monospace;">{{ number_format($adv->requested_amount, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <svg class="w-10 h-10 mx-auto mb-2" style="color: #e5e7eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-sm text-gray-400">ຍັງບໍ່ມີການເຄື່ອນໄຫວ</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
