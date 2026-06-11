@extends('layouts.admin')

@section('title', 'Create expense year')
@section('page-title', 'Create expense year')

@section('content')
<div class="fns-card" style="max-width:560px;">
    <form method="POST" action="{{ route('head_of_finance.expense.store') }}">
        @csrf

        <div class="fns-form-group">
            <label class="fns-label">Planning year <span style="color:red">*</span></label>
            <input type="number" name="year" class="fns-input @error('year') is-invalid @enderror"
                   value="{{ old('year', date('Y')) }}" min="2000" max="2100" required>
            @error('year')
                <div class="fns-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="fns-form-group">
            <label class="fns-label">Name</label>
            <input type="text" name="name" class="fns-input" value="{{ old('name') }}" placeholder="Planning {{ date('Y') }}">
        </div>

        <div class="fns-form-group">
            <label class="fns-label">Description</label>
            <textarea name="description" class="fns-input" rows="3">{{ old('description') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:1rem;">
            <button type="submit" class="fns-btn fns-btn-primary">Create</button>
            <a href="{{ route('head_of_finance.manage-plan.index') }}" class="fns-btn fns-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
