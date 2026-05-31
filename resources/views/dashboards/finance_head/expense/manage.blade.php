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

    $totalEntries = $expensePlan->entries->count();
@endphp

{{-- ===== Sticky context bar ===== --}}
<div class="mgr-sticky-bar">
    <a href="{{ route('head_of_finance.expense.index') }}" class="mgr-back" title="ກັບຄືນ">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>

    <div class="mgr-id">
        <span class="mgr-id-kicker">ສົກງົບປະມານ</span>
        <span class="mgr-id-num">{{ $expensePlan->fiscal_year }}</span>
    </div>

    <div class="mgr-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input id="entrySearch" type="text" placeholder="ຄົ້ນຫາລາຍການຍ່ອຍ ຫຼື ລະຫັດບັນຊີ..." autocomplete="off">
        <kbd class="mgr-kbd">/</kbd>
    </div>

    <div class="mgr-total">
        <span class="mgr-total-label">ງົບລວມ</span>
        <span class="mgr-total-value"><strong id="grand-total">{{ number_format($expensePlan->grandTotal(), 0) }}</strong><span class="mgr-total-unit">ກີບ</span></span>
    </div>

    <button type="button" class="mgr-icon-btn" onclick="openRefModal()" title="ຈັດການລະຫັດອ້າງອີງ">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
    </button>
</div>

{{-- ===== COA picker popover (shared across all rows) ===== --}}
<div id="xpop" class="xpop" role="dialog" aria-label="ເລືອກລະຫັດບັນຊີ">
    <div class="xpop-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input id="xpop-search" type="text" placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ບັນຊີ..." autocomplete="off">
    </div>
    <div id="xpop-list" class="xpop-list" role="listbox"></div>
    <div id="xpop-empty" class="xpop-empty" style="display:none;">ບໍ່ພົບລະຫັດທີ່ກົງກັນ</div>
</div>

{{-- ===== Add / control row ===== --}}
<section class="mgr-toolbox">
    <div class="mgr-picker group-picker" data-plan-id="{{ $expensePlan->id }}">
        <span class="mgr-picker-label">ເພີ່ມລາຍການຫຼັກ</span>
        <select id="pick-cat" class="mgr-select">
            <option value="">— ໝວດຫຼັກ —</option>
            @foreach($level1 as $rc)
                <option value="{{ $rc->code }}">{{ $rc->code }}{{ $rc->label ? ' · '.$rc->label : '' }}</option>
            @endforeach
        </select>
        <select id="pick-item" class="mgr-select">
            <option value="">— ລາຍການຫຼັກ —</option>
        </select>
        <button type="button" class="mgr-btn mgr-btn-gold" onclick="addFromPicker()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            ເພີ່ມ
        </button>
    </div>

    <div class="mgr-toolbox-right">
        <span class="mgr-meta" id="entryMeta">{{ $totalEntries }} ລາຍການ</span>
        <button type="button" class="mgr-btn mgr-btn-ghost" onclick="toggleAll(false)" title="ຂະຫຍາຍທັງໝົດ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            ຂະຫຍາຍທັງໝົດ
        </button>
        <button type="button" class="mgr-btn mgr-btn-ghost" onclick="toggleAll(true)" title="ຫຍໍ້ທັງໝົດ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
            ຫຍໍ້ທັງໝົດ
        </button>
    </div>
</section>

{{-- ===== Accordion groups ===== --}}
<div id="groups" class="mgr-groups">
@forelse($entriesByCat as $catCode => $catEntries)
    @php
        $catRef   = $refByCode[$catCode] ?? null;
        $catLabel = $catRef?->label ?? ($catEntries->first()->main_cat ?? '');
        $itemsGrouped = $catEntries->groupBy('main_item_code')
            ->sortBy(fn ($g, $ic) => $refByCode[$ic]->sort_order ?? 9999);
    @endphp
    <div class="cat-group" data-cat-code="{{ $catCode }}">
        <div class="cat-head">
            <span class="cat-code-chip">{{ $catCode }}</span>
            <span class="cat-title">{{ $catLabel ?: 'ໝວດ' }}</span>
            <span class="cat-total-wrap"><strong class="cat-total">{{ number_format($catEntries->sum('total'), 0) }}</strong><span class="cat-total-unit">ກີບ</span></span>
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
                    <span class="item-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></span>
                    <span class="item-code-chip">{{ $itemCode }}</span>
                    <span class="item-title">{{ $itemLabel ?: 'ລາຍການຫຼັກ' }}</span>
                    <span class="item-count">{{ $itemEntries->count() }} ແຖວ</span>
                    <span class="item-total-wrap"><strong class="item-total">{{ number_format($itemEntries->sum('total'), 0) }}</strong><span class="item-total-unit">ກີບ</span></span>
                    <button type="button" class="btn-del-group" title="ລຶບກຸ່ມ">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="item-body-wrap">
                    <div class="detail-scroll">
                        <table class="fns-table detail-table">
                            @include('dashboards.finance_head.expense._detail_head')
                            <tbody class="item-body">
                                @foreach($itemEntries as $e)
                                    @include('dashboards.finance_head.expense._entry_row', ['e' => $e])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn-add-row mgr-btn mgr-btn-ghost mgr-btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        ເພີ່ມລາຍການຍ່ອຍ
                    </button>
                </div>
            </div>
        @endforeach
        </div>
    </div>
@empty
    <div class="mgr-empty">
        <div class="mgr-empty-num">{{ $expensePlan->fiscal_year }}</div>
        <h3 class="mgr-empty-title">ຍັງບໍ່ມີລາຍການໃນແຜນນີ້</h3>
        <p class="mgr-empty-sub">ເລືອກ <strong>ໝວດຫຼັກ</strong> ແລະ <strong>ລາຍການຫຼັກ</strong> ດ້ານເທິງ ແລ້ວກົດ <strong>ເພີ່ມ</strong> ເພື່ອເລີ່ມຕົ້ນ.</p>
        <p class="mgr-empty-hint">ຍັງບໍ່ມີລະຫັດອ້າງອີງ? ກົດປຸ່ມເມນູໃນແຖບເທິງເພື່ອຈັດການ.</p>
    </div>
@endforelse
</div>

{{-- ===== Keyboard hint footer ===== --}}
<div class="mgr-kbd-bar">
    <span><kbd class="mgr-kbd">/</kbd> ຄົ້ນຫາ</span>
    <span><kbd class="mgr-kbd">Enter</kbd> ບັນທຶກແຖວ</span>
    <span><kbd class="mgr-kbd">Tab</kbd> ໄປຊ່ອງຖັດໄປ</span>
    <span><kbd class="mgr-kbd">Esc</kbd> ປິດໂມດອລ</span>
    <span class="mgr-kbd-note">ບັນທຶກອັດຕະໂນມັດເມື່ອອອກຈາກແຖວ</span>
</div>

{{-- ===== Toast container ===== --}}
<div id="mgrToasts" class="mgr-toasts" aria-live="polite"></div>

{{-- ===== Ref-code modal (kept functional; styled wrapper only) ===== --}}
<div id="refModal" class="mgr-modal-backdrop" role="dialog" aria-modal="true">
    <div class="mgr-modal">
        <div class="mgr-modal-head">
            <div>
                <span class="mgr-modal-kicker">ສ້າງ / ດັດແກ້</span>
                <h3 class="mgr-modal-title">ລະຫັດອ້າງອີງລາຍຈ່າຍ</h3>
            </div>
            <button type="button" class="mgr-modal-close" onclick="closeRefModal()" aria-label="ປິດ">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6l-12 12"/></svg>
            </button>
        </div>

        <div class="mgr-modal-body">
            {{-- Add ໝວດຫຼັກ (level 1) --}}
            <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.store') }}" class="rc-add">
                @csrf
                <span class="rc-add-label">ເພີ່ມໝວດຫຼັກ</span>
                <input type="text" name="code" class="mgr-input rc-code-input" placeholder="2.3" required>
                <input type="text" name="label" class="mgr-input rc-label-input" placeholder="ຊື່ໝວດ">
                <button type="submit" class="mgr-btn mgr-btn-primary mgr-btn-sm">ເພີ່ມ</button>
            </form>

            {{-- Add ລາຍການຫຼັກ (level 2) --}}
            <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.store') }}" class="rc-add">
                @csrf
                <span class="rc-add-label">ເພີ່ມລາຍການຫຼັກ</span>
                <select name="parent" class="mgr-select rc-parent-select" required>
                    <option value="">— ໝວດຫຼັກ —</option>
                    @foreach($rcLevel1 as $p)
                        <option value="{{ $p->code }}">{{ $p->code }}{{ $p->label ? ' · '.$p->label : '' }}</option>
                    @endforeach
                </select>
                <input type="text" name="label" class="mgr-input rc-label-input" placeholder="ຊື່ລາຍການ">
                <button type="submit" class="mgr-btn mgr-btn-primary mgr-btn-sm">ເພີ່ມ</button>
            </form>

            <div class="rc-list">
            @forelse($rcLevel1 as $p)
                @include('dashboards.finance_head.expense._refcode_row', ['rc' => $p, 'isCat' => true])
                @foreach(($rcChildren[$p->code] ?? collect())->sortBy('code') as $c)
                    @include('dashboards.finance_head.expense._refcode_row', ['rc' => $c, 'isCat' => false])
                @endforeach
            @empty
                <div class="rc-empty">ຍັງບໍ່ມີລະຫັດອ້າງອີງ — ເພີ່ມໝວດຫຼັກກ່ອນ</div>
            @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===================================================================== --}}
{{-- Styles                                                                  --}}
{{-- ===================================================================== --}}
<style>
    /* ===== Sticky context bar ===== */
    .mgr-sticky-bar {
        position: sticky; top: 0; z-index: 50;
        display: grid;
        grid-template-columns: auto auto 1fr auto auto;
        align-items: center; gap: .9rem;
        padding: .65rem 1rem;
        margin: -1rem -1rem 1.1rem;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid var(--fns-gray-200);
        box-shadow: 0 4px 14px -10px rgba(17,27,51,0.18);
    }
    .mgr-back {
        display:inline-flex; align-items:center; justify-content:center;
        width: 36px; height: 36px;
        background: var(--fns-gray-100); border-radius: 8px;
        color: var(--fns-navy); text-decoration:none;
        transition: background .15s, transform .12s;
    }
    .mgr-back:hover { background: var(--fns-gray-200); transform: translateX(-2px); }
    .mgr-back svg { width: 16px; height: 16px; }

    .mgr-id { display:flex; flex-direction:column; line-height: 1; }
    .mgr-id-kicker {
        font-size: .58rem; letter-spacing: .22em; text-transform: uppercase;
        color: var(--fns-gray-400); font-weight: 700;
    }
    .mgr-id-num {
        font-family: 'Cinzel', serif; font-size: 1.4rem; font-weight: 700;
        color: var(--fns-navy); margin-top: .15rem;
    }

    .mgr-search {
        display:flex; align-items:center; gap:.5rem;
        padding: .5rem .75rem;
        background: var(--fns-gray-100); border: 1px solid transparent; border-radius: 8px;
        max-width: 520px;
        transition: background .15s, border-color .15s, box-shadow .15s;
    }
    .mgr-search:focus-within {
        background: #fff; border-color: var(--fns-navy-light);
        box-shadow: 0 0 0 3px rgba(46,63,110,0.1);
    }
    .mgr-search svg { width: 14px; height: 14px; color: var(--fns-gray-400); }
    .mgr-search input {
        flex: 1; border: none; outline: none; background: transparent;
        font-family: inherit; font-size: .85rem; color: var(--fns-navy);
    }

    .mgr-total {
        display:flex; flex-direction:column; align-items:flex-end; line-height: 1;
        padding-left: .8rem; border-left: 1px solid var(--fns-gray-200);
    }
    .mgr-total-label {
        font-size: .6rem; letter-spacing: .2em; text-transform: uppercase;
        color: var(--fns-gray-400); font-weight: 700;
    }
    .mgr-total-value { margin-top: .25rem; font-family: 'Cinzel', serif; font-size: 1.2rem; color: var(--fns-navy); font-weight: 700; }
    .mgr-total-unit { font-family: 'Noto Sans Lao', sans-serif; font-size: .65rem; color: var(--fns-gray-400); margin-left: .35rem; font-weight: 500; }

    .mgr-icon-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width: 36px; height: 36px;
        background: var(--fns-navy); color: #fff; border: none; border-radius: 8px;
        cursor: pointer; transition: background .15s;
    }
    .mgr-icon-btn:hover { background: var(--fns-navy-light); }
    .mgr-icon-btn svg { width: 16px; height: 16px; }

    /* ===== Toolbox ===== */
    .mgr-toolbox {
        display:flex; align-items:center; gap: 1rem; flex-wrap: wrap;
        padding: .9rem 1rem;
        margin-bottom: 1.2rem;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 10px;
    }
    .mgr-picker { display:flex; align-items:center; gap:.55rem; flex: 1 1 auto; flex-wrap: wrap; }
    .mgr-picker-label {
        font-size: .68rem; letter-spacing: .12em; text-transform: uppercase;
        color: var(--fns-gray-600); font-weight: 700; margin-right: .25rem;
    }
    .mgr-select, .mgr-input {
        padding: .5rem .65rem; border: 1px solid var(--fns-gray-200);
        border-radius: 7px; font-family: inherit; font-size: .82rem;
        color: var(--fns-navy); background: #fff;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .mgr-select { min-width: 200px; }
    .mgr-select:focus, .mgr-input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }

    .mgr-toolbox-right { display:flex; align-items:center; gap: .5rem; }
    .mgr-meta { font-size: .72rem; color: var(--fns-gray-400); margin-right: .4rem; }

    .mgr-btn {
        display: inline-flex; align-items: center; justify-content:center; gap: .35rem;
        padding: .5rem .85rem; border-radius: 7px;
        font-family: inherit; font-size: .78rem; font-weight: 600;
        border: 1px solid transparent; cursor: pointer;
        transition: background .15s, color .15s, border-color .15s, transform .1s;
    }
    .mgr-btn svg { width: 13px; height: 13px; }
    .mgr-btn-gold { background: var(--fns-gold); color: var(--fns-navy-deep); box-shadow: 0 2px 8px -2px rgba(201,153,26,0.45); }
    .mgr-btn-gold:hover { background: var(--fns-gold-light, #e7be4f); transform: translateY(-1px); }
    .mgr-btn-primary { background: var(--fns-navy); color: #fff; }
    .mgr-btn-primary:hover { background: var(--fns-navy-light); }
    .mgr-btn-ghost { background: #fff; color: var(--fns-navy); border-color: var(--fns-gray-200); }
    .mgr-btn-ghost:hover { background: var(--fns-gray-100); border-color: var(--fns-gray-400); }
    .mgr-btn-sm { padding: .35rem .65rem; font-size: .72rem; border-radius: 6px; }

    /* ===== Accordion groups ===== */
    .mgr-groups { display:flex; flex-direction:column; gap: 1rem; }

    .cat-group {
        border-radius: 10px; overflow: hidden;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        box-shadow: 0 1px 2px rgba(17,27,51,0.04);
    }
    .cat-head {
        display:flex; align-items:center; gap: .7rem;
        padding: .65rem .95rem;
        background: linear-gradient(135deg, var(--fns-navy) 0%, var(--fns-navy-mid) 100%);
        color: #fff; font-weight: 700;
    }
    .cat-code-chip {
        font-family: 'Cinzel', serif;
        background: rgba(255,255,255,0.16); color: #fff;
        padding: .18rem .55rem; border-radius: 5px;
        font-size: .8rem; letter-spacing: .05em;
    }
    .cat-title { flex: 1; font-size: .9rem; letter-spacing: .01em; }
    .cat-total-wrap { white-space: nowrap; }
    .cat-total { font-family: 'Cinzel', serif; font-size: 1rem; color: var(--fns-gold-light, #e7be4f); }
    .cat-total-unit { font-size: .68rem; opacity: .7; margin-left: .35rem; font-weight: 500; }

    .cat-items { background: #fff; }
    .item-group { border-top: 1px solid var(--fns-gray-200); }
    .item-group:first-child { border-top: none; }

    .item-head {
        display:flex; align-items:center; gap: .7rem;
        padding: .55rem .95rem;
        background: #fafaf7;
        cursor: pointer; user-select: none;
        transition: background .12s;
    }
    .item-head:hover { background: var(--fns-gray-100); }
    .item-toggle {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px;
        color: var(--fns-navy);
        transition: transform .18s;
    }
    .item-toggle svg { width: 14px; height: 14px; }
    .item-group.collapsed .item-toggle { transform: rotate(-90deg); }
    .item-group.collapsed .item-body-wrap { display: none; }

    .item-code-chip {
        font-family: 'Cinzel', serif;
        background: rgba(26,39,68,0.08); color: var(--fns-navy);
        padding: .14rem .5rem; border-radius: 4px; font-size: .72rem;
        letter-spacing: .04em;
    }
    .item-title { flex: 1; font-weight: 600; color: var(--fns-navy); font-size: .85rem; }
    .item-count {
        font-size: .68rem; color: var(--fns-gray-400);
        padding: .14rem .45rem; background: #fff;
        border: 1px solid var(--fns-gray-200); border-radius: 999px;
    }
    .item-total-wrap { white-space: nowrap; }
    .item-total { font-family: 'Cinzel', serif; font-size: .9rem; color: var(--fns-navy); font-weight: 700; }
    .item-total-unit { font-size: .65rem; color: var(--fns-gray-400); margin-left: .3rem; font-weight: 500; }

    .btn-del-group {
        display:inline-flex; align-items:center; justify-content:center;
        width: 26px; height: 26px;
        background: transparent; border: none; color: #ef4444;
        cursor: pointer; border-radius: 5px;
        transition: background .12s;
    }
    .btn-del-group:hover { background: rgba(239,68,68,0.1); }
    .btn-del-group svg { width: 13px; height: 13px; }

    .item-body-wrap { padding: .25rem .85rem 1rem; background: #fff; }
    .detail-scroll { overflow-x: auto; }

    .detail-table { margin: 0; font-size: .76rem; width: 100%; min-width: 880px; }
    .detail-table thead { background: #fbfbf7 !important; }
    .detail-table thead th {
        color: var(--fns-gray-600) !important;
        font-weight: 700 !important; letter-spacing: .04em;
        text-transform: none !important;
        padding: .45rem .35rem !important;
        font-size: .65rem !important;
        border-bottom: 1px solid var(--fns-gray-200);
    }
    .detail-table tbody td { padding: 3px 4px !important; border-bottom: 1px dashed var(--fns-gray-200); }
    .detail-table tbody tr:hover { background: #fdfbf3; }
    .detail-table tbody tr:last-child td { border-bottom: none; }

    .detail-table .gi {
        border: 1px solid transparent; background: transparent;
        font-size: .78rem; padding: 4px 6px; width: 100%;
        outline: none; font-family: inherit; color: var(--fns-navy);
        border-radius: 4px;
        transition: background .12s, border-color .12s, box-shadow .12s;
    }
    .detail-table .gi::placeholder { color: var(--fns-gray-400); font-weight: 400; opacity: .8; }
    .detail-table .gi:hover { background: #fafaf7; }
    .detail-table .gi:focus {
        background: #fff; border-color: var(--fns-navy-light);
        box-shadow: 0 0 0 2px rgba(46,63,110,0.12);
    }
    .detail-table .gi-invalid { animation: flash-red .8s ease; }
    .detail-table input[type=number].gi { text-align: right; font-variant-numeric: tabular-nums; }
    .detail-table .gi-sub { font-weight: 500; }
    .detail-table .cell-total {
        font-family: 'Cinzel', serif; font-size: .82rem !important;
        color: var(--fns-navy); font-weight: 700;
    }

    .btn-add-row { margin-top: .55rem; }

    .btn-del-row {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px;
        background: transparent; border: none; color: #ef4444;
        cursor: pointer; border-radius: 5px;
        transition: background .12s, color .12s;
    }
    .btn-del-row:hover { background: rgba(239,68,68,0.1); color: #b91c1c; }
    .btn-del-row svg { width: 13px; height: 13px; }

    /* ===== Empty state ===== */
    .mgr-empty {
        padding: 4rem 1.5rem; text-align: center;
        background: #fff; border: 1px dashed var(--fns-gray-200); border-radius: 12px;
        color: var(--fns-gray-600);
    }
    .mgr-empty-num {
        font-family: 'Cinzel', serif; font-size: 4rem; font-weight: 700;
        color: var(--fns-gray-200); line-height: 1; margin-bottom: .6rem;
    }
    .mgr-empty-title { font-size: 1.1rem; color: var(--fns-navy); font-weight: 700; margin: .3rem 0 .5rem; }
    .mgr-empty-sub { font-size: .9rem; margin: 0 0 .8rem; }
    .mgr-empty-hint { font-size: .78rem; color: var(--fns-gray-400); margin: 0; }

    /* ===== Keyboard hint footer ===== */
    .mgr-kbd-bar {
        display:flex; align-items:center; gap: 1.4rem; flex-wrap: wrap;
        padding: .75rem 1rem; margin-top: 1.5rem;
        font-size: .7rem; color: var(--fns-gray-600);
        border-top: 1px dashed var(--fns-gray-200);
    }
    .mgr-kbd-bar span { display:inline-flex; align-items:center; gap: .4rem; }
    .mgr-kbd-note { margin-left: auto; color: var(--fns-gray-400); font-style: italic; }
    .mgr-kbd {
        display: inline-flex; align-items:center; justify-content:center;
        min-width: 18px; height: 18px; padding: 0 .3rem;
        background: #fff; border: 1px solid var(--fns-gray-200);
        border-bottom-width: 2px;
        border-radius: 4px; font-family: 'Cinzel', serif; font-size: .65rem;
        color: var(--fns-navy); font-weight: 700;
    }

    /* ===== Toasts ===== */
    .mgr-toasts {
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9500;
        display:flex; flex-direction:column; gap: .55rem; pointer-events: none;
    }
    .mgr-toast {
        display:flex; align-items:center; gap: .55rem;
        padding: .65rem .9rem; border-radius: 8px;
        background: var(--fns-navy-deep); color: #fff;
        box-shadow: 0 12px 30px -10px rgba(17,27,51,0.5);
        font-size: .8rem; pointer-events: auto;
        animation: toastIn .22s ease-out;
    }
    .mgr-toast.is-success { background: #166534; }
    .mgr-toast.is-error { background: #991b1b; }
    .mgr-toast svg { width: 15px; height: 15px; }
    @keyframes toastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @keyframes toastOut { to { opacity: 0; transform: translateY(8px); } }

    /* ===== Row save indicators ===== */
    .detail-table tr.row-saving { opacity: .55; pointer-events: none; }
    .detail-table tr.row-saved td { animation: flash-green .9s ease; }
    .detail-table tr.row-error td { animation: flash-red .9s ease; }
    @keyframes flash-green { 0%,100% { background: inherit; } 25% { background: #bbf7d0; } }
    @keyframes flash-red   { 0%,100% { background: inherit; } 25% { background: #fecaca; } }

    /* ===== Filter hidden rows ===== */
    .grid-row.is-hidden { display: none; }
    .item-group.is-hidden { display: none; }
    .cat-group.is-hidden { display: none; }

    /* ===== Ref modal ===== */
    .mgr-modal-backdrop {
        display:none; position: fixed; inset: 0; z-index: 9000;
        background: rgba(17,27,51,0.55); backdrop-filter: blur(2px);
        align-items: flex-start; justify-content: center;
        padding: 6vh 1rem 1rem;
    }
    .mgr-modal {
        background: #fff; border-radius: 14px;
        width: 640px; max-width: 100%; max-height: 86vh; overflow: hidden;
        display:flex; flex-direction: column;
        box-shadow: 0 22px 60px -20px rgba(17,27,51,0.6);
        animation: toastIn .22s ease-out;
    }
    .mgr-modal-head {
        display:flex; justify-content:space-between; align-items:flex-start;
        padding: 1.1rem 1.3rem 1rem;
        background: linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid));
        color: #fff;
    }
    .mgr-modal-kicker {
        font-family: 'Cinzel', serif; font-size: .65rem; letter-spacing: .2em;
        color: var(--fns-gold-light, #e7be4f); text-transform: uppercase; font-weight: 700;
    }
    .mgr-modal-title { margin: .3rem 0 0; font-size: 1.05rem; font-weight: 700; }
    .mgr-modal-close {
        background: none; border: none; color: rgba(255,255,255,0.7);
        cursor: pointer; padding: .3rem; transition: color .15s;
    }
    .mgr-modal-close:hover { color: #fff; }
    .mgr-modal-close svg { width: 18px; height: 18px; }

    .mgr-modal-body { padding: 1.2rem 1.3rem 1.3rem; overflow-y: auto; }

    .rc-add {
        display:flex; gap: .5rem; align-items:center;
        margin-bottom: .65rem; flex-wrap: wrap;
        padding: .55rem .65rem;
        background: var(--fns-gray-100); border-radius: 8px;
    }
    .rc-add-label {
        font-size: .72rem; font-weight: 700;
        color: var(--fns-navy); width: 105px;
        letter-spacing: .02em;
    }
    .rc-code-input { width: 80px; }
    .rc-label-input { flex: 1; min-width: 130px; }
    .rc-parent-select { width: 160px; }

    .rc-list { margin-top: 1rem; border-top: 1px solid var(--fns-gray-200); padding-top: .85rem; }
    .rc-row { display:flex; gap: .4rem; align-items: center; padding: .3rem .3rem; border-radius: 6px; }
    .rc-row:hover { background: var(--fns-gray-100); }
    .rc-row.rc-cat { margin-top: .55rem; padding-top: .55rem; border-top: 1px dashed var(--fns-gray-200); }
    .rc-row.rc-cat:first-child { margin-top: 0; padding-top: .3rem; border-top: none; }
    .rc-row.rc-cat .rc-code, .rc-row.rc-cat .rc-label { font-weight: 700; color: var(--fns-navy); }
    .rc-row.rc-child { padding-left: 1.6rem; position: relative; }
    .rc-row.rc-child::before {
        content: "└"; position: absolute; left: .55rem; top: .35rem;
        color: var(--fns-gray-400); font-family: 'Cinzel', serif;
    }
    .rc-edit { display: flex; gap: .4rem; flex: 1; }
    .rc-edit .rc-code { width: 80px; }
    .rc-edit .rc-label { flex: 1; }
    .rc-edit .fns-input, .rc-edit input.fns-input {
        padding: .35rem .55rem; border: 1px solid var(--fns-gray-200);
        border-radius: 6px; font-family: inherit; font-size: .8rem;
        color: var(--fns-navy); background: #fff; outline: none;
    }
    .rc-edit .fns-input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 2px rgba(46,63,110,0.1); }
    .rc-empty { text-align: center; color: var(--fns-gray-400); padding: 1.4rem; font-size: .85rem; }

    /* Make existing fns-btn-sm inside ref rows match new palette */
    .rc-row .fns-btn { padding: .3rem .65rem; font-size: .72rem; border-radius: 6px; }

    /* ===== COA picker trigger (per row) ===== */
    .gi-coa-trigger {
        display: inline-flex; align-items: center; justify-content: space-between;
        gap: .3rem; width: 100%;
        padding: 3px 6px;
        background: transparent;
        border: 1px solid transparent; border-radius: 4px;
        font-family: 'Cinzel', serif; font-size: .78rem; font-weight: 600;
        color: var(--fns-navy); cursor: pointer;
        text-align: left; letter-spacing: .02em;
        transition: background .12s, border-color .12s, box-shadow .12s;
    }
    .gi-coa-trigger:hover { background: #fafaf7; }
    .gi-coa-trigger.is-open {
        background: #fff;
        border-color: var(--fns-navy-light);
        box-shadow: 0 0 0 2px rgba(46,63,110,0.12);
    }
    .gi-coa-trigger.is-empty {
        font-family: 'Noto Sans Lao', sans-serif;
        font-weight: 500; color: var(--fns-gray-400); font-style: italic;
    }
    .gi-coa-trigger svg {
        width: 11px; height: 11px;
        color: var(--fns-gray-400); flex-shrink: 0;
        transition: transform .18s;
    }
    .gi-coa-trigger.is-open svg { transform: rotate(180deg); color: var(--fns-navy); }
    .gi-coa-trigger-code { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ===== COA popover ===== */
    .xpop {
        display: none;
        position: fixed; z-index: 100;
        width: 380px; max-width: 95vw;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 10px;
        box-shadow: 0 14px 40px -12px rgba(17,27,51,0.35);
        overflow: hidden;
        animation: xpopIn .14s ease-out;
    }
    .xpop.is-open { display: block; }
    @keyframes xpopIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

    .xpop-search {
        display: flex; align-items: center; gap: .5rem;
        padding: .55rem .75rem;
        background: var(--fns-gray-100);
        border-bottom: 1px solid var(--fns-gray-200);
    }
    .xpop-search svg { width: 14px; height: 14px; color: var(--fns-gray-400); flex-shrink: 0; }
    .xpop-search input {
        flex: 1; border: none; outline: none; background: transparent;
        font-family: inherit; font-size: .85rem; color: var(--fns-navy);
    }
    .xpop-list { max-height: 320px; overflow-y: auto; padding: .3rem; }
    .xpop-item {
        display: flex; align-items: baseline; gap: .55rem;
        padding: .45rem .65rem; border-radius: 6px;
        cursor: pointer; font-size: .82rem; color: var(--fns-navy);
        transition: background .1s;
    }
    .xpop-item:hover, .xpop-item.is-active { background: rgba(26,39,68,0.06); }
    .xpop-item.is-selected { background: rgba(201,153,26,0.12); color: #8b6a12; }
    .xpop-item-code {
        font-family: 'Cinzel', serif; font-weight: 700;
        min-width: 70px; flex-shrink: 0;
    }
    .xpop-item-name {
        font-weight: 500; color: var(--fns-gray-600);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .xpop-item.is-selected .xpop-item-name { color: #8b6a12; }
    .xpop-empty { padding: 1.2rem; text-align: center; font-size: .82rem; color: var(--fns-gray-400); }

    /* ===== Responsive ===== */
    @media (max-width: 900px) {
        .mgr-sticky-bar { grid-template-columns: auto auto 1fr auto; gap: .6rem; }
        .mgr-search { grid-column: 1 / -1; max-width: none; order: 5; }
        .mgr-search .mgr-kbd { display: none; }
        .mgr-toolbox { flex-direction: column; align-items: stretch; }
        .mgr-picker { width: 100%; }
        .mgr-select { min-width: 0; flex: 1; }
        .mgr-toolbox-right { justify-content: flex-end; }
    }
</style>

{{-- ===================================================================== --}}
{{-- Scripts (logic preserved verbatim from prior version + minor additions)  --}}
{{-- ===================================================================== --}}
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

// ---- Toast notifications ----
function showToast(msg, type = 'info') {
    const wrap = document.getElementById('mgrToasts');
    const t = document.createElement('div');
    t.className = `mgr-toast is-${type}`;
    const icon = type === 'success'
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>'
        : type === 'error'
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
    t.innerHTML = icon + `<span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'toastOut .22s ease-in forwards';
        setTimeout(() => t.remove(), 220);
    }, 2200);
}

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
    const rowCount = grp.querySelectorAll('.item-body .grid-row').length;
    const cEl = grp.querySelector('.item-count');
    if (cEl) cEl.textContent = `${rowCount} ແຖວ`;
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
    const total = document.querySelectorAll('.grid-row').length;
    document.getElementById('entryMeta').textContent = `${total} ລາຍການ`;
}

// ── COA picker popover ──────────────────────────────────────
const COA_LIST = Object.entries(COA_MAP).map(([code, info]) => ({ id: info.id, code, name: info.name }));
COA_LIST.sort((a, b) => a.code.localeCompare(b.code));
const COA_BY_ID = {};
COA_LIST.forEach(c => COA_BY_ID[c.id] = c);

const $xpop      = document.getElementById('xpop');
const $xpopList  = document.getElementById('xpop-list');
const $xpopInput = document.getElementById('xpop-search');
const $xpopEmpty = document.getElementById('xpop-empty');
let xpopRow = null, xpopTrigger = null, xpopVisible = [], xpopActiveIdx = 0;

function renderCoaList(q) {
    q = (q || '').trim().toLowerCase();
    xpopVisible = q
        ? COA_LIST.filter(c => c.code.toLowerCase().includes(q) || c.name.toLowerCase().includes(q))
        : COA_LIST;
    const selectedId = xpopRow?.querySelector('.gi-acctid')?.value || '';
    $xpopList.innerHTML = xpopVisible.map((c, i) =>
        `<div class="xpop-item${String(c.id) === selectedId ? ' is-selected' : ''}${i === xpopActiveIdx ? ' is-active' : ''}" data-id="${c.id}" role="option">
            <span class="xpop-item-code">${c.code}</span>
            <span class="xpop-item-name">${c.name}</span>
         </div>`
    ).join('');
    $xpopEmpty.style.display = xpopVisible.length ? 'none' : '';
    const active = $xpopList.querySelector('.xpop-item.is-active');
    if (active) active.scrollIntoView({ block: 'nearest' });
}

function openCoaPop(trigger, row) {
    closeCoaPop();
    xpopTrigger = trigger; xpopRow = row;
    trigger.classList.add('is-open');

    const r = trigger.getBoundingClientRect();
    const popW = 380;
    const left = Math.min(r.left, window.innerWidth - popW - 12);
    $xpop.style.top  = (r.bottom + 4) + 'px';
    $xpop.style.left = Math.max(8, left) + 'px';
    $xpop.classList.add('is-open');

    xpopActiveIdx = 0;
    $xpopInput.value = '';
    renderCoaList('');
    const sel = $xpopList.querySelector('.xpop-item.is-selected');
    if (sel) sel.scrollIntoView({ block: 'center' });
    setTimeout(() => $xpopInput.focus(), 0);
}

function closeCoaPop() {
    $xpop.classList.remove('is-open');
    if (xpopTrigger) xpopTrigger.classList.remove('is-open');
    xpopTrigger = null; xpopRow = null;
}

function selectCoa(coaId) {
    if (!xpopRow) return;
    const info = COA_BY_ID[coaId];
    if (!info) return;
    f(xpopRow, 'gi-acctid').value = info.id;
    const codeSpan = xpopRow.querySelector('.gi-coa-trigger-code');
    if (codeSpan) codeSpan.textContent = info.code;
    xpopRow.querySelector('.gi-coa-trigger')?.classList.remove('is-empty');
    const rowToSave = xpopRow;
    closeCoaPop();
    saveRow(rowToSave);
}

$xpopInput.addEventListener('input', () => { xpopActiveIdx = 0; renderCoaList($xpopInput.value); });
$xpopInput.addEventListener('keydown', e => {
    if (e.key === 'Escape') { e.preventDefault(); closeCoaPop(); xpopTrigger?.focus(); return; }
    if (e.key === 'ArrowDown') { e.preventDefault(); xpopActiveIdx = Math.min(xpopActiveIdx + 1, xpopVisible.length - 1); renderCoaList($xpopInput.value); return; }
    if (e.key === 'ArrowUp')   { e.preventDefault(); xpopActiveIdx = Math.max(xpopActiveIdx - 1, 0); renderCoaList($xpopInput.value); return; }
    if (e.key === 'Enter') {
        e.preventDefault();
        const item = xpopVisible[xpopActiveIdx];
        if (item) selectCoa(item.id);
    }
});
$xpopList.addEventListener('click', e => {
    const item = e.target.closest('.xpop-item');
    if (item) selectCoa(item.dataset.id);
});
document.addEventListener('click', e => {
    if (!$xpop.classList.contains('is-open')) return;
    if ($xpop.contains(e.target)) return;
    if (e.target.closest('.gi-coa-trigger')) return;
    closeCoaPop();
});
window.addEventListener('scroll', (e) => {
    if ($xpop.contains(e.target)) return;
    closeCoaPop();
}, true);
window.addEventListener('resize', () => closeCoaPop());

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

        const isNew = !itemId;
        if (isNew && data.entry?.id) row.dataset.itemId = data.entry.id;
        if (data.entry?.total !== undefined) {
            const cell = row.querySelector('.cell-total');
            if (cell) cell.textContent = numFmt.format(parseFloat(data.entry.total));
        }
        recalcItem(grp);
        row.classList.add('row-saved');
        setTimeout(() => row.classList.remove('row-saved'), 900);
        if (isNew) showToast('ບັນທຶກລາຍການໃໝ່ສຳເລັດ', 'success');
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
        showToast('ບໍ່ສາມາດບັນທຶກໄດ້ — ກະລຸນາລອງໃໝ່', 'error');
    }
}

async function deleteRow(row){
    const grp = row.closest('.item-group');
    const itemId = row.dataset.itemId;
    if (!itemId) { row.remove(); recalcItem(grp); return; }
    if (!confirm('ລຶບລາຍການນີ້?')) return;
    row.classList.add('row-saving');
    try {
        const res = await fetch(`/head-of-finance/expense-entries/${itemId}`, {
            method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF},
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error();
        row.remove(); recalcItem(grp);
        showToast('ລຶບລາຍການແລ້ວ', 'success');
    } catch(err) {
        row.classList.remove('row-saving');
        row.classList.add('row-error');
        setTimeout(() => row.classList.remove('row-error'), 900);
        showToast('ບໍ່ສາມາດລຶບໄດ້', 'error');
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
    cat.querySelector('.cat-code-chip').textContent = catCode;
    cat.querySelector('.cat-title').textContent = ref.label || 'ໝວດ';
    document.getElementById('groups').appendChild(cat);
    // Remove empty-state placeholder if present.
    document.querySelector('.mgr-empty')?.remove();
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
    grp.querySelector('.item-code-chip').textContent = itemCode;
    grp.querySelector('.item-title').textContent = ref.label || 'ລາຍການຫຼັກ';
    bindGroup(grp);
    cat.querySelector('.cat-items').appendChild(grp);
    addDetailRow(grp);
    recalcCat(cat);
}

function deleteGroup(grp){
    let hasSaved = false;
    grp.querySelectorAll('.grid-row').forEach(r => { if (r.dataset.itemId) hasSaved = true; });
    if (hasSaved) { showToast('ກະລຸນາລຶບລາຍການຍ່ອຍທັງໝົດກ່ອນ', 'error'); return; }
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
    if (!c || !i) { showToast('ເລືອກ ໝວດຫຼັກ ແລະ ລາຍການຫຼັກ ກ່ອນ', 'error'); return; }
    addGroup(c, i);
    document.getElementById('pick-item').value = '';
}

// ---- Toggle all / Filter ----
function toggleAll(collapse) {
    document.querySelectorAll('.item-group').forEach(g => {
        g.classList.toggle('collapsed', collapse);
    });
}

const searchEl = document.getElementById('entrySearch');
searchEl?.addEventListener('input', () => {
    const q = searchEl.value.trim().toLowerCase();
    document.querySelectorAll('.item-group').forEach(grp => {
        let anyVisible = false;
        grp.querySelectorAll('.grid-row').forEach(row => {
            if (!q) { row.classList.remove('is-hidden'); anyVisible = true; return; }
            const sub  = (row.querySelector('.gi-sub')?.value || '').toLowerCase();
            const acct = (row.querySelector('.gi-coa-trigger-code')?.textContent || '').toLowerCase();
            const note = (row.querySelector('.gi-note')?.value || '').toLowerCase();
            const hit  = sub.includes(q) || acct.includes(q) || note.includes(q);
            row.classList.toggle('is-hidden', !hit);
            if (hit) anyVisible = true;
        });
        grp.classList.toggle('is-hidden', !!q && !anyVisible);
        if (q && anyVisible) grp.classList.remove('collapsed');
    });
    document.querySelectorAll('.cat-group').forEach(cat => {
        const anyItemVisible = !!cat.querySelector('.item-group:not(.is-hidden)');
        cat.classList.toggle('is-hidden', !!q && !anyItemVisible);
    });
});

// ---- Keyboard shortcuts ----
document.addEventListener('keydown', e => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
        e.preventDefault();
        searchEl?.focus();
    }
    if (e.key === 'Escape') {
        if (document.getElementById('refModal').style.display === 'flex') closeRefModal();
        else if (document.activeElement === searchEl) { searchEl.value = ''; searchEl.dispatchEvent(new Event('input')); searchEl.blur(); }
    }
});

// Close ref modal on backdrop click
document.getElementById('refModal').addEventListener('click', (e) => {
    if (e.target.id === 'refModal') closeRefModal();
});

// ---- Binding ----
function bindRow(row){
    row.querySelectorAll('.gi-r1,.gi-r2,.gi-qty,.gi-period,.gi-freq,.gi-addon,.gi-note').forEach(inp =>
        inp.addEventListener('input', () => recalc(row)));

    const trig = row.querySelector('.gi-coa-trigger');
    if (trig) trig.addEventListener('click', e => {
        e.stopPropagation();
        if (trig.classList.contains('is-open')) closeCoaPop();
        else openCoaPop(trig, row);
    });

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

{{-- ===== Templates cloned by JS (structure preserved for new visual chrome) ===== --}}
<template id="detail-row-tpl">
    @include('dashboards.finance_head.expense._entry_row', ['e' => null])
</template>

<template id="cat-group-tpl">
    <div class="cat-group" data-cat-code="">
        <div class="cat-head">
            <span class="cat-code-chip"></span>
            <span class="cat-title"></span>
            <span class="cat-total-wrap"><strong class="cat-total">0</strong><span class="cat-total-unit">ກີບ</span></span>
        </div>
        <div class="cat-items"></div>
    </div>
</template>

<template id="item-group-tpl">
    <div class="item-group" data-cat-code="" data-cat-label="" data-item-code="" data-item-label="">
        <div class="item-head">
            <span class="item-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></span>
            <span class="item-code-chip"></span>
            <span class="item-title"></span>
            <span class="item-count">0 ແຖວ</span>
            <span class="item-total-wrap"><strong class="item-total">0</strong><span class="item-total-unit">ກີບ</span></span>
            <button type="button" class="btn-del-group" title="ລຶບກຸ່ມ">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="item-body-wrap">
            <div class="detail-scroll">
                <table class="fns-table detail-table">
                    @include('dashboards.finance_head.expense._detail_head')
                    <tbody class="item-body"></tbody>
                </table>
            </div>
            <button type="button" class="btn-add-row mgr-btn mgr-btn-ghost mgr-btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                ເພີ່ມລາຍການຍ່ອຍ
            </button>
        </div>
    </div>
</template>

@endsection
