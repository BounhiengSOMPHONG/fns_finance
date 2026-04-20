@extends('layouts.admin')

@section('title', 'ລາຍລະອຽດບັນຊີ')
@section('page-title', 'ລາຍລະອຽດບັນຊີ')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--color-border);">
            <span style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary);">ຂໍ້ມູນບັນຊີ</span>
            <a href="{{ route('admin.chart-of-accounts.edit', $chartOfAccount) }}" class="btn btn-secondary btn-sm">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                ແກ້ໄຂ
            </a>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ລະຫັດບັນຊີ</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary); font-family:monospace; font-weight:500;">{{ $chartOfAccount->account_code }}</span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ຊື່ບັນຊີ</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary);">{{ $chartOfAccount->account_name }}</span>
            </div>
            @if($chartOfAccount->parent)
                <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                    <span class="form-label">ບັນຊີຫຼັກ</span>
                    <span style="font-size:var(--font-size-base); color:var(--color-text-secondary);">
                        {{ $chartOfAccount->parent->account_code }} - {{ $chartOfAccount->parent->account_name }}
                    </span>
                </div>
            @endif
        </div>

        <div style="padding-top:20px; border-top:1px solid var(--color-border); margin-top:20px;">
            <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-secondary">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                ກັບໄປຫາລາຍການ
            </a>
        </div>
    </div>
</div>
@endsection
