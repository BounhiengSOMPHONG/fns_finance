{{-- Variables: $items (collection), $category (ExpenseCategory) --}}
@php
    $isABC   = $category->isABC();
    $labelA  = $category->labelA();
    $labelB  = $category->labelB();
    $labelC  = $category->labelC();
    $catId   = $category->id;
    $ftype   = $category->formula_type;
@endphp

<div class="items-grid-wrap" data-category-id="{{ $catId }}" data-formula="{{ $ftype }}">
<table class="fns-table items-grid" style="margin:0;border-radius:0;table-layout:fixed;">
    <colgroup>
        <col style="width:36px">
        <col>
        <col style="width:90px">
        <col style="width:110px">
        <col style="width:70px">
        @if($isABC)<col style="width:70px">@endif
        <col style="width:120px">
        <col style="width:90px">
        <col style="width:32px">
    </colgroup>
    <thead>
        <tr style="font-size:0.7rem;">
            <th style="text-align:center;">#</th>
            <th>ລາຍການ</th>
            <th>ອ້າງອີງ</th>
            <th style="text-align:right;">{{ $labelA }}</th>
            <th style="text-align:center;">{{ $labelB }}</th>
            @if($isABC)<th style="text-align:center;">{{ $labelC }}</th>@endif
            <th style="text-align:right;">ໝົດປີ (ກີບ)</th>
            <th>ໝາຍເຫດ</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="grid-body">
        @foreach($items as $i => $item)
        <tr class="grid-row" data-item-id="{{ $item->id }}"
            data-orig='{{ e(json_encode(["name"=>$item->name,"reference"=>$item->reference,"monthly_amount"=>$item->monthly_amount,"quantity"=>$item->quantity,"qty_c"=>$item->qty_c,"remark"=>$item->remark])) }}'>
            <td class="c dim row-num" style="text-align:center;font-size:0.72rem;">{{ $i + 1 }}</td>
            <td><input class="gi gi-name" type="text" value="{{ $item->name }}" placeholder="ລາຍການ..." style="width:100%;"></td>
            <td><input class="gi gi-ref" type="text" value="{{ $item->reference }}" placeholder="ອ້າງອີງ" style="width:100%;"></td>
            <td><input class="gi gi-a" type="number" value="{{ $item->monthly_amount }}" min="0" step="1" style="width:100%;text-align:right;"></td>
            <td><input class="gi gi-b" type="number" value="{{ $item->quantity }}" min="0" step="1" style="width:100%;text-align:center;"></td>
            @if($isABC)
            <td><input class="gi gi-c" type="number" value="{{ $item->qty_c ?? 1 }}" min="0" step="0.01" style="width:100%;text-align:center;"></td>
            @endif
            <td class="cell-annual" style="text-align:right;font-weight:600;font-size:0.8rem;padding:0 8px;">{{ number_format($item->annual_amount, 0) }}</td>
            <td><input class="gi gi-remark" type="text" value="{{ $item->remark }}" placeholder="ໝາຍເຫດ" style="width:100%;"></td>
            <td style="text-align:center;padding:0;">
                <button class="btn-del-row" type="button" title="ລຶບ" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;padding:2px 4px;">✕</button>
            </td>
        </tr>
        @endforeach
        {{-- New blank row --}}
        <tr class="grid-row row-new" data-item-id="">
            <td class="c dim row-num" style="text-align:center;font-size:0.72rem;">+</td>
            <td><input class="gi gi-name" type="text" value="" placeholder="ລາຍການໃໝ່..." style="width:100%;"></td>
            <td><input class="gi gi-ref" type="text" value="" placeholder="ອ້າງອີງ" style="width:100%;"></td>
            <td><input class="gi gi-a" type="number" value="0" min="0" step="1" style="width:100%;text-align:right;"></td>
            <td><input class="gi gi-b" type="number" value="{{ $isABC ? 1 : 12 }}" min="0" step="1" style="width:100%;text-align:center;"></td>
            @if($isABC)
            <td><input class="gi gi-c" type="number" value="1" min="0" step="0.01" style="width:100%;text-align:center;"></td>
            @endif
            <td class="cell-annual" style="text-align:right;font-weight:600;font-size:0.8rem;padding:0 8px;">0</td>
            <td><input class="gi gi-remark" type="text" value="" placeholder="ໝາຍເຫດ" style="width:100%;"></td>
            <td></td>
        </tr>
    </tbody>
</table>
</div>

<style>
.items-grid-wrap { overflow-x:auto; }
.items-grid .gi {
    border:none;background:transparent;font-size:0.78rem;padding:2px 4px;
    outline:none;font-family:inherit;color:inherit;
    transition:background 0.15s;
}
.items-grid .gi:focus {
    background:#eff6ff;border-radius:3px;outline:2px solid #93c5fd;outline-offset:-1px;
}
.items-grid tr.row-new { background:#fefce8; }
.items-grid tr.row-new .row-num { color:#ca8a04; }
.items-grid tr.row-saving { opacity:0.55;pointer-events:none; }
.items-grid tr.row-saved td { animation:flash-green 0.8s ease; }
.items-grid tr.row-error td { animation:flash-red 0.8s ease; }
@keyframes flash-green { 0%,100%{background:inherit} 20%{background:#bbf7d0} }
@keyframes flash-red   { 0%,100%{background:inherit} 20%{background:#fecaca} }
</style>

<script>
(function(){
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function initGrid(wrap) {
    const catId    = wrap.dataset.categoryId;
    const ftype    = wrap.dataset.formula;
    const isABC    = ftype === 'ABC';
    const tbody    = wrap.querySelector('.grid-body');

    function recalc(row) {
        const a = parseFloat(row.querySelector('.gi-a')?.value) || 0;
        const b = parseFloat(row.querySelector('.gi-b')?.value) || 0;
        const c = isABC ? (parseFloat(row.querySelector('.gi-c')?.value) || 1) : 1;
        const cell = row.querySelector('.cell-annual');
        if (cell) cell.textContent = (a * b * c).toLocaleString('en-US', {maximumFractionDigits:0});
    }

    function allInputs(row) {
        return Array.from(row.querySelectorAll('.gi'));
    }

    async function saveRow(row) {
        const name = row.querySelector('.gi-name')?.value?.trim();
        if (!name) return; // ບໍ່ save ຖ້າ name ຫວ່າງ

        const itemId = row.dataset.itemId;
        const payload = {
            category_id:    catId,
            name:           name,
            reference:      row.querySelector('.gi-ref')?.value || '',
            monthly_amount: parseFloat(row.querySelector('.gi-a')?.value) || 0,
            quantity:       parseFloat(row.querySelector('.gi-b')?.value) || 0,
            remark:         row.querySelector('.gi-remark')?.value || '',
        };
        if (isABC) payload.qty_c = parseFloat(row.querySelector('.gi-c')?.value) || 1;

        const url    = itemId
            ? `/head-of-finance/expense-items/${itemId}`
            : '/head-of-finance/expense-items';
        const method = itemId ? 'PATCH' : 'POST';

        row.classList.add('row-saving');
        try {
            const res  = await fetch(url, {
                method,
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  CSRF,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            row.classList.remove('row-saving');
            if (!res.ok || !data.success) throw new Error(data.message || 'Error');

            // ຕັ້ງ item id ໃສ່ row (ສຳລັບ new row)
            if (!itemId && data.item?.id) {
                row.dataset.itemId = data.item.id;
                row.classList.remove('row-new');
                row.querySelector('.row-num').textContent = tbody.querySelectorAll('.grid-row:not(.row-new)').length;
                // ສ້າງ new blank row ຫຼັງ save
                appendBlankRow();
            }
            // Update annual display from server value
            if (data.item?.annual_amount !== undefined) {
                const cell = row.querySelector('.cell-annual');
                if (cell) cell.textContent = parseFloat(data.item.annual_amount).toLocaleString('en-US', {maximumFractionDigits:0});
            }
            row.classList.add('row-saved');
            setTimeout(() => row.classList.remove('row-saved'), 900);
        } catch(err) {
            row.classList.remove('row-saving');
            row.classList.add('row-error');
            setTimeout(() => row.classList.remove('row-error'), 900);
        }
    }

    async function deleteRow(row) {
        const itemId = row.dataset.itemId;
        if (!itemId) { row.remove(); renumber(); return; }

        if (!confirm('ລຶບລາຍການ?')) return;
        row.classList.add('row-saving');
        try {
            const res = await fetch(`/head-of-finance/expense-items/${itemId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Error');
            row.remove();
            renumber();
        } catch(err) {
            row.classList.remove('row-saving');
            row.classList.add('row-error');
            setTimeout(() => row.classList.remove('row-error'), 900);
        }
    }

    function renumber() {
        let n = 1;
        tbody.querySelectorAll('.grid-row:not(.row-new)').forEach(r => {
            r.querySelector('.row-num').textContent = n++;
        });
    }

    function appendBlankRow() {
        const defaultB = isABC ? '1' : '12';
        const cCol = isABC ? `<td><input class="gi gi-c" type="number" value="1" min="0" step="0.01" style="width:100%;text-align:center;"></td>` : '';
        const tr = document.createElement('tr');
        tr.className = 'grid-row row-new';
        tr.dataset.itemId = '';
        tr.innerHTML = `
            <td class="c dim row-num" style="text-align:center;font-size:0.72rem;color:#ca8a04;">+</td>
            <td><input class="gi gi-name" type="text" value="" placeholder="ລາຍການໃໝ່..." style="width:100%;"></td>
            <td><input class="gi gi-ref"  type="text" value="" placeholder="ອ້າງອີງ" style="width:100%;"></td>
            <td><input class="gi gi-a" type="number" value="0" min="0" step="1" style="width:100%;text-align:right;"></td>
            <td><input class="gi gi-b" type="number" value="${defaultB}" min="0" step="1" style="width:100%;text-align:center;"></td>
            ${cCol}
            <td class="cell-annual" style="text-align:right;font-weight:600;font-size:0.8rem;padding:0 8px;">0</td>
            <td><input class="gi gi-remark" type="text" value="" placeholder="ໝາຍເຫດ" style="width:100%;"></td>
            <td></td>
        `;
        tbody.appendChild(tr);
        bindRow(tr);
    }

    function bindRow(row) {
        // Recalc on number input
        row.querySelectorAll('.gi-a,.gi-b,.gi-c').forEach(inp => {
            inp.addEventListener('input', () => recalc(row));
        });

        // Delete button
        const delBtn = row.querySelector('.btn-del-row');
        if (delBtn) delBtn.addEventListener('click', () => deleteRow(row));

        // Tab navigation
        row.querySelectorAll('.gi').forEach((inp, idx, arr) => {
            inp.addEventListener('keydown', e => {
                if (e.key === 'Tab' && !e.shiftKey) {
                    if (idx === arr.length - 1) {
                        // Last input: save then focus first input of next row
                        e.preventDefault();
                        saveRow(row);
                        const rows = Array.from(tbody.querySelectorAll('.grid-row'));
                        const nextRow = rows[rows.indexOf(row) + 1];
                        if (nextRow) {
                            nextRow.querySelector('.gi')?.focus();
                        }
                    }
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveRow(row);
                    inp.blur();
                }
                if (e.key === 'Escape') {
                    // Revert: reload orig values
                    try {
                        const orig = JSON.parse(row.dataset.orig || '{}');
                        if (orig.name !== undefined) row.querySelector('.gi-name').value = orig.name;
                        if (orig.reference !== undefined) row.querySelector('.gi-ref').value = orig.reference ?? '';
                        if (orig.monthly_amount !== undefined) row.querySelector('.gi-a').value = orig.monthly_amount;
                        if (orig.quantity !== undefined) row.querySelector('.gi-b').value = orig.quantity;
                        if (isABC && orig.qty_c !== undefined) row.querySelector('.gi-c').value = orig.qty_c ?? 1;
                        if (orig.remark !== undefined) row.querySelector('.gi-remark').value = orig.remark ?? '';
                        recalc(row);
                    } catch(e) {}
                    inp.blur();
                }
            });

            // Save on blur (only if value changed)
            inp.addEventListener('blur', () => {
                // Small delay so Tab key can process first
                setTimeout(() => {
                    const active = document.activeElement;
                    if (row.contains(active)) return; // still inside row
                    saveRow(row);
                    // Update orig snapshot
                    try {
                        const orig = {
                            name: row.querySelector('.gi-name').value,
                            reference: row.querySelector('.gi-ref').value,
                            monthly_amount: row.querySelector('.gi-a').value,
                            quantity: row.querySelector('.gi-b').value,
                            qty_c: isABC ? row.querySelector('.gi-c').value : null,
                            remark: row.querySelector('.gi-remark').value,
                        };
                        row.dataset.orig = JSON.stringify(orig);
                    } catch(e) {}
                }, 150);
            });
        });

        recalc(row);
    }

    // Bind existing rows
    tbody.querySelectorAll('.grid-row').forEach(row => bindRow(row));
}

// Init all grids on page
document.querySelectorAll('.items-grid-wrap').forEach(wrap => initGrid(wrap));
})();
</script>
