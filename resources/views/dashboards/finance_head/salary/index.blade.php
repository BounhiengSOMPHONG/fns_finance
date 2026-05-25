@extends('layouts.admin')

@section('title', 'ຕາຕະລາງເງິນເດືອນ')
@section('page-title', 'ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ')

@section('content')

{{-- Toolbar --}}
<div class="fns-toolbar">
    <span style="font-size:0.8rem; color:var(--fns-gray-400);">ທັງໝົດ {{ $plans->total() }} ແຜນ</span>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.salary.create') }}" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
            </svg>
            ສ້າງແຜນໃໝ່
        </a>
    </div>
</div>

{{-- Table --}}
<div class="fns-table-wrap">
    <table class="fns-table">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th>ເດືອນ / ສົກ</th>
                <th style="text-align:center;">ສະຖານະ</th>
                <th style="text-align:right;">ລວມຕໍ່ເດືອນ (ກີບ)</th>
                <th style="text-align:right;">ລວມ 12 ເດືອນ (ກີບ)</th>
                <th>ສ້າງໂດຍ</th>
                <th style="width:16%;">ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td class="c dim">{{ $plans->firstItem() + $loop->index }}</td>
                <td style="font-weight:600;">
                    ເດືອນ {{ $plan->monthLabel() }}
                </td>
                <td style="text-align:center;">
                    @if($plan->status === 'APPROVED')
                        <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;">ອະນຸມັດ</span>
                    @else
                        <span style="background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:999px;font-size:0.75rem;font-weight:600;">ຮ່າງ</span>
                    @endif
                </td>
                <td style="text-align:right;">{{ number_format($plan->monthlyTotal(), 0) }}</td>
                <td style="text-align:right;font-weight:600;">{{ number_format($plan->grandTotal(), 0) }}</td>
                <td>{{ $plan->creator?->name ?? '-' }}</td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="{{ route('head_of_finance.salary.manage', $plan) }}" class="fns-btn fns-btn-sm fns-btn-primary">ຈັດການ</a>
                        @if(!$plan->isApproved())
                        <form method="POST" action="{{ route('head_of_finance.salary.destroy', $plan) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger"
                                onclick="return confirm('ລຶບຂໍ້ມູນເງິນເດືອນ ເດືອນ {{ $plan->monthLabel() }}?')">ລຶບ</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:var(--fns-gray-400); padding:2rem;">ຍັງບໍ່ມີຂໍ້ມູນ — ກົດ "ສ້າງແຜນໃໝ່" ເພື່ອເລີ່ມຕົ້ນ</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $plans->links() }}

@endsection
