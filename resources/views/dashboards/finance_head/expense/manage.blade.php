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

@php
    $refByCode = $refCodes->keyBy('code');
    $level1    = $refCodes->filter(fn ($rc) => substr_count($rc->code, '.') === 1)->values();
    $rcLevel1  = $level1->sortBy('code')->values();
    $rcChildren = $refCodes->filter(fn ($rc) => substr_count($rc->code, '.') === 2)
        ->groupBy(fn ($rc) => \Illuminate\Support\Str::beforeLast($rc->code, '.'));
    $entriesByCat = $expensePlan->entries
        ->groupBy('main_cat_code')
        ->sortBy(fn ($g, $catCode) => $refByCode[$catCode]->sort_order ?? 9999);
@endphp

{{-- Plan header bar --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <span style="font-size:1rem;font-weight:700;color:var(--fns-navy);">ສົກ {{ $expensePlan->fiscal_year }}</span>
    <button type="button" class="fns-btn fns-btn-secondary fns-btn-sm" onclick="openRefModal()">ຈັດການລະຫັດອ້າງອີງ</button>
    <span style="margin-left:auto;font-size:0.85rem;color:#64748b;">
        ງົບລວມ: <strong id="grand-total">{{ number_format($expensePlan->grandTotal(), 0) }}</strong> ກີບ
    </span>
</div>

{{-- COA datalist (used by the ref-code modal) --}}
<datalist id="coa-codes">
    @foreach($coaMap as $code => $info)
        <option value="{{ $code }}">{{ $code }} — {{ $info['name'] }}</option>
    @endforeach
</datalist>

{{-- Group picker --}}
<div class="group-picker" data-plan-id="{{ $expensePlan->id }}">
    <select id="pick-cat" class="fns-input">
        <option value="">— ໝວດຫຼັກ —</option>
        @foreach($level1 as $rc)
            <option value="{{ $rc->code }}">{{ $rc->code }}{{ $rc->label ? ' · '.$rc->label : '' }}</option>
        @endforeach
    </select>
    <select id="pick-item" class="fns-input">
        <option value="">— ລາຍການຫຼັກ —</option>
    </select>
    <button type="button" class="fns-btn fns-btn-primary fns-btn-sm" onclick="addFromPicker()">+ ເພີ່ມ ລາຍການຫຼັກ</button>
</div>

{{-- Accordion groups --}}
<div id="groups">
@forelse($entriesByCat as $catCode => $catEntries)
    @php
        $catRef   = $refByCode[$catCode] ?? null;
        $catLabel = $catRef?->label ?? ($catEntries->first()->main_cat ?? '');
        $itemsGrouped = $catEntries->groupBy('main_item_code')
            ->sortBy(fn ($g, $ic) => $refByCode[$ic]->sort_order ?? 9999);
    @endphp
    <div class="cat-group" data-cat-code="{{ $catCode }}">
        <div class="cat-head">
            <span class="cat-title">{{ $catCode }}{{ $catLabel ? ' · '.$catLabel : '' }}</span>
            <span class="cat-total-wrap"><strong class="cat-total">{{ number_format($catEntries->sum('total'), 0) }}</strong> ກີບ</span>
        </div>
        <div class="cat-items">
        @foreach($itemsGrouped as $itemCode => $itemEntries)
            @php
                $itemRef   = $refByCode[$itemCode] ?? null;
                $itemLabel = $itemRef?->label ?? ($itemEntries->first()->main_item ?? '');
            @endphp
            <div class="item-group collapsed" data-cat-code="{{ $catCode }}" data-cat-label="{{ $catLabel }}"
                 data-item-code="{{ $itemCode }}" data-item-label="{{ $itemLabel }}">
                <div class="item-head">
                    <span class="item-toggle">▼</span>
                    <span class="item-title">{{ $itemCode }}{{ $itemLabel ? ' · '.$itemLabel : '' }}</span>
                    <span class="item-total-wrap"><strong class="item-total">{{ number_format($itemEntries->sum('total'), 0) }}</strong> ກີບ</span>
                    <button type="button" class="btn-del-group" title="ລຶບກຸ່ມ">✕</button>
                </div>
                <div class="item-body-wrap">
                    <table class="fns-table detail-table">
                        @include('dashboards.finance_head.expense._detail_head')
                        <tbody class="item-body">
                            @foreach($itemEntries as $e)
                                @include('dashboards.finance_head.expense._entry_row', ['e' => $e])
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn-add-row fns-btn fns-btn-secondary fns-btn-sm">+ ເພີ່ມລາຍການ</button>
                </div>
            </div>
        @endforeach
        </div>
    </div>
@empty
    <div class="fns-card" style="padding:2rem;text-align:center;color:#94a3b8;">
        ຍັງບໍ່ມີລາຍການ — ກົດ "+ ເພີ່ມ ລາຍການຫຼັກ" ເພື່ອເລີ່ມ
    </div>
@endforelse
</div>

{{-- ===== Ref-code management modal ===== --}}
<div id="refModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:600px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="margin:0;font-size:1rem;">ຈັດການລະຫັດອ້າງອີງ</h3>
            <button type="button" class="fns-btn fns-btn-secondary fns-btn-sm" onclick="closeRefModal()">ປິດ</button>
        </div>

        {{-- Add ໝວດຫຼັກ (level 1) — code typed directly --}}
        <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.store') }}" class="rc-add">
            @csrf
            <span class="rc-add-label">ເພີ່ມ ໝວດຫຼັກ</span>
            <input type="text" name="code" class="fns-input" style="width:80px;" placeholder="2.3" required>
            <input type="text" name="label" class="fns-input" style="flex:1;min-width:120px;" placeholder="ຊື່ໝວດ">
            <button type="submit" class="fns-btn fns-btn-primary fns-btn-sm">ເພີ່ມ</button>
        </form>

        {{-- Add ລາຍການຫຼັກ (level 2) — pick parent, code auto-built --}}
        <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.store') }}" class="rc-add">
            @csrf
            <span class="rc-add-label">ເພີ່ມ ລາຍການຫຼັກ</span>
            <select name="parent" class="fns-input" style="width:150px;" required>
                <option value="">— ໝວດຫຼັກ —</option>
                @foreach($rcLevel1 as $p)
                    <option value="{{ $p->code }}">{{ $p->code }}{{ $p->label ? ' · '.$p->label : '' }}</option>
                @endforeach
            </select>
            <input type="text" name="label" class="fns-input" style="flex:1;min-width:120px;" placeholder="ຊື່ລາຍການ">
            <button type="submit" class="fns-btn fns-btn-primary fns-btn-sm">ເພີ່ມ</button>
        </form>

        <div class="rc-list">
        @forelse($rcLevel1 as $p)
            @include('dashboards.finance_head.expense._refcode_row', ['rc' => $p, 'isCat' => true])
            @foreach(($rcChildren[$p->code] ?? collect())->sortBy('code') as $c)
                @include('dashboards.finance_head.expense._refcode_row', ['rc' => $c, 'isCat' => false])
            @endforeach
        @empty
            <div style="text-align:center;color:#94a3b8;padding:1rem;">ຍັງບໍ່ມີລະຫັດອ້າງອີງ — ເພີ່ມ ໝວດຫຼັກ ກ່ອນ</div>
        @endforelse
        </div>
    </div>
</div>

<style>
.group-picker { display:flex;gap:8px;align-items:center;margin-bottom:1rem;flex-wrap:wrap; }
.group-picker .fns-input { width:auto;min-width:200px; }

.cat-group { margin-bottom:1.2rem;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.cat-head { display:flex;align-items:center;gap:10px;background:var(--fns-navy);color:#fff;padding:9px 14px;font-weight:700; }
.cat-head .cat-title { flex:1; }
.cat-head .cat-total-wrap { white-space:nowrap; }
.cat-items { padding:0 0 0 0; background:#fff; }

.item-group { border-top:1px solid #e2e8f0; }
.item-head { display:flex;align-items:center;gap:10px;background:#f1f5f9;padding:7px 14px;cursor:pointer;user-select:none; }
.item-head .item-toggle { display:inline-block;transition:transform 0.15s;color:var(--fns-navy);font-size:0.7rem; }
.item-group.collapsed .item-toggle { transform:rotate(-90deg); }
.item-group.collapsed .item-body-wrap { display:none; }
.item-head .item-title { flex:1;font-weight:600;color:var(--fns-navy);font-size:0.86rem; }
.item-head .item-total-wrap { white-space:nowrap;font-weight:600;color:var(--fns-navy);font-size:0.85rem; }
.item-head .btn-del-group { background:none;border:none;color:#ef4444;cursor:pointer;font-size:0.95rem;padding:2px 6px; }
.item-body-wrap { padding:0 14px 12px; }

.detail-table { margin:0;font-size:0.78rem;width:100%; }
.detail-table th, .detail-table td { padding:2px 4px; }
.detail-table .gi {
    border:none;background:transparent;font-size:0.78rem;padding:3px 4px;width:100%;
    outline:none;font-family:inherit;color:inherit;transition:background 0.15s;
}
.detail-table .gi:focus { background:#eff6ff;border-radius:3px;outline:2px solid #93c5fd;outline-offset:-1px; }
.detail-table .gi-invalid { animation:flash-red 0.8s ease; }
.detail-table input[type=number].gi { text-align:right; }
.btn-add-row { margin-top:6px; }

.rc-add { display:flex;gap:6px;align-items:center;margin-bottom:8px;flex-wrap:wrap; }
.rc-add-label { font-size:0.75rem;font-weight:600;color:var(--fns-navy);width:110px; }
.rc-list { margin-top:10px;border-top:1px solid #e2e8f0;padding-top:8px; }
.rc-row { display:flex;gap:6px;align-items:center;padding:3px 0; }
.rc-row.rc-cat { margin-top:6px; }
.rc-row.rc-cat .rc-code, .rc-row.rc-cat .rc-label { font-weight:700;color:var(--fns-navy); }
.rc-row.rc-child { padding-left:22px; }
.rc-row.rc-child::before { content:"└";color:#cbd5e1;margin-left:-16px;margin-right:4px; }
.rc-edit { display:flex;gap:6px;flex:1; }
.rc-edit .rc-code { width:80px; }
.rc-edit .rc-label { flex:1; }
.detail-table tr.row-saving { opacity:0.55;pointer-events:none; }
.detail-table tr.row-saved td { animation:flash-green 0.8s ease; }
.detail-table tr.row-error td { animation:flash-red 0.8s ease; }
@keyframes flash-green { 0%,100%{background:inherit} 20%{background:#bbf7d0} }
@keyframes flash-red   { 0%,100%{background:inherit} 20%{background:#fecaca} }
</style>

<script>
const COA_MAP = @json($coaMap);
const REF_CODES = @json($refCodes->map(fn ($r) => ['code' => $r->code, 'label' => $r->label])->values());

const REF_BY_CODE = {};
REF_CODES.forEach(r => REF_BY_CODE[r.code] = r);

// Level-1 code (e.g. "2.1") -> array of its Level-2 children (e.g. 2.1.1...).
const REF_CHILDREN = {};
REF_CODES.forEach(r => {
    if ((r.code.match(/\./g) || []).length === 2) {
        const parent = r.code.slice(0, r.code.lastIndexOf('.'));
        (REF_CHILDREN[parent] = REF_CHILDREN[parent] || []).push(r);
    }
});

function openRefModal(){ document.getElementById('refModal').style.display='flex'; }
function closeRefModal(){ document.getElementById('refModal').style.display='none'; }

const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const PLAN_ID = document.querySelector('.group-picker').dataset.planId;
const numFmt = new Intl.NumberFormat('en-US', {maximumFractionDigits:0});
const f = (row, cls) => row.querySelector('.'+cls);
const num = (row, cls) => parseFloat(f(row, cls)?.value) || 0;
const val = (row, cls) => f(row, cls)?.value ?? '';
const money = s => parseFloat((s || '0').replace(/,/g,'')) || 0;

// ---- Totals ----
function recalc(row){
    // A row with a ໝາຍເຫດ is a note line — it never carries an amount.
    const hasNote = val(row,'gi-note').trim() !== '';
    const total = hasNote ? 0 : (
        (num(row,'gi-r1') + num(row,'gi-r2'))
        * (num(row,'gi-qty') || 0) * (num(row,'gi-period') || 0) * (num(row,'gi-freq') || 0)
        + num(row,'gi-addon')
    );
    const cell = row.querySelector('.cell-total');
    if (cell) cell.textContent = numFmt.format(total);
    const grp = row.closest('.item-group');
    if (grp) recalcItem(grp);
}
function recalcItem(grp){
    let s = 0;
    grp.querySelectorAll('.item-body .cell-total').forEach(c => s += money(c.textContent));
    grp.querySelector('.item-total').textContent = numFmt.format(s);
    recalcCat(grp.closest('.cat-group'));
    recalcGrand();
}
function recalcCat(cat){
    if (!cat) return;
    let s = 0;
    cat.querySelectorAll('.item-group .item-total').forEach(t => s += money(t.textContent));
    cat.querySelector('.cat-total').textContent = numFmt.format(s);
}
function recalcGrand(){
    let s = 0;
    document.querySelectorAll('.item-group .item-total').forEach(t => s += money(t.textContent));
    document.getElementById('grand-total').textContent = numFmt.format(s);
}

// Resolve the per-row account code to its chart_of_accounts id; reject garbage.
function applyCoa(row){
    const acctEl = f(row,'gi-acct');
    const code = acctEl.value.trim();
    const info = COA_MAP[code];
    f(row,'gi-acctid').value = info ? info.id : '';
    if (code && !info) {
        acctEl.value = '';
        acctEl.classList.add('gi-invalid');
        setTimeout(() => acctEl.classList.remove('gi-invalid'), 900);
    }
}

// ---- Save / delete a detail row ----
async function saveRow(row){
    const sub = val(row,'gi-sub').trim();
    if (!sub) return;

    const grp = row.closest('.item-group');
    const d = grp.dataset;
    const itemId = row.dataset.itemId;
    const payload = {
        plan_id:             PLAN_ID,
        main_cat_code:       d.catCode || null,
        main_cat:            d.catLabel || null,
        main_item_code:      d.itemCode || null,
        main_item:           d.itemLabel || null,
        ref_code:            d.itemCode || null,
        chart_of_account_id: f(row,'gi-acctid').value || null,
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

        if (!itemId && data.entry?.id) row.dataset.itemId = data.entry.id;
        if (data.entry?.total !== undefined) {
            const cell = row.querySelector('.cell-total');
            if (cell) cell.textContent = numFmt.format(parseFloat(data.entry.total));
        }
        recalcItem(grp);
        row.classList.add('row-saved');
        setTimeout(() => row.classList.remove('row-saved'), 900);
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
    }
}

async function deleteRow(row){
    const grp = row.closest('.item-group');
    const itemId = row.dataset.itemId;
    if (!itemId) { row.remove(); recalcItem(grp); return; }
    if (!confirm('ລຶບລາຍການ?')) return;
    row.classList.add('row-saving');
    try {
        const res = await fetch(`/head-of-finance/expense-entries/${itemId}`, {
            method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF},
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error();
        row.remove(); recalcItem(grp);
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
    }
}

// ---- Add rows / groups ----
function addDetailRow(grp){
    const tr = document.getElementById('detail-row-tpl').content.firstElementChild.cloneNode(true);
    grp.querySelector('.item-body').appendChild(tr);
    bindRow(tr);
    tr.querySelector('.gi-sub')?.focus();
    return tr;
}

function getOrCreateCatGroup(catCode){
    let cat = document.querySelector(`.cat-group[data-cat-code="${catCode}"]`);
    if (cat) return cat;
    cat = document.getElementById('cat-group-tpl').content.firstElementChild.cloneNode(true);
    cat.dataset.catCode = catCode;
    const ref = REF_BY_CODE[catCode] || {};
    cat.querySelector('.cat-title').textContent = catCode + (ref.label ? ' · ' + ref.label : '');
    document.getElementById('groups').appendChild(cat);
    return cat;
}

function addGroup(catCode, itemCode){
    if (!catCode || !itemCode) return;
    let grp = document.querySelector(`.item-group[data-item-code="${itemCode}"]`);
    if (grp) {
        grp.classList.remove('collapsed');
        grp.scrollIntoView({behavior:'smooth', block:'center'});
        addDetailRow(grp);
        return;
    }
    const cat = getOrCreateCatGroup(catCode);
    const ref = REF_BY_CODE[itemCode] || {};
    grp = document.getElementById('item-group-tpl').content.firstElementChild.cloneNode(true);
    grp.dataset.catCode  = catCode;
    grp.dataset.catLabel = REF_BY_CODE[catCode]?.label || '';
    grp.dataset.itemCode = itemCode;
    grp.dataset.itemLabel = ref.label || '';
    grp.querySelector('.item-title').textContent = itemCode + (ref.label ? ' · ' + ref.label : '');
    bindGroup(grp);
    cat.querySelector('.cat-items').appendChild(grp);
    addDetailRow(grp);
    recalcCat(cat);
}

function deleteGroup(grp){
    let hasSaved = false;
    grp.querySelectorAll('.grid-row').forEach(r => { if (r.dataset.itemId) hasSaved = true; });
    if (hasSaved) { alert('ກະລຸນາລຶບລາຍການຍ່ອຍທັງໝົດກ່ອນ'); return; }
    const cat = grp.closest('.cat-group');
    grp.remove();
    if (cat && !cat.querySelector('.item-group')) cat.remove();
    else recalcCat(cat);
    recalcGrand();
}

// ---- Picker ----
function fillPickItems(){
    const cat = document.getElementById('pick-cat').value;
    const sel = document.getElementById('pick-item');
    sel.innerHTML = '<option value="">— ລາຍການຫຼັກ —</option>';
    (REF_CHILDREN[cat] || []).forEach(r => {
        const o = document.createElement('option');
        o.value = r.code;
        o.textContent = r.code + (r.label ? ' · ' + r.label : '');
        sel.appendChild(o);
    });
}
function addFromPicker(){
    const c = document.getElementById('pick-cat').value;
    const i = document.getElementById('pick-item').value;
    if (!c || !i) { alert('ເລືອກ ໝວດຫຼັກ ແລະ ລາຍການຫຼັກ'); return; }
    addGroup(c, i);
    document.getElementById('pick-item').value = '';
}

// ---- Binding ----
function bindRow(row){
    row.querySelectorAll('.gi-r1,.gi-r2,.gi-qty,.gi-period,.gi-freq,.gi-addon,.gi-note').forEach(inp =>
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

function bindGroup(grp){
    const head = grp.querySelector('.item-head');
    head.addEventListener('click', e => {
        if (e.target.closest('button')) return;
        grp.classList.toggle('collapsed');
    });
    grp.querySelector('.btn-add-row').addEventListener('click', () => addDetailRow(grp));
    const delBtn = grp.querySelector('.btn-del-group');
    if (delBtn) delBtn.addEventListener('click', () => deleteGroup(grp));
}

document.getElementById('pick-cat').addEventListener('change', fillPickItems);
document.querySelectorAll('.item-group').forEach(bindGroup);
document.querySelectorAll('.grid-row').forEach(bindRow);
</script>

{{-- ===== Templates cloned by JS ===== --}}
<template id="detail-row-tpl">
    @include('dashboards.finance_head.expense._entry_row', ['e' => null])
</template>

<template id="cat-group-tpl">
    <div class="cat-group" data-cat-code="">
        <div class="cat-head">
            <span class="cat-title"></span>
            <span class="cat-total-wrap"><strong class="cat-total">0</strong> ກີບ</span>
        </div>
        <div class="cat-items"></div>
    </div>
</template>

<template id="item-group-tpl">
    <div class="item-group" data-cat-code="" data-cat-label="" data-item-code="" data-item-label="">
        <div class="item-head">
            <span class="item-toggle">▼</span>
            <span class="item-title"></span>
            <span class="item-total-wrap"><strong class="item-total">0</strong> ກີບ</span>
            <button type="button" class="btn-del-group" title="ລຶບກຸ່ມ">✕</button>
        </div>
        <div class="item-body-wrap">
            <table class="fns-table detail-table">
                @include('dashboards.finance_head.expense._detail_head')
                <tbody class="item-body"></tbody>
            </table>
            <button type="button" class="btn-add-row fns-btn fns-btn-secondary fns-btn-sm">+ ເພີ່ມລາຍການ</button>
        </div>
    </div>
</template>

@endsection
