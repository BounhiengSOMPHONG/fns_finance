@extends('layouts.admin')

@section('title', 'ສ້າງແຜນລາຍຮັບ')
@section('page-title', 'ສ້າງແຜນລາຍຮັບວິຊາການ')

@section('content')
<div style="max-width:540px;">
    <div class="fns-card">
        {{-- Card header --}}
        <div style="border-bottom:1px solid var(--fns-gray-200); padding-bottom:1rem; margin-bottom:1.25rem;">
            <h2 style="font-weight:700; font-size:1rem; color:var(--fns-navy);">ຂໍ້ມູນພື້ນຖານ</h2>
            <p style="font-size:0.78rem; color:var(--fns-gray-400); margin-top:0.2rem;">ກຳນົດສົກປີ ແລະ ໝາຍເຫດ (ຖ້າມີ)</p>
        </div>

        <form method="POST" action="{{ route('head_of_finance.academic-income.store') }}">
            @csrf

            <div class="fns-form-group">
                <label class="fns-label">ສົກປີງົບປະມານ <span style="color:#dc2626;">*</span></label>
                <input type="number" name="fiscal_year" min="2000" max="2100"
                    value="{{ old('fiscal_year', date('Y')) }}"
                    class="fns-input @error('fiscal_year') fns-input-error @enderror"
                    style="max-width:160px; font-family:'Cinzel',serif; font-size:1rem; font-weight:700; letter-spacing:0.05em;"
                    required>
                @error('fiscal_year')<p class="fns-error">{{ $message }}</p>@enderror
            </div>

            <div class="fns-form-group">
                <label class="fns-label">ໝາຍເຫດ</label>
                <textarea name="notes" rows="3"
                    class="fns-input"
                    placeholder="ໝາຍເຫດເພີ່ມເຕີມ (ຖ້າມີ)...">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--fns-gray-200);">
                <button type="submit" class="fns-btn fns-btn-primary">ສ້າງແຜນ</button>
                <a href="{{ route('head_of_finance.academic-income.index') }}" class="fns-btn fns-btn-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>

    {{-- Info box --}}
    <div style="margin-top:1rem; background:rgba(26,39,68,0.04); border:1px solid rgba(26,39,68,0.1); border-radius:10px; padding:0.85rem 1.1rem; font-size:0.8rem; color:var(--fns-navy); line-height:1.6;">
        <strong>ຂັ້ນຕອນຕໍ່ໄປ:</strong> ຫຼັງຈາກສ້າງແຜນແລ້ວ ສາມາດ <em>ປ້ອນຈຳນວນນັກສຶກສາ</em> ແລະ ລະບົບຈະຄຳນວນລາຍຮັບອັດຕະໂນມັດ.
    </div>
</div>
@endsection
