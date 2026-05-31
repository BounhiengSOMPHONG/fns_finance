@extends('layouts.admin')

@section('title', 'ຕາຕະລາງເງິນເດືອນ')
@section('page-title', 'ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ')

@section('content')

@php
    $currentYear  = (int) date('Y');
    $currentMonth = (int) date('n');
    $monthNames   = ['','ມັງກອນ','ກຸມພາ','ມີນາ','ເມສາ','ພຶດສະພາ','ມິຖຸນາ','ກໍລະກົດ','ສິງຫາ','ກັນຍາ','ຕຸລາ','ພະຈິກ','ທັນວາ'];
    $totalAcross  = 0;
    foreach ($plans as $p) { $totalAcross += $p->monthlyTotal(); }
@endphp

{{-- ===== Editorial hero ===== --}}
<section class="sal-hero">
    <div class="sal-hero-text">
        <span class="sal-hero-kicker">FY · {{ $currentYear }}</span>
        <h2 class="sal-hero-title">ຕາຕະລາງເງິນເດືອນ</h2>
        <p class="sal-hero-sub">ສ້າງ ແລະ ຈັດການແຜນເງິນເດືອນປະຈຳເດືອນ — ລະບົບຄຳນວນລາຍຈ່າຍລວມໂດຍອັດຕະໂນມັດ.</p>
    </div>

    <div class="sal-hero-stats">
        <div class="sal-stat">
            <span class="sal-stat-label">ຈຳນວນແຜນ</span>
            <span class="sal-stat-value">{{ number_format($plans->total()) }}</span>
        </div>
        <div class="sal-stat sal-stat-accent">
            <span class="sal-stat-label">ລວມຕໍ່ໜ້ານີ້ (ກີບ)</span>
            <span class="sal-stat-value">{{ number_format($totalAcross, 0) }}</span>
        </div>
        <button type="button" onclick="openCreatePlanModal()" class="sal-hero-cta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            <span>ສ້າງແຜນໃໝ່</span>
        </button>
    </div>
</section>

{{-- ===== Filter bar ===== --}}
<div class="sal-filter-bar">
    <label class="sal-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="text" id="planFilter" placeholder="ຄົ້ນຫາສົກປີ, ເດືອນ ຫຼື ຜູ້ສ້າງ..." autocomplete="off">
    </label>
    <span class="sal-filter-meta" id="planFilterMeta">ສະແດງ {{ $plans->count() }} / {{ $plans->total() }} ແຜນ</span>
</div>

{{-- ===== Plan card grid ===== --}}
<div class="sal-card-grid" id="planGrid">
    @forelse($plans as $plan)
        @php
            $isCurrent = (int) $plan->fiscal_year === $currentYear && (int) $plan->month === $currentMonth;
            $approved  = $plan->isApproved();
            $monthly   = $plan->monthlyTotal();
            $annual    = $plan->grandTotal();
        @endphp
        <article class="sal-card {{ $isCurrent ? 'sal-card-current' : '' }} {{ $approved ? 'sal-card-approved' : '' }}"
                 data-year="{{ $plan->fiscal_year }}"
                 data-month="{{ $plan->month }}"
                 data-monthname="{{ strtolower($monthNames[$plan->month] ?? '') }}"
                 data-creator="{{ strtolower($plan->creator?->full_name ?? $plan->creator?->username ?? '') }}">

            <header class="sal-card-head">
                <div class="sal-card-month">
                    <span class="sal-card-month-kicker">ເດືອນ</span>
                    <span class="sal-card-month-num">{{ str_pad((string) $plan->month, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="sal-card-month-name">{{ $monthNames[$plan->month] ?? '' }}</span>
                </div>
                <div class="sal-card-year">
                    <span class="sal-card-year-kicker">ສົກ</span>
                    <span class="sal-card-year-num">{{ $plan->fiscal_year }}</span>
                </div>
                @if($approved)
                    <span class="sal-pill sal-pill-green">ອະນຸມັດແລ້ວ</span>
                @elseif($isCurrent)
                    <span class="sal-pill sal-pill-gold">ປະຈຸບັນ</span>
                @endif
            </header>

            <div class="sal-card-body">
                <div class="sal-card-row">
                    <span class="sal-card-row-key">ລວມຕໍ່ເດືອນ</span>
                    <span class="sal-card-row-val sal-money">
                        {{ number_format($monthly, 0) }}<span class="sal-money-unit">ກີບ</span>
                    </span>
                </div>
                <div class="sal-card-row">
                    <span class="sal-card-row-key">ລວມ 12 ເດືອນ</span>
                    <span class="sal-card-row-val sal-money sal-money-accent">
                        {{ number_format($annual, 0) }}<span class="sal-money-unit">ກີບ</span>
                    </span>
                </div>
                <div class="sal-card-row">
                    <span class="sal-card-row-key">ສ້າງໂດຍ</span>
                    <span class="sal-card-row-val sal-creator">
                        {{ $plan->creator?->full_name ?? $plan->creator?->username ?? '—' }}
                    </span>
                </div>
            </div>

            <footer class="sal-card-foot">
                <a href="{{ route('head_of_finance.salary.manage', $plan) }}" class="sal-btn sal-btn-primary">
                    ຈັດການ
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                @if(!$approved)
                    <form method="POST" action="{{ route('head_of_finance.salary.destroy', $plan) }}"
                          onsubmit="return confirmDelete(event, '{{ str_pad((string) $plan->month, 2, '0', STR_PAD_LEFT) }}/{{ $plan->fiscal_year }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="sal-btn sal-btn-ghost-danger" title="ລຶບແຜນ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/></svg>
                        </button>
                    </form>
                @endif
            </footer>
        </article>
    @empty
        <div class="sal-empty">
            <div class="sal-empty-num">00</div>
            <h3 class="sal-empty-title">ຍັງບໍ່ມີຂໍ້ມູນເງິນເດືອນ</h3>
            <p class="sal-empty-sub">ກົດ <strong>ສ້າງແຜນໃໝ່</strong> ເພື່ອເລີ່ມຕົ້ນຕາຕະລາງເງິນເດືອນສຳລັບເດືອນນີ້.</p>
            <button type="button" onclick="openCreatePlanModal()" class="sal-btn sal-btn-primary">ສ້າງແຜນທຳອິດ</button>
        </div>
    @endforelse
</div>

<div class="sal-pagination">{{ $plans->links() }}</div>

{{-- ===== Create Plan Modal ===== --}}
<div id="createPlanModal" class="sal-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="createPlanTitle">
    <div class="sal-modal">
        <div class="sal-modal-head">
            <div>
                <span class="sal-modal-kicker">ສ້າງແຜນໃໝ່</span>
                <h3 id="createPlanTitle" class="sal-modal-title">ຕາຕະລາງເງິນເດືອນ</h3>
            </div>
            <button type="button" class="sal-modal-close" onclick="closeCreatePlanModal()" aria-label="ປິດ">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6l-12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('head_of_finance.salary.store') }}" class="sal-modal-body">
            @csrf

            <div class="sal-form-row">
                <div class="sal-form-group">
                    <label class="sal-form-label" for="fiscal_year">ສົກ (ປີ) <span class="sal-req">*</span></label>
                    <input id="fiscal_year" type="number" name="fiscal_year"
                           class="sal-form-input @error('fiscal_year') is-invalid @enderror"
                           value="{{ old('fiscal_year', $currentYear) }}" min="2000" max="2100" required>
                    @error('fiscal_year')
                        <div class="sal-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sal-form-group">
                    <label class="sal-form-label" for="month">ເດືອນ <span class="sal-req">*</span></label>
                    <select id="month" name="month" class="sal-form-input @error('month') is-invalid @enderror" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ (int) old('month', 1) === $m ? 'selected' : '' }}>
                                {{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }} — {{ $monthNames[$m] }}
                            </option>
                        @endforeach
                    </select>
                    @error('month')
                        <div class="sal-form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="sal-modal-foot">
                <button type="button" class="sal-btn sal-btn-secondary" onclick="closeCreatePlanModal()">ຍົກເລີກ</button>
                <button type="submit" class="sal-btn sal-btn-primary">ສ້າງແຜນ</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Styles (scoped) ===== --}}
<style>
    /* === Hero === */
    .sal-hero {
        display:grid; grid-template-columns: 1fr auto; gap:2rem; align-items:end;
        padding: 1.75rem 1.85rem 1.85rem;
        margin-bottom: 1.5rem;
        background:
            radial-gradient(circle at 88% 12%, rgba(201,153,26,0.18), transparent 55%),
            linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid) 70%);
        color:#fff; border-radius: 14px; position:relative; overflow:hidden;
        box-shadow: 0 8px 24px -12px rgba(17,27,51,0.45);
    }
    .sal-hero::after {
        content:""; position:absolute; right:-40px; bottom:-40px;
        width:220px; height:220px;
        background: radial-gradient(circle, rgba(255,255,255,0.05), transparent 65%);
        pointer-events:none;
    }
    .sal-hero-kicker {
        font-family:'Cinzel', serif; font-size:0.72rem; letter-spacing:0.32em;
        color: var(--fns-gold-light, #e7be4f); text-transform:uppercase; font-weight:700;
    }
    .sal-hero-title {
        margin:.45rem 0 .55rem; font-size:1.85rem; font-weight:700; line-height:1.1;
        letter-spacing:-0.01em;
    }
    .sal-hero-sub { margin:0; opacity:0.78; font-size:0.86rem; max-width:46ch; line-height:1.55; }
    .sal-hero-stats { display:flex; align-items:flex-end; gap:1.4rem; }
    .sal-stat { display:flex; flex-direction:column; gap:.3rem; min-width:90px; }
    .sal-stat-label {
        font-size:0.65rem; letter-spacing:0.18em; text-transform:uppercase;
        color: rgba(255,255,255,0.55); font-weight:600;
    }
    .sal-stat-value { font-family:'Cinzel', serif; font-size:1.45rem; font-weight:700; line-height:1; }
    .sal-stat-accent .sal-stat-value { color: var(--fns-gold-light, #e7be4f); font-size:1.65rem; }
    .sal-hero-cta {
        display:inline-flex; align-items:center; gap:.55rem;
        padding: .72rem 1.15rem; border-radius:10px;
        background: var(--fns-gold, #c9991a); color: var(--fns-navy-deep);
        border: none; font-weight:700; font-size:0.85rem; cursor:pointer;
        font-family: inherit;
        box-shadow: 0 4px 14px -4px rgba(201,153,26,0.55);
        transition: transform .15s, box-shadow .15s, background .15s;
    }
    .sal-hero-cta:hover { background: var(--fns-gold-light, #e7be4f); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(201,153,26,0.7); }
    .sal-hero-cta svg { width:16px; height:16px; }

    /* === Filter bar === */
    .sal-filter-bar { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
    .sal-search {
        display:flex; align-items:center; gap:.5rem; flex:1; min-width:260px; max-width:440px;
        padding: .55rem .85rem; background:#fff;
        border:1px solid var(--fns-gray-200); border-radius:9px;
        transition: border-color .15s, box-shadow .15s;
    }
    .sal-search:focus-within { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }
    .sal-search svg { width:15px; height:15px; color: var(--fns-gray-400); }
    .sal-search input {
        flex:1; border:none; outline:none; background:transparent;
        font-family:inherit; font-size:0.85rem; color: var(--fns-navy);
    }
    .sal-filter-meta { font-size:0.75rem; color: var(--fns-gray-400); }

    /* === Card grid === */
    .sal-card-grid {
        display:grid; gap:1rem;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        margin-bottom: 1.5rem;
    }
    .sal-card {
        display:flex; flex-direction:column;
        background:#fff; border:1px solid var(--fns-gray-200);
        border-radius:12px; overflow:hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        position:relative;
    }
    .sal-card::before {
        content:""; position:absolute; top:0; left:0; width:4px; height:100%;
        background: linear-gradient(to bottom, var(--fns-navy), var(--fns-navy-light));
    }
    .sal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -14px rgba(17,27,51,0.35);
        border-color: var(--fns-navy-light);
    }
    .sal-card-current::before { background: linear-gradient(to bottom, var(--fns-gold), var(--fns-gold-light, #e7be4f)); }
    .sal-card-approved::before { background: linear-gradient(to bottom, #166534, #22c55e); }

    .sal-card-head {
        display:grid; grid-template-columns: auto 1fr auto;
        align-items: center; gap: .65rem;
        padding: 1rem 1.15rem .6rem 1.4rem;
    }
    .sal-card-month {
        display:flex; flex-direction:column; line-height:1;
    }
    .sal-card-month-kicker {
        font-size:0.6rem; letter-spacing:0.22em; text-transform:uppercase;
        color: var(--fns-gray-400); font-weight:700;
    }
    .sal-card-month-num {
        font-family:'Cinzel', serif; font-size:1.95rem; font-weight:700;
        color: var(--fns-navy); margin-top:.1rem; letter-spacing:-0.01em;
    }
    .sal-card-month-name {
        font-size:0.7rem; color: var(--fns-gray-600); margin-top:.15rem; font-weight:500;
    }
    .sal-card-year { display:flex; flex-direction:column; line-height:1; }
    .sal-card-year-kicker {
        font-size:0.58rem; letter-spacing:0.2em; text-transform:uppercase;
        color: var(--fns-gray-400); font-weight:700;
    }
    .sal-card-year-num {
        font-family:'Cinzel', serif; font-size:1rem; font-weight:600;
        color: var(--fns-gray-600); margin-top:.18rem; letter-spacing:.04em;
    }

    .sal-pill {
        font-size:0.62rem; padding: .2rem .55rem; border-radius:999px;
        letter-spacing:0.1em; text-transform:uppercase; font-weight:700;
        white-space: nowrap; align-self: flex-start; margin-top: .25rem;
    }
    .sal-pill-gold { background: rgba(201,153,26,0.14); color: #8b6a12; border:1px solid rgba(201,153,26,0.3); }
    .sal-pill-green { background: rgba(22,101,52,0.12); color: #166534; border:1px solid rgba(22,101,52,0.3); }

    .sal-card-body {
        padding: .6rem 1.4rem 1rem; display:flex; flex-direction:column; gap:.5rem;
        border-bottom:1px dashed var(--fns-gray-200);
    }
    .sal-card-row { display:flex; justify-content:space-between; align-items:baseline; gap:.6rem; }
    .sal-card-row-key { font-size:0.72rem; color: var(--fns-gray-400); }
    .sal-card-row-val { font-size:0.86rem; color: var(--fns-navy); font-weight:600; text-align:right; }
    .sal-creator { font-weight:500; color: var(--fns-gray-600); }
    .sal-money { font-family:'Cinzel', serif; font-size:1rem; }
    .sal-money-accent { color: var(--fns-navy); font-size:1.1rem; }
    .sal-money-unit { font-family:'Noto Sans Lao', sans-serif; font-size:0.66rem; color: var(--fns-gray-400); margin-left:.3rem; font-weight:500; }

    .sal-card-foot { display:flex; gap:.5rem; padding: .8rem 1.15rem .85rem 1.4rem; align-items:center; }

    .sal-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:.4rem;
        padding: .55rem 1rem; border-radius:8px;
        font-family:inherit; font-size:0.8rem; font-weight:600;
        border:1px solid transparent; cursor:pointer;
        transition: background .15s, border-color .15s, color .15s, transform .1s;
        text-decoration:none;
    }
    .sal-btn svg { width:14px; height:14px; }
    .sal-btn-primary { flex:1; background: var(--fns-navy); color:#fff; }
    .sal-btn-primary:hover { background: var(--fns-navy-light); }
    .sal-btn-secondary { background:#fff; color: var(--fns-navy); border-color: var(--fns-gray-200); }
    .sal-btn-secondary:hover { background: var(--fns-gray-100); }
    .sal-btn-ghost-danger {
        background: transparent; color: #b91c1c; border-color: var(--fns-gray-200);
        padding: .55rem .65rem;
    }
    .sal-btn-ghost-danger:hover { background: rgba(185,28,28,0.08); border-color: rgba(185,28,28,0.25); }

    /* === Empty === */
    .sal-empty {
        grid-column: 1 / -1;
        padding: 3.2rem 1.5rem;
        background:#fff; border:1px dashed var(--fns-gray-200); border-radius:14px;
        text-align:center; color: var(--fns-gray-600);
    }
    .sal-empty-num {
        font-family:'Cinzel', serif; font-size:4.5rem; font-weight:700;
        color: var(--fns-gray-200); line-height:1; margin-bottom:.5rem;
    }
    .sal-empty-title { margin:.25rem 0 .35rem; font-size:1.1rem; color: var(--fns-navy); font-weight:700; }
    .sal-empty-sub { margin:0 0 1.2rem; font-size:0.85rem; }

    .sal-pagination { margin-top:.5rem; }

    /* === Modal === */
    .sal-modal-backdrop {
        display:none; position:fixed; inset:0; z-index:9000;
        background: rgba(17,27,51,0.55); backdrop-filter: blur(2px);
        align-items:flex-start; justify-content:center;
        padding: 6vh 1rem 1rem;
    }
    .sal-modal {
        background:#fff; border-radius:14px; width:520px; max-width:100%;
        box-shadow: 0 22px 60px -20px rgba(17,27,51,0.6);
        animation: salModalIn .22s ease-out;
        overflow:hidden;
    }
    @keyframes salModalIn { from { opacity:0; transform: translateY(-8px) scale(.98); } to { opacity:1; transform:none; } }

    .sal-modal-head {
        display:flex; justify-content:space-between; align-items:flex-start;
        padding: 1.2rem 1.4rem 1rem;
        background: linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid));
        color:#fff;
    }
    .sal-modal-kicker {
        font-family:'Cinzel', serif; font-size:0.7rem; letter-spacing:0.2em;
        color: var(--fns-gold-light, #e7be4f); text-transform:uppercase; font-weight:700;
    }
    .sal-modal-title { margin:.3rem 0 0; font-size:1.1rem; font-weight:700; }
    .sal-modal-close {
        background:none; border:none; color:rgba(255,255,255,0.7); cursor:pointer; padding:.3rem;
        transition: color .15s;
    }
    .sal-modal-close:hover { color:#fff; }
    .sal-modal-close svg { width:18px; height:18px; }

    .sal-modal-body { padding: 1.3rem 1.4rem 1.4rem; }
    .sal-form-row { display:grid; grid-template-columns: 1fr 2fr; gap:1rem; }
    .sal-form-group { display:flex; flex-direction:column; gap:.4rem; }
    .sal-form-label { font-size:0.76rem; font-weight:600; color: var(--fns-gray-600); letter-spacing:.02em; }
    .sal-req { color:#b91c1c; }
    .sal-form-input {
        padding: .65rem .85rem; border:1px solid var(--fns-gray-200);
        border-radius:8px; font-family:inherit; font-size:0.9rem; color: var(--fns-navy);
        background:#fff; outline:none; transition: border-color .15s, box-shadow .15s;
    }
    .sal-form-input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.12); }
    .sal-form-input.is-invalid { border-color:#dc2626; }
    .sal-form-error { color:#b91c1c; font-size:0.75rem; }

    .sal-modal-foot { display:flex; gap:.6rem; margin-top:1.3rem; justify-content:flex-end; }

    @media (max-width: 720px) {
        .sal-hero { grid-template-columns: 1fr; gap:1.4rem; padding:1.4rem; }
        .sal-hero-stats { flex-wrap:wrap; gap:1rem; }
        .sal-hero-cta { width:100%; justify-content:center; }
        .sal-hero-title { font-size:1.5rem; }
        .sal-form-row { grid-template-columns: 1fr; }
    }
</style>

<script>
    // Modal open/close
    function openCreatePlanModal() {
        document.getElementById('createPlanModal').style.display = 'flex';
        setTimeout(() => document.getElementById('fiscal_year')?.focus(), 50);
    }
    function closeCreatePlanModal() {
        document.getElementById('createPlanModal').style.display = 'none';
    }
    document.getElementById('createPlanModal').addEventListener('click', (e) => {
        if (e.target.id === 'createPlanModal') closeCreatePlanModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeCreatePlanModal();
    });

    @if($errors->has('fiscal_year') || $errors->has('month'))
        document.addEventListener("DOMContentLoaded", openCreatePlanModal);
    @endif

    // Live filter
    const filter = document.getElementById('planFilter');
    const grid   = document.getElementById('planGrid');
    const meta   = document.getElementById('planFilterMeta');
    const totalPlans = {{ $plans->total() }};
    filter?.addEventListener('input', () => {
        const q = filter.value.trim().toLowerCase();
        let shown = 0;
        grid.querySelectorAll('.sal-card').forEach(c => {
            const hit = !q
                || c.dataset.year.includes(q)
                || c.dataset.month.includes(q)
                || (c.dataset.monthname || '').includes(q)
                || (c.dataset.creator || '').includes(q);
            c.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });
        meta.textContent = `ສະແດງ ${shown} / ${totalPlans} ແຜນ`;
    });

    // SweetAlert delete confirm
    function confirmDelete(e, label) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: 'ຢືນຢັນການລຶບແຜນ',
            html: `ຕາຕະລາງເງິນເດືອນ <strong>${label}</strong> ແລະ ຂໍ້ມູນທັງໝົດຈະຖືກລຶບຖາວອນ.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ລຶບແຜນ',
            cancelButtonText: 'ຍົກເລີກ',
            reverseButtons: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#6b7280',
        }).then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }
</script>

@endsection
