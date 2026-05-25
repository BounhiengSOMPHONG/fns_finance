<div class="fns-form-group">
    <label class="fns-label">ລະດັບ <span style="color:red;">*</span></label>
    <select name="level" class="fns-input @error('level') fns-input-error @enderror" required>
        <option value="">-- ເລືອກລະດັບ --</option>
        <option value="bachelor"   @selected(old('level', $setting->level ?? '')==='bachelor')>ປ.ຕີ (ປະລິນຍາຕີ)</option>
        <option value="master_phd" @selected(old('level', $setting->level ?? '')==='master_phd')>ປ.ໂທ / ປ.ເອກ</option>
    </select>
    @error('level')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group">
    <label class="fns-label">ອັດຕາ ມຊ (%) <span style="color:red;">*</span></label>
    <input type="number" name="percentage" step="0.01" min="0" max="100"
        value="{{ old('percentage', isset($setting->percentage) ? number_format($setting->percentage * 100, 2, '.', '') : '') }}"
        class="fns-input @error('percentage') fns-input-error @enderror"
        placeholder="ເຊັ່ນ: 17.00" required>
    @error('percentage')<p class="fns-error">{{ $message }}</p>@enderror
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
