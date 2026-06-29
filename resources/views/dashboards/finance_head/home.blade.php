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
    $budgetTotal = max((float) ($budgetSummary['budget_total'] ?? 0), 0.0);
    $percentOfBudget = function ($amount) use ($budgetTotal): float {
        if ($budgetTotal <= 0) {
            return 0.0;
        }

        return min(100.0, max(0.0, ((float) $amount / $budgetTotal) * 100));
    };
    $committedPercent = $percentOfBudget($budgetSummary['committed_total'] ?? 0);
    $actualPercent = $percentOfBudget($budgetSummary['actual_expense_total'] ?? 0);
    $remainingPercent = $remainingIsNegative ? 100.0 : $percentOfBudget($budgetSummary['remaining_total'] ?? 0);
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
            url('{{ asset('storage/BG-login-768.webp') }}');
        background-size: cover;
        background-position: center 48%;
        color: #fff;
        border-radius: 8px;
        padding: 1.35rem;
        box-shadow: 0 16px 36px rgba(26,39,68,0.18);
        overflow: hidden;
    }

    @media (min-width: 769px) {
        .fh-hero {
            background:
                linear-gradient(135deg, rgba(26,39,68,0.97), rgba(36,50,87,0.93)),
                url('{{ asset('storage/BG-login-1280.webp') }}');
            background-size: cover;
            background-position: center 48%;
        }
    }

    @media (min-width: 1441px) {
        .fh-hero {
            background:
                linear-gradient(135deg, rgba(26,39,68,0.97), rgba(36,50,87,0.93)),
                url('{{ asset('storage/BG-login-1920.webp') }}');
            background-size: cover;
            background-position: center 48%;
        }
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

    .fh-finance-board {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(17, 27, 51, .08);
        overflow: hidden;
    }

    .fh-board-head {
        align-items: flex-start;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.1rem;
    }

    .fh-board-title {
        display: grid;
        gap: .18rem;
        min-width: 0;
    }

    .fh-board-title span {
        color: #9a6b05;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .fh-board-title h2 {
        color: var(--fns-navy);
        font-size: 1.08rem;
        font-weight: 900;
        margin: 0;
    }

    .fh-board-title p {
        color: var(--fns-gray-600);
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.45;
        margin: 0;
    }

    .fh-board-year {
        background: #f8fafc;
        border: 1px solid #dbe3ef;
        border-radius: 999px;
        color: var(--fns-navy);
        flex: 0 0 auto;
        font-size: .78rem;
        font-weight: 900;
        padding: .46rem .72rem;
    }

    .fh-board-body {
        display: grid;
        grid-template-columns: minmax(260px, .85fr) minmax(0, 1.5fr);
    }

    .fh-remaining-panel {
        background: linear-gradient(145deg, #111b33 0%, #203356 100%);
        color: #fff;
        display: grid;
        gap: .9rem;
        min-width: 0;
        padding: 1.15rem;
    }

    .fh-remaining-panel.is-negative {
        background: linear-gradient(145deg, #4a1111 0%, #7f1d1d 100%);
    }

    .fh-remaining-label {
        align-items: center;
        display: flex;
        gap: .7rem;
        justify-content: space-between;
    }

    .fh-remaining-label span {
        color: rgba(255,255,255,.74);
        font-size: .76rem;
        font-weight: 900;
    }

    .fh-remaining-pill {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 999px;
        color: var(--fns-gold-light);
        font-size: .68rem;
        font-weight: 900;
        padding: .26rem .48rem;
    }

    .fh-remaining-value {
        font-family: 'Cinzel', serif;
        font-size: clamp(1.7rem, 3.2vw, 2.45rem);
        font-weight: 900;
        line-height: 1;
        overflow-wrap: anywhere;
    }

    .fh-remaining-note {
        color: rgba(255,255,255,.7);
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.45;
        margin: 0;
    }

    .fh-remaining-meter {
        background: rgba(255,255,255,.15);
        border-radius: 999px;
        height: .55rem;
        overflow: hidden;
    }

    .fh-remaining-meter span {
        background: {{ $remainingIsNegative ? '#fecaca' : '#86efac' }};
        border-radius: inherit;
        display: block;
        height: 100%;
        width: var(--remaining-width, 0%);
    }

    .fh-remaining-foot {
        align-items: center;
        color: rgba(255,255,255,.72);
        display: flex;
        font-size: .72rem;
        font-weight: 800;
        gap: .75rem;
        justify-content: space-between;
    }

    .fh-ledger {
        display: grid;
        gap: .75rem;
        padding: 1rem;
    }

    .fh-ledger-row {
        align-items: center;
        display: grid;
        gap: .8rem;
        grid-template-columns: minmax(145px, .75fr) minmax(0, 1fr) minmax(130px, auto);
        min-width: 0;
    }

    .fh-ledger-name {
        align-items: center;
        display: flex;
        gap: .55rem;
        min-width: 0;
    }

    .fh-ledger-step {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: .68rem;
        font-weight: 900;
        height: 1.55rem;
        justify-content: center;
        min-width: 1.55rem;
    }

    .fh-ledger-copy {
        min-width: 0;
    }

    .fh-ledger-copy strong {
        color: var(--fns-navy);
        display: block;
        font-size: .8rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .fh-ledger-copy span {
        color: #64748b;
        display: block;
        font-size: .7rem;
        font-weight: 700;
        line-height: 1.35;
        margin-top: .16rem;
    }

    .fh-ledger-meter {
        background: #eef2f7;
        border-radius: 999px;
        height: .5rem;
        overflow: hidden;
    }

    .fh-ledger-meter span {
        background: var(--ledger-color, var(--fns-gold));
        border-radius: inherit;
        display: block;
        height: 100%;
        width: var(--ledger-width, 0%);
    }

    .fh-ledger-amount {
        text-align: right;
    }

    .fh-ledger-amount strong {
        color: var(--fns-navy);
        display: block;
        font-family: 'Cinzel', serif;
        font-size: 1.02rem;
        font-weight: 900;
        line-height: 1.05;
        overflow-wrap: anywhere;
    }

    .fh-ledger-amount span {
        color: #64748b;
        display: block;
        font-size: .68rem;
        font-weight: 800;
        margin-top: .18rem;
    }

    .fh-ledger-row.is-budget {
        --ledger-color: var(--fns-gold);
    }

    .fh-ledger-row.is-commitment {
        --ledger-color: #d97706;
    }

    .fh-ledger-row.is-actual {
        --ledger-color: #b91c1c;
    }

    .fh-ledger-row.is-budget .fh-ledger-step {
        background: #fff7d6;
        color: #7a5b0b;
    }

    .fh-ledger-row.is-commitment .fh-ledger-step {
        background: #ffedd5;
        color: #9a3412;
    }

    .fh-ledger-row.is-actual .fh-ledger-step {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 980px) {
        .fh-hero {
            grid-template-columns: 1fr;
        }

        .fh-user-panel {
            min-width: 0;
        }

        .fh-board-body {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .fh-hero {
            padding: 1rem;
        }

        .fh-board-head {
            align-items: flex-start;
            flex-direction: column;
            gap: .25rem;
        }

        .fh-ledger-row {
            grid-template-columns: 1fr;
            gap: .45rem;
        }

        .fh-ledger-amount {
            text-align: left;
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
                ແຜນປີປັດຈຸບັນ
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

    <section class="fh-finance-board" aria-label="Finance summary">
        <div class="fh-board-head">
            <div class="fh-board-title">
                <span>Financial Control</span>
                <h2>ສະຫຼຸບຍອດການເງິນ</h2>
                <p>ສະແດງພາບລວມງົບ, ຍອດຜູກພັນ, ຍອດໃຊ້ຈ່າຍຈິງ ແລະຍອດທີ່ຍັງເຫຼືອ.</p>
            </div>
            <span class="fh-board-year">{{ $summaryYear ? 'ແຜນປີ '.$summaryYear : 'ຍັງບໍ່ມີແຜນປີ' }}</span>
        </div>

        <div class="fh-board-body">
            <article class="fh-remaining-panel {{ $remainingIsNegative ? 'is-negative' : '' }}" style="--remaining-width: {{ $remainingPercent }}%;">
                <div class="fh-remaining-label">
                    <span>ຍອດຄົງເຫຼືອ</span>
                    <b class="fh-remaining-pill">{{ $remainingIsNegative ? 'ເກີນງົບ' : 'ພ້ອມໃຊ້' }}</b>
                </div>
                <strong class="fh-remaining-value">{{ number_format((float) $budgetSummary['remaining_total'], 0, '.', '.') }}</strong>
                <p class="fh-remaining-note">ຫຼັງຫັກຍອດໃຊ້ຈ່າຍຈິງ ແລະຍອດຜູກພັນອອກຈາກແຜນງົບປະມານ.</p>
                <div class="fh-remaining-meter"><span></span></div>
                <div class="fh-remaining-foot">
                    <span>{{ $remainingIsNegative ? 'ຄວນກວດງົບ' : 'ຄົງເຫຼືອຈາກງົບ' }}</span>
                    <span>{{ number_format($remainingPercent, 2) }}%</span>
                </div>
            </article>

            <div class="fh-ledger">
                <div class="fh-ledger-row is-budget" style="--ledger-width: 100%;">
                    <div class="fh-ledger-name">
                        <span class="fh-ledger-step">01</span>
                        <div class="fh-ledger-copy">
                            <strong>ຍອດງົບປະມານ</strong>
                            <span>ຍອດລວມຈາກແຜນປີ</span>
                        </div>
                    </div>
                    <div class="fh-ledger-meter"><span></span></div>
                    <div class="fh-ledger-amount">
                        <strong>{{ number_format((float) $budgetSummary['budget_total'], 0, '.', '.') }}</strong>
                        <span>100%</span>
                    </div>
                </div>

                <div class="fh-ledger-row is-commitment" style="--ledger-width: {{ $committedPercent }}%;">
                    <div class="fh-ledger-name">
                        <span class="fh-ledger-step">02</span>
                        <div class="fh-ledger-copy">
                            <strong>ຍອດຜູກພັນ</strong>
                            <span>Advance requests ທີ່ບໍ່ຖືກປະຕິເສດ</span>
                        </div>
                    </div>
                    <div class="fh-ledger-meter"><span></span></div>
                    <div class="fh-ledger-amount">
                        <strong>{{ number_format((float) $budgetSummary['committed_total'], 0, '.', '.') }}</strong>
                        <span>{{ number_format($committedPercent, 2) }}%</span>
                    </div>
                </div>

                <div class="fh-ledger-row is-actual" style="--ledger-width: {{ $actualPercent }}%;">
                    <div class="fh-ledger-name">
                        <span class="fh-ledger-step">03</span>
                        <div class="fh-ledger-copy">
                            <strong>ຍອດໃຊ້ຈ່າຍຈິງ</strong>
                            <span>Transactions ປະເພດ expense</span>
                        </div>
                    </div>
                    <div class="fh-ledger-meter"><span></span></div>
                    <div class="fh-ledger-amount">
                        <strong>{{ number_format((float) $budgetSummary['actual_expense_total'], 0, '.', '.') }}</strong>
                        <span>{{ number_format($actualPercent, 2) }}%</span>
                    </div>
                </div>
            </div>
        </div>

    </section>

</div>
</x-app-layout>
