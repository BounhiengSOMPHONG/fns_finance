@extends('layouts.admin')

@section('title', 'ປະເມີນລາຍຮັບວິຊາການ')
@section('page-title', 'ປະເມີນລາຍຮັບວິຊາການ')

@section('content')

@php
    $currentYear = (int) date('Y');
@endphp

{{-- ===== Editorial hero ===== --}}
<section class="ai-hero">
    <div class="ai-hero-text">
        <span class="ai-hero-kicker">FY · {{ $currentYear }}</span>
        <h2 class="ai-hero-title">ແຜນປະເມີນລາຍຮັບວິຊາການ</h2>
        <p class="ai-hero-sub">ກຳນົດຈຳນວນນັກສຶກສາ ແລະ ໃຫ້ລະບົບຄຳນວນລາຍຮັບປະຈຳສົກໂດຍອັດຕະໂນມັດ.</p>
    </div>

    <div class="ai-hero-stats">
        <div class="ai-stat">
            <span class="ai-stat-label">ຈຳນວນແຜນ</span>
            <span class="ai-stat-value">{{ number_format($plans->total()) }}</span>
        </div>
        <button type="button" onclick="openCreatePlanModal()" class="ai-hero-cta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            <span>ສ້າງແຜນໃໝ່</span>
        </button>
    </div>
</section>

{{-- ===== Filter bar ===== --}}
<div class="ai-filter-bar">
    <label class="ai-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="text" id="planFilter" placeholder="ຄົ້ນຫາສົກປີ ຫຼື ຜູ້ສ້າງ..." autocomplete="off">
    </label>
    <span class="ai-filter-meta" id="planFilterMeta">ສະແດງ {{ $plans->count() }} / {{ $plans->total() }} ແຜນ</span>
</div>

{{-- ===== Plan card grid ===== --}}
<div class="ai-card-grid" id="planGrid">
    @forelse($plans as $plan)
        @php $isCurrent = (int) $plan->fiscal_year === $currentYear; @endphp
        <article class="ai-card {{ $isCurrent ? 'ai-card-current' : '' }}"
                 data-year="{{ $plan->fiscal_year }}"
                 data-creator="{{ strtolower($plan->creator?->full_name ?? '') }}">

            <header class="ai-card-head">
                <div class="ai-card-year">
                    <span class="ai-card-year-kicker">ສົກ</span>
                    <span class="ai-card-year-num">{{ $plan->fiscal_year }}</span>
                </div>
                @if($isCurrent)
                    <span class="ai-pill ai-pill-gold">ປະຈຸບັນ</span>
                @endif
            </header>

            <div class="ai-card-body">
                <div class="ai-card-row">
                    <span class="ai-card-row-key">ສ້າງໂດຍ</span>
                    <span class="ai-card-row-val ai-creator">
                        {{ $plan->creator?->full_name ?? '—' }}
                    </span>
                </div>
                <div class="ai-card-row">
                    <span class="ai-card-row-key">ວັນທີສ້າງ</span>
                    <span class="ai-card-row-val">{{ $plan->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <footer class="ai-card-foot">
                <a href="{{ route('head_of_finance.academic-income.evaluate', $plan) }}" class="ai-btn ai-btn-primary">
                    ປ້ອນຂໍ້ມູນ
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <form method="POST" action="{{ route('head_of_finance.academic-income.destroy', $plan) }}"
                      onsubmit="return confirmDelete(event, '{{ $plan->fiscal_year }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ai-btn ai-btn-ghost-danger" title="ລຶບແຜນ">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/></svg>
                    </button>
                </form>
            </footer>
        </article>
    @empty
        <div class="ai-empty">
            <div class="ai-empty-num">00</div>
            <h3 class="ai-empty-title">ຍັງບໍ່ມີແຜນລາຍຮັບ</h3>
            <p class="ai-empty-sub">ກົດ <strong>ສ້າງແຜນໃໝ່</strong> ເພື່ອເລີ່ມຕົ້ນປະເມີນລາຍຮັບສຳລັບສົກນີ້.</p>
            <button type="button" onclick="openCreatePlanModal()" class="ai-btn ai-btn-primary">ສ້າງແຜນທຳອິດ</button>
        </div>
    @endforelse
</div>

<div class="ai-pagination">{{ $plans->links() }}</div>

{{-- ===== Create Plan Modal ===== --}}
<div id="createPlanModal" class="ai-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="createPlanTitle">
    <div class="ai-modal">
        <div class="ai-modal-head">
            <div>
                <span class="ai-modal-kicker">ສ້າງແຜນໃໝ່</span>
                <h3 id="createPlanTitle" class="ai-modal-title">ປະເມີນລາຍຮັບວິຊາການ</h3>
            </div>
            <button type="button" class="ai-modal-close" onclick="closeCreatePlanModal()" aria-label="ປິດ">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6l-12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('head_of_finance.academic-income.store') }}" class="ai-modal-body">
            @csrf

            <div class="ai-form-group">
                <label class="ai-form-label" for="fiscal_year">ສົກປີງົບປະມານ <span class="ai-req">*</span></label>
                <input id="fiscal_year" type="number" name="fiscal_year"
                       class="ai-form-input @error('fiscal_year') is-invalid @enderror"
                       value="{{ old('fiscal_year', date('Y')) }}" min="2000" max="2100" required>
                @error('fiscal_year')
                    <div class="ai-form-error">{{ $message }}</div>
                @enderror
                <div class="ai-form-hint">ປ້ອນເລກ 4 ຫຼັກ ເຊັ່ນ {{ date('Y') }}</div>
            </div>

            <div class="ai-modal-foot">
                <button type="button" class="ai-btn ai-btn-secondary" onclick="closeCreatePlanModal()">ຍົກເລີກ</button>
                <button type="submit" class="ai-btn ai-btn-primary">ສ້າງແຜນ</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Styles (scoped to this page) ===== --}}
<style>
    /* === Hero === */
    .ai-hero {
        display:grid; grid-template-columns: 1fr auto; gap:2rem; align-items:end;
        padding: 1.75rem 1.85rem 1.85rem;
        margin-bottom: 1.5rem;
        background:
            radial-gradient(circle at 88% 12%, rgba(201,153,26,0.18), transparent 55%),
            linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid) 70%);
        color:#fff; border-radius: 14px; position:relative; overflow:hidden;
        box-shadow: 0 8px 24px -12px rgba(17,27,51,0.45);
    }
    .ai-hero::after {
        content:""; position:absolute; right:-40px; bottom:-40px;
        width:220px; height:220px;
        background: radial-gradient(circle, rgba(255,255,255,0.05), transparent 65%);
        pointer-events:none;
    }
    .ai-hero-kicker {
        font-family:'Cinzel', serif; font-size:0.72rem; letter-spacing:0.32em;
        color: var(--fns-gold-light, #e7be4f); text-transform:uppercase; font-weight:700;
    }
    .ai-hero-title {
        margin:.45rem 0 .55rem; font-size:1.85rem; font-weight:700; line-height:1.1;
        letter-spacing:-0.01em;
    }
    .ai-hero-sub { margin:0; opacity:0.78; font-size:0.86rem; max-width:46ch; line-height:1.55; }
    .ai-hero-stats { display:flex; align-items:flex-end; gap:1.4rem; }
    .ai-stat { display:flex; flex-direction:column; gap:.3rem; min-width:90px; }
    .ai-stat-label {
        font-size:0.65rem; letter-spacing:0.18em; text-transform:uppercase;
        color: rgba(255,255,255,0.55); font-weight:600;
    }
    .ai-stat-value { font-family:'Cinzel', serif; font-size:1.45rem; font-weight:700; line-height:1; }
    .ai-hero-cta {
        display:inline-flex; align-items:center; gap:.55rem;
        padding: .72rem 1.15rem; border-radius:10px;
        background: var(--fns-gold, #c9991a); color: var(--fns-navy-deep);
        border: none; font-weight:700; font-size:0.85rem; cursor:pointer;
        font-family: inherit; text-decoration:none;
        box-shadow: 0 4px 14px -4px rgba(201,153,26,0.55);
        transition: transform .15s, box-shadow .15s, background .15s;
    }
    .ai-hero-cta:hover { background: var(--fns-gold-light, #e7be4f); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(201,153,26,0.7); }
    .ai-hero-cta svg { width:16px; height:16px; }

    /* === Filter bar === */
    .ai-filter-bar {
        display:flex; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap;
    }
    .ai-search {
        display:flex; align-items:center; gap:.5rem; flex:1; min-width:260px; max-width:420px;
        padding: .55rem .85rem; background:#fff;
        border:1px solid var(--fns-gray-200); border-radius:9px;
        transition: border-color .15s, box-shadow .15s;
    }
    .ai-search:focus-within { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.1); }
    .ai-search svg { width:15px; height:15px; color: var(--fns-gray-400); }
    .ai-search input {
        flex:1; border:none; outline:none; background:transparent;
        font-family:inherit; font-size:0.85rem; color: var(--fns-navy);
    }
    .ai-filter-meta { font-size:0.75rem; color: var(--fns-gray-400); }

    /* === Card grid === */
    .ai-card-grid {
        display:grid; gap:1rem;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        margin-bottom: 1.5rem;
    }
    .ai-card {
        display:flex; flex-direction:column;
        background:#fff; border:1px solid var(--fns-gray-200);
        border-radius:12px; overflow:hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        position:relative;
    }
    .ai-card::before {
        content:""; position:absolute; top:0; left:0; width:4px; height:100%;
        background: linear-gradient(to bottom, var(--fns-navy), var(--fns-navy-light));
    }
    .ai-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -14px rgba(17,27,51,0.35);
        border-color: var(--fns-navy-light);
    }
    .ai-card-current::before { background: linear-gradient(to bottom, var(--fns-gold), var(--fns-gold-light, #e7be4f)); }

    .ai-card-head {
        display:flex; justify-content:space-between; align-items:flex-start;
        padding: 1.1rem 1.15rem .35rem 1.4rem;
    }
    .ai-card-year { display:flex; flex-direction:column; }
    .ai-card-year-kicker {
        font-size:0.62rem; letter-spacing:0.24em; text-transform:uppercase;
        color: var(--fns-gray-400); font-weight:700;
    }
    .ai-card-year-num {
        font-family:'Cinzel', serif; font-size:2.1rem; font-weight:700;
        color: var(--fns-navy); line-height:1; letter-spacing:-0.01em;
    }
    .ai-pill {
        font-size:0.65rem; padding: .22rem .55rem; border-radius:999px;
        letter-spacing:0.12em; text-transform:uppercase; font-weight:700;
    }
    .ai-pill-gold {
        background: rgba(201,153,26,0.14); color: #8b6a12;
        border:1px solid rgba(201,153,26,0.3);
    }

    .ai-card-body {
        padding: .9rem 1.4rem 1rem; display:flex; flex-direction:column; gap:.55rem;
        border-bottom:1px dashed var(--fns-gray-200);
    }
    .ai-card-row { display:flex; justify-content:space-between; align-items:baseline; gap:.6rem; }
    .ai-card-row-key { font-size:0.72rem; color: var(--fns-gray-400); }
    .ai-card-row-val { font-size:0.86rem; color: var(--fns-navy); font-weight:600; text-align:right; }
    .ai-creator { font-weight:500; color: var(--fns-gray-600); }

    .ai-card-foot { display:flex; gap:.5rem; padding: .8rem 1.15rem .85rem 1.4rem; align-items:center; }

    .ai-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:.4rem;
        padding: .55rem 1rem; border-radius:8px;
        font-family:inherit; font-size:0.8rem; font-weight:600;
        border:1px solid transparent; cursor:pointer;
        transition: background .15s, border-color .15s, color .15s, transform .1s;
        text-decoration:none;
    }
    .ai-btn svg { width:14px; height:14px; }
    .ai-btn-primary { flex:1; background: var(--fns-navy); color:#fff; }
    .ai-btn-primary:hover { background: var(--fns-navy-light); }
    .ai-btn-ghost-danger {
        background: transparent; color: #b91c1c; border-color: var(--fns-gray-200);
        padding: .55rem .65rem;
    }
    .ai-btn-ghost-danger:hover { background: rgba(185,28,28,0.08); border-color: rgba(185,28,28,0.25); }

    /* === Empty state === */
    .ai-empty {
        grid-column: 1 / -1;
        padding: 3.2rem 1.5rem;
        background:#fff; border:1px dashed var(--fns-gray-200); border-radius:14px;
        text-align:center; color: var(--fns-gray-600);
    }
    .ai-empty-num {
        font-family:'Cinzel', serif; font-size:4.5rem; font-weight:700;
        color: var(--fns-gray-200); line-height:1; margin-bottom:.5rem;
    }
    .ai-empty-title { margin:.25rem 0 .35rem; font-size:1.1rem; color: var(--fns-navy); font-weight:700; }
    .ai-empty-sub { margin:0 0 1.2rem; font-size:0.85rem; }

    .ai-pagination { margin-top:.5rem; }

    /* === Buttons in modal (extend existing .ai-btn) === */
    .ai-btn-secondary {
        background:#fff; color: var(--fns-navy);
        border-color: var(--fns-gray-200);
    }
    .ai-btn-secondary:hover { background: var(--fns-gray-100); }

    /* === Modal === */
    .ai-modal-backdrop {
        display:none; position:fixed; inset:0; z-index:9000;
        background: rgba(17,27,51,0.55); backdrop-filter: blur(2px);
        align-items:flex-start; justify-content:center;
        padding: 6vh 1rem 1rem;
    }
    .ai-modal {
        background:#fff; border-radius:14px; width:480px; max-width:100%;
        box-shadow: 0 22px 60px -20px rgba(17,27,51,0.6);
        animation: aiModalIn .22s ease-out;
        overflow:hidden;
    }
    @keyframes aiModalIn { from { opacity:0; transform: translateY(-8px) scale(.98); } to { opacity:1; transform:none; } }

    .ai-modal-head {
        display:flex; justify-content:space-between; align-items:flex-start;
        padding: 1.2rem 1.4rem 1rem;
        background: linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid));
        color:#fff;
    }
    .ai-modal-kicker {
        font-family:'Cinzel', serif; font-size:0.7rem; letter-spacing:0.2em;
        color: var(--fns-gold-light, #e7be4f); text-transform:uppercase; font-weight:700;
    }
    .ai-modal-title { margin:.3rem 0 0; font-size:1.1rem; font-weight:700; }
    .ai-modal-close {
        background:none; border:none; color:rgba(255,255,255,0.7); cursor:pointer; padding:.3rem;
        transition: color .15s;
    }
    .ai-modal-close:hover { color:#fff; }
    .ai-modal-close svg { width:18px; height:18px; }

    .ai-modal-body { padding: 1.3rem 1.4rem 1.4rem; display:flex; flex-direction:column; gap:1rem; }
    .ai-form-group { display:flex; flex-direction:column; gap:.4rem; }
    .ai-form-label { font-size:0.76rem; font-weight:600; color: var(--fns-gray-600); letter-spacing:.02em; }
    .ai-req { color:#b91c1c; }
    .ai-opt { font-weight:500; color: var(--fns-gray-400); font-size:.7rem; margin-left:.3rem; }
    .ai-form-input {
        padding: .65rem .85rem; border:1px solid var(--fns-gray-200);
        border-radius:8px; font-family:inherit; font-size:0.9rem; color: var(--fns-navy);
        background:#fff; outline:none; transition: border-color .15s, box-shadow .15s;
        resize: vertical;
    }
    .ai-form-input:focus { border-color: var(--fns-navy-light); box-shadow: 0 0 0 3px rgba(46,63,110,0.12); }
    .ai-form-input.is-invalid { border-color:#dc2626; }
    .ai-form-error { color:#b91c1c; font-size:0.75rem; }
    .ai-form-hint { color: var(--fns-gray-400); font-size:0.72rem; }

    .ai-modal-foot {
        display:flex; gap:.6rem; margin-top:.4rem; justify-content:flex-end;
    }

    @media (max-width: 720px) {
        .ai-hero { grid-template-columns: 1fr; gap:1.4rem; padding:1.4rem; }
        .ai-hero-stats { flex-wrap:wrap; gap:1rem; }
        .ai-hero-cta { width:100%; justify-content:center; }
        .ai-hero-title { font-size:1.5rem; }
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

    // Re-open modal if validation failed server-side
    @if($errors->has('fiscal_year'))
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
        grid.querySelectorAll('.ai-card').forEach(c => {
            const hit = !q || c.dataset.year.includes(q) || (c.dataset.creator || '').includes(q);
            c.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });
        meta.textContent = `ສະແດງ ${shown} / ${totalPlans} ແຜນ`;
    });

    // SweetAlert-based delete confirm (Sweetalert2 loaded in layout)
    function confirmDelete(e, year) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: 'ຢືນຢັນການລຶບແຜນ',
            html: `ແຜນປະເມີນລາຍຮັບ <strong>ສົກ ${year}</strong> ແລະ ຂໍ້ມູນທັງໝົດຈະຖືກລຶບຖາວອນ.`,
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
