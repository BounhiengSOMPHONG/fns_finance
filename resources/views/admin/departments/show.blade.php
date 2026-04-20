@extends('layouts.admin')

@section('title', 'ລາຍລະອຽດພະແນກ')
@section('page-title', 'ລາຍລະອຽດພະແນກ')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--color-border);">
            <span style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary);">ຂໍ້ມູນພະແນກ</span>
            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-secondary btn-sm">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                ແກ້ໄຂ
            </a>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ຊື່ພະແນກ</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary); font-weight:500;">{{ $department->department_name }}</span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ປະເພດ</span>
                <span><span class="badge badge-primary">{{ $department->department_type }}</span></span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ຈຳນວນຜູ້ໃຊ້</span>
                <span><span class="badge badge-gray">{{ $department->users->count() }} ຄົນ</span></span>
            </div>
        </div>

        @if ($department->users->count() > 0)
            <div style="padding-top:20px; margin-top:20px; border-top:1px solid var(--color-border);">
                <p style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary); margin-bottom:12px;">ຜູ້ໃຊ້ໃນພະແນກນີ້</p>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    @foreach ($department->users as $user)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--color-bg-surface); border-radius:var(--radius-md);">
                            <div>
                                <p style="font-size:var(--font-size-base); font-weight:500; color:var(--color-text-primary); margin:0;">{{ $user->full_name }}</p>
                                <p style="font-size:var(--font-size-xs); color:var(--color-text-tertiary); margin:2px 0 0;">{{ $user->username }} - {{ $user->role->role_name }}</p>
                            </div>
                            @if ($user->is_active)
                                <span class="badge badge-success">ໃຊ້ງານ</span>
                            @else
                                <span class="badge badge-danger">ບໍ່ໃຊ້ງານ</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="padding-top:20px; border-top:1px solid var(--color-border); margin-top:20px;">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                ກັບໄປລາຍການ
            </a>
        </div>
    </div>
</div>
@endsection
