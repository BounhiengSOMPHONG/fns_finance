@extends('layouts.admin')

@section('title', 'ເພີ່ມພະແນກ')
@section('page-title', 'ເພີ່ມພະແນກໃໝ່')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div class="form-section-title">ຂໍ້ມູນພະແນກ</div>
        <form action="{{ route('admin.departments.store') }}" method="POST">
            @csrf
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label for="department_name" class="form-label required">ຊື່ພະແນກ</label>
                    <input type="text" name="department_name" id="department_name" value="{{ old('department_name') }}" class="form-input" placeholder="ປ້ອນຊື່ພະແນກ">
                    @error('department_name')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="department_type" class="form-label required">ປະເພດພະແນກ</label>
                    <input type="text" name="department_type" id="department_type" value="{{ old('department_type') }}" class="form-input" placeholder="ປ້ອນປະເພດພະແນກ">
                    @error('department_type')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--color-border);">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">ຍົກເລີກ</a>
                    <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
