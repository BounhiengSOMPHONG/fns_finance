@extends('layouts.admin')

@section('title', 'ປະເມີນລາຍຈ່າຍ')
@section('page-title', 'ປະເມີນລາຍຈ່າຍ')

@section('content')

@if(session('success'))
<div class="fns-alert fns-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="fns-alert fns-alert-danger">{{ session('error') }}</div>
@endif

{{-- Toolbar --}}
<div class="fns-toolbar">
    <span style="font-size:0.8rem; color:var(--fns-gray-400);">ທັງໝົດ {{ $plans->total() }} ແຜນ</span>
    <div class="fns-toolbar-right">
        <a href="{{ route('head_of_finance.expense.create') }}" class="fns-btn fns-btn-primary">
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
                <th style="width:5%">#</th>
                <th>ສົກງົບປະມານ</th>
                <th>ສ້າງໂດຍ</th>
                <th style="text-align:right;">ງົບລວມ (ກີບ)</th>
                <th style="width:18%;">ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td class="c dim">{{ $plans->firstItem() + $loop->index }}</td>
                <td style="font-weight:600;">ສົກ {{ $plan->fiscal_year }}</td>
                <td>{{ $plan->creator?->name ?? '-' }}</td>
                <td style="text-align:right; font-weight:600;">
                    {{ number_format($plan->allCategories->flatMap->items->sum('annual_amount'), 0) }}
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="{{ route('head_of_finance.expense.manage', $plan) }}" class="fns-btn fns-btn-sm fns-btn-primary">ຈັດການ</a>
                        <form method="POST" action="{{ route('head_of_finance.expense.destroy', $plan) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger"
                                onclick="return confirm('ຢືນຢັນການລຶບ?')">ລຶບ</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:var(--fns-gray-400); padding:2rem;">
                    ຍັງບໍ່ມີແຜນງົບປະມານ — ກົດ "ສ້າງແຜນໃໝ່" ເພື່ອເລີ່ມຕົ້ນ
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $plans->links() }}

@endsection
