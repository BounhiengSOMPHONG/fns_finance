<x-app-layout>
@php
    $user = auth()->user();
    $displayName = $user->full_name ?? $user->username ?? 'Finance';
    $latestStatus = $latestPlan?->status ?? null;
    $statusLabels = [
        'DRAFT' => 'ກຳລັງຈັດເຮັດ',
        'PENDING_REVIEW' => 'ລໍຖ້າກວດ',
        'MODIFYING' => 'ກຳລັງແກ້ໄຂ',
        'SAVED' => 'ບັນທຶກແລ້ວ',
    ];
    $budgetSummary = $budgetSummary ?? [
        'year' => null,
        'budget_total' => 0,
        'committed_total' => 0,
        'actual_expense_total' => 0,
        'remaining_total' => 0,
    ];
    $summaryYear = $budgetSummary['year'] ?? null;
    $remainingIsNegative = (float) ($budgetSummary['remaining_total'] ?? 0) < 0;
@endphp

<style>
    .fh-home {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .fh-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: stretch;
        background:
            linear-gradient(135deg, rgba(26,39,68,0.97), rgba(36,50,87,0.93)),
            url('{{ asset('storage/BG-login.png') }}');
        background-size: cover;
        background-position: center 48%;
        color: #fff;
        border-radius: 8px;
        padding: 1.35rem;
        box-shadow: 0 16px 36px rgba(26,39,68,0.18);
        overflow: hidden;
    }

    .fh-hero-main {
        min-width: 0;
    }

    .fh-kicker {
        color: var(--fns-gold-light);
        font-size: .72rem;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .fh-title {
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.25;
        margin: 0;
    }

    .fh-copy {
        color: rgba(255,255,255,.72);
        font-size: .86rem;
        margin-top: .45rem;
        max-width: 42rem;
    }

    .fh-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: 1rem;
    }

    .fh-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        min-height: 2.25rem;
        padding: .55rem .85rem;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .fh-btn svg {
        width: 15px;
        height: 15px;
    }

    .fh-btn-primary {
        background: var(--fns-gold);
        color: var(--fns-navy-deep);
        box-shadow: 0 10px 24px rgba(201,153,26,.2);
    }

    .fh-btn-secondary {
        background: rgba(255,255,255,.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,.16);
    }

    .fh-btn:hover {
        transform: translateY(-1px);
    }

    .fh-user-panel {
        min-width: 250px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 8px;
        padding: 1rem;
        backdrop-filter: blur(12px);
    }

    .fh-user-row {
        display: flex;
        align-items: center;
        gap: .7rem;
    }

    .fh-avatar {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
        color: var(--fns-gold-light);
        border: 1px solid rgba(255,255,255,.16);
    }

    .fh-avatar svg {
        width: 19px;
        height: 19px;
    }

    .fh-user-name {
        font-weight: 800;
        line-height: 1.2;
    }

    .fh-user-role {
        color: rgba(255,255,255,.62);
        font-size: .75rem;
        margin-top: .15rem;
    }

    .fh-latest {
        border-top: 1px solid rgba(255,255,255,.12);
        margin-top: .9rem;
        padding-top: .85rem;
        font-size: .78rem;
        color: rgba(255,255,255,.68);
    }

    .fh-latest strong {
        display: block;
        color: #fff;
        font-size: 1rem;
        margin-top: .15rem;
    }

    .fh-summary-head {
        align-items: flex-end;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-top: .2rem;
    }

    .fh-summary-head h2 {
        color: var(--fns-navy);
        font-size: 1rem;
        font-weight: 900;
        margin: 0;
    }

    .fh-summary-head span {
        color: var(--fns-gray-600);
        font-size: .78rem;
        font-weight: 700;
    }

    .fh-summary-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .fh-money-card {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(26,39,68,.08);
        min-width: 0;
        overflow: hidden;
        padding: 1rem;
        position: relative;
    }

    .fh-money-card::before {
        content: "";
        height: 3px;
        inset: 0 0 auto;
        position: absolute;
    }

    .fh-money-card.is-budget::before {
        background: var(--fns-gold);
    }

    .fh-money-card.is-commitment::before {
        background: #d97706;
    }

    .fh-money-card.is-actual::before {
        background: #991b1b;
    }

    .fh-money-card.is-remaining-positive::before {
        background: #15803d;
    }

    .fh-money-card.is-remaining-negative::before {
        background: #b91c1c;
    }

    .fh-money-card span {
        color: var(--fns-gray-600);
        display: block;
        font-size: .76rem;
        font-weight: 800;
        margin-bottom: .5rem;
    }

    .fh-money-card strong {
        color: var(--fns-navy);
        display: block;
        font-family: 'Cinzel', serif;
        font-size: clamp(1.25rem, 2vw, 1.65rem);
        line-height: 1.05;
        overflow-wrap: anywhere;
    }

    .fh-money-card small {
        color: #64748b;
        display: block;
        font-size: .72rem;
        font-weight: 700;
        margin-top: .45rem;
    }

    .fh-money-card.is-commitment strong {
        color: #92400e;
    }

    .fh-money-card.is-actual strong {
        color: #7f1d1d;
    }

    .fh-money-card.is-remaining-positive strong {
        color: #166534;
    }

    .fh-money-card.is-remaining-negative strong {
        color: #991b1b;
    }

    @media (max-width: 980px) {
        .fh-hero {
            grid-template-columns: 1fr;
        }

        .fh-user-panel {
            min-width: 0;
        }

        .fh-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .fh-hero {
            padding: 1rem;
        }

        .fh-summary-head {
            align-items: flex-start;
            flex-direction: column;
            gap: .25rem;
        }

        .fh-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="fh-home">
    <section class="fh-hero">
        <div class="fh-hero-main">
            <div class="fh-kicker">FNS FINANCE</div>
            <h1 class="fh-title">ພາບລວມວຽກການເງິນ</h1>
            <p class="fh-copy">
                ຈັດການແຜນງົບປະມານ, ກວດຂໍ້ມູນລາຍຮັບ-ລາຍຈ່າຍ ແລະກຽມລາຍງານແຜນປະຈຳປີຈາກຈຸດດຽວ.
            </p>
            <div class="fh-hero-actions">
                <a href="{{ route('head_of_finance.manage-plan.index') }}" class="fh-btn fh-btn-primary">
                    <x-icons.book-open /> ໄປຈັດການແຜນ
                </a>
                <a href="{{ route('head_of_finance.settings.course-credits.index') }}" class="fh-btn fh-btn-secondary">
                    <x-icons.settings /> ຕັ້ງຄ່າລາຄາ/ໜ່ວຍກິດ
                </a>
            </div>
        </div>

        <aside class="fh-user-panel">
            <div class="fh-user-row">
                <div class="fh-avatar"><x-icons.user /></div>
                <div>
                    <div class="fh-user-name">{{ $displayName }}</div>
                    <div class="fh-user-role">ຫົວໜ້າການເງິນ</div>
                </div>
            </div>
            <div class="fh-latest">
                ແຜນປີລ່າສຸດ
                <strong>
                    @if($latestPlan)
                        {{ $latestPlan->year }} · {{ $statusLabels[$latestStatus] ?? $latestStatus }}
                    @else
                        ຍັງບໍ່ມີແຜນ
                    @endif
                </strong>
            </div>
        </aside>
    </section>

    <div class="fh-summary-head">
        <h2>ສະຫຼຸບຍອດການເງິນ</h2>
        <span>{{ $summaryYear ? 'ອີງຕາມແຜນປີ '.$summaryYear : 'ຍັງບໍ່ມີແຜນປີ' }}</span>
    </div>

    <section class="fh-summary-grid" aria-label="Finance summary">
        <article class="fh-money-card is-budget">
            <span>ຍອດງົບປະມານ</span>
            <strong>{{ number_format((float) $budgetSummary['budget_total'], 0, '.', '.') }}</strong>
            <small>ຍອດຈາກແຜນປີ</small>
        </article>
        <article class="fh-money-card is-commitment">
            <span>ຍອດຜູກພັນ</span>
            <strong>{{ number_format((float) $budgetSummary['committed_total'], 0, '.', '.') }}</strong>
            <small>Advance requests ທີ່ບໍ່ຖືກປະຕິເສດ</small>
        </article>
        <article class="fh-money-card is-actual">
            <span>ຍອດໃຊ້ຈ່າຍຈິງ</span>
            <strong>{{ number_format((float) $budgetSummary['actual_expense_total'], 0, '.', '.') }}</strong>
            <small>Transactions ປະເພດ expense</small>
        </article>
        <article class="fh-money-card {{ $remainingIsNegative ? 'is-remaining-negative' : 'is-remaining-positive' }}">
            <span>ຍອດຄົງເຫຼືອ</span>
            <strong>{{ number_format((float) $budgetSummary['remaining_total'], 0, '.', '.') }}</strong>
            <small>ງົບປະມານ - ໃຊ້ຈ່າຍຈິງ</small>
        </article>
    </section>

</div>
</x-app-layout>
