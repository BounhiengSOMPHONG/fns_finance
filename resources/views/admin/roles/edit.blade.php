@extends('layouts.admin')

@section('title', 'ແກ້ໄຂບົດບາດ')
@section('page-title', 'ແກ້ໄຂຂໍ້ມູນບົດບາດ')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div class="form-section-title">ຂໍ້ມູນບົດບາດ</div>
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label for="role_name" class="form-label required">ຊື່ບົດບາດ</label>
                    <input type="text" name="role_name" id="role_name" value="{{ old('role_name', $role->role_name) }}" class="form-input" placeholder="ປ້ອນຊື່ບົດບາດ">
                    @error('role_name')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--color-border);">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">ຍົກເລີກ</a>
                    <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
