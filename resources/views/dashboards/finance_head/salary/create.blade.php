@extends('layouts.admin')

@section('title', 'ສ້າງແຜນເງິນເດືອນ')
@section('page-title', 'ສ້າງແຜນເງິນເດືອນໃໝ່')

@section('content')
<div style="max-width:480px;">
    <a href="{{ route('head_of_finance.salary.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm" style="margin-bottom:1.2rem;display:inline-flex;">← ກັບຄືນ</a>

    <div class="fns-card" style="padding:1.5rem;">
        <form method="POST" action="{{ route('head_of_finance.salary.store') }}">
            @csrf

            <div class="fns-form-group">
                <label class="fns-label">ສົກ (ປີ) <span style="color:red">*</span></label>
                <input type="number" name="fiscal_year" class="fns-input @error('fiscal_year') is-invalid @enderror"
                    value="{{ old('fiscal_year', date('Y')) }}" min="2000" max="2100" required>
                @error('fiscal_year')<p style="color:#ef4444;font-size:0.78rem;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div class="fns-form-group">
                <label class="fns-label">ເດືອນ <span style="color:red">*</span></label>
                <select name="month" class="fns-input @error('month') is-invalid @enderror" required>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                        {{ str_pad($m,2,'0',STR_PAD_LEFT) }} — {{ ['','ມັງກອນ','ກຸມພາ','ມີນາ','ເມສາ','ພຶດສະພາ','ມິຖຸນາ','ກໍລະກົດ','ສິງຫາ','ກັນຍາ','ຕຸລາ','ພະຈິກ','ທັນວາ'][$m] }}
                    </option>
                    @endforeach
                </select>
                @error('month')<p style="color:#ef4444;font-size:0.78rem;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;gap:8px;margin-top:1.2rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ສ້າງແຜນ</button>
                <a href="{{ route('head_of_finance.salary.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>
</div>
@endsection
