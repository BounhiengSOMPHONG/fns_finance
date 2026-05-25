@extends('layouts.admin')

@section('title', 'ຄ່າລົງທະບຽນ')
@section('page-title', 'ການຕັ້ງຄ່າລົງທະບຽນ')

@section('content')

<div class="fns-toolbar">
    <span style="font-size:0.8rem; color:var(--fns-gray-400);">ຕັ້ງຄ່າຄ່າລົງທະບຽນ ສຳລັບ ນ/ສ ປີ 1 ແລະ ປີ 2–4</span>
</div>

@forelse($settings as $s)
@php $total = $s->total_rate; @endphp
<div class="fns-card" style="margin-bottom:1.25rem;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; flex-wrap:wrap; gap:0.75rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span class="fns-badge {{ $s->section_type === 'year2_4' ? 'fns-badge-blue' : 'fns-badge-green' }}" style="font-size:0.82rem; padding:0.3rem 0.85rem;">
                {{ \App\Models\RegistrationFeeSetting::sectionLabel($s->section_type) }}
            </span>
            <div>
                <span style="font-family:'Cinzel',serif; font-weight:700; color:var(--fns-navy); font-size:0.95rem;">{{ number_format($total, 0) }} ກີບ</span>
                <span style="font-size:0.78rem; color:var(--fns-gray-400); margin-left:0.5rem;">· ປີ {{ $s->start_year }}</span>
                @if($s->gov_doc_id)
                    <span style="font-size:0.78rem; color:var(--fns-gray-400); margin-left:0.35rem;">· {{ $s->gov_doc_id }}</span>
                @endif
            </div>
        </div>
        <div style="display:flex; gap:0.35rem;">
            <a href="{{ route('head_of_finance.settings.registration-fee.edit', $s) }}" class="fns-btn fns-btn-sm fns-btn-secondary">ແກ້ໄຂ</a>
        </div>
    </div>
    <table class="fns-table" style="border:1px solid var(--fns-gray-200); border-radius:8px; overflow:hidden;">
        <thead>
            <tr>
                <th style="width:3rem; text-align:center;">#</th>
                <th>ລາຍການ</th>
                <th class="col-num" style="width:12rem;">ຈຳນວນ (ກີບ)</th>
                <th class="col-num" style="width:8rem;">% ມຊ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($s->items as $item)
            <tr>
                <td style="text-align:center; font-size:0.75rem; color:var(--fns-gray-400);">{{ $loop->iteration }}</td>
                <td style="font-size:0.85rem;">{{ $item->name }}</td>
                <td class="col-num" style="font-weight:600; color:var(--fns-navy);">{{ number_format($item->amount, 0) }}</td>
                <td class="col-num" style="font-size:0.83rem; color:var(--fns-gray-600);">{{ number_format($item->nuol_pct * 100, 2) }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">ລວມ</td>
                <td class="col-num" style="color:var(--fns-navy); font-size:0.95rem;">{{ number_format($total, 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@empty
<div class="fns-card" style="text-align:center; padding:2.5rem;">
    <div style="font-size:2rem; opacity:0.2; margin-bottom:0.75rem;">💰</div>
    <p style="color:var(--fns-gray-400); font-size:0.88rem;">ຍັງບໍ່ມີຂໍ້ມູນ</p>
</div>
@endforelse

@endsection
