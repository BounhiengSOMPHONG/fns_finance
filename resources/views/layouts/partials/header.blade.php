<header class="h-16 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-6">
    <!-- Page Title -->
    <div>
        <h1 class="text-xl font-semibold text-gray-800">
            @yield('page-title', 'ແດຊບອດ')
        </h1>
    </div>

    <!-- User Menu -->
    <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">
            {{ auth()->user()->full_name ?? 'ຜູ້ໃຊ້' }}
        </span>
        <div class="relative">
            <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                    {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
                </div>
            </button>
        </div>
    </div>
</header>
