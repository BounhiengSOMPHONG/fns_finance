<style>
    .read-notification {
        opacity: 0.6 !important;
        background-color: #f9fafb;
    }

    .read-notification .font-semibold {
        color: #6b7280 !important;
    }

    .read-notification .text-sm {
        color: #9ca3af !important;
    }
</style>

<header class="bg-white border-b">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                <button id="sidebar-toggle" class="p-2 rounded hover:bg-gray-100 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="#" class="font-semibold text-gray-800">FNS</a>
            </div>
            <div class="hidden md:flex items-center space-x-4">
                @auth
                    {{-- Notification Bell --}}
                    <div x-data="notificationBell()" x-init="fetchNotifications()" class="relative inline-block">
                        <button @click="open = !open" class="p-2 rounded-full hover:bg-gray-200 relative focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount"
                                class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[18px]">
                            </span>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden" style="width: 350px;">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-800">ການແຈ້ງເຕືອນ</h3>
                                <button @click="markAllRead()"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                    x-show="unreadCount > 0">
                                    ອ່ານທັງໝົດ
                                </button>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center text-gray-400 text-sm">
                                        ບໍ່ມີການແຈ້ງເຕືອນ
                                    </div>
                                </template>
                                <template x-for="n in notifications" :key="n.id">
                                    <a :href="n.url" @click="markRead(n)"
                                        class="block px-4 py-3 hover:bg-gray-50 transition-colors"
                                        :class="{ 'bg-blue-50': !n.read, 'opacity-60': n.read }">
                                        <p class="text-sm text-gray-700 line-clamp-2" x-text="n.message"></p>
                                        <p class="text-xs text-gray-400 mt-1" x-text="n.time"></p>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                @endauth
                <span class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</span>
                <div class="flex items-center space-x-2">
                    <span class="text-sm">{{ Auth::user()->full_name ?? 'Admin' }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-md">Logout</button>
                    </form>
                </div>
            </div>
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
