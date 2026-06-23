@extends('layouts.admin')

@section('title', 'Admin')
@section('page-title', 'ຈັດການລະບົບ')

@section('page-title-actions')
    <a href="{{ route('admin.users.create') }}" class="fns-btn fns-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມຜູ້ໃຊ້
    </a>
@endsection

@section('content')
@php
    $displayName = auth()->user()->full_name ?? auth()->user()->username ?? 'Admin';
    $inactiveUserCount = max($userCount - $activeUserCount, 0);

    $sections = [
        [
            'title' => 'ຜູ້ໃຊ້',
            'value' => number_format($userCount),
            'meta' => number_format($activeUserCount) . ' ໃຊ້ງານ · ' . number_format($inactiveUserCount) . ' ປິດໃຊ້',
            'route' => route('admin.users.index'),
            'create' => route('admin.users.create'),
            'create_label' => 'ເພີ່ມຜູ້ໃຊ້',
            'icon' => 'users',
            'tone' => 'navy',
        ],
        [
            'title' => 'ບົດບາດ',
            'value' => number_format($roleCount),
            'meta' => 'ກຳນົດສິດເຂົ້າໃຊ້ຕາມໜ້າທີ່',
            'route' => route('admin.roles.index'),
            'create' => route('admin.roles.create'),
            'create_label' => 'ເພີ່ມບົດບາດ',
            'icon' => 'shield',
            'tone' => 'green',
        ],
        [
            'title' => 'ພະແນກ',
            'value' => number_format($departmentCount),
            'meta' => 'ໃຊ້ຜູກຜູ້ໃຊ້ກັບໜ່ວຍງານ',
            'route' => route('admin.departments.index'),
            'create' => route('admin.departments.create'),
            'create_label' => 'ເພີ່ມພະແນກ',
            'icon' => 'office',
            'tone' => 'gold',
        ],
        [
            'title' => 'ຜັງບັນຊີ',
            'value' => number_format($accountCount),
            'meta' => 'ລະຫັດບັນຊີສຳລັບແຜນງົບປະມານ',
            'route' => route('admin.chart-of-accounts.index'),
            'create' => route('admin.chart-of-accounts.create'),
            'create_label' => 'ເພີ່ມບັນຊີ',
            'icon' => 'ledger',
            'tone' => 'wine',
        ],
    ];

    $systemRows = [
        ['label' => 'ບັນຊີພ້ອມໃຊ້', 'value' => number_format($activeUserCount), 'route' => route('admin.users.index', ['is_active' => 1])],
        ['label' => 'ສິດໃນລະບົບ', 'value' => number_format($roleCount), 'route' => route('admin.roles.index')],
        ['label' => 'ໜ່ວຍງານ', 'value' => number_format($departmentCount), 'route' => route('admin.departments.index')],
        ['label' => 'ບັນຊີງົບ', 'value' => number_format($accountCount), 'route' => route('admin.chart-of-accounts.index')],
    ];
@endphp

<style>
    .admin-home {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .admin-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
        gap: 1rem;
        align-items: stretch;
    }

    .admin-hero-main,
    .admin-panel,
    .admin-work-card,
    .admin-status-row {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(26,39,68,.06);
    }

    .admin-hero-main {
        padding: 1.2rem;
        border-left: 4px solid var(--fns-gold);
    }

    .admin-kicker {
        color: var(--fns-gray-600);
        font-size: .72rem;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .admin-title {
        color: var(--fns-navy);
        font-size: 1.35rem;
        font-weight: 850;
        line-height: 1.25;
        margin: 0;
    }

    .admin-copy {
        color: var(--fns-gray-600);
        font-size: .84rem;
        line-height: 1.65;
        margin-top: .45rem;
        max-width: 48rem;
    }

    .admin-hero-actions,
    .admin-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .9rem;
    }

    .admin-hero-actions .fns-btn svg,
    .admin-card-actions .fns-btn svg,
    .admin-status-link svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
    }

    .admin-panel {
        padding: 1rem;
    }

    .admin-panel-title {
        color: var(--fns-navy);
        font-size: .9rem;
        font-weight: 850;
        margin: 0 0 .7rem;
    }

    .admin-status-list {
        display: grid;
        gap: .5rem;
    }

    .admin-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .68rem .75rem;
        text-decoration: none;
        color: inherit;
        box-shadow: none;
        transition: border-color .16s ease, background .16s ease;
    }

    .admin-status-row:hover {
        border-color: rgba(201,153,26,.45);
        background: rgba(201,153,26,.05);
    }

    .admin-status-row span {
        color: var(--fns-gray-600);
        font-size: .76rem;
        font-weight: 750;
    }

    .admin-status-row strong {
        color: var(--fns-navy);
        font-family: 'Cinzel', serif;
        font-size: 1.12rem;
        line-height: 1;
    }

    .admin-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: .2rem;
    }

    .admin-section-head h2 {
        color: var(--fns-navy);
        font-size: .98rem;
        font-weight: 850;
        margin: 0;
    }

    .admin-section-head p {
        color: var(--fns-gray-600);
        font-size: .78rem;
        margin: .15rem 0 0;
    }

    .admin-work-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }

    .admin-work-card {
        display: flex;
        flex-direction: column;
        min-height: 190px;
        padding: .95rem;
        color: inherit;
        text-decoration: none;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .admin-work-card:hover {
        transform: translateY(-1px);
        border-color: rgba(26,39,68,.28);
        box-shadow: 0 10px 26px rgba(26,39,68,.08);
    }

    .admin-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
    }

    .admin-icon {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid;
    }

    .admin-icon svg {
        width: 18px;
        height: 18px;
    }

    .admin-tone-navy .admin-icon { color: var(--fns-navy); background: rgba(26,39,68,.07); border-color: rgba(26,39,68,.16); }
    .admin-tone-green .admin-icon { color: var(--fns-green); background: rgba(26,74,46,.08); border-color: rgba(26,74,46,.18); }
    .admin-tone-gold .admin-icon { color: #8b6a12; background: rgba(201,153,26,.12); border-color: rgba(201,153,26,.24); }
    .admin-tone-wine .admin-icon { color: var(--fns-crimson); background: rgba(139,26,26,.07); border-color: rgba(139,26,26,.16); }

    .admin-card-value {
        color: var(--fns-navy);
        font-family: 'Cinzel', serif;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
    }

    .admin-work-card h3 {
        color: var(--fns-navy);
        font-size: .92rem;
        font-weight: 850;
        margin: .85rem 0 .25rem;
    }

    .admin-work-card p {
        color: var(--fns-gray-600);
        font-size: .76rem;
        line-height: 1.5;
        margin: 0;
    }

    .admin-card-actions {
        margin-top: auto;
        padding-top: .9rem;
    }

    .admin-status-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: var(--fns-navy);
        font-size: .76rem;
        font-weight: 800;
        text-decoration: none;
    }

    .admin-status-link:hover {
        color: var(--fns-gold);
    }

    .admin-queue {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, .45fr);
        gap: 1rem;
    }

    .admin-steps {
        display: grid;
        gap: .6rem;
    }

    .admin-step {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        gap: .75rem;
        align-items: center;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        padding: .78rem .9rem;
    }

    .admin-step-index {
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--fns-navy);
        color: #fff;
        font-size: .74rem;
        font-weight: 850;
    }

    .admin-step strong {
        display: block;
        color: var(--fns-navy);
        font-size: .84rem;
        margin-bottom: .12rem;
    }

    .admin-step span {
        color: var(--fns-gray-600);
        font-size: .74rem;
    }

    @media (max-width: 1180px) {
        .admin-work-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .admin-hero,
        .admin-queue {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .admin-hero-main,
        .admin-panel,
        .admin-work-card {
            padding: .85rem;
        }

        .admin-section-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .admin-work-grid {
            grid-template-columns: 1fr;
        }

        .admin-step {
            grid-template-columns: 30px minmax(0, 1fr);
        }

        .admin-step .fns-btn {
            grid-column: 2;
            width: fit-content;
        }
    }
</style>

<div class="admin-home">
    <section class="admin-hero" aria-label="Admin overview">
        <div class="admin-hero-main">
            <div class="admin-kicker">ADMIN WORKSPACE</div>
            <h2 class="admin-title">{{ $displayName }} · ພາບລວມການຄຸ້ມຄອງລະບົບ</h2>
            <p class="admin-copy">
                ຈັດການບັນຊີຜູ້ໃຊ້, ສິດການເຂົ້າໃຊ້, ໂຄງສ້າງພະແນກ ແລະຜັງບັນຊີຈາກໜ້າດຽວ.
            </p>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.users.create') }}" class="fns-btn fns-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    ເພີ່ມຜູ້ໃຊ້
                </a>
                <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary">
                    <x-icons.users class="w-4 h-4" />
                    ລາຍຊື່ຜູ້ໃຊ້
                </a>
                <a href="{{ route('admin.chart-of-accounts.index') }}" class="fns-btn fns-btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><path d="M8 5v14M16 5v14"/></svg>
                    ຜັງບັນຊີ
                </a>
            </div>
        </div>

        <aside class="admin-panel">
            <h2 class="admin-panel-title">ສະຖານະຂໍ້ມູນຫຼັກ</h2>
            <div class="admin-status-list">
                @foreach($systemRows as $row)
                    <a href="{{ $row['route'] }}" class="admin-status-row">
                        <span>{{ $row['label'] }}</span>
                        <strong>{{ $row['value'] }}</strong>
                    </a>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="admin-section-head" aria-label="Admin modules heading">
        <div>
            <h2>ໜ້າທີ່ຫຼັກ</h2>
            <p>ເລືອກໄປຈັດການ ຫຼືເພີ່ມຂໍ້ມູນໃໝ່ໄດ້ທັນທີ.</p>
        </div>
    </section>

    <section class="admin-work-grid" aria-label="Admin modules">
        @foreach($sections as $section)
            <article class="admin-work-card admin-tone-{{ $section['tone'] }}">
                <div class="admin-card-top">
                    <div class="admin-icon">
                        @if($section['icon'] === 'users')
                            <x-icons.users class="w-5 h-5" />
                        @elseif($section['icon'] === 'shield')
                            <x-icons.shield-check class="w-5 h-5" />
                        @elseif($section['icon'] === 'office')
                            <x-icons.building-office class="w-5 h-5" />
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><path d="M8 5v14M16 5v14"/></svg>
                        @endif
                    </div>
                    <div class="admin-card-value">{{ $section['value'] }}</div>
                </div>
                <h3>{{ $section['title'] }}</h3>
                <p>{{ $section['meta'] }}</p>

                <div class="admin-card-actions">
                    <a href="{{ $section['route'] }}" class="fns-btn fns-btn-secondary fns-btn-sm">
                        ເປີດລາຍການ
                    </a>
                    <a href="{{ $section['create'] }}" class="fns-btn fns-btn-primary fns-btn-sm">
                        {{ $section['create_label'] }}
                    </a>
                </div>
            </article>
        @endforeach
    </section>

    <section class="admin-queue" aria-label="Suggested admin workflow">
        <div>
            <div class="admin-section-head">
                <div>
                    <h2>ລຳດັບວຽກທີ່ໃຊ້ບ່ອຍ</h2>
                    <p>ຈັດຂໍ້ມູນພື້ນຖານໃຫ້ພ້ອມກ່ອນເລີ່ມວຽກງົບປະມານ.</p>
                </div>
            </div>

            <div class="admin-steps">
                <div class="admin-step">
                    <div class="admin-step-index">1</div>
                    <div>
                        <strong>ກວດສອບບົດບາດ ແລະສິດ</strong>
                        <span>ໃຫ້ບົດບາດກົງກັບໜ້າທີ່ຂອງຜູ້ໃຊ້.</span>
                    </div>
                    <a href="{{ route('admin.roles.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">ເປີດ</a>
                </div>

                <div class="admin-step">
                    <div class="admin-step-index">2</div>
                    <div>
                        <strong>ເພີ່ມ ຫຼືອັບເດດຜູ້ໃຊ້</strong>
                        <span>ກຳນົດບົດບາດ, ພະແນກ ແລະສະຖານະໃຊ້ງານ.</span>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">ເປີດ</a>
                </div>

                <div class="admin-step">
                    <div class="admin-step-index">3</div>
                    <div>
                        <strong>ກວດຜັງບັນຊີ</strong>
                        <span>ລະຫັດບັນຊີຕ້ອງພ້ອມກ່ອນນຳໄປໃຊ້ໃນແຜນ.</span>
                    </div>
                    <a href="{{ route('admin.chart-of-accounts.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">ເປີດ</a>
                </div>
            </div>
        </div>

        <aside class="admin-panel">
            <h2 class="admin-panel-title">ກວດໄວ</h2>
            <div class="admin-status-list">
                <a href="{{ route('admin.users.index', ['is_active' => 0]) }}" class="admin-status-row">
                    <span>ບັນຊີປິດໃຊ້</span>
                    <strong>{{ number_format($inactiveUserCount) }}</strong>
                </a>
                <a href="{{ route('admin.departments.index') }}" class="admin-status-row">
                    <span>ພະແນກທັງໝົດ</span>
                    <strong>{{ number_format($departmentCount) }}</strong>
                </a>
            </div>
        </aside>
    </section>
</div>
@endsection
