@extends('layouts.admin')

@section('title', 'ລາຍລະອຽດຜູ້ໃຊ້')
@section('page-title', 'ລາຍລະອຽດຜູ້ໃຊ້')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--color-border);">
            <span style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary);">ຂໍ້ມູນຜູ້ໃຊ້</span>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                ແກ້ໄຂ
            </a>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ຊື່ຜູ້ໃຊ້</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary); font-weight:500;">{{ $user->username }}</span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ຊື່ເຕັມ</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary);">{{ $user->full_name }}</span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ບົດບາດ</span>
                <span><span class="badge badge-primary">{{ $user->role->role_name }}</span></span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ພະແນກ</span>
                <span style="font-size:var(--font-size-base); color:var(--color-text-primary);">{{ $user->department->department_name ?? '-' }}</span>
            </div>
            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; align-items:baseline;">
                <span class="form-label">ສະຖານະ</span>
                <span>
                    @if ($user->is_active)
                        <span class="badge badge-success">ໃຊ້ງານ</span>
                    @else
                        <span class="badge badge-danger">ບໍ່ໃຊ້ງານ</span>
                    @endif
                </span>
            </div>
        </div>

        <div style="padding-top:20px; border-top:1px solid var(--color-border); margin-top:20px;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                ກັບໄປຫາລາຍການ
            </a>
        </div>
    </div>
</div>
@endsection
