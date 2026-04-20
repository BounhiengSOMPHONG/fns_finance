<header class="topbar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <button id="sidebar-toggle" class="btn-icon" style="display: none;">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
    </div>

    <div class="topbar-right">
        @auth
            {{-- Notification Bell --}}
            <div x-data="notificationBell()" x-init="fetchNotifications()" class="relative inline-block">
                <button @click="open = !open" class="btn-icon" style="border: none; position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span x-show="unreadCount > 0" x-text="unreadCount"
                        style="position:absolute; top:-4px; right:-4px; min-width:16px; height:16px; font-size:10px; font-weight:600; background:#DC2626; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 4px;">
                    </span>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                    style="position:absolute; right:0; z-index:50; margin-top:8px; width:360px; max-width:90vw; overflow:hidden; border-radius:var(--radius-lg); border:1px solid var(--color-border); background:var(--color-bg-white); box-shadow:var(--shadow-dropdown);">
                    <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--color-border); padding:12px 16px;">
                        <span style="font-size:var(--font-size-md); font-weight:500; color:var(--color-text-primary);">ການແຈ້ງເຕືອນ</span>
                        <button @click="markAllRead()" style="font-size:var(--font-size-sm); color:var(--color-primary); background:none; border:none; cursor:pointer;"
                            x-show="unreadCount > 0">
                            ອ່ານທັງໝົດ
                        </button>
                    </div>
                    <div style="max-height:280px; overflow-y:auto;">
                        <template x-if="notifications.length === 0">
                            <div style="padding:32px 16px; text-align:center; font-size:var(--font-size-sm); color:var(--color-text-tertiary);">
                                ບໍ່ມີການແຈ້ງເຕືອນ
                            </div>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <a :href="n.url" @click="markRead(n)"
                                style="display:block; padding:12px 16px; border-bottom:1px solid var(--color-border); transition:background 0.12s; text-decoration:none;"
                                :style="{ background: !n.read ? 'var(--color-primary-light)' : 'transparent', opacity: n.read ? 0.6 : 1 }"
                                onmouseover="this.style.background=!this.style.background.includes('transparent') ? this.style.background : 'var(--color-bg-hover)'"
                                >
                                <p style="font-size:var(--font-size-base); color:var(--color-text-primary); margin:0;" x-text="n.message"></p>
                                <p style="font-size:var(--font-size-xs); color:var(--color-text-tertiary); margin-top:4px;" x-text="n.time"></p>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        @endauth

        <span class="topbar-date">{{ now()->format('d/m/Y') }}</span>

        {{-- User Info --}}
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="topbar-avatar">
                {{ strtoupper(substr(Auth::user()->full_name ?? 'A', 0, 2)) }}
            </div>
            <span class="topbar-username">{{ Auth::user()->full_name ?? 'Admin' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>
</header>

@push('scripts')
<script>
function notificationBell() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],
        async fetchNotifications() {
            try {
                const res = await fetch('{{ route("notifications.data") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.unreadCount = data.unread_count;
                this.notifications = data.notifications;
            } catch (e) {
                console.error('Failed to fetch notifications', e);
            }
        },
        async markRead(n) {
            if (n.read) return;
            try {
                await fetch('/notifications/' + n.id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                n.read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            } catch (e) {}
        },
        async markAllRead() {
            try {
                await fetch('{{ route("notifications.read-all") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                this.unreadCount = 0;
                this.notifications.forEach(n => n.read = true);
            } catch (e) {}
        }
    };
}
</script>
@endpush
