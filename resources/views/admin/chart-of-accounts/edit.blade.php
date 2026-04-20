@extends('layouts.admin')

@section('title', 'ແກ້ໄຂບັນຊີ')
@section('page-title', 'ແກ້ໄຂຂໍ້ມູນບັນຊີ')

@section('content')
<div style="max-width:640px;">
    <div class="form-section">
        <div class="form-section-title">ຂໍ້ມູນບັນຊີ</div>
        <form action="{{ route('admin.chart-of-accounts.update', $chartOfAccount) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label for="account_code" class="form-label required">ລະຫັດບັນຊີ</label>
                    <input type="text" name="account_code" id="account_code" value="{{ old('account_code', $chartOfAccount->account_code) }}" class="form-input" style="font-family:monospace;" placeholder="ປ້ອນລະຫັດບັນຊີ">
                    @error('account_code')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                
                <div class="form-group">
                    <label for="account_name" class="form-label required">ຊື່ບັນຊີ</label>
                    <input type="text" name="account_name" id="account_name" value="{{ old('account_name', $chartOfAccount->account_name) }}" class="form-input" placeholder="ປ້ອນຊື່ບັນຊີ">
                    @error('account_name')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="parent_id" class="form-label">ບັນຊີຫຼັກ (Parent Account)</label>
                    <select name="parent_id" id="parent_id" class="form-input">
                        <option value="">-- ບໍ່ມີ --</option>
                        @foreach($allAccounts as $account)
                            @if($account->id !== $chartOfAccount->id)
                                <option value="{{ $account->id }}" {{ old('parent_id', $chartOfAccount->parent_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id')<p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>@enderror
                </div>

                <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--color-border);">
                    <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-secondary">ຍົກເລີກ</a>
                    <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#parent_id', {
            create: false,
            placeholder: "-- ບໍ່ມີ --",
            allowEmptyOption: true,
        });
    });
</script>
@endpush
