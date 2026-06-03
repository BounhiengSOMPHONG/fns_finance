@extends('layouts.admin')

@section('title', 'ຄ່າລົງທະບຽນ')
@section('page-title', 'ການຕັ້ງຄ່າລົງທະບຽນ')

@section('content')

<style>
/* ── Registration-fee settings — two fixed types, calm & clear ── */
.rf-wrap { width: 100%; }
.rf-intro { font-size: 0.8rem; color: var(--fns-gray-400); margin-bottom: 1.1rem; }

.rf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.15rem; }

.rf-card {
    position: relative; overflow: hidden;
    background: #fff; border: 1px solid var(--fns-gray-200); border-radius: 14px;
    padding: 1.3rem 1.4rem; box-shadow: 0 2px 12px rgba(26,39,68,0.06);
}
.rf-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--rf-accent); }

/* header: type badge + edit */
.rf-hd { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; }
.rf-edit {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-family: inherit; font-size: 0.78rem; font-weight: 600; color: var(--fns-navy);
    padding: 0.4rem 0.8rem; border: 1px solid var(--fns-gray-200); border-radius: 8px;
    background: #fff; text-decoration: none; transition: all .15s;
}
.rf-edit:hover { background: var(--fns-gray-100); border-color: #b0aead; }
.rf-edit svg { width: 14px; height: 14px; }

/* hero total */
.rf-total-label { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fns-gray-400); margin-bottom: 0.2rem; }
.rf-total { display: flex; align-items: baseline; gap: 0.4rem; }
.rf-total-num { font-family: 'Cinzel', serif; font-size: 1.85rem; font-weight: 700; color: var(--fns-navy); line-height: 1; }
.rf-total-unit { font-size: 0.85rem; color: var(--fns-gray-400); }
.rf-meta { font-size: 0.74rem; color: var(--fns-gray-400); margin-top: 0.45rem; display: flex; flex-wrap: wrap; gap: 0.2rem 0.55rem; }
.rf-meta b { color: var(--fns-gray-600); font-weight: 600; }

/* items breakdown */
.rf-items-label { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--fns-gray-400); margin: 1.1rem 0 0.25rem; }
.rf-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.5rem 0.1rem; border-bottom: 1px solid var(--fns-gray-200);
}
.rf-item:last-child { border-bottom: none; }
.rf-item-name { flex: 1; min-width: 0; font-size: 0.85rem; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rf-pct { flex-shrink: 0; font-size: 0.66rem; font-weight: 600; color: var(--fns-gold); background: rgba(201,153,26,0.1); border-radius: 6px; padding: 0.1rem 0.45rem; }
.rf-pct.zero { color: var(--fns-gray-400); background: var(--fns-gray-100); }
.rf-amt { flex-shrink: 0; font-family: 'Cinzel', serif; font-size: 0.92rem; font-weight: 700; color: var(--fns-navy); font-variant-numeric: tabular-nums; }
.rf-amt small { font-family: inherit; font-weight: 400; font-size: 0.66rem; color: var(--fns-gray-400); margin-left: 0.15rem; }

.rf-empty { text-align: center; padding: 2.5rem; color: var(--fns-gray-400); }
.rf-empty div { font-size: 2rem; opacity: 0.2; margin-bottom: 0.6rem; }
</style>

<div class="rf-wrap">
    <p class="rf-intro">ຕັ້ງຄ່າຄ່າລົງທະບຽນ ສຳລັບ ນ/ສ ປີ 1 (ໃໝ່) ແລະ ປີ 2–4 — ມີ 2 ປະເພດຄົງທີ່, ແກ້ໄຂໄດ້ຢ່າງດຽວ</p>

    <div class="rf-grid">
        @forelse($settings as $s)
        @php
            $isY1    = $s->section_type === 'year1';
            $accent  = $isY1 ? 'var(--fns-green-mid)' : 'var(--fns-navy-light)';
            $badge   = $isY1 ? 'fns-badge-green' : 'fns-badge-blue';
        @endphp
        <div class="rf-card" style="--rf-accent:{{ $accent }};">
            <div class="rf-hd">
                <span class="fns-badge {{ $badge }}" style="font-size:0.8rem; padding:0.3rem 0.85rem;">
                    {{ \App\Models\RegistrationFeeSetting::sectionLabel($s->section_type) }}
                </span>
                <a href="{{ route('head_of_finance.settings.registration-fee.edit', $s) }}" class="rf-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/></svg>
                    ແກ້ໄຂ
                </a>
            </div>

            <div class="rf-total-label">ລວມຄ່າລົງທະບຽນ</div>
            <div class="rf-total">
                <span class="rf-total-num">{{ number_format($s->total_rate, 0) }}</span>
                <span class="rf-total-unit">ກີບ</span>
            </div>
            <div class="rf-meta">
                <span>ປີທີ່ເລີ່ມໃຊ້ <b>{{ $s->start_year }}</b></span>
                @if($s->gov_doc_id)<span>· ເອກະສານ <b>{{ $s->gov_doc_id }}</b></span>@endif
                <span>· {{ $s->items->count() }} ລາຍການ</span>
            </div>

            <div class="rf-items-label">ລາຍລະອຽດ</div>
            @foreach($s->items as $item)
            <div class="rf-item">
                <span class="rf-item-name" title="{{ $item->name }}">{{ $item->name }}</span>
                <span class="rf-pct {{ $item->nuol_pct > 0 ? '' : 'zero' }}">ມຊ {{ rtrim(rtrim(number_format($item->nuol_pct * 100, 2), '0'), '.') }}%</span>
                <span class="rf-amt">{{ number_format($item->amount, 0) }}<small>ກີບ</small></span>
            </div>
            @endforeach
        </div>
        @empty
        <div class="rf-card" style="--rf-accent:var(--fns-gray-200); grid-column:1/-1;">
            <div class="rf-empty">
                <div>💰</div>
                <p>ຍັງບໍ່ມີຂໍ້ມູນຄ່າລົງທະບຽນ</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

@endsection
