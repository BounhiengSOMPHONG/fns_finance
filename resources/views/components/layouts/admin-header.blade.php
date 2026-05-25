<style>
    .read-notification { opacity: 0.6 !important; }
    .read-notification .font-semibold { color: rgba(255,255,255,0.4) !important; }
    .read-notification .text-sm { color: rgba(255,255,255,0.3) !important; }
</style>

<header class="fns-header">
    {{-- Mobile toggle --}}
    <button id="sidebar-toggle"
        style="display:none; padding:0.4rem; border-radius:8px; background:rgba(255,255,255,0.08); border:none; cursor:pointer; color:rgba(255,255,255,0.75);"
        class="md-hidden-toggle">
        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Brand (text only — logo shown in sidebar) --}}
    <a href="#" class="fns-header-logo-link">
        <span>FNS Finance</span>
    </a>

    {{-- Divider --}}
    <div style="flex:1;"></div>

    {{-- Right side --}}
    <div style="display:flex; align-items:center; gap:0.75rem;">
        {{-- Date --}}
        <span class="fns-header-pill">
            <x-icons.calendar style="width:13px;height:13px;opacity:0.7;" />
            {{ now()->locale('lo')->isoFormat('D MMM YYYY') }}
        </span>

        @auth
            {{-- User name --}}
            <span class="fns-header-pill">
                <x-icons.user style="width:13px;height:13px;opacity:0.7;" />
                {{ Auth::user()->full_name ?? 'Admin' }}
            </span>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="logout-form" style="margin:0;">
                @csrf
                <button type="submit" class="fns-btn-logout">
                    <x-icons.logout style="width:13px;height:13px;" />
                    ອອກ
                </button>
            </form>
        @endauth
    </div>
</header>
