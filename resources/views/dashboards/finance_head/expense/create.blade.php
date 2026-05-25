@extends('layouts.admin')

@section('title', 'ສ້າງແຜນປະເມີນລາຍຈ່າຍ')
@section('page-title', 'ສ້າງແຜນປະເມີນລາຍຈ່າຍ')

@section('content')

<div class="fns-card" style="max-width:480px;">
    <div class="fns-card-header">ຂໍ້ມູນແຜນງົບປະມານ</div>
    <div class="fns-card-body">
        <form method="POST" action="{{ route('head_of_finance.expense.store') }}">
            @csrf

            <div class="fns-form-group">
                <label class="fns-label">ສົກງົບປະມານ <span style="color:red">*</span></label>
                <input type="number" name="fiscal_year" class="fns-input @error('fiscal_year') is-invalid @enderror"
                    value="{{ old('fiscal_year', date('Y')) }}" min="2000" max="2100" required>
                @error('fiscal_year')
                <div class="fns-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="fns-form-group">
                <label class="fns-label">ໝາຍເຫດ</label>
                <textarea name="notes" class="fns-input" rows="3">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:1rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ສ້າງແຜນ</button>
                <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>
</div>

@endsection
