@extends('layouts.admin')

@section('title', 'ແກ້ໄຂຜູ້ໃຊ້')
@section('page-title', 'ແກ້ໄຂຂໍ້ມູນຜູ້ໃຊ້')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div class="form-section-title">ຂໍ້ມູນຜູ້ໃຊ້</div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label for="username" class="form-label required">ຊື່ຜູ້ໃຊ້</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-input" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້">
                    @error('username')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">ລະຫັດຜ່ານ <span style="color:var(--color-text-tertiary); font-weight:400; font-size:var(--font-size-xs);">(ປ່ອຍວ່າງຖ້າບໍ່ຕ້ອງການປ່ຽນ)</span></label>
                    <input type="password" name="password" id="password" class="form-input" placeholder="ປ້ອນລະຫັດຜ່ານໃໝ່ (ເປັນຫນ່ວຍຢ່າງນ້ອຍ 6 ໂຕ)">
                    @error('password')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label required">ຊື່ເຕັມ</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $user->full_name) }}" class="form-input" placeholder="ປ້ອນຊື່ເຕັມ">
                    @error('full_name')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="role_id" class="form-label required">ບົດບາດ</label>
                    <select name="role_id" id="role_id" class="form-input">
                        <option value="">ເລືອກບົດບາດ</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="department_id" class="form-label">ພະແນກ</label>
                    <select name="department_id" id="department_id" class="form-input">
                        <option value="">ເລືອກພະແນກ</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width:16px; height:16px; accent-color:var(--color-primary);">
                    <label for="is_active" class="form-label" style="margin:0;">ເປີດໃຊ້ງານ</label>
                </div>

                <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--color-border);">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">ຍົກເລີກ</a>
                    <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
