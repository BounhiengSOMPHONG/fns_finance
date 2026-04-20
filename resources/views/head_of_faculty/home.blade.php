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

<div style="display:flex; flex-direction:column; gap:24px;">
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-label">ພາບລວມງົບປະມານ</div>
            <div class="welcome-name">ສະບາຍດີ, {{ auth()->user()->full_name }}</div>
            <div class="welcome-desc">ພາບລວມການນຳໃຊ້ງົບປະມານ ປະຈຳປີ {{ $fiscalYear }}</div>
        </div>
        <div class="welcome-icon">
            <svg style="width:24px;height:24px;color:#2D55C8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
    </div>

    {{-- Stat Cards Grid --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">ຍອດງົບປະມານ</div>
            <div class="stat-value" style="color:var(--color-primary); font-size:20px;">{{ number_format($totalBudget, 2) }}</div>
            <div class="stat-sub">ແຜນງົບປະມານທີ່ອະນຸມັດ</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">ຍອດຜູກພັນ</div>
            <div class="stat-value" style="color:#92400E; font-size:20px;">{{ number_format($totalCommitted, 2) }}</div>
            <div class="stat-sub">ກຳລັງດຳເນີນການ / ຍັງບໍ່ທັນຈ່າຍ</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">ຍອດໃຊ້ຈ່າຍຈິງ</div>
            <div class="stat-value" style="color:#991B1B; font-size:20px;">{{ number_format($totalSpent, 2) }}</div>
            <div class="stat-sub">ທຸລະກຳ + ການເບີກທີ່ cleared ແລ້ວ</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">ຍອດຄົງເຫຼືອ</div>
            <div class="stat-value" style="color:{{ $totalRemaining >= 0 ? '#065F46' : '#991B1B' }}; font-size:20px;">{{ number_format($totalRemaining, 2) }}</div>
            <div class="stat-sub">ງົບປະມານ - ຜູກພັນ - ໃຊ້ຈ່າຍຈິງ</div>
        </div>
    </div>

    {{-- Budget Usage Progress --}}
    <div class="form-section">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <svg style="width:16px;height:16px;color:var(--color-primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary);">ອັດຕາການນຳໃຊ້ງົບປະມານ</span>
        </div>

        <div style="position:relative; height:20px; overflow:hidden; border-radius:20px; background:var(--color-bg-surface); margin-bottom:12px;">
            <div style="position:absolute; inset:0 auto 0 0; border-radius:20px 0 0 20px; background:#DC2626; transition:width 0.7s; width:{{ $spentBar }}%;"></div>
            <div style="position:absolute; inset:0 auto 0 0; background:#F59E0B; transition:all 0.7s; left:{{ $spentBar }}%; width:{{ $committedBar }}%;"></div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:20px; font-size:var(--font-size-sm); color:var(--color-text-secondary);">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="width:10px; height:10px; border-radius:50%; background:#DC2626; display:inline-block;"></span>
                ໃຊ້ຈ່າຍຈິງ: {{ number_format($spentPercent, 1) }}%
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="width:10px; height:10px; border-radius:50%; background:#F59E0B; display:inline-block;"></span>
                ຜູກພັນ: {{ number_format($committedPercent, 1) }}%
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="width:10px; height:10px; border-radius:50%; background:var(--color-bg-surface); border:1px solid var(--color-border); display:inline-block;"></span>
                ຄົງເຫຼືອ: {{ number_format($remainingPercent, 1) }}%
            </div>
        </div>
    </div>

    {{-- Pending Plans --}}
    <div class="table-wrapper">
        <div class="table-header">
            <span class="table-title" style="display:flex; align-items:center; gap:8px;">
                <svg style="width:16px;height:16px;color:var(--color-primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                ແຜນງົບລໍຖ້າອະນຸມັດ
            </span>
            @if ($pendingPlans->isNotEmpty())
                <span class="badge badge-primary">{{ $pendingPlans->count() }}</span>
            @endif
        </div>

        <div>
            @forelse($pendingPlans as $plan)
                <a href="{{ route('head_of_faculty.annual-budget.show', $plan) }}"
                    style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--color-border); text-decoration:none; transition:background 0.12s;"
                    onmouseover="this.style.background='var(--color-bg-hover)'" onmouseout="this.style.background='transparent'">
                    <div>
                        <p style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary); margin:0;">ແຜນປະຈຳປີ {{ $plan->fiscal_year }}</p>
                        <p style="font-size:var(--font-size-xs); color:var(--color-text-tertiary); margin-top:2px;">ສະຖານະ: ລໍຖ້າອະນຸມັດຂັ້ນສຸດທ້າຍ</p>
                    </div>
                    <svg style="width:16px;height:16px;color:var(--color-text-tertiary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="empty-title">ບໍ່ມີແຜນທີ່ລໍຖ້າອະນຸມັດ</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
