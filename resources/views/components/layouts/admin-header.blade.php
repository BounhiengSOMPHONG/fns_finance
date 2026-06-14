@php
    $isAdmin         = auth()->user()?->can('admin');
    $isHeadOfFinance = auth()->user()?->can('head_of_finance');

    $settingsActive = request()->routeIs('head_of_finance.settings.degree-programs.*')
        || request()->routeIs('head_of_finance.settings.course-credits.*')
        || request()->routeIs('head_of_finance.settings.credit-unit-price.*')
        || request()->routeIs('head_of_finance.settings.nuol-pct.*')
        || request()->routeIs('head_of_finance.settings.registration-fee.*')
        || request()->routeIs('head_of_finance.settings.expense-patterns.*')
        || request()->routeIs('head_of_finance.settings.expense-pattern-fields.*')
        || request()->routeIs('head_of_finance.settings.expense-default-rows.*')
        || request()->routeIs('head_of_finance.settings.expense-structure.*');

    $systemActive = request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.departments.*')
        || request()->routeIs('admin.chart-of-accounts.*');

    $managePlanActive = request()->routeIs('head_of_finance.manage-plan.*')
        || request()->routeIs('head_of_finance.academic-income.*')
        || request()->routeIs('head_of_finance.expense.*')
        || request()->routeIs('head_of_finance.salary.*');
@endphp

<header class="fns-topnav" x-data="{ mobileOpen: false }">
    <div class="fns-topnav-inner">

        {{-- ===== Brand ===== --}}
        <a href="{{ $isHeadOfFinance ? route('head_of_finance.home') : '/' }}" class="fns-topnav-brand">
            <img src="{{ asset('storage/logo fns.jpg') }}" alt="FNS">
            <span class="fns-topnav-brand-text">
                <strong>FNS</strong><span>Finance</span>
            </span>
        </a>

        {{-- ===== Burger (mobile) ===== --}}
        <button type="button" class="fns-topnav-burger" @click="mobileOpen = !mobileOpen" aria-label="ເມນູ">
            <svg x-show="!mobileOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M6 6l12 12M18 6l-12 12"/></svg>
        </button>

        {{-- ===== Nav links ===== --}}
        <nav class="fns-topnav-links" :class="{ 'is-open': mobileOpen }" @click.away="mobileOpen = false">

            @if($isHeadOfFinance)
                <a href="{{ route('head_of_finance.home') }}"
                   class="fns-topnav-item {{ request()->routeIs('head_of_finance.home') ? 'active' : '' }}">
                    <x-icons.home />
                    ໜ້າຫຼັກ
                </a>

                {{-- Manage plan dropdown --}}
                <div class="fns-topnav-dropdown" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" class="fns-topnav-item {{ $managePlanActive ? 'active' : '' }}"
                            @click="open = !open" :aria-expanded="open">
                        <x-icons.book-open />
                        Manage plan
                        <svg class="fns-topnav-chev" :class="{ 'is-open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="fns-topnav-menu" x-show="open" x-transition.opacity.duration.150ms style="display:none;">
                        <a href="{{ route('head_of_finance.manage-plan.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.manage-plan.*') ? 'active' : '' }}">
                            <x-icons.book-open /> Manage plan
                        </a>
                    </div>
                </div>

                {{-- Settings dropdown --}}
                <div class="fns-topnav-dropdown" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" class="fns-topnav-item {{ $settingsActive ? 'active' : '' }}"
                            @click="open = !open" :aria-expanded="open">
                        <x-icons.settings />
                        ຕັ້ງຄ່າ
                        <svg class="fns-topnav-chev" :class="{ 'is-open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="fns-topnav-menu" x-show="open" x-transition.opacity.duration.150ms style="display:none;">
                        <a href="{{ route('head_of_finance.settings.degree-programs.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.settings.degree-programs.*') ? 'active' : '' }}">
                            <x-icons.building-office /> ສາຂາວິຊາ
                        </a>
                        <a href="{{ route('head_of_finance.settings.course-credits.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.settings.course-credits.*') || request()->routeIs('head_of_finance.settings.credit-unit-price.*') || request()->routeIs('head_of_finance.settings.nuol-pct.*') ? 'active' : '' }}">
                            <x-icons.book-open /> ລາຄາ & ໜ່ວຍກິດ & ມຊ%
                        </a>
                        <a href="{{ route('head_of_finance.settings.expense-patterns.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.settings.expense-patterns.*') || request()->routeIs('head_of_finance.settings.expense-pattern-fields.*') ? 'active' : '' }}">
                            <x-icons.settings /> Expense patterns
                        </a>
                        <a href="{{ route('head_of_finance.settings.expense-structure.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.settings.expense-structure.*') || request()->routeIs('head_of_finance.settings.expense-default-rows.*') ? 'active' : '' }}">
                            <x-icons.book-open /> Expense structure & accounts
                        </a>
                        <a href="{{ route('head_of_finance.settings.registration-fee.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('head_of_finance.settings.registration-fee.*') ? 'active' : '' }}">
                            <x-icons.shield-check /> ຄ່າລົງທະບຽນ
                        </a>
                    </div>
                </div>
            @endif

            @if($isAdmin)
                {{-- Admin system dropdown --}}
                <div class="fns-topnav-dropdown" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" class="fns-topnav-item {{ $systemActive ? 'active' : '' }}"
                            @click="open = !open" :aria-expanded="open">
                        <x-icons.settings />
                        ການຈັດການລະບົບ
                        <svg class="fns-topnav-chev" :class="{ 'is-open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="fns-topnav-menu" x-show="open" x-transition.opacity.duration.150ms style="display:none;">
                        <a href="{{ route('admin.users.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <x-icons.users /> ຜູ້ໃຊ້
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                            <x-icons.shield-check /> ບົດບາດ
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                            <x-icons.building-office /> ພະແນກ
                        </a>
                        <a href="{{ route('admin.chart-of-accounts.index') }}"
                           class="fns-topnav-menu-item {{ request()->routeIs('admin.chart-of-accounts.*') ? 'active' : '' }}">
                            <x-icons.book-open /> ແຜນບັນຊີ
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        {{-- ===== Right side: date + user + logout ===== --}}
        <div class="fns-topnav-right" :class="{ 'is-open': mobileOpen }">
            <span class="fns-topnav-pill fns-topnav-pill-date">
                <x-icons.calendar style="width:13px;height:13px;opacity:0.7;" />
                {{ now()->locale('lo')->isoFormat('D MMM YYYY') }}
            </span>

            @auth
                <span class="fns-topnav-pill">
                    <x-icons.user style="width:13px;height:13px;opacity:0.7;" />
                    {{ Auth::user()->full_name ?? Auth::user()->username ?? 'Admin' }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="logout-form" style="margin:0;">
                    @csrf
                    <button type="submit" class="fns-btn-logout">
                        <x-icons.logout style="width:13px;height:13px;" />
                        ອອກ
                    </button>
                </form>
            @endauth
        </div>
    </div>
</header>
