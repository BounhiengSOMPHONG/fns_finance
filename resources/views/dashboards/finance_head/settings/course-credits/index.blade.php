@extends('layouts.admin')

@section('title', 'ລາຄາ & ໜ່ວຍກິດ & ມຊ%')
@section('page-title', 'ການຕັ້ງລາຄາ & ໜ່ວຍກິດ & ມຊ%')

@section('content')

<style>
/* ── Credit price + course-credit settings (merged) ───────────── */
.cc-wrap { width: 100%; }
.cc-card { padding: 1.25rem 1.4rem !important; margin-bottom: 1.1rem; }

/* section heading */
.cc-head { display: flex; align-items: center; gap: 0.7rem; margin-bottom: 1rem; }
.cc-head .fns-sec-num { flex-shrink: 0; }
.cc-head-title { font-weight: 700; font-size: 0.95rem; color: var(--fns-navy); }
.cc-head-desc  { font-size: 0.76rem; color: var(--fns-gray-400); margin-top: 0.1rem; }
.cc-head-meta { margin-left: auto; }

/* ── Section 1: per-level prices (inline edit) ────────────────── */
.cc-price { display: grid; gap: 0.55rem; }
.cc-prow {
    display: grid; align-items: center; gap: 0.7rem;
    grid-template-columns: 9.5rem minmax(150px,1fr) minmax(140px,1fr) 7rem auto;
    padding: 0.55rem 0.6rem; border: 1px solid var(--fns-gray-200); border-radius: 10px;
    background: #fafbfc;
}
.cc-prow.is-dirty { border-color: var(--fns-gold); background: rgba(201,153,26,0.05); }
.cc-plevel { display: flex; align-items: center; }
.cc-field { display: flex; flex-direction: column; gap: 0.2rem; min-width: 0; }
.cc-field label { font-size: 0.64rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: var(--fns-gray-400); }
.cc-in {
    width: 100%; padding: 0.4rem 0.55rem; border: 1px solid var(--fns-gray-200); border-radius: 8px;
    font-family: inherit; font-size: 0.86rem; color: #111827; background: #fff; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.cc-in.num { text-align: right; font-family: 'Cinzel', serif; font-weight: 600; }
.cc-in:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }
.cc-save {
    align-self: end; height: 2.05rem; padding: 0 0.85rem; border-radius: 8px; cursor: pointer;
    font-family: inherit; font-size: 0.78rem; font-weight: 600; white-space: nowrap;
    border: 1px solid var(--fns-gray-200); background: #fff; color: var(--fns-gray-400);
    transition: all .15s;
}
.cc-prow.is-dirty .cc-save { background: var(--fns-gold); border-color: var(--fns-gold); color: var(--fns-navy-deep); }
.cc-prow.is-dirty .cc-save:hover { background: var(--fns-gold-light); }
.cc-pempty { font-size: 0.8rem; color: var(--fns-gray-400); padding: 0.4rem 0.2rem; }

/* ── Section 2: course credits per program ────────────────────── */
.cc-bar {
    position: sticky; top: 0; z-index: 20;
    display: flex; align-items: center; gap: 0.8rem; flex-wrap: wrap;
    padding: 0.7rem 0.9rem; margin-bottom: 1.1rem;
    background: rgba(248,247,244,0.92); backdrop-filter: blur(8px);
    border: 1px solid var(--fns-gray-200); border-radius: 12px;
}
.cc-search { position: relative; flex: 1; min-width: 200px; }
.cc-search svg { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--fns-gray-400); pointer-events: none; }
.cc-search input {
    width: 100%; padding: 0.5rem 0.8rem 0.5rem 2.1rem;
    border: 1px solid var(--fns-gray-200); border-radius: 9px; background: #fff;
    font-family: inherit; font-size: 0.85rem; color: #111827; outline: none; transition: border-color .18s, box-shadow .18s;
}
.cc-search input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }

.cc-glabel {
    display: flex; align-items: center; gap: 0.6rem;
    font-size: 0.8rem; font-weight: 700; color: var(--fns-navy);
    padding-bottom: 0.45rem; margin: 0.95rem 0 0.3rem;
    border-bottom: 2px solid var(--fns-gold-pale);
}
.cc-glabel:first-of-type { margin-top: 0.2rem; }
.cc-gcount { font-weight: 600; font-size: 0.68rem; color: var(--fns-gray-400); background: var(--fns-gray-100); border-radius: 999px; padding: 0.1rem 0.55rem; }

.cc-rows { display: grid; grid-template-columns: repeat(auto-fill, minmax(440px, 1fr)); column-gap: 1.6rem; }
.cc-row {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.5rem 0.4rem; border-bottom: 1px solid var(--fns-gray-200); transition: background .12s;
}
.cc-row:hover { background: rgba(26,39,68,0.035); }
.cc-name { flex: 1; min-width: 0; font-size: 0.85rem; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cc-name .yr { font-size: 0.68rem; color: var(--fns-gray-400); margin-left: 0.3rem; }
.cc-units { flex-shrink: 0; font-size: 0.78rem; color: var(--fns-gray-500,#6b7280); white-space: nowrap; }
.cc-units b { font-family: 'Cinzel', serif; font-size: 0.92rem; color: var(--fns-navy); }
.cc-units .y1 { color: #15803d; margin-left: 0.35rem; }
.cc-yr-doc { flex-shrink: 0; font-size: 0.68rem; color: var(--fns-gray-400); font-variant-numeric: tabular-nums; min-width: 2.6rem; text-align: right; }
.cc-acts { display: flex; align-items: center; gap: 0.15rem; flex-shrink: 0; }
.cc-act { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 7px; cursor: pointer; border: 1px solid transparent; background: none; color: var(--fns-gray-400); transition: all .14s; padding: 0; }
.cc-act svg { width: 15px; height: 15px; }
.cc-act-edit:hover { color: var(--fns-navy); background: rgba(26,39,68,0.08); }
.cc-act-del:hover  { color: #b91c1c; background: rgba(185,28,28,0.08); }
.cc-empty { text-align: center; padding: 2.5rem 1rem; color: var(--fns-gray-400); font-size: 0.85rem; }
.cc-nores { display: none; text-align: center; padding: 2rem 1rem; color: var(--fns-gray-400); font-size: 0.85rem; }
</style>

@php
    $levelMeta = [
        'bachelor' => ['label' => 'ປ.ຕີ', 'full' => 'ປະລິນຍາຕີ (ປ.ຕີ)', 'badge' => 'fns-badge-blue'],
        'master'   => ['label' => 'ປ.ໂທ', 'full' => 'ປະລິນຍາໂທ (ປ.ໂທ)', 'badge' => 'fns-badge-green'],
        'phd'      => ['label' => 'ປ.ເອກ', 'full' => 'ປະລິນຍາເອກ (ປ.ເອກ)', 'badge' => 'fns-badge-purple'],
    ];
    $byLevel = $courseCredits->groupBy(fn($s) => $s->degreeProgram?->level);
@endphp

<div class="cc-wrap">

    {{-- ── Section 1: credit-unit price per level ─────────────────── --}}
    <div class="fns-card cc-card">
        <div class="cc-head">
            <div class="fns-sec-num">₭</div>
            <div>
                <div class="cc-head-title">ລາຄາຕໍ່ໜ່ວຍກິດ (ຕາມລະດັບ)</div>
                <div class="cc-head-desc">ກີບຕໍ່ໜ່ວຍກິດ · ແກ້ໄຂແລ້ວກົດ “ບັນທຶກ” ໃນແຖວນັ້ນ</div>
            </div>
        </div>

        <div class="cc-price">
            @foreach($levelMeta as $key => $meta)
                @php $price = $prices->get($key); @endphp
                @if($price)
                    <form method="POST" action="{{ route('head_of_finance.settings.credit-unit-price.update', $price) }}" class="cc-prow" data-dirty-scope>
                        @csrf @method('PUT')
                        <input type="hidden" name="level" value="{{ $key }}">
                        <div class="cc-plevel"><span class="fns-badge {{ $meta['badge'] }}">{{ $meta['full'] }}</span></div>
                        <div class="cc-field">
                            <label>ລາຄາ / ໜ່ວຍກິດ (ກີບ)</label>
                            <input type="number" name="credit_unit_price" step="0.01" min="0" required
                                value="{{ (float) $price->credit_unit_price }}" class="cc-in num" data-dirty>
                        </div>
                        <div class="cc-field">
                            <label>ເລກທີເອກະສານ</label>
                            <input type="text" name="gov_doc_id" value="{{ $price->gov_doc_id }}" class="cc-in" data-dirty>
                        </div>
                        <div class="cc-field">
                            <label>ປີທີ່ໃຊ້</label>
                            <input type="number" name="start_year" min="2000" max="2100" required
                                value="{{ $price->start_year }}" class="cc-in num" data-dirty>
                        </div>
                        <button type="submit" class="cc-save">ບັນທຶກ</button>
                    </form>
                @else
                    <div class="cc-prow" style="grid-template-columns:9.5rem 1fr;">
                        <div class="cc-plevel"><span class="fns-badge {{ $meta['badge'] }}">{{ $meta['full'] }}</span></div>
                        <div class="cc-pempty">ຍັງບໍ່ໄດ້ຕັ້ງລາຄາສຳລັບລະດັບນີ້</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Section 1b: NUOL % per level ───────────────────────────── --}}
    <div class="fns-card cc-card">
        <div class="cc-head">
            <div class="fns-sec-num">%</div>
            <div>
                <div class="cc-head-title">ເປີເຊັນ ມຊ (%) ຕາມລະດັບ</div>
                <div class="cc-head-desc">ອັດຕາ ມຊ ທີ່ຫັກຈາກລາຍຮັບ · ແກ້ໄຂແລ້ວກົດ “ບັນທຶກ” ໃນແຖວນັ້ນ</div>
            </div>
        </div>

        <div class="cc-price">
            @foreach($levelMeta as $key => $meta)
                @php $n = $nuolPcts->get($key); @endphp
                @if($n)
                    <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.update', $n) }}" class="cc-prow" data-dirty-scope>
                        @csrf @method('PUT')
                        <input type="hidden" name="level" value="{{ $key }}">
                        <div class="cc-plevel"><span class="fns-badge {{ $meta['badge'] }}">{{ $meta['full'] }}</span></div>
                        <div class="cc-field">
                            <label>ເປີເຊັນ ມຊ (%)</label>
                            <input type="number" name="percentage" step="0.01" min="0" max="100" required
                                value="{{ rtrim(rtrim(number_format($n->percentage * 100, 4, '.', ''), '0'), '.') }}" class="cc-in num" data-dirty>
                        </div>
                        <div class="cc-field">
                            <label>ເລກທີເອກະສານ</label>
                            <input type="text" name="gov_doc_id" value="{{ $n->gov_doc_id }}" class="cc-in" data-dirty>
                        </div>
                        <div class="cc-field">
                            <label>ປີທີ່ໃຊ້</label>
                            <input type="number" name="start_year" min="2000" max="2100" required
                                value="{{ $n->start_year }}" class="cc-in num" data-dirty>
                        </div>
                        <button type="submit" class="cc-save">ບັນທຶກ</button>
                    </form>
                @else
                    <div class="cc-prow" style="grid-template-columns:9.5rem 1fr;">
                        <div class="cc-plevel"><span class="fns-badge {{ $meta['badge'] }}">{{ $meta['full'] }}</span></div>
                        <div class="cc-pempty">ຍັງບໍ່ໄດ້ຕັ້ງ ມຊ ສຳລັບລະດັບນີ້</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Section 2: course credits per program ──────────────────── --}}
    <div class="cc-bar">
        <div class="cc-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
            <input type="text" id="cc-filter" placeholder="ຄົ້ນຫາ ໜ່ວຍກິດຕາມຫຼັກສູດ / ສາຂາວິຊາ…" autocomplete="off">
        </div>
        <a href="{{ route('head_of_finance.settings.course-credits.create') }}" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            ເພີ່ມໜ່ວຍກິດ
        </a>
    </div>

    <div class="fns-card cc-card">
        <div class="cc-head">
            <div class="fns-sec-num">ໜ</div>
            <div>
                <div class="cc-head-title">ໜ່ວຍກິດຕາມຫຼັກສູດ</div>
                <div class="cc-head-desc">ຈຳນວນໜ່ວຍກິດຂອງແຕ່ລະສາຂາວິຊາ</div>
            </div>
            <div class="cc-head-meta"><span class="cc-gcount">{{ $courseCredits->count() }} ລາຍການ</span></div>
        </div>

        @forelse($levelMeta as $key => $meta)
            @php $items = $byLevel->get($key); @endphp
            @if($items && $items->count())
            <div class="cc-glabel" data-group>
                <span class="fns-badge {{ $meta['badge'] }}">{{ $meta['full'] }}</span>
                <span class="cc-gcount">{{ $items->count() }} ສາຂາ</span>
            </div>
            <div class="cc-rows" data-group-rows>
                @foreach($items as $s)
                    <div class="cc-row" data-name="{{ \Illuminate\Support\Str::lower($s->degreeProgram?->name) }}">
                        <span class="cc-name" title="{{ $s->degreeProgram?->name }}">
                            {{ $s->degreeProgram?->name ?? '—' }}@if($s->degreeProgram?->study_year)<span class="yr">ປີ {{ $s->degreeProgram->study_year }}</span>@endif
                        </span>
                        <span class="cc-units">
                            <b>{{ (float) $s->course_credit_unit }}</b> ໜ່ວຍ@if($s->year1_credit_unit)<span class="y1">· ປີ1 {{ (float) $s->year1_credit_unit }}</span>@endif
                        </span>
                        <span class="cc-yr-doc" title="ປີທີ່ເລີ່ມໃຊ້">{{ $s->start_year }}</span>
                        <div class="cc-acts">
                            <a href="{{ route('head_of_finance.settings.course-credits.edit', $s) }}" class="cc-act cc-act-edit" title="ແກ້ໄຂ">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('head_of_finance.settings.course-credits.destroy', $s) }}" onsubmit="return confirm('ລຶບ {{ $s->degreeProgram?->name }} ບໍ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="cc-act cc-act-del" title="ລຶບ">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        @empty
        @endforelse

        @if($courseCredits->isEmpty())
            <div class="cc-empty">ຍັງບໍ່ມີໜ່ວຍກິດຕາມຫຼັກສູດ — ກົດ “ເພີ່ມໜ່ວຍກິດ” ເພື່ອເລີ່ມຕົ້ນ</div>
        @endif
        <div class="cc-nores" id="cc-nores">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບການຄົ້ນຫາ</div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    // Section 1 — highlight a price row's Save button only when something changed
    document.querySelectorAll('[data-dirty-scope]').forEach(form => {
        const mark = () => form.classList.add('is-dirty');
        form.querySelectorAll('[data-dirty]').forEach(el => {
            el.addEventListener('input', mark);
            el.addEventListener('change', mark);
        });
    });

    // Section 2 — instant filter over course-credit rows
    const filter = document.getElementById('cc-filter');
    const nores  = document.getElementById('cc-nores');
    if (filter) {
        filter.addEventListener('input', () => {
            const q = filter.value.trim().toLowerCase();
            let any = false;
            document.querySelectorAll('.cc-row').forEach(r => {
                const hit = !q || (r.dataset.name || '').includes(q);
                r.style.display = hit ? '' : 'none';
                if (hit) any = true;
            });
            // hide a level group when all its rows are hidden
            document.querySelectorAll('[data-group-rows]').forEach(g => {
                const visible = g.querySelector('.cc-row:not([style*="display: none"])');
                g.style.display = visible ? '' : 'none';
                const label = g.previousElementSibling;
                if (label && label.hasAttribute('data-group')) label.style.display = visible ? '' : 'none';
            });
            nores.style.display = any ? 'none' : 'block';
        });
    }
})();
</script>
@endpush

@endsection
