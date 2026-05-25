<div class="fns-form-group">
    <label class="fns-label">ປະເພດ <span style="color:red;">*</span></label>
    <select name="section_type" class="fns-input @error('section_type') fns-input-error @enderror" required>
        <option value="">-- ເລືອກ --</option>
        <option value="year2_4" @selected(old('section_type', $fee->section_type ?? '')==='year2_4')>ນ/ສ ປີ 2–4</option>
        <option value="year1" @selected(old('section_type', $fee->section_type ?? '')==='year1')>ນ/ສ ປີ 1 (ໃໝ່)</option>
    </select>
    @error('section_type')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group">
    <label class="fns-label">ເລກທີເອກະສານອ້າງອີງ</label>
    <input type="text" name="gov_doc_id" value="{{ old('gov_doc_id', $fee->gov_doc_id ?? '') }}" class="fns-input">
</div>

<div class="fns-form-group">
    <label class="fns-label">ປີທີ່ເລີ່ມໃຊ້ <span style="color:red;">*</span></label>
    <input type="number" name="start_year" min="2000" max="2100"
        value="{{ old('start_year', $fee->start_year ?? date('Y')) }}"
        class="fns-input @error('start_year') fns-input-error @enderror" required>
    @error('start_year')<p class="fns-error">{{ $message }}</p>@enderror
</div>

<div class="fns-form-group" style="margin-top:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
        <label class="fns-label" style="margin-bottom:0;">ລາຍການຄ່າໃຊ້ຈ່າຍ <span style="color:red;">*</span></label>
        <button type="button" id="add-item-btn" class="fns-btn fns-btn-sm fns-btn-secondary">+ ເພີ່ມລາຍການ</button>
    </div>
    @error('items')<p class="fns-error">{{ $message }}</p>@enderror

    <table class="fns-table" id="items-table">
        <thead>
            <tr>
                <th>ລາຍການ</th>
                <th style="width:140px;">ຈຳນວນ (ກີບ)</th>
                <th style="width:120px;">% ມຊ (0–100)</th>
                <th style="width:60px;"></th>
            </tr>
        </thead>
        <tbody id="items-body">
            {{-- JS will render rows --}}
        </tbody>
    </table>
</div>

@push('scripts')
<script>
(function() {
    const initialItems = @json(
        old('items',
            isset($fee) ? $fee->items->map(fn($i) => [
                'name'     => $i->name,
                'amount'   => $i->amount,
                'nuol_pct' => number_format($i->nuol_pct * 100, 2, '.', ''),
            ])->toArray()
            : [['name'=>'','amount'=>'','nuol_pct'=>'0']]
        )
    );

    let rowIndex = 0;
    const tbody = document.getElementById('items-body');

    function addRow(data = {}) {
        const i = rowIndex++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="items[${i}][name]" value="${escHtml(data.name ?? '')}" class="fns-input" required placeholder="ຊື່ລາຍການ"></td>
            <td><input type="number" name="items[${i}][amount]" value="${escHtml(data.amount ?? '')}" class="fns-input" min="0" step="0.01" required></td>
            <td><input type="number" name="items[${i}][nuol_pct]" value="${escHtml(data.nuol_pct ?? '0')}" class="fns-input" min="0" max="100" step="0.01" required></td>
            <td><button type="button" class="fns-btn fns-btn-sm fns-btn-danger remove-row">ລຶບ</button></td>
        `;
        tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
        tbody.appendChild(tr);
    }

    function escHtml(v) {
        return String(v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }

    initialItems.forEach(item => addRow(item));

    document.getElementById('add-item-btn').addEventListener('click', () => addRow());
})();
</script>
@endpush
