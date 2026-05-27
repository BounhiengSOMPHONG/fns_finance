@extends('layouts.admin')

@section('title', 'ຈັດການງົບປະມານ ສົກ ' . $expensePlan->fiscal_year)
@section('page-title', 'ຈັດປະເມີນລາຍຈ່າຍ ສົກ ' . $expensePlan->fiscal_year)

@section('content')

@if(session('success'))
<div class="fns-alert fns-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="fns-alert fns-alert-danger">{{ session('error') }}</div>
@endif

{{-- Plan header bar --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <span style="font-size:1rem;font-weight:700;color:var(--fns-navy);">ສົກ {{ $expensePlan->fiscal_year }}</span>
    <button type="button" class="fns-btn fns-btn-secondary fns-btn-sm" onclick="openRefModal()">ຈັດການລະຫັດອ້າງອີງ</button>
    <a href="{{ route('head_of_finance.expense.show', $expensePlan) }}" class="fns-btn fns-btn-secondary fns-btn-sm">ສັງລວມ</a>
    <span style="margin-left:auto;font-size:0.85rem;color:#64748b;">
        ງົບລວມ: <strong id="grand-total">{{ number_format($expensePlan->grandTotal(), 0) }}</strong> ກີບ
    </span>
</div>

{{-- COA datalist (shared) --}}
<datalist id="coa-codes">
    @foreach($coaMap as $code => $info)
        <option value="{{ $code }}">{{ $code }} — {{ $info['name'] }}</option>
    @endforeach
</datalist>

{{-- Flat entry grid --}}
<div class="entry-grid-wrap" data-plan-id="{{ $expensePlan->id }}">
<table class="fns-table entry-grid" style="margin:0;min-width:1500px;">
    <thead>
        <tr style="font-size:0.68rem;">
            <th style="width:32px;text-align:center;">#</th>
            <th style="width:120px;">ວັນທີ</th>
            <th style="width:95px;">ອ້າງອີງ</th>
            <th style="width:120px;">ລະຫັດບັນຊີ</th>
            <th style="width:150px;">ໝວດຫຼັກ</th>
            <th style="width:150px;">ລາຍການຫຼັກ</th>
            <th style="min-width:170px;">ລາຍການຍ່ອຍ</th>
            <th style="width:95px;text-align:right;">ອັດຕາ 1</th>
            <th style="width:95px;text-align:right;">ອັດຕາ 2</th>
            <th style="width:60px;text-align:center;">ຈຳນວນ</th>
            <th style="width:60px;text-align:center;">ໄລຍະ</th>
            <th style="width:60px;text-align:center;">ຄວາມຖີ່</th>
            <th style="width:95px;text-align:right;">ບວກເພີ່ມ</th>
            <th style="width:120px;text-align:right;">ຍອດລວມ</th>
            <th style="width:130px;">ໝາຍເຫດ</th>
            <th style="width:32px;"></th>
        </tr>
    </thead>
    <tbody class="grid-body">
        @foreach($expensePlan->entries as $i => $e)
        @include('dashboards.finance_head.expense._entry_row', ['e' => $e, 'i' => $i])
        @endforeach
        @include('dashboards.finance_head.expense._entry_row', ['e' => null, 'i' => $expensePlan->entries->count()])
    </tbody>
</table>
</div>

{{-- ===== Ref-code management modal ===== --}}
<div id="refModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:560px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="margin:0;font-size:1rem;">ຈັດການລະຫັດອ້າງອີງ</h3>
            <button type="button" class="fns-btn fns-btn-secondary fns-btn-sm" onclick="closeRefModal()">ປິດ</button>
        </div>

        {{-- Add form --}}
        <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.store') }}"
              style="display:flex;gap:6px;align-items:flex-end;margin-bottom:1rem;">
            @csrf
            <div style="width:110px;">
                <label class="fns-label" style="font-size:0.72rem;">ລະຫັດ *</label>
                <input type="text" name="code" class="fns-input" placeholder="2.1.8" required>
            </div>
            <div style="flex:1;">
                <label class="fns-label" style="font-size:0.72rem;">ຊື່</label>
                <input type="text" name="label" class="fns-input" placeholder="ຄຳອະທິບາຍ">
            </div>
            <button type="submit" class="fns-btn fns-btn-primary">ເພີ່ມ</button>
        </form>

        <table class="fns-table" style="font-size:0.8rem;">
            <thead><tr><th style="width:120px;">ລະຫັດ</th><th>ຊື່</th><th style="width:120px;"></th></tr></thead>
            <tbody>
                @forelse($refCodes as $rc)
                <tr>
                    <td colspan="2" style="padding:4px 8px;">
                        <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.update', $rc) }}"
                              id="rcform-{{ $rc->id }}" style="display:flex;gap:6px;">
                            @csrf @method('PATCH')
                            <input type="text" name="code" value="{{ $rc->code }}" class="fns-input" style="width:110px;" required>
                            <input type="text" name="label" value="{{ $rc->label }}" class="fns-input" style="flex:1;" placeholder="ຊື່">
                        </form>
                    </td>
                    <td style="text-align:right;padding:4px 8px;white-space:nowrap;">
                        <button type="submit" form="rcform-{{ $rc->id }}" class="fns-btn fns-btn-sm fns-btn-primary">ບັນທຶກ</button>
                        <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.destroy', $rc) }}"
                              style="display:inline;" onsubmit="return confirm('ລຶບລະຫັດ {{ $rc->code }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:1rem;">ຍັງບໍ່ມີລະຫັດອ້າງອີງ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.entry-grid-wrap { overflow-x:auto; }
.entry-grid th, .entry-grid td { padding:2px 4px; }
.entry-grid .gi {
    border:none;background:transparent;font-size:0.76rem;padding:3px 4px;width:100%;
    outline:none;font-family:inherit;color:inherit;transition:background 0.15s;
}
.entry-grid .gi:focus { background:#eff6ff;border-radius:3px;outline:2px solid #93c5fd;outline-offset:-1px; }
.entry-grid select.gi { cursor:pointer; }
.entry-grid input[type=number].gi { text-align:right; }
.entry-grid tr.row-new { background:#fefce8; }
.entry-grid tr.row-new .row-num { color:#ca8a04; }
.entry-grid tr.row-saving { opacity:0.55;pointer-events:none; }
.entry-grid tr.row-saved td { animation:flash-green 0.8s ease; }
.entry-grid tr.row-error td { animation:flash-red 0.8s ease; }
@keyframes flash-green { 0%,100%{background:inherit} 20%{background:#bbf7d0} }
@keyframes flash-red   { 0%,100%{background:inherit} 20%{background:#fecaca} }
</style>

<script>
const COA_MAP = @json($coaMap);

function openRefModal(){ document.getElementById('refModal').style.display='flex'; }
function closeRefModal(){ document.getElementById('refModal').style.display='none'; }

(function(){
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const wrap = document.querySelector('.entry-grid-wrap');
const planId = wrap.dataset.planId;
const tbody = wrap.querySelector('.grid-body');

const numFmt = new Intl.NumberFormat('en-US', {maximumFractionDigits:0});
const f = (row, cls) => row.querySelector('.'+cls);
const val = (row, cls) => f(row, cls)?.value ?? '';
const num = (row, cls) => parseFloat(f(row, cls)?.value) || 0;

function recalc(row){
    const total = (num(row,'gi-r1') + num(row,'gi-r2'))
        * (num(row,'gi-qty') || 0) * (num(row,'gi-period') || 0) * (num(row,'gi-freq') || 0)
        + num(row,'gi-addon');
    const cell = row.querySelector('.cell-total');
    if (cell) cell.textContent = numFmt.format(total);
    recalcGrand();
}

function recalcGrand(){
    let g = 0;
    tbody.querySelectorAll('.grid-row:not(.row-new) .cell-total').forEach(c => {
        g += parseFloat((c.textContent||'0').replace(/,/g,'')) || 0;
    });
    document.getElementById('grand-total').textContent = numFmt.format(g);
}

function applyCoa(row){
    const code = val(row,'gi-acct').trim();
    const info = COA_MAP[code];
    f(row,'gi-acctid').value = info ? info.id : '';
    if (info) {
        f(row,'gi-maincat').value = info.main_cat || '';
        f(row,'gi-mainitem').value = info.main_item || '';
    }
}

async function saveRow(row){
    const sub = val(row,'gi-sub').trim();
    if (!sub) return; // require sub item

    const itemId = row.dataset.itemId;
    const payload = {
        plan_id:             planId,
        entry_date:          val(row,'gi-date') || null,
        ref_code:            val(row,'gi-ref') || null,
        chart_of_account_id: f(row,'gi-acctid').value || null,
        main_cat:            val(row,'gi-maincat') || null,
        main_item:           val(row,'gi-mainitem') || null,
        sub_item:            sub,
        rate1:               num(row,'gi-r1'),
        rate2:               num(row,'gi-r2'),
        qty:                 num(row,'gi-qty'),
        period:              num(row,'gi-period'),
        frequency:           num(row,'gi-freq'),
        add_on:              num(row,'gi-addon'),
        note:                val(row,'gi-note') || null,
    };

    const url = itemId ? `/head-of-finance/expense-entries/${itemId}` : '/head-of-finance/expense-entries';
    const method = itemId ? 'PATCH' : 'POST';

    row.classList.add('row-saving');
    try {
        const res = await fetch(url, {
            method,
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        row.classList.remove('row-saving');
        if (!res.ok || !data.success) throw new Error(data.message || 'Error');

        if (!itemId && data.entry?.id) {
            row.dataset.itemId = data.entry.id;
            row.classList.remove('row-new');
            appendBlankRow();
            renumber();
        }
        if (data.entry?.total !== undefined) {
            const cell = row.querySelector('.cell-total');
            if (cell) cell.textContent = numFmt.format(parseFloat(data.entry.total));
        }
        recalcGrand();
        row.classList.add('row-saved');
        setTimeout(() => row.classList.remove('row-saved'), 900);
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
    }
}

async function deleteRow(row){
    const itemId = row.dataset.itemId;
    if (!itemId) { row.remove(); renumber(); return; }
    if (!confirm('ລຶບລາຍການ?')) return;
    row.classList.add('row-saving');
    try {
        const res = await fetch(`/head-of-finance/expense-entries/${itemId}`, {
            method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF},
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error();
        row.remove(); renumber(); recalcGrand();
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
    }
}

function renumber(){
    let n = 1;
    tbody.querySelectorAll('.grid-row:not(.row-new) .row-num').forEach(c => c.textContent = n++);
}

function appendBlankRow(){
    const tpl = document.getElementById('blank-row-tpl');
    const tr = tpl.content.firstElementChild.cloneNode(true);
    tbody.appendChild(tr);
    bindRow(tr);
}

function bindRow(row){
    row.querySelectorAll('.gi-r1,.gi-r2,.gi-qty,.gi-period,.gi-freq,.gi-addon').forEach(inp =>
        inp.addEventListener('input', () => recalc(row)));

    const acct = f(row,'gi-acct');
    if (acct) acct.addEventListener('change', () => applyCoa(row));

    const delBtn = row.querySelector('.btn-del-row');
    if (delBtn) delBtn.addEventListener('click', () => deleteRow(row));

    row.querySelectorAll('.gi').forEach(inp => {
        inp.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); saveRow(row); inp.blur(); }
        });
        inp.addEventListener('blur', () => setTimeout(() => {
            if (row.contains(document.activeElement)) return;
            saveRow(row);
        }, 150));
    });

    recalc(row);
}

tbody.querySelectorAll('.grid-row').forEach(bindRow);
})();
</script>

{{-- Template for new blank rows (cloned by JS) --}}
<template id="blank-row-tpl">
    @include('dashboards.finance_head.expense._entry_row', ['e' => null, 'i' => 0])
</template>

@endsection
