@extends('layouts.admin')

@section('title', 'ເພີ່ມຫຼັກສູດ')
@section('page-title', 'ເພີ່ມຫຼັກສູດໃໝ່')

@section('content')
<div class="fns-card" style="max-width:560px;">
    <form method="POST" action="{{ route('head_of_finance.settings.degree-programs.store') }}">
        @csrf

        <div class="fns-form-group">
            <label class="fns-label">ລະຫັດສາຂາ <span style="color:red;">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}" class="fns-input @error('code') fns-input-error @enderror" required>
            @error('code')<p class="fns-error">{{ $message }}</p>@enderror
        </div>

        <div class="fns-form-group">
            <label class="fns-label">ຊື່ຫຼັກສູດ <span style="color:red;">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="fns-input @error('name') fns-input-error @enderror" required>
            @error('name')<p class="fns-error">{{ $message }}</p>@enderror
        </div>

        <div class="fns-form-group">
            <label class="fns-label">ລະດັບ <span style="color:red;">*</span></label>
            <select name="level" class="fns-input @error('level') fns-input-error @enderror" required>
                <option value="">-- ເລືອກລະດັບ --</option>
                <option value="bachelor" @selected(old('level')==='bachelor')>ປ.ຕີ (ປະລິນຍາຕີ)</option>
                <option value="master" @selected(old('level')==='master')>ປ.ໂທ (ປະລິນຍາໂທ)</option>
                <option value="phd" @selected(old('level')==='phd')>ປ.ເອກ (ປະລິນຍາເອກ)</option>
            </select>
            @error('level')<p class="fns-error">{{ $message }}</p>@enderror
        </div>

        <div class="fns-form-group">
            <label class="fns-label">ພາກວິຊາ <span style="color:red;">*</span></label>
            <select name="academic_department" class="fns-input @error('academic_department') fns-input-error @enderror" required>
                @foreach($departments as $department)
                    <option value="{{ $department['key'] }}" @selected(old('academic_department', 'other') === $department['key'])>{{ $department['label'] }}</option>
                @endforeach
            </select>
            @error('academic_department')<p class="fns-error">{{ $message }}</p>@enderror
        </div>

        <div class="fns-form-group">
            <label class="fns-label">ຊັ້ນປີ (ສຳລັບ ປ.ຕີ)</label>
            <input type="number" name="study_year" min="1" max="4"
                value="{{ old('study_year') }}"
                class="fns-input @error('study_year') fns-input-error @enderror"
                placeholder="ຕື່ມສຳລັບ ປ.ຕີ ເທົ່ານັ້ນ">
            @error('study_year')<p class="fns-error">{{ $message }}</p>@enderror
        </div>

        <div class="fns-form-group">
            <input type="hidden" name="include_in_planning" value="0">
            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                <input type="checkbox" name="include_in_planning" value="1" @checked(old('include_in_planning', true))>
                ນຳເຂົ້າລາຍການຂຶ້ນແຜນ
            </label>
        </div>

        <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
            <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            <a href="{{ route('head_of_finance.settings.degree-programs.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
        </div>
    </form>
</div>
@endsection
