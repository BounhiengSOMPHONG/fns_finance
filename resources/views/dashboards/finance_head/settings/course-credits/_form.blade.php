<div class="fns-form-group">
    <label class="fns-label">ສາຂາວິຊາ <span style="color:red;">*</span></label>
    <select name="degree_program_id" id="degree_program_id"
        class="fns-input @error('degree_program_id') fns-input-error @enderror" required>
        <option value="">-- ເລືອກສາຂາວິຊາ --</option>
        @foreach($programs as $p)
            <option value="{{ $p->id }}"
                data-level="{{ $p->level }}"
                @selected(old('degree_program_id', $setting->degree_program_id ?? '') == $p->id)>
                [{{ $p->level_label }}{{ $p->study_year ? ' ປີ '.$p->study_year : '' }}] {{ $p->name }}
            </option>
        @endforeach
    </select>
    @error('degree_program_id')<p class="fns-error">{{ $message }}</p>@enderror
</div>

{{-- Master/PhD: total units helper + KIP income fields --}}
<div id="master-fields" style="display:none;">
    <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:1rem; margin-bottom:1rem;">
        <label class="fns-label" style="color:#0369a1;">
            ໜ່ວຍກິດລວມທັງໝົດ (ອັດຕາ 60 : 40)
            <span style="font-weight:400; font-size:0.78rem; color:#64748b;">
                — ລະບົບຈະຄຳນວນ ກີບ ໃຫ້ອັດຕະໂນມັດ
            </span>
        </label>
        <input type="number" id="total_units" min="1" max="9999" step="1"
            placeholder="ເຊັ່ນ: 115"
            class="fns-input" style="max-width:200px;">
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
        <div class="fns-form-group" style="margin-bottom:0;">
            <label class="fns-label">
                ລາຍຮັບ ປີ 2+ (ກີບ)
                <span style="font-weight:400; font-size:0.75rem; color:#64748b;" id="yr2-unit-hint"></span>
            </label>
            <input type="number" id="income_yr2" min="0" step="1"
                placeholder="ເຊັ່ນ: 11,040,000"
                class="fns-input">
        </div>
        <div class="fns-form-group" style="margin-bottom:0;">
            <label class="fns-label">
                ລາຍຮັບ ປີ 1 — 60% (ກີບ)
                <span style="font-weight:400; font-size:0.75rem; color:#64748b;" id="yr1-unit-hint"></span>
            </label>
            <input type="number" id="income_yr1" min="0" step="1"
                placeholder="ເຊັ່າ: 16,560,000"
                class="fns-input">
        </div>
    </div>

    {{-- hidden: actual unit values submitted to server (names added by JS only when master/phd active) --}}
    <input type="hidden" id="cc_unit_mp" value="{{ old('course_credit_unit', $setting->course_credit_unit ?? '') }}">
    <input type="hidden" id="yr1_unit_mp" value="{{ old('year1_credit_unit',  $setting->year1_credit_unit  ?? '') }}">
</div>

{{-- Bachelor: plain unit count field --}}
<div id="bachelor-fields">
    <div class="fns-form-group">
        <label class="fns-label">ໜ່ວຍກິດ <span style="color:red;">*</span></label>
        <input type="number" id="cc_unit_bach" name="course_credit_unit"
            min="1" max="999" step="0.5"
            value="{{ old('course_credit_unit', $setting->course_credit_unit ?? '') }}"
            class="fns-input @error('course_credit_unit') fns-input-error @enderror">
        @error('course_credit_unit')<p class="fns-error">{{ $message }}</p>@enderror
    </div>
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

<script>
(function () {
    const prices = @json($creditPrices ?? []);  // {bachelor:35000, master:240000, phd:600000}

    const select      = document.getElementById('degree_program_id');
    const masterWrap  = document.getElementById('master-fields');
    const bachWrap    = document.getElementById('bachelor-fields');
    const totalIn     = document.getElementById('total_units');
    const incYr2      = document.getElementById('income_yr2');
    const incYr1      = document.getElementById('income_yr1');
    const hiddenCcu   = document.getElementById('cc_unit_mp');
    const hiddenYr1   = document.getElementById('yr1_unit_mp');
    const bachCcu     = document.getElementById('cc_unit_bach');
    const hintYr2     = document.getElementById('yr2-unit-hint');
    const hintYr1     = document.getElementById('yr1-unit-hint');

    function currentLevel() {
        const opt = select.options[select.selectedIndex];
        return opt ? opt.dataset.level : '';
    }

    function isMasterPhd() {
        return ['master', 'phd'].includes(currentLevel());
    }

    function price() {
        return prices[currentLevel()] ?? 0;
    }

    function setHints(yr2Units, yr1Units) {
        hintYr2.textContent = yr2Units ? '= ' + yr2Units + ' ໜ່ວຍ' : '';
        hintYr1.textContent = yr1Units ? '= ' + yr1Units + ' ໜ່ວຍ' : '';
    }

    function kipToUnit(kip) {
        const p = price();
        if (!p || !kip) return 0;
        return Math.round(kip / p * 10) / 10;
    }

    function syncHiddens() {
        const u2 = kipToUnit(parseFloat(incYr2.value) || 0);
        const u1 = kipToUnit(parseFloat(incYr1.value) || 0);
        hiddenCcu.value = u2 || '';
        hiddenYr1.value = u1 || '';
        setHints(u2 || '', u1 || '');
    }

    function fillFromTotal() {
        const total = parseFloat(totalIn.value);
        if (!total || total <= 0) return;
        const p = price();
        const yr1Units = Math.round(total * 0.6);
        const yr2Units = Math.round(total * 0.4);
        incYr1.value = yr1Units * p;
        incYr2.value = yr2Units * p;
        hiddenCcu.value = yr2Units;
        hiddenYr1.value = yr1Units;
        setHints(yr2Units, yr1Units);
    }

    function prefillKipFromUnits() {
        // On edit page: existing unit values → pre-fill KIP fields
        const p = price();
        if (!p) return;
        const u2 = parseFloat(hiddenCcu.value) || 0;
        const u1 = parseFloat(hiddenYr1.value) || 0;
        if (u2) incYr2.value = u2 * p;
        if (u1) incYr1.value = u1 * p;
        setHints(u2 || '', u1 || '');
    }

    function toggleFields() {
        const mp = isMasterPhd();
        masterWrap.style.display = mp ? '' : 'none';
        bachWrap.style.display   = mp ? 'none' : '';

        if (mp) {
            bachCcu.removeAttribute('required');
            bachCcu.removeAttribute('name');
            hiddenCcu.setAttribute('name', 'course_credit_unit');
            hiddenYr1.setAttribute('name', 'year1_credit_unit');
            prefillKipFromUnits();
        } else {
            bachCcu.setAttribute('required', '');
            bachCcu.setAttribute('name', 'course_credit_unit');
            hiddenCcu.removeAttribute('name');
            hiddenYr1.removeAttribute('name');
        }
    }

    select.addEventListener('change', toggleFields);
    totalIn.addEventListener('input', fillFromTotal);
    incYr2.addEventListener('input', syncHiddens);
    incYr1.addEventListener('input', syncHiddens);

    toggleFields();
})();
</script>
