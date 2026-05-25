@extends('layouts.admin')

@section('title', 'ລາຄາຄ່າໜ່ວຍກິດ')
@section('page-title', 'ການຕັ້ງລາຄາຄ່າໜ່ວຍກິດ')

@section('content')

<div class="fns-toolbar">
    <div class="fns-toolbar-left">
        <form method="GET" style="display:flex; gap:0.5rem;">
            <select name="level" class="fns-input" style="width:170px;">
                <option value="">ທຸກລະດັບ</option>
                <option value="bachelor" @selected(request('level')==='bachelor')>ປ.ຕີ</option>
                <option value="master" @selected(request('level')==='master')>ປ.ໂທ</option>
                <option value="phd" @selected(request('level')==='phd')>ປ.ເອກ</option>
            </select>
            <button type="submit" class="fns-btn fns-btn-secondary">ຄົ້ນຫາ</button>
            @if(request('level'))
                <a href="{{ route('head_of_finance.settings.credit-unit-price.index') }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
            @endif
        </form>
    </div>
</div>

<div class="fns-table-wrap">
    <table class="fns-table">
        <thead>
            <tr>
                <th style="width:3rem; text-align:center;">#</th>
                <th>ລະດັບ</th>
                <th class="col-num">ລາຄາຕໍ່ໜ່ວຍກິດ (ກີບ)</th>
                <th>ເລກທີເອກະສານ</th>
                <th class="col-num" style="width:8rem;">ປີທີ່ເລີ່ມໃຊ້</th>
                <th style="width:9rem;">ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $s)
            <tr>
                <td style="text-align:center; font-size:0.75rem; color:var(--fns-gray-400);">{{ $settings->firstItem() + $loop->index }}</td>
                <td>
                    <span class="fns-badge {{ $s->level === 'bachelor' ? 'fns-badge-blue' : ($s->level === 'master' ? 'fns-badge-green' : 'fns-badge-purple') }}">
                        {{ \App\Models\CreditUnitPriceSetting::levelLabel($s->level) }}
                    </span>
                </td>
                <td class="col-num">
                    <span style="font-weight:700; color:var(--fns-navy); font-variant-numeric:tabular-nums;">{{ number_format($s->credit_unit_price, 0) }}</span>
                    <span style="font-size:0.75rem; color:var(--fns-gray-400);"> ກີບ</span>
                </td>
                <td style="font-size:0.83rem; color:var(--fns-gray-600);">{{ $s->gov_doc_id ?? '—' }}</td>
                <td class="col-num" style="font-size:0.85rem; font-weight:600;">{{ $s->start_year }}</td>
                <td>
                    <a href="{{ route('head_of_finance.settings.credit-unit-price.edit', $s) }}" class="fns-btn fns-btn-sm fns-btn-secondary">ແກ້ໄຂ</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:2.5rem; color:var(--fns-gray-400);">ບໍ່ມີຂໍ້ມູນ</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($settings->hasPages())
    <div style="padding:0.75rem 1rem; border-top:1px solid var(--fns-gray-200);">{{ $settings->links() }}</div>
    @endif
</div>

@endsection
