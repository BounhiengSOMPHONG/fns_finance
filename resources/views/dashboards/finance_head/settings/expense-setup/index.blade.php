@extends('layouts.admin')

@section('title', 'ຕັ້ງລາຍຈ່າຍ')
@section('page-title', 'ຕັ້ງລາຍຈ່າຍ')

@section('content')
@php
    $linkPercent = $catalogItemsCount > 0 ? round(($linkedCatalogItemsCount / $catalogItemsCount) * 100) : 0;
@endphp

<div class="ex-setup">
    @include('dashboards.finance_head.settings.expense-setup-tabs')

    <section class="ex-hero">
        <div>
            <span class="ex-kicker">Expense Setup</span>
            <h2>ເລືອກວຽກທີ່ຈະຕັ້ງຄ່າ</h2>
            <p>ໜ້ານີ້ແຍກວຽກອອກເປັນ 3 ສ່ວນ: DEF ລາຍປີ, ລິ້ງບັນຊີ, ແລະ ສູດຄຳນວນ.</p>
        </div>
        <div class="ex-hero-stat">
            <strong>{{ number_format($catalogItemsCount) }}</strong>
            <span>ລາຍການທັງໝົດ</span>
        </div>
    </section>

    <div class="ex-workflows">
        <section class="ex-card ex-card-def">
            <div class="ex-card-head">
                <span class="ex-step">1</span>
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

        <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}" class="ex-card ex-card-link">
            <div class="ex-card-head">
                <span class="ex-step">2</span>
                <div>
                    <h3>ລາຍການລິ້ງບັນຊີ</h3>
                    <p>ເຊື່ອມລາຍການລາຍຈ່າຍກັບ Chart of Account.</p>
                </div>
            </div>
            <div class="ex-progress">
                <div>
                    <strong>{{ number_format($linkedCatalogItemsCount) }}</strong>
                    <span>/ {{ number_format($catalogItemsCount) }} ເຊື່ອມແລ້ວ</span>
                </div>
                <small>{{ number_format($unlinkedCatalogItemsCount) }} ລາຍການຍັງບໍ່ເຊື່ອມ</small>
                <span class="ex-progress-bar">
                    <i style="width: {{ $linkPercent }}%"></i>
                </span>
            </div>
            <span class="ex-open">ໄປລິ້ງບັນຊີ</span>
        </a>

        <a href="{{ route('head_of_finance.settings.expense-patterns.index') }}" class="ex-card ex-card-formula">
            <div class="ex-card-head">
                <span class="ex-step">3</span>
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
    .ex-setup { display:flex; flex-direction:column; gap:1rem; }
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
        padding:1.1rem 1.25rem;
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
    .ex-hero-stat {
        display:grid;
        place-items:center;
        min-width:9rem;
        border-radius:8px;
        background:#fff8df;
        padding:.75rem 1rem;
        color:#6b4a00;
    }
    .ex-hero-stat strong { font-size:1.55rem; line-height:1; }
    .ex-hero-stat span { margin-top:.25rem; font-size:.72rem; font-weight:900; }
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
    .ex-card[href]:hover {
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
        font-size:.8rem;
        font-weight:900;
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
    .ex-progress { display:grid; gap:.45rem; margin-top:auto; }
    .ex-progress strong,
    .ex-formula-stat strong { color:#13213b; font-size:1.65rem; line-height:1; }
    .ex-progress span,
    .ex-formula-stat span { color:#64748b; font-size:.8rem; font-weight:800; }
    .ex-progress small { color:#9a3412; font-size:.76rem; font-weight:900; }
    .ex-progress-bar {
        display:block;
        height:.55rem;
        overflow:hidden;
        border-radius:999px;
        background:#e2e8f0;
    }
    .ex-progress-bar i {
        display:block;
        height:100%;
        min-width:.35rem;
        border-radius:999px;
        background:#16a34a;
    }
    .ex-formula-stat { display:flex; align-items:end; gap:.45rem; margin-top:auto; }
    .ex-open {
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
        .ex-hero-stat { place-items:start; min-width:0; }
    }
</style>
@endsection
