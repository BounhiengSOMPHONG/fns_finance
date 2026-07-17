@extends('layouts.admin')

@section('title', 'ຕັ້ງລາຍຈ່າຍ')
@section('page-title', 'ຕັ້ງລາຍຈ່າຍ')

@section('content')
<div class="ex-setup">
    <section class="ex-hero">
        <div>
            <span class="ex-kicker">Expense Setup</span>
            <h2>ຕັ້ງຄ່າລາຍຈ່າຍໃນຈຸດດຽວ</h2>
            <p>ເລີ່ມຈາກໂຄງສ້າງ DEF, ກວດລິ້ງບັນຊີ, ແລ້ວປັບສູດຄຳນວນຕາມວຽກທີ່ຕ້ອງເຮັດ.</p>
        </div>
        <div class="ex-hero-metrics" aria-label="Expense setup summary">
            <span>
                <strong>{{ number_format($catalogItemsCount) }}</strong>
                <small>ລາຍການ</small>
            </span>
            <span>
                <strong>{{ number_format($linkedCatalogItemsCount) }}</strong>
                <small>ລິ້ງແລ້ວ</small>
            </span>
            <span>
                <strong>{{ number_format($activePatternsCount) }}</strong>
                <small>ສູດໃຊ້ງານ</small>
            </span>
        </div>
    </section>

    <div class="ex-workflows">
        <section class="ex-card ex-card-def">
            <div class="ex-card-head">
                <span class="ex-step">01</span>
                <div>
                    <h3>DEF ແຕ່ລະປີ</h3>
                    <p>ກຳນົດໝວດ, ກຸ່ມ, ແລະລາຍການລາຍຈ່າຍໃນແຕ່ລະສົກປີ.</p>
                </div>
            </div>

            <div class="ex-year-list">
                @forelse($yearSummaries as $summary)
                    @php
                        $year = $summary['year'];
                    @endphp
                    <a href="{{ route('head_of_finance.settings.expense-structure.index', ['planning_year_id' => $year->id]) }}"
                       class="ex-year-row">
                        <span>
                            <strong>{{ $year->year }}</strong>
                            <small>{{ $year->name }}</small>
                        </span>
                        <span class="ex-year-meta">
                            {{ number_format($summary['sections_count']) }} ໝວດ ·
                            {{ number_format($summary['subsections_count']) }} ກຸ່ມ ·
                            {{ number_format($summary['items_count']) }} ລາຍການ
                        </span>
                    </a>
                @empty
                    <div class="ex-empty">ຍັງບໍ່ມີສົກປີ.</div>
                @endforelse
            </div>
        </section>

        <section class="ex-card ex-card-link">
            <div class="ex-card-head">
                <span class="ex-step">02</span>
                <div>
                    <h3>ລາຍການລິ້ງບັນຊີ</h3>
                    <p>ເຊື່ອມລາຍການລາຍຈ່າຍກັບ Chart of Account ແຍກຕາມສົກປີ.</p>
                </div>
            </div>
            <div class="ex-link-years">
                @forelse($accountLinkYearSummaries as $summary)
                    @php
                        $year = $summary['year'];
                        $yearPercent = $summary['items_count'] > 0 ? round(($summary['linked_items_count'] / $summary['items_count']) * 100) : 0;
                    @endphp
                    <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index', ['planning_year_id' => $year->id]) }}"
                       class="ex-link-year-row">
                        <span>
                            <strong>{{ $year->year }}</strong>
                            <small>{{ number_format($summary['unlinked_items_count']) }} ລາຍການຍັງບໍ່ເຊື່ອມ</small>
                        </span>
                        <span>{{ number_format($summary['linked_items_count']) }}/{{ number_format($summary['items_count']) }}</span>
                        <i style="width: {{ $yearPercent }}%"></i>
                    </a>
                @empty
                    <div class="ex-empty">ຍັງບໍ່ມີ DEF ສຳລັບລິ້ງບັນຊີ.</div>
                @endforelse
            </div>
        </section>

        <a href="{{ route('head_of_finance.settings.expense-patterns.index') }}" class="ex-card ex-card-formula">
            <div class="ex-card-head">
                <span class="ex-step">03</span>
                <div>
                    <h3>ສູດຄຳນວນ</h3>
                    <p>ກຳນົດຊ່ອງກອກ ແລະຕົວຄູນທີ່ໃຊ້ຄິດຍອດລວມ.</p>
                </div>
            </div>
            <div class="ex-formula-stat">
                <strong>{{ number_format($activePatternsCount) }}</strong>
                <span>ສູດທີ່ໃຊ້ງານ</span>
            </div>
            <p class="ex-muted">{{ number_format($patternsCount) }} ສູດທັງໝົດ · {{ number_format($patternFieldsCount) }} ຊ່ອງກອກ</p>
            <span class="ex-open">ໄປສູດຄຳນວນ</span>
        </a>
    </div>
</div>

<style>
    .ex-setup { display:flex; flex-direction:column; gap:1.05rem; }
    .ex-hero,
    .ex-card {
        border:1px solid #d9e0ea;
        border-radius:8px;
        background:#fff;
        box-shadow:0 1px 10px rgba(15,23,42,.05);
    }
    .ex-hero {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1.2rem 1.35rem;
        background:linear-gradient(135deg,#fff 0%,#f8fafc 100%);
    }
    .ex-kicker {
        display:inline-flex;
        color:#8a5a00;
        font-size:.7rem;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
    }
    .ex-hero h2,
    .ex-card h3 { margin:0; color:#13213b; font-weight:900; }
    .ex-hero h2 { margin-top:.2rem; font-size:1.25rem; }
    .ex-hero p,
    .ex-card p { margin:.35rem 0 0; color:#64748b; font-size:.86rem; line-height:1.55; }
    .ex-hero-metrics {
        display:grid;
        grid-template-columns:repeat(3,minmax(5.5rem,1fr));
        gap:.45rem;
        min-width:23rem;
    }
    .ex-hero-metrics span {
        display:grid;
        place-items:center;
        border-radius:8px;
        background:#fff8df;
        border:1px solid rgba(201,153,26,.18);
        padding:.7rem .85rem;
        color:#6b4a00;
    }
    .ex-hero-metrics strong { font-size:1.3rem; line-height:1; font-variant-numeric:tabular-nums; }
    .ex-hero-metrics small { margin-top:.22rem; font-size:.68rem; font-weight:900; white-space:nowrap; }
    .ex-workflows {
        display:grid;
        grid-template-columns:minmax(0,1.25fr) minmax(18rem,.85fr) minmax(18rem,.85fr);
        gap:1rem;
        align-items:stretch;
    }
    .ex-card {
        display:flex;
        flex-direction:column;
        gap:1rem;
        min-width:0;
        padding:1.05rem;
        color:inherit;
        text-decoration:none;
        transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .ex-card-def { border-top:4px solid #13213b; }
    .ex-card-link { border-top:4px solid #16a34a; }
    .ex-card-formula { border-top:4px solid #c9991a; }
    .ex-card[href]:hover,
    .ex-link-year-row:hover {
        border-color:#d39b27;
        box-shadow:0 12px 26px rgba(15,23,42,.10);
        transform:translateY(-1px);
    }
    .ex-card-head { display:flex; gap:.75rem; align-items:flex-start; }
    .ex-step {
        display:grid;
        place-items:center;
        flex:0 0 auto;
        width:2rem;
        height:2rem;
        border-radius:999px;
        background:#13213b;
        color:#fff;
        font-size:.72rem;
        font-weight:900;
        font-variant-numeric:tabular-nums;
    }
    .ex-year-list { display:grid; gap:.55rem; }
    .ex-year-row {
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        gap:.75rem;
        align-items:center;
        border:1px solid #e2e8f0;
        border-radius:8px;
        background:#f8fafc;
        padding:.7rem .8rem;
        color:#172033;
        text-decoration:none;
        transition:background .15s ease, border-color .15s ease;
    }
    .ex-year-row:hover { border-color:#e6b84e; background:#fff9e8; }
    .ex-year-row strong { display:block; font-size:.95rem; }
    .ex-year-row small,
    .ex-year-meta,
    .ex-muted { color:#64748b; font-size:.75rem; font-weight:700; }
    .ex-year-meta { white-space:nowrap; }
    .ex-empty {
        border:1px dashed #cbd5e1;
        border-radius:8px;
        padding:1rem;
        color:#64748b;
        text-align:center;
    }
    .ex-formula-stat strong { color:#13213b; font-size:1.65rem; line-height:1; }
    .ex-formula-stat span { color:#64748b; font-size:.8rem; font-weight:800; }
    .ex-link-years {
        display:grid;
        gap:.45rem;
    }
    .ex-link-year-row {
        position:relative;
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        gap:.65rem;
        align-items:center;
        overflow:hidden;
        border:1px solid #e2e8f0;
        border-radius:8px;
        background:#f8fafc;
        padding:.65rem .75rem;
        color:#172033;
        text-decoration:none;
        transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .ex-link-year-row strong,
    .ex-link-year-row > span:last-of-type {
        position:relative;
        z-index:1;
        font-size:.88rem;
        font-weight:900;
        font-variant-numeric:tabular-nums;
    }
    .ex-link-year-row small {
        position:relative;
        z-index:1;
        display:block;
        margin-top:.12rem;
        color:#64748b;
        font-size:.68rem;
        font-weight:800;
    }
    .ex-link-year-row i {
        position:absolute;
        inset:auto auto 0 0;
        height:3px;
        min-width:.35rem;
        background:#16a34a;
    }
    .ex-formula-stat { display:flex; align-items:end; gap:.45rem; margin-top:auto; }
    .ex-open {
        display:inline-flex;
        align-items:center;
        width:max-content;
        border-radius:999px;
        background:#fff8df;
        border:1px solid rgba(201,153,26,.28);
        padding:.28rem .65rem;
        margin-top:auto;
        color:#8a5a00;
        font-size:.8rem;
        font-weight:900;
    }
    @media (max-width:1100px) {
        .ex-workflows { grid-template-columns:1fr; }
        .ex-year-row { grid-template-columns:1fr; }
        .ex-year-meta { white-space:normal; }
    }
    @media (max-width:700px) {
        .ex-hero { align-items:stretch; flex-direction:column; }
        .ex-hero-metrics { grid-template-columns:1fr; min-width:0; }
        .ex-hero-metrics span { place-items:start; }
    }
</style>
@endsection
