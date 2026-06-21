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

    .fh-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }

    .fh-stat,
    .fh-action {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 1px 5px rgba(26,39,68,.06);
    }

    .fh-stat {
        padding: .95rem 1rem;
        border-top: 3px solid var(--fns-gold);
    }

    .fh-stat span {
        display: block;
        color: var(--fns-gray-600);
        font-size: .74rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .fh-stat strong {
        display: block;
        color: var(--fns-navy);
        font-family: 'Cinzel', serif;
        font-size: 1.55rem;
        line-height: 1;
    }

    .fh-section-title {
        color: var(--fns-navy);
        font-size: .95rem;
        font-weight: 800;
        margin: .25rem 0 0;
    }

    .fh-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }

    .fh-action {
        display: flex;
        gap: .8rem;
        align-items: flex-start;
        padding: 1rem;
        color: inherit;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .fh-action:hover {
        transform: translateY(-1px);
        border-color: rgba(201,153,26,.48);
        box-shadow: 0 10px 28px rgba(26,39,68,.08);
    }

    .fh-action-icon {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #f8fafc;
        color: var(--fns-navy);
        border: 1px solid #e2e8f0;
    }

    .fh-action-icon svg {
        width: 17px;
        height: 17px;
    }

    .fh-action strong {
        display: block;
        color: var(--fns-navy);
        font-size: .88rem;
        line-height: 1.25;
    }

    .fh-action-body {
        display: block;
        min-width: 0;
    }

    .fh-action-body span {
        display: block;
        color: var(--fns-gray-600);
        font-size: .76rem;
        line-height: 1.45;
        margin-top: .2rem;
    }

    @media (max-width: 980px) {
        .fh-hero {
            grid-template-columns: 1fr;
        }

        .fh-user-panel {
            min-width: 0;
        }

        .fh-grid,
        .fh-actions-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .fh-hero {
            padding: 1rem;
        }

        .fh-grid,
        .fh-actions-grid {
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

    <section class="fh-grid" aria-label="Plan summary">
        <div class="fh-stat"><span>ແຜນທັງໝົດ</span><strong>{{ number_format($planStats['total']) }}</strong></div>
        <div class="fh-stat"><span>ກຳລັງຈັດເຮັດ</span><strong>{{ number_format($planStats['draft']) }}</strong></div>
        <div class="fh-stat"><span>ລໍຖ້າກວດ</span><strong>{{ number_format($planStats['pending_review']) }}</strong></div>
        <div class="fh-stat"><span>ບັນທຶກແລ້ວ</span><strong>{{ number_format($planStats['saved']) }}</strong></div>
    </section>

    <h2 class="fh-section-title">ເມນູທີ່ໃຊ້ເລື້ອຍ</h2>
    <section class="fh-actions-grid" aria-label="Quick actions">
        <a href="{{ route('head_of_finance.manage-plan.index') }}" class="fh-action">
            <span class="fh-action-icon"><x-icons.book-open /></span>
            <span class="fh-action-body">
                <strong>ຈັດການແຜນປະຈຳປີ</strong>
                <span>ເພີ່ມປີ, ປະເມີນລາຍຮັບ, ລາຍຈ່າຍ ແລະເງິນເດືອນ</span>
            </span>
        </a>
        <a href="{{ route('head_of_finance.settings.degree-programs.index') }}" class="fh-action">
            <span class="fh-action-icon"><x-icons.building-office /></span>
            <span class="fh-action-body">
                <strong>ຂໍ້ມູນສາຂາວິຊາ</strong>
                <span>ກຳນົດໂຄງສ້າງຫຼັກສູດທີ່ໃຊ້ປະເມີນລາຍຮັບ</span>
            </span>
        </a>
        <a href="{{ route('head_of_finance.settings.expense-setup.index') }}" class="fh-action">
            <span class="fh-action-icon"><x-icons.settings /></span>
            <span class="fh-action-body">
                <strong>ໂຄງສ້າງລາຍຈ່າຍ</strong>
                <span>ຈັດກຸ່ມລາຍຈ່າຍ ແລະກຳນົດລາຍການເລີ່ມຕົ້ນ</span>
            </span>
        </a>
    </section>
</div>
</x-app-layout>
