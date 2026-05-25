@extends('layouts.admin')

@section('title', 'ສາຂາວິຊາ')
@section('page-title', 'ຈັດການສາຂາວິຊາ')

@section('content')

<style>
/* ── Degree-program manager — calm, scannable, instant filter ──── */
.dp-wrap { width: 100%; }

/* sticky toolbar: search + level chips + add */
.dp-bar {
    position: sticky; top: 0; z-index: 20;
    display: flex; align-items: center; gap: 0.8rem; flex-wrap: wrap;
    padding: 0.7rem 0.9rem; margin-bottom: 1.1rem;
    background: rgba(248,247,244,0.92); backdrop-filter: blur(8px);
    border: 1px solid var(--fns-gray-200); border-radius: 12px;
}
.dp-search { position: relative; flex: 1; min-width: 200px; }
.dp-search svg { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--fns-gray-400); pointer-events: none; }
.dp-search input {
    width: 100%; padding: 0.5rem 2rem 0.5rem 2.1rem;
    border: 1px solid var(--fns-gray-200); border-radius: 9px; background: #fff;
    font-family: inherit; font-size: 0.85rem; color: #111827; outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.dp-search input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }
.dp-search-clear { position: absolute; right: 0.55rem; top: 50%; transform: translateY(-50%); display: none; border: none; background: none; cursor: pointer; color: var(--fns-gray-400); font-size: 1.1rem; line-height: 1; padding: 0 0.2rem; }
.dp-search-clear:hover { color: var(--fns-navy); }

.dp-chips { display: flex; gap: 0.35rem; }
.dp-chip {
    font-family: inherit; font-size: 0.78rem; font-weight: 600;
    padding: 0.42rem 0.85rem; border-radius: 999px; cursor: pointer;
    border: 1px solid var(--fns-gray-200); background: #fff; color: var(--fns-gray-600);
    transition: all .15s; white-space: nowrap;
}
.dp-chip:hover { border-color: var(--fns-navy-light); color: var(--fns-navy); }
.dp-chip.is-on { background: var(--fns-navy); border-color: var(--fns-navy); color: #fff; }

/* groups */
.dp-card { padding: 1.25rem 1.4rem !important; margin-bottom: 1.1rem; }
.dp-glabel {
    display: flex; align-items: center; gap: 0.6rem;
    font-size: 0.82rem; font-weight: 700; color: var(--fns-navy);
    padding-bottom: 0.5rem; margin-bottom: 0.4rem;
    border-bottom: 2px solid var(--fns-gold-pale);
}
.dp-gcount { font-weight: 600; font-size: 0.68rem; color: var(--fns-gray-400); background: var(--fns-gray-100); border-radius: 999px; padding: 0.1rem 0.55rem; }
.dp-sublabel { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--fns-gray-400); margin: 0.95rem 0 0.15rem; }

/* clean divided rows in responsive columns */
.dp-rows { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); column-gap: 1.6rem; }
.dp-row {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.5rem 0.4rem; border-bottom: 1px solid var(--fns-gray-200);
    transition: background .12s;
}
.dp-row:hover { background: rgba(26,39,68,0.035); }
.dp-code {
    flex-shrink: 0; min-width: 4.2rem; text-align: center;
    font-family: 'Cinzel', serif; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.02em;
    color: var(--fns-navy); background: rgba(26,39,68,0.06); border-radius: 6px; padding: 0.2rem 0.45rem;
}
.dp-name { flex: 1; min-width: 0; font-size: 0.86rem; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-off-tag { font-size: 0.66rem; color: var(--fns-gray-400); margin-left: 0.35rem; }
.dp-row.is-off .dp-name { color: var(--fns-gray-400); }
.dp-row.is-off .dp-code { opacity: 0.55; }
.dp-dot { flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%; }
.dp-dot.on  { background: #1a8f4a; box-shadow: 0 0 0 2px rgba(26,143,74,0.15); }
.dp-dot.off { background: var(--fns-gray-400); box-shadow: 0 0 0 2px rgba(155,151,144,0.18); }

/* compact icon actions */
.dp-acts { display: flex; align-items: center; gap: 0.15rem; flex-shrink: 0; }
.dp-act {
    display: inline-flex; align-items: center; justify-content: center;
    width: 1.75rem; height: 1.75rem; border-radius: 7px; cursor: pointer;
    border: 1px solid transparent; background: none; color: var(--fns-gray-400);
    transition: all .14s; padding: 0;
}
.dp-act svg { width: 15px; height: 15px; }
.dp-act-edit:hover { color: var(--fns-navy); background: rgba(26,39,68,0.08); }
.dp-act-del:hover  { color: #b91c1c; background: rgba(185,28,28,0.08); }

.dp-empty { text-align: center; padding: 3rem 1rem; color: var(--fns-gray-400); }
.dp-empty svg { width: 40px; height: 40px; opacity: 0.25; margin-bottom: 0.6rem; }
.dp-nores { display: none; text-align: center; padding: 2rem 1rem; color: var(--fns-gray-400); font-size: 0.85rem; }
</style>

@php
    $levels = [
        'bachelor' => ['label' => 'ປະລິນຍາຕີ (ປ.ຕີ)', 'badge' => 'fns-badge-blue'],
        'master'   => ['label' => 'ປະລິນຍາໂທ (ປ.ໂທ)', 'badge' => 'fns-badge-green'],
        'phd'      => ['label' => 'ປະລິນຍາເອກ (ປ.ເອກ)', 'badge' => 'fns-badge-purple'],
    ];
    $byLevel = $programs->groupBy('level');
@endphp

<div class="dp-wrap">

    {{-- Toolbar: instant search · level chips · add --}}
    <div class="dp-bar">
        <div class="dp-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.823l3.633 3.634a.75.75 0 1 0 1.06-1.06l-3.633-3.634A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
            <input type="text" id="dp-filter" placeholder="ຄົ້ນຫາ ລະຫັດ / ຊື່ສາຂາວິຊາ…" autocomplete="off">
            <button type="button" class="dp-search-clear" id="dp-clear" title="ລ້າງ">&times;</button>
        </div>
        <div class="dp-chips" id="dp-chips">
            <button type="button" class="dp-chip is-on" data-level="">ທັງໝົດ</button>
            <button type="button" class="dp-chip" data-level="bachelor">ປ.ຕີ</button>
            <button type="button" class="dp-chip" data-level="master">ປ.ໂທ</button>
            <button type="button" class="dp-chip" data-level="phd">ປ.ເອກ</button>
        </div>
        <a href="{{ route('head_of_finance.settings.degree-programs.create') }}" class="fns-btn fns-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            ເພີ່ມສາຂາວິຊາ
        </a>
    </div>

    @forelse($levels as $key => $meta)
        @php $items = $byLevel->get($key); @endphp
        @if($items && $items->count())
        <div class="fns-card dp-card" data-level="{{ $key }}">
            <div class="dp-glabel">
                <span class="fns-badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                <span class="dp-gcount">{{ $items->count() }} ສາຂາ</span>
            </div>

            @if($key === 'bachelor')
                {{-- bachelor: sub-group by study year --}}
                @foreach($items->groupBy(fn($p) => $p->study_year ?? 0) as $yr => $rows)
                    <div class="dp-sublabel">{{ $yr ? 'ປີ '.$yr : 'ບໍ່ລະບຸຊັ້ນປີ' }} · {{ $rows->count() }}</div>
                    <div class="dp-rows">
                        @each('dashboards.finance_head.settings.degree-programs._row', $rows, 'p')
                    </div>
                @endforeach
            @else
                <div class="dp-rows">
                    @each('dashboards.finance_head.settings.degree-programs._row', $items, 'p')
                </div>
            @endif
        </div>
        @endif
    @empty
    @endforelse

    {{-- empty (no programs at all) --}}
    @if($programs->isEmpty())
    <div class="fns-card dp-card">
        <div class="dp-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            <p>ຍັງບໍ່ມີສາຂາວິຊາ</p>
        </div>
    </div>
    @endif

    {{-- shown when filter matches nothing --}}
    <div class="dp-nores" id="dp-nores">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບການຄົ້ນຫາ</div>

</div>

@push('scripts')
<script>
(function () {
    const filter = document.getElementById('dp-filter');
    const clear  = document.getElementById('dp-clear');
    const chips  = document.getElementById('dp-chips');
    const nores  = document.getElementById('dp-nores');
    let activeLevel = '';

    function apply() {
        const q = filter.value.trim().toLowerCase();
        clear.style.display = q ? 'block' : 'none';
        let anyVisible = false;

        document.querySelectorAll('.dp-card[data-level]').forEach(card => {
            const levelOk = !activeLevel || card.dataset.level === activeLevel;
            let cardHasRow = false;
            card.querySelectorAll('.dp-row').forEach(row => {
                const hit = levelOk && (!q || row.dataset.code.includes(q) || row.dataset.name.includes(q));
                row.style.display = hit ? '' : 'none';
                if (hit) { cardHasRow = true; anyVisible = true; }
            });
            // hide year sub-labels whose following row group is now empty
            card.querySelectorAll('.dp-sublabel').forEach(sl => {
                const rows = sl.nextElementSibling;
                const visible = rows && rows.querySelector('.dp-row:not([style*="display: none"])');
                sl.style.display = visible ? '' : 'none';
            });
            card.style.display = cardHasRow ? '' : 'none';
        });

        nores.style.display = anyVisible ? 'none' : 'block';
    }

    filter.addEventListener('input', apply);
    clear.addEventListener('click', () => { filter.value = ''; filter.focus(); apply(); });
    chips.addEventListener('click', e => {
        const btn = e.target.closest('.dp-chip'); if (!btn) return;
        activeLevel = btn.dataset.level;
        chips.querySelectorAll('.dp-chip').forEach(c => c.classList.toggle('is-on', c === btn));
        apply();
    });
})();
</script>
@endpush

@endsection
