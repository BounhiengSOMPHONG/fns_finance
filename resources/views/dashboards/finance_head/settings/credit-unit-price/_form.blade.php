<div class="fns-form-group">
    <label class="fns-label">ລະດັບ <span style="color:red;">*</span></label>
    <select name="level" class="fns-input @error('level') fns-input-error @enderror" required>
        <option value="">-- ເລືອກລະດັບ --</option>
        <option value="bachelor" @selected(old('level', $setting->level ?? '')==='bachelor')>ປ.ຕີ (ປະລິນຍາຕີ)</option>
        <option value="master" @selected(old('level', $setting->level ?? '')==='master')>ປ.ໂທ (ປະລິນຍາໂທ)</option>
        <option value="phd" @selected(old('level', $setting->level ?? '')==='phd')>ປ.ເອກ (ປະລິນຍາເອກ)</option>
    </select>
    @error('level')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group">
    <label class="fns-label">ລາຄາຕໍ່ໜ່ວຍກິດ (ກີບ) <span style="color:red;">*</span></label>
    <input type="number" name="credit_unit_price" step="0.01" min="0"
        value="{{ old('credit_unit_price', $setting->credit_unit_price ?? '') }}"
        class="fns-input @error('credit_unit_price') fns-input-error @enderror" required>
    @error('credit_unit_price')<p class="fns-error">{{ $message }}</p>@enderror
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
