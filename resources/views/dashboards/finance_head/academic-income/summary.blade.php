@extends('layouts.admin')

@section('title', 'ຮ່າງສັງລວມລາຍຮັບ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ຮ່າງສັງລວມລາຍຮັບວິຊາການ ປີ ' . $academicIncome->fiscal_year)

@section('content')

{{-- Toolbar --}}
<div class="fns-toolbar">
    <div class="fns-toolbar-left">
        <a href="{{ route('head_of_finance.academic-income.show', $academicIncome) }}" class="fns-btn fns-btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
            ກັບໄປ
        </a>
    </div>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.academic-income.evaluate', $academicIncome) }}" class="fns-btn fns-btn-secondary">ແກ້ໄຂຂໍ້ມູນ</a>
        <a href="{{ route('head_of_finance.academic-income.print', $academicIncome) }}" target="_blank" class="fns-btn fns-btn-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.49 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.749-.107 1.126-.153V2.75Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5H5.915l-.385 2.5h8.94l-.385-2.5H6.608Zm7.117-7.5H6.275a40.015 40.015 0 0 0-1.398.098A.712.712 0 0 0 4.25 5.796v6.954c0 .414.336.75.75.75h.241l.305-1.984a1.75 1.75 0 0 1 1.73-1.516h5.448a1.75 1.75 0 0 1 1.73 1.516l.305 1.984H15.75a.75.75 0 0 0 .75-.75V5.796a.712.712 0 0 0-.626-.698A40.15 40.15 0 0 0 13.725 5Z" clip-rule="evenodd"/></svg>
            ພິມ / PDF
        </a>
    </div>
</div>

{{-- Plan info header --}}
<div class="fns-card" style="margin-bottom:1.5rem; background:linear-gradient(135deg,var(--fns-navy) 0%,var(--fns-navy-mid) 100%); color:#fff; border:none;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
            <p style="font-size:0.68rem; font-weight:700; color:rgba(255,255,255,0.5); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:0.3rem;">ຮ່າງສັງລວມລາຍຮັບວິຊາການ</p>
            <p style="font-family:'Cinzel',serif; font-size:1.6rem; font-weight:700; color:var(--fns-gold-light); line-height:1;">ສົກປີ {{ $academicIncome->fiscal_year }}</p>
        </div>
    </div>
</div>

@php
    $grandTotal = 0;
    $sectionLabels = [
        '1.1' => '1.1  ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 2–4 ແລະ ປ.ໂທ',
        '1.2' => '1.2  ລາຍຮັບຄ່າລົງທະບຽນ ນ/ສ ປີ 2–4 ຂອງ ຄວທ',
        '1.3' => '1.3  ລາຍຮັບຄ່າໜ່ວຍກິດ ນ/ສ ປີ 1',
        '1.4' => '1.4  ຄ່າລົງທະບຽນ ນ/ສ ປີ 1 ຂອງ ຄວທ',
        '2.1' => '2.1  ລາຍຮັບ Item 3',
        '2.2' => '2.2  ລາຍຮັບ Item 4',
        '2.3' => '2.3  ລາຍຮັບ Item 5',
        '2.4' => '2.4  ລາຍຮັບ Item 6',
    ];
    $sectionColors = [
        '1.1' => '#3b82f6', '1.2' => '#10b981', '1.3' => '#f59e0b', '1.4' => '#8b5cf6',
        '2.1' => '#6366f1', '2.2' => '#0ea5e9', '2.3' => '#14b8a6', '2.4' => '#f97316',
    ];
@endphp

@foreach($grouped as $sectionCode => $items)
@php
    $secTotal  = $items->sum('total_income');
    $grandTotal += $secTotal;
    $color     = $sectionColors[$sectionCode] ?? '#6b7280';
@endphp
<div class="fns-card" style="margin-bottom:1.25rem; border-top:3px solid {{ $color }};">
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
        <div style="width:2.2rem; height:2.2rem; border-radius:8px; background:{{ $color }}1a; color:{{ $color }}; display:flex; align-items:center; justify-content:center; font-family:'Cinzel',serif; font-size:0.68rem; font-weight:700; flex-shrink:0;">
            {{ $sectionCode }}
        </div>
        <h3 style="font-weight:700; font-size:0.9rem; color:var(--fns-navy);">{{ $sectionLabels[$sectionCode] ?? $sectionCode }}</h3>
    </div>
    <table class="fns-table">
        <thead>
            <tr>
                <th style="width:5rem;">ຊັ້ນປີ</th>
                <th>ສາຂາວິຊາ / ລາຍການ</th>
                <th class="col-num" style="width:8rem;">ຈຳນວນ ນ/ສ</th>
                <th class="col-num" style="width:7rem;">% ມຊ</th>
                <th class="col-num" style="width:14rem;">ລວມລາຍຮັບ (ກີບ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="font-size:0.83rem; color:var(--fns-gray-600);">{{ $item->degreeProgram?->study_year ? 'ປີ '.$item->degreeProgram->study_year : '—' }}</td>
                <td style="font-size:0.85rem;">{{ $item->degreeProgram?->name ?? 'ລວມ (ທຸກສາຂາ)' }}</td>
                <td class="col-num" style="font-size:0.85rem;">{{ number_format($item->student_count) }}</td>
                <td class="col-num" style="font-size:0.83rem; color:var(--fns-gray-600);">{{ number_format($item->snap_nuol_pct * 100, 2) }}%</td>
                <td class="col-num" style="font-weight:600; color:var(--fns-navy);">{{ number_format($item->total_income, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right; color:var(--fns-navy);">ລວມໝວດ {{ $sectionCode }}</td>
                <td class="col-num" style="color:{{ $color }}; font-size:0.95rem;">{{ number_format($secTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endforeach

{{-- Grand total --}}
<div class="fns-grand-total">
    <div>
        <div class="fns-grand-total-label">ລວມລາຍຮັບວິຊາການທັງໝົດ</div>
        <div style="font-size:0.72rem; color:rgba(255,255,255,0.35); margin-top:0.1rem;">ໝວດ 1.1 + 1.2 + 1.3 + 1.4 + Items 3–6</div>
    </div>
    <div class="fns-grand-total-amount">{{ number_format($grandTotal, 2) }} <span style="font-size:0.85rem; color:rgba(255,255,255,0.5);">ກີບ</span></div>
</div>

@if($grouped->isEmpty())
<div class="fns-card" style="text-align:center; padding:2.5rem 1rem;">
    <div style="font-size:2.5rem; opacity:0.2; margin-bottom:0.75rem;">📊</div>
    <p style="color:var(--fns-gray-400); font-size:0.88rem; margin-bottom:1rem;">ຍັງບໍ່ມີຂໍ້ມູນການປະເມີນ</p>
    <a href="{{ route('head_of_finance.academic-income.evaluate', $academicIncome) }}" class="fns-btn fns-btn-primary">ປ້ອນຂໍ້ມູນ / ປະເມີນ</a>
</div>
@endif

@endsection
