@extends('layouts.admin')

@section('title', 'ອັດຕາເປີເຊັນ ມຊ')
@section('page-title', 'ຕັ້ງຄ່າຈຳນວນເປີເຊັນ ມຊ (%)')

@section('content')

@php
    $activeBachelor  = $settings->where('level', 'bachelor')->first();
    $activeMasterPhd = $settings->where('level', 'master_phd')->first();
@endphp

{{-- Current active rates --}}
<div class="fns-stats-row" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
    <div class="fns-stat-card">
        <div class="fns-stat-bar" style="background:#3b82f6;"></div>
        <div class="fns-stat-label">ອັດຕາປັດຈຸບັນ — ປ.ຕີ</div>
        <div class="fns-stat-value" style="color:#1d4ed8;">
            {{ $activeBachelor ? number_format($activeBachelor->percentage * 100, 2).'%' : '—' }}
        </div>
        @if($activeBachelor)
            <div style="font-size:0.72rem; color:var(--fns-gray-400); margin-top:0.35rem;">ປີ {{ $activeBachelor->start_year }}</div>
        @else
            <div style="font-size:0.72rem; color:#d97706; margin-top:0.35rem;">ຍັງບໍ່ມີຂໍ້ມູນ</div>
        @endif
    </div>
    <div class="fns-stat-card">
        <div class="fns-stat-bar" style="background:#10b981;"></div>
        <div class="fns-stat-label">ອັດຕາປັດຈຸບັນ — ປ.ໂທ / ປ.ເອກ</div>
        <div class="fns-stat-value" style="color:#065f46;">
            {{ $activeMasterPhd ? number_format($activeMasterPhd->percentage * 100, 2).'%' : '—' }}
        </div>
        @if($activeMasterPhd)
            <div style="font-size:0.72rem; color:var(--fns-gray-400); margin-top:0.35rem;">ປີ {{ $activeMasterPhd->start_year }}</div>
        @else
            <div style="font-size:0.72rem; color:#d97706; margin-top:0.35rem;">ຍັງບໍ່ມີຂໍ້ມູນ</div>
        @endif
    </div>
</div>

<div class="fns-toolbar">
    <span style="font-size:0.8rem; color:var(--fns-gray-400);">ປະຫວັດການຕັ້ງຄ່າ</span>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.settings.nuol-pct.create') }}" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            ເພີ່ມອັດຕາໃໝ່
        </a>
    </div>
</div>

<div class="fns-table-wrap">
    <table class="fns-table">
        <thead>
            <tr>
                <th style="width:3rem; text-align:center;">#</th>
                <th>ລະດັບ</th>
                <th class="col-num">ອັດຕາ (%)</th>
                <th>ເລກທີເອກະສານ</th>
                <th class="col-num" style="width:8rem;">ປີທີ່ເລີ່ມໃຊ້</th>
                <th style="width:9rem;">ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $s)
            <tr>
                <td style="text-align:center; font-size:0.75rem; color:var(--fns-gray-400);">{{ $loop->iteration }}</td>
                <td>
                    <span class="fns-badge {{ $s->level === 'bachelor' ? 'fns-badge-blue' : 'fns-badge-green' }}">
                        {{ \App\Models\NuolPctSetting::levelLabel($s->level) }}
                    </span>
                </td>
                <td class="col-num">
                    <span style="font-family:'Cinzel',serif; font-size:0.95rem; font-weight:700; color:var(--fns-navy);">{{ number_format($s->percentage * 100, 2) }}%</span>
                </td>
                <td style="font-size:0.83rem; color:var(--fns-gray-600);">{{ $s->gov_doc_id ?? '—' }}</td>
                <td class="col-num" style="font-size:0.85rem; font-weight:600;">{{ $s->start_year }}</td>
                <td>
                    <div style="display:flex; gap:0.35rem;">
                        <a href="{{ route('head_of_finance.settings.nuol-pct.edit', $s) }}" class="fns-btn fns-btn-sm fns-btn-secondary">ແກ້ໄຂ</a>
                        <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.destroy', $s) }}" style="display:inline;"
                            onsubmit="return confirm('ລຶບລາຍການນີ້ບໍ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:2.5rem; color:var(--fns-gray-400);">
                    ຍັງບໍ່ມີຂໍ້ມູນ — ກະລຸນາເພີ່ມອັດຕາ ມຊ
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
