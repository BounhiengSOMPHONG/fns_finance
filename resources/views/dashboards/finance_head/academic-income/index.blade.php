@extends('layouts.admin')

@section('title', 'ປະເມີນລາຍຮັບວິຊາການ')
@section('page-title', 'ປະເມີນລາຍຮັບວິຊາການ')

@section('content')

{{-- Toolbar --}}
<div class="fns-toolbar">
    <span style="font-size:0.8rem; color:var(--fns-gray-400);">ທັງໝົດ {{ $plans->total() }} ແຜນ</span>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.academic-income.create') }}" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            ສ້າງແຜນໃໝ່
        </a>
    </div>
</div>

{{-- Table --}}
<div class="fns-table-wrap">
    <table class="fns-table">
        <thead>
            <tr>
                <th style="width:3rem; text-align:center;">#</th>
                <th>ສົກປີງົບປະມານ</th>
                <th>ຜູ້ສ້າງ</th>
                <th>ວັນທີສ້າງ</th>
                <th>ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td style="text-align:center; font-size:0.75rem; color:var(--fns-gray-400);">{{ $plans->firstItem() + $loop->index }}</td>
                <td>
                    <span style="font-family:'Cinzel',serif; font-weight:700; font-size:1rem; color:var(--fns-navy); letter-spacing:0.04em;">
                        {{ $plan->fiscal_year }}
                    </span>
                </td>
                <td style="font-size:0.83rem; color:#374151;">{{ $plan->creator?->full_name ?? '—' }}</td>
                <td style="font-size:0.82rem; color:var(--fns-gray-400); font-variant-numeric:tabular-nums;">{{ $plan->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display:flex; gap:0.35rem; flex-wrap:wrap; align-items:center;">
                        <a href="{{ route('head_of_finance.academic-income.evaluate', $plan) }}" class="fns-btn fns-btn-sm fns-btn-primary">ປ້ອນຂໍ້ມູນ</a>
                        <form method="POST" action="{{ route('head_of_finance.academic-income.destroy', $plan) }}" style="display:inline;"
                            onsubmit="return confirm('ລຶບແຜນປີ {{ $plan->fiscal_year }} ບໍ?\nການລຶບບໍ່ສາມາດກູ້ຄືນໄດ້.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:3rem 1rem;">
                    <div style="font-size:2.5rem; margin-bottom:0.75rem; opacity:0.25;">📋</div>
                    <p style="color:var(--fns-gray-400); font-size:0.88rem; margin-bottom:0.5rem;">ຍັງບໍ່ມີແຜນລາຍຮັບ</p>
                    <a href="{{ route('head_of_finance.academic-income.create') }}" class="fns-btn fns-btn-primary" style="margin-top:0.5rem;">+ ສ້າງແຜນໃໝ່</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($plans->hasPages())
    <div style="padding:0.75rem 1rem; border-top:1px solid var(--fns-gray-200);">{{ $plans->links() }}</div>
    @endif
</div>

@endsection
