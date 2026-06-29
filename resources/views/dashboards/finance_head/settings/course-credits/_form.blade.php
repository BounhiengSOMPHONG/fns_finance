@php
    $displayProgramName = function (?string $name): string {
        $name = (string) ($name ?? '');
        $display = trim((string) preg_replace('/\s*(?:ປີ|\x{0E1B}\x{0E35})\s*1\s*$/u', '', $name));

        return $display !== '' ? $display : $name;
    };
@endphp

<div class="fns-form-group">
    <label class="fns-label">ສາຂາວິຊາ <span style="color:red;">*</span></label>
    <select name="degree_program_id" id="degree_program_id"
        class="fns-input @error('degree_program_id') fns-input-error @enderror" required>
        <option value="">-- ເລືອກສາຂາວິຊາ --</option>
        @foreach($programs as $p)
            <option value="{{ $p->id }}"
                data-level="{{ $p->level }}"
                @selected(old('degree_program_id', $setting->degree_program_id ?? '') == $p->id)>
                [{{ $p->level_label }}{{ $p->study_year ? ' ປີ '.$p->study_year : '' }}] {{ $displayProgramName($p->name) }}
            </option>
        @endforeach
    </select>
    @error('degree_program_id')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group">
    <label class="fns-label">ໜ່ວຍກິດລວມຂອງຫຼັກສູດ <span style="color:red;">*</span></label>
    <input type="number" name="course_credit_unit"
        min="1" max="999" step="0.5"
        value="{{ old('course_credit_unit', $setting->course_credit_unit ?? '') }}"
        class="fns-input @error('course_credit_unit') fns-input-error @enderror" required>
    @error('course_credit_unit')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group">
    <label class="fns-label">ເລກທີເອກະສານອ້າງອີງ (ລັດຖະບານ)</label>
    <input type="text" name="gov_doc_id" value="{{ old('gov_doc_id', $setting->gov_doc_id ?? '') }}" class="fns-input">
</div>

<div class="fns-form-group">
    <label class="fns-label">ປີທີ່ເລີ່ມໃຊ້ <span style="color:red;">*</span></label>
    <input type="number" name="start_year" min="2000" max="2100"
        value="{{ old('start_year', $setting->start_year ?? date('Y')) }}"
        class="fns-input @error('start_year') fns-input-error @enderror" required>
    @error('start_year')<p class="fns-error">{{ $message }}</p>@enderror
</div>
