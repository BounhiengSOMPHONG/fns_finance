@extends('layouts.admin')

@section('title', 'ແຜນລາຍຮັບວິຊາການ ປີ ' . $academicIncome->fiscal_year)
@section('page-title', 'ແຜນລາຍຮັບວິຊາການ')

@section('content')

{{-- Action toolbar --}}
<div class="fns-toolbar">
    <div class="fns-toolbar-left">
        <a href="{{ route('head_of_finance.academic-income.index') }}" class="fns-btn fns-btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
            ກັບໄປ
        </a>
    </div>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.academic-income.evaluate', $academicIncome) }}" class="fns-btn fns-btn-primary">ປ້ອນຂໍ້ມູນ / ປະເມີນ</a>
        <a href="{{ route('head_of_finance.academic-income.summary', $academicIncome) }}" class="fns-btn fns-btn-secondary">ເບິ່ງສັງລວມ</a>
    </div>
</div>

{{-- Plan metadata --}}
<div class="fns-card" style="margin-bottom:1.25rem;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1.25rem;">
        <div>
            <p style="font-size:0.68rem; font-weight:700; color:var(--fns-gray-400); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.35rem;">ສົກປີງົບປະມານ</p>
            <p style="font-family:'Cinzel',serif; font-size:1.5rem; font-weight:700; color:var(--fns-navy); line-height:1;">{{ $academicIncome->fiscal_year }}</p>
        </div>
        <div>
            <p style="font-size:0.68rem; font-weight:700; color:var(--fns-gray-400); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.35rem;">ຜູ້ສ້າງ</p>
            <p style="font-size:0.9rem; font-weight:500; color:#374151;">{{ $academicIncome->creator?->full_name ?? '—' }}</p>
        </div>
        <div>
            <p style="font-size:0.68rem; font-weight:700; color:var(--fns-gray-400); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.35rem;">ວັນທີສ້າງ</p>
            <p style="font-size:0.9rem; color:#374151;">{{ $academicIncome->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @if($academicIncome->notes)
        <div style="grid-column:1/-1;">
            <p style="font-size:0.68rem; font-weight:700; color:var(--fns-gray-400); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.35rem;">ໝາຍເຫດ</p>
            <p style="font-size:0.88rem; color:#374151; background:var(--fns-gray-100); padding:0.6rem 0.85rem; border-radius:8px; border-left:3px solid var(--fns-gray-200);">{{ $academicIncome->notes }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Items table --}}
@if($academicIncome->items->count())
@php $grandTotal = $academicIncome->items->sum('total_income'); @endphp
<div class="fns-table-wrap">
    <div style="padding:0.9rem 1rem; border-bottom:1px solid var(--fns-gray-200); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.82rem; font-weight:600; color:var(--fns-navy);">ລາຍການລາຍຮັບ</span>
        <span style="font-size:0.78rem; color:var(--fns-gray-400);">{{ $academicIncome->items->count() }} ລາຍການ</span>
    </div>
    <table class="fns-table">
        <thead>
            <tr>
                <th>ໝວດ</th>
                <th>ສາຂາວິຊາ / ລາຍການ</th>
                <th class="col-num">ນ/ສ</th>
                <th class="col-num">% ມຊ</th>
                <th class="col-num">ລວມລາຍຮັບ (ກີບ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($academicIncome->items->sortBy('section_code') as $item)
            <tr>
                <td>
                    <span style="font-family:'Cinzel',serif; font-weight:700; font-size:0.82rem; color:var(--fns-navy);">{{ $item->section_code }}</span>
                </td>
                <td style="font-size:0.85rem;">{{ $item->degreeProgram?->name ?? 'ລວມ (ທຸກສາຂາ)' }}</td>
                <td class="col-num" style="font-size:0.85rem;">{{ number_format($item->student_count) }}</td>
                <td class="col-num" style="font-size:0.83rem;">{{ number_format($item->snap_nuol_pct * 100, 2) }}%</td>
                <td class="col-num" style="font-weight:600; color:var(--fns-navy);">{{ number_format($item->total_income, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;">ລວມທັງໝົດ</td>
                <td class="col-num" style="color:var(--fns-navy); font-size:0.95rem;">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@else
<div class="fns-card" style="text-align:center; padding:2.5rem 1rem;">
    <div style="font-size:2.5rem; opacity:0.2; margin-bottom:0.75rem;">📊</div>
    <p style="color:var(--fns-gray-400); font-size:0.88rem; margin-bottom:1rem;">ຍັງບໍ່ມີຂໍ້ມູນການປະເມີນ</p>
    <a href="{{ route('head_of_finance.academic-income.evaluate', $academicIncome) }}" class="fns-btn fns-btn-primary">ປ້ອນຂໍ້ມູນ / ປະເມີນ</a>
</div>
@endif

@endsection
