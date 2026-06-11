@extends('layouts.admin')

@section('title', 'ເງິນເດືອນ ເດືອນ ' . $salaryPlan->monthLabel())
@section('page-title', 'ເງິນເດືອນ')

@section('content')

@php
    $monthNames = ['','ມັງກອນ','ກຸມພາ','ມີນາ','ເມສາ','ພຶດສະພາ','ມິຖຸນາ','ກໍລະກົດ','ສິງຫາ','ກັນຍາ','ຕຸລາ','ພະຈິກ','ທັນວາ'];
    $salaryAccountRows = collect($coa);
    $entriesByAccount = $entries->keyBy('chart_of_account_id');
    $coveredEntryIds = $salaryAccountRows
        ->map(fn ($account) => $entriesByAccount->get($account['id'])?->id)
        ->filter();
    $extraEntries = $entries->reject(fn ($entry) => $coveredEntryIds->contains($entry->id));
    $visibleRowCount = $salaryAccountRows->count() + $extraEntries->count();
@endphp

{{-- ===== Sticky context bar ===== --}}
<div class="smg-sticky-bar">
    <a href="{{ route('head_of_finance.manage-plan.index') }}" class="smg-back" title="ກັບຄືນ">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>ກັບຄືນ</span>
    </a>
    <div class="smg-id">
        <span class="smg-id-kicker">ເດືອນ {{ str_pad((string) $salaryPlan->month, 2, '0', STR_PAD_LEFT) }} / ສົກ</span>
        <span class="smg-id-num">{{ $salaryPlan->fiscal_year }}</span>
        <span class="smg-id-sub">{{ $monthNames[$salaryPlan->month] ?? '' }}</span>
    </div>
    <div class="smg-spacer"></div>
    <div class="smg-total">
        <span class="smg-total-label">ລວມ 12 ເດືອນ</span>
        <span class="smg-total-value"><strong id="grand-annual">{{ number_format((float) $entries->sum('annual_amount'), 0) }}</strong><span>ກີບ</span></span>
    </div>
    <div class="smg-total smg-total-sm">
        <span class="smg-total-label">ລວມ/ເດືອນ</span>
        <span class="smg-total-value-sm"><strong id="grand-monthly">{{ number_format((float) $entries->sum('monthly_total'), 0) }}</strong><span>ກີບ</span></span>
    </div>
</div>

{{-- COA picker popover (single shared instance) --}}
<div id="smg-coa-pop" class="smg-coa-pop" role="dialog" aria-label="ເລືອກລະຫັດບັນຊີ">
    <div class="smg-coa-pop-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input id="smg-coa-search" type="text" placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ບັນຊີ..." autocomplete="off">
    </div>
    <div id="smg-coa-list" class="smg-coa-list" role="listbox"></div>
    <div id="smg-coa-empty" class="smg-coa-empty" style="display:none;">ບໍ່ພົບລະຫັດທີ່ກົງກັນ</div>
</div>

{{-- ===== Toolbox ===== --}}
<section class="smg-toolbox">
    <span class="smg-meta" id="smg-meta">{{ $visibleRowCount }} ລາຍການ</span>
</section>

{{-- ===== Entry table ===== --}}
<div class="smg-table-wrap" data-plan-id="{{ $salaryPlan->id }}">
    <table class="smg-table">
        <thead>
            <tr>
                <th style="width:200px;">ລະຫັດບັນຊີ</th>
                <th>ຊື່ບັນຊີ</th>
                <th style="width:88px; text-align:center;">ຈຳນວນພົນ</th>
                <th style="width:130px; text-align:right;">ໂອນເຂົ້າ ATM</th>
                <th style="width:130px; text-align:right;">ຖອນເງິນສົດ</th>
                <th style="width:160px;">ໝາຍເຫດ</th>
            </tr>
        </thead>
        <tbody id="smg-body">
            @foreach($salaryAccountRows as $account)
                @include('dashboards.finance_head.salary._entry_row', [
                    'e' => $entriesByAccount->get($account['id']),
                    'account' => $account,
                ])
            @endforeach

            @foreach($extraEntries as $e)
                @include('dashboards.finance_head.salary._entry_row', ['e' => $e, 'account' => null])
            @endforeach
        </tbody>
    </table>

    <div class="smg-empty" id="smg-empty" @if($visibleRowCount) style="display:none;" @endif>
        <div class="smg-empty-num">00</div>
        <h3 class="smg-empty-title">ຍັງບໍ່ມີລາຍການ</h3>
        <p class="smg-empty-sub">ບໍ່ພົບລະຫັດບັນຊີ 60 ຫຼື 61 ສຳລັບສະແດງໃນຕາຕະລາງນີ້.</p>
    </div>
</div>

{{-- ===== Toast container ===== --}}
<div id="smgToasts" class="smg-toasts" aria-live="polite"></div>

<style>
    /* === Sticky bar === */
    .smg-sticky-bar {
        position: sticky; top: 0; z-index: 50;
        display: flex; align-items: center; gap: .9rem;
        padding: .65rem 1rem; margin: -1rem -1rem 1.1rem;
        background: rgba(255,255,255,0.96); backdrop-filter: blur(8px);
        border-bottom: 1px solid var(--fns-gray-200);
        box-shadow: 0 4px 14px -10px rgba(17,27,51,0.18);
    }
    .smg-back {
        display:inline-flex; align-items:center; justify-content:center;
        gap: .4rem; min-width: 36px; height: 36px;
        padding: 0 .75rem;
        background: var(--fns-gray-100); border-radius: 8px;
        font-size: .78rem; font-weight: 700;
        color: var(--fns-navy); text-decoration:none;
        transition: background .15s, transform .12s;
    }
    .smg-back:hover { background: var(--fns-gray-200); transform: translateX(-2px); }
    .smg-back svg { width: 16px; height: 16px; }

    .smg-id { display:flex; flex-direction:column; line-height: 1; min-width: 0; }
    .smg-id-kicker {
        font-size: .58rem; letter-spacing: .2em; text-transform: uppercase;
        color: var(--fns-gray-400); font-weight: 700;
    }
    .smg-id-num {
        font-family: 'Cinzel', serif; font-size: 1.4rem; font-weight: 700;
        color: var(--fns-navy); margin-top: .12rem; letter-spacing: -.01em;
    }
    .smg-id-sub { font-size: .72rem; color: var(--fns-gray-600); font-weight: 500; margin-top: .12rem; }

    .smg-spacer { flex: 1; }

    .smg-total {
        display: flex; flex-direction: column; align-items: flex-end; line-height: 1;
        padding-left: 1rem; border-left: 1px solid var(--fns-gray-200);
    }
    .smg-total-label {
        font-size: .6rem; letter-spacing: .2em; text-transform: uppercase;
        color: var(--fns-gray-400); font-weight: 700;
    }
    .smg-total-value { margin-top: .3rem; font-family: 'Cinzel', serif; font-size: 1.35rem; color: var(--fns-navy); font-weight: 700; }
    .smg-total-value span { font-family: 'Noto Sans Lao', sans-serif; font-size: .65rem; color: var(--fns-gray-400); margin-left: .35rem; font-weight: 500; }
    .smg-total-value-sm { margin-top: .3rem; font-family: 'Cinzel', serif; font-size: 1rem; color: var(--fns-gray-600); font-weight: 600; }
    .smg-total-value-sm span { font-family: 'Noto Sans Lao', sans-serif; font-size: .6rem; color: var(--fns-gray-400); margin-left: .25rem; font-weight: 500; }

    /* === Toolbox === */
    .smg-toolbox {
        display: flex; align-items: center; gap: 1rem;
        padding: .9rem 1rem; margin-bottom: 1rem;
        background: #fff; border: 1px solid var(--fns-gray-200);
        border-radius: 10px;
    }
    .smg-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .55rem .95rem; border-radius: 8px;
        font-family: inherit; font-size: .82rem; font-weight: 700;
        border: 1px solid transparent; cursor: pointer;
        transition: background .15s, color .15s, transform .1s;
    }
    .smg-btn svg { width: 14px; height: 14px; }
    .smg-btn-gold { background: var(--fns-gold); color: var(--fns-navy-deep); box-shadow: 0 2px 8px -2px rgba(201,153,26,0.45); }
    .smg-btn-gold:hover { background: var(--fns-gold-light, #e7be4f); transform: translateY(-1px); }
    .smg-meta { font-size: .74rem; color: var(--fns-gray-400); }

    /* === Table === */
    .smg-table-wrap {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.2rem;
    }
    .smg-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .smg-table thead {
        background: linear-gradient(135deg, var(--fns-navy) 0%, var(--fns-navy-mid) 100%);
        color: #fff;
    }
    .smg-table thead th {
        padding: .6rem .55rem;
        font-size: .7rem; font-weight: 700; letter-spacing: .03em;
        text-align: left;
    }
    .smg-table tbody td {
        padding: 4px 6px;
        border-bottom: 1px dashed var(--fns-gray-200);
    }
    .smg-table tbody tr:last-child td { border-bottom: none; }
    .smg-table tbody tr:hover { background: #fdfbf3; }

    .smg-input {
        width: 100%;
        background: transparent; border: 1px solid transparent; border-radius: 5px;
        padding: 5px 7px; font-family: inherit; font-size: .8rem;
        color: var(--fns-navy); outline: none;
        transition: background .12s, border-color .12s, box-shadow .12s;
    }
    .smg-input::placeholder { color: var(--fns-gray-400); }
    .smg-input:hover { background: #fafaf7; }
    .smg-input:focus {
        background: #fff; border-color: var(--fns-navy-light);
        box-shadow: 0 0 0 2px rgba(46,63,110,0.12);
    }
    .smg-input.is-invalid { animation: smgFlashRed .8s ease; }

    .smg-table input[type=number].smg-input,
    .smg-table .smg-cell-total { text-align: right; font-variant-numeric: tabular-nums; }
    .smg-table .smg-cell-center { text-align: center; }
    .smg-table .smg-cell-total {
        padding: 4px 8px;
        font-family: 'Cinzel', serif; font-weight: 700; color: var(--fns-navy);
    }
    .smg-table .smg-cell-annual {
        font-family: 'Cinzel', serif; font-weight: 700; color: var(--fns-navy);
        background: rgba(201,153,26,0.04);
    }

    .smg-name {
        font-size: .8rem; color: var(--fns-gray-600); padding: 0 .55rem;
        line-height: 1.4; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 100%;
    }
    .smg-name-empty { color: var(--fns-gray-400); font-style: italic; }

    /* === COA picker trigger === */
    .smg-coa-trigger {
        display: inline-flex; align-items: center; justify-content: space-between;
        gap: .35rem; width: 100%;
        padding: 5px 8px;
        background: transparent;
        border: 1px solid transparent; border-radius: 6px;
        font-family: 'Cinzel', serif; font-size: .82rem; font-weight: 600;
        color: var(--fns-navy); cursor: pointer;
        text-align: left; letter-spacing: .02em;
        transition: background .12s, border-color .12s, box-shadow .12s;
    }
    .smg-coa-trigger:hover { background: #fafaf7; }
    .smg-coa-trigger:disabled {
        cursor: default;
        color: var(--fns-navy);
    }
    .smg-coa-trigger:disabled:hover { background: transparent; }
    .smg-coa-trigger:disabled svg { display: none; }
    .smg-coa-trigger.is-open {
        background: #fff;
        border-color: var(--fns-navy-light);
        box-shadow: 0 0 0 2px rgba(46,63,110,0.12);
    }
    .smg-coa-trigger.is-empty {
        font-family: 'Noto Sans Lao', sans-serif;
        font-weight: 500; color: var(--fns-gray-400); font-style: italic;
    }
    .smg-coa-trigger svg {
        width: 12px; height: 12px;
        color: var(--fns-gray-400); flex-shrink: 0;
        transition: transform .18s;
    }
    .smg-coa-trigger.is-open svg { transform: rotate(180deg); color: var(--fns-navy); }
    .smg-coa-trigger-code { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* === COA popover === */
    .smg-coa-pop {
        display: none;
        position: fixed; z-index: 100;
        width: 380px; max-width: 95vw;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 10px;
        box-shadow: 0 14px 40px -12px rgba(17,27,51,0.35);
        overflow: hidden;
        animation: smgPopIn .14s ease-out;
    }
    .smg-coa-pop.is-open { display: block; }
    @keyframes smgPopIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

    .smg-coa-pop-search {
        display: flex; align-items: center; gap: .5rem;
        padding: .55rem .75rem;
        background: var(--fns-gray-100);
        border-bottom: 1px solid var(--fns-gray-200);
    }
    .smg-coa-pop-search svg { width: 14px; height: 14px; color: var(--fns-gray-400); flex-shrink: 0; }
    .smg-coa-pop-search input {
        flex: 1; border: none; outline: none; background: transparent;
        font-family: inherit; font-size: .85rem; color: var(--fns-navy);
    }
    .smg-coa-list {
        max-height: 320px; overflow-y: auto; padding: .3rem;
    }
    .smg-coa-item {
        display: flex; align-items: baseline; gap: .55rem;
        padding: .45rem .65rem; border-radius: 6px;
        cursor: pointer; font-size: .82rem; color: var(--fns-navy);
        transition: background .1s;
    }
    .smg-coa-item:hover, .smg-coa-item.is-active {
        background: rgba(26,39,68,0.06);
    }
    .smg-coa-item.is-selected {
        background: rgba(201,153,26,0.12);
        color: #8b6a12;
    }
    .smg-coa-item-code {
        font-family: 'Cinzel', serif; font-weight: 700;
        min-width: 70px; flex-shrink: 0;
    }
    .smg-coa-item-name {
        font-weight: 500; color: var(--fns-gray-600);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .smg-coa-item.is-selected .smg-coa-item-name { color: #8b6a12; }
    .smg-coa-empty { padding: 1.2rem; text-align: center; font-size: .82rem; color: var(--fns-gray-400); }

    .smg-row.row-saving { opacity: .55; pointer-events: none; }
    .smg-row.row-saved td { animation: smgFlashGreen .9s ease; }
    .smg-row.row-error td { animation: smgFlashRed .9s ease; }
    @keyframes smgFlashGreen { 0%,100% { background: inherit; } 25% { background: #bbf7d0; } }
    @keyframes smgFlashRed   { 0%,100% { background: inherit; } 25% { background: #fecaca; } }

    /* === Empty === */
    .smg-empty {
        padding: 3.2rem 1.5rem; text-align: center; color: var(--fns-gray-600);
    }
    .smg-empty-num {
        font-family: 'Cinzel', serif; font-size: 4rem; font-weight: 700;
        color: var(--fns-gray-200); line-height: 1; margin-bottom: .6rem;
    }
    .smg-empty-title { font-size: 1.1rem; color: var(--fns-navy); font-weight: 700; margin: .3rem 0 .5rem; }
    .smg-empty-sub { font-size: .85rem; margin: 0; }

    /* === Toasts === */
    .smg-toasts {
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9500;
        display: flex; flex-direction: column; gap: .55rem; pointer-events: none;
    }
    .smg-toast {
        display: flex; align-items: center; gap: .55rem;
        padding: .65rem .9rem; border-radius: 8px;
        background: var(--fns-navy-deep); color: #fff;
        box-shadow: 0 12px 30px -10px rgba(17,27,51,0.5);
        font-size: .8rem; pointer-events: auto;
        animation: smgToastIn .22s ease-out;
    }
    .smg-toast.is-success { background: #166534; }
    .smg-toast.is-error { background: #991b1b; }
    .smg-toast svg { width: 15px; height: 15px; }
    @keyframes smgToastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
</style>

<script>
(function () {
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const PLAN_ID = document.querySelector('.smg-table-wrap').dataset.planId;
    const COA_LIST = @json($coa);
    const COA_BY_ID = {};
    COA_LIST.forEach(c => COA_BY_ID[c.id] = c);

    const fmt = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });
    const $body = document.getElementById('smg-body');
    const $empty = document.getElementById('smg-empty');
    const $meta  = document.getElementById('smg-meta');
    const $grandM = document.getElementById('grand-monthly');
    const $grandA = document.getElementById('grand-annual');

    function num(el) { return parseFloat(el?.value || 0) || 0; }

    function recalc(row) {
        // Per-row total/annual cells were removed; sticky-bar grand totals still recalc below.
        recalcTotals();
    }

    function recalcTotals() {
        let monthly = 0, annual = 0;
        $body.querySelectorAll('.smg-row').forEach(r => {
            const atm = num(r.querySelector('.smg-atm'));
            const cash = num(r.querySelector('.smg-cash'));
            monthly += atm + cash;
            annual  += (atm + cash) * 12;
        });
        if ($grandM) $grandM.textContent = fmt.format(monthly);
        if ($grandA) $grandA.textContent = fmt.format(annual);
        const n = $body.querySelectorAll('.smg-row').length;
        if ($meta) $meta.textContent = `${n} ລາຍການ`;
        $empty.style.display = n ? 'none' : '';
    }

    function showToast(msg, kind = 'info') {
        const wrap = document.getElementById('smgToasts');
        const t = document.createElement('div');
        t.className = `smg-toast is-${kind}`;
        const icon = kind === 'success'
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>'
            : kind === 'error'
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
        t.innerHTML = icon + `<span>${msg}</span>`;
        wrap.appendChild(t);
        setTimeout(() => t.remove(), 2400);
    }

    // ── COA picker popover ─────────────────────────────────────
    const $pop      = document.getElementById('smg-coa-pop');
    const $popList  = document.getElementById('smg-coa-list');
    const $popInput = document.getElementById('smg-coa-search');
    const $popEmpty = document.getElementById('smg-coa-empty');
    let popRow = null;          // the row whose code we're editing
    let popTrigger = null;      // the button that opened it
    let popVisible = [];        // currently visible COA items (after filter)
    let popActiveIdx = 0;       // keyboard cursor

    function renderCoaList(q) {
        q = (q || '').trim().toLowerCase();
        const matchQ = c => c.code.toLowerCase().includes(q) || c.name.toLowerCase().includes(q);
        popVisible = COA_LIST.filter(c => !q || matchQ(c));
        const selectedId = popRow?.dataset.coaId || '';
        $popList.innerHTML = popVisible.map((c, i) =>
            `<div class="smg-coa-item${String(c.id) === selectedId ? ' is-selected' : ''}${i === popActiveIdx ? ' is-active' : ''}" data-id="${c.id}" role="option">
                <span class="smg-coa-item-code">${c.code}</span>
                <span class="smg-coa-item-name">${c.name}</span>
             </div>`
        ).join('');
        $popEmpty.style.display = popVisible.length ? 'none' : '';
        // Scroll the active item into view
        const active = $popList.querySelector('.smg-coa-item.is-active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    function openCoaPop(trigger, row) {
        closeCoaPop();
        popTrigger = trigger; popRow = row;
        trigger.classList.add('is-open');

        // Use position:fixed → viewport coords from getBoundingClientRect, no scroll offset
        const r = trigger.getBoundingClientRect();
        const popW = 380;
        const left = Math.min(r.left, window.innerWidth - popW - 12);
        $pop.style.top  = (r.bottom + 4) + 'px';
        $pop.style.left = Math.max(8, left) + 'px';
        $pop.classList.add('is-open');

        popActiveIdx = 0;
        $popInput.value = '';
        renderCoaList('');
        // Auto-scroll to selected if any
        const sel = $popList.querySelector('.smg-coa-item.is-selected');
        if (sel) sel.scrollIntoView({ block: 'center' });
        setTimeout(() => $popInput.focus(), 0);
    }

    function closeCoaPop() {
        $pop.classList.remove('is-open');
        if (popTrigger) popTrigger.classList.remove('is-open');
        popTrigger = null; popRow = null;
    }

    function selectCoa(coaId) {
        if (!popRow) return;
        const info = COA_BY_ID[coaId];
        if (!info) return;
        popRow.dataset.coaId = info.id;
        const codeEl = popRow.querySelector('.smg-coa-trigger-code');
        if (codeEl) codeEl.textContent = info.code;
        popRow.querySelector('.smg-coa-trigger')?.classList.remove('is-empty');
        const nameEl = popRow.querySelector('.smg-name');
        if (nameEl) {
            nameEl.textContent = info.name;
            nameEl.classList.remove('smg-name-empty');
            nameEl.title = info.name;
        }
        const rowToSave = popRow;
        closeCoaPop();
        saveRow(rowToSave);
    }

    $popInput.addEventListener('input', () => { popActiveIdx = 0; renderCoaList($popInput.value); });
    $popInput.addEventListener('keydown', e => {
        if (e.key === 'Escape') { e.preventDefault(); closeCoaPop(); popTrigger?.focus(); return; }
        if (e.key === 'ArrowDown') { e.preventDefault(); popActiveIdx = Math.min(popActiveIdx + 1, popVisible.length - 1); renderCoaList($popInput.value); return; }
        if (e.key === 'ArrowUp')   { e.preventDefault(); popActiveIdx = Math.max(popActiveIdx - 1, 0); renderCoaList($popInput.value); return; }
        if (e.key === 'Enter') {
            e.preventDefault();
            const item = popVisible[popActiveIdx];
            if (item) selectCoa(item.id);
        }
    });
    $popList.addEventListener('click', e => {
        const item = e.target.closest('.smg-coa-item');
        if (item) selectCoa(item.dataset.id);
    });
    // Close on outside click
    document.addEventListener('click', e => {
        if (!$pop.classList.contains('is-open')) return;
        if ($pop.contains(e.target)) return;
        if (e.target.closest('.smg-coa-trigger')) return;
        closeCoaPop();
    });
    // Close popover on page scroll (but NOT on scroll inside the popover's own list)
    window.addEventListener('scroll', (e) => {
        if ($pop.contains(e.target)) return;
        closeCoaPop();
    }, true);
    window.addEventListener('resize', () => closeCoaPop());

    async function saveRow(row) {
        const coaId = row.dataset.coaId;
        if (!coaId) return; // need a valid COA before persisting

        const itemId = row.dataset.itemId;
        const payload = {
            plan_id:             PLAN_ID,
            chart_of_account_id: coaId,
            person_count:        parseInt(row.querySelector('.smg-persons')?.value || 0, 10) || 0,
            atm_amount:          num(row.querySelector('.smg-atm')),
            cash_amount:         num(row.querySelector('.smg-cash')),
            remark:              row.querySelector('.smg-remark')?.value || null,
        };
        const url = itemId ? `/head-of-finance/salary-entries/${itemId}` : '/head-of-finance/salary-entries';
        const method = itemId ? 'PATCH' : 'POST';

        row.classList.add('row-saving');
        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            row.classList.remove('row-saving');
            if (!res.ok || !data.success) throw new Error(data.message || 'Error');

            const wasNew = !itemId;
            if (wasNew && data.entry?.id) row.dataset.itemId = data.entry.id;
            recalcTotals();
            row.classList.add('row-saved');
            setTimeout(() => row.classList.remove('row-saved'), 900);
            if (wasNew) showToast('ບັນທຶກລາຍການໃໝ່ສຳເລັດ', 'success');
        } catch {
            row.classList.remove('row-saving');
            row.classList.add('row-error');
            setTimeout(() => row.classList.remove('row-error'), 900);
            showToast('ບໍ່ສາມາດບັນທຶກໄດ້', 'error');
        }
    }

    function bindRow(row) {
        const trigger = row.querySelector('.smg-coa-trigger');
        trigger?.addEventListener('click', e => {
            e.stopPropagation();
            if (trigger.classList.contains('is-open')) closeCoaPop();
            else openCoaPop(trigger, row);
        });

        row.querySelectorAll('.smg-atm, .smg-cash').forEach(inp =>
            inp.addEventListener('input', () => recalc(row)));

        row.querySelectorAll('.smg-input').forEach(inp => {
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

    document.querySelectorAll('.smg-row').forEach(bindRow);
    recalcTotals();
})();
</script>

@endsection
