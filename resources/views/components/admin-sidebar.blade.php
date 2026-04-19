<aside id="admin-sidebar" class="w-64 bg-white border-r min-h-screen md:sticky md:top-0">
    <nav class="p-4 space-y-4">
        <!-- Dashboard Section -->
        <div class="space-y-1">
            @if (auth()->user()->role?->role_name === 'admin')
                <a href="{{ route('admin.home') }}"
                    class="{{ request()->routeIs('admin.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @elseif (auth()->user()->role?->role_name === 'head_of_finance')
                <a href="{{ route('head_of_finance.home') }}"
                    class="{{ request()->routeIs('head_of_finance.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @elseif (auth()->user()->role?->role_name === 'head_of_department')
                <a href="{{ route('head_of_department.home') }}"
                    class="{{ request()->routeIs('head_of_department.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @elseif (auth()->user()->role?->role_name === 'deputy_head_of_faculty')
                <a href="{{ route('deputy_head_of_faculty.home') }}"
                    class="{{ request()->routeIs('deputy_head_of_faculty.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @elseif (auth()->user()->role?->role_name === 'head_of_faculty')
                <a href="{{ route('head_of_faculty.home') }}"
                    class="{{ request()->routeIs('head_of_faculty.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @else
                <a href="{{ route('accountant.home') }}"
                    class="{{ request()->routeIs('accountant.home') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
            @endif
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                </svg>
                Dashboard
            </a>
        </div>

        <!-- Divider Line -->
        <hr class="border-gray-200">

        <!-- Section Header: MANAGEMENT -->
        <div>
            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                ການຈັດການ
            </h3>

            <div x-data="{ open: false }" class="space-y-1">

                @can ("admin")
                <a href="{{ route('admin.users.index') }}"
                    class="{{ request()->routeIs('admin.users.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    ຜູ້ໃຊ້
                </a>

                <a href="{{ route('admin.roles.index') }}"
                    class="{{ request()->routeIs('admin.roles.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    ບົດບາດ
                </a>

                <a href="{{ route('admin.departments.index') }}"
                    class="{{ request()->routeIs('admin.departments.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                    ພະແນກ
                </a>

                <a href="{{ route('admin.chart-of-accounts.index') }}"
                    class="{{ request()->routeIs('admin.chart-of-accounts.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    ແຜນບັນຊີ
                </a>
                @endcan

                @can ("head_of_finance")
                <div class="space-y-1 mt-4">
                    <a href="{{ route('head_of_finance.annual-budget.index') }}"
                        class="{{ request()->routeIs('head_of_finance.annual-budget.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        ແຜນງົບປະມານປະຈຳປີ
                    </a>
                    
                    <a href="{{ route('head_of_finance.budget-installment.index') }}"
                        class="{{ request()->routeIs('head_of_finance.budget-installment.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-purple-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        ແຜນງວດງົບປະມານ
                    </a>
                </div>
                @endcan
                
                @if (auth()->user()->reviewerAssignments()->exists() && !in_array(auth()->user()->role?->role_name, ['deputy_head_of_faculty', 'head_of_faculty']))
                <div class="space-y-1 mt-4">
                    <a href="{{ route('head_of_department.annual-budget.index') }}"
                        class="{{ request()->routeIs('head_of_department.annual-budget.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        ກວດສອບແຜນງົບປະມານ
                    </a>
                </div>
                @endif

                @can ("deputy_head_of_faculty")
                @if (auth()->user()->reviewerAssignments()->whereHas('budgetPlan', function ($q) { $q->where('status', 'PENDING_REVIEW'); })->exists())
                <div class="space-y-1 mt-4">
                    <a href="{{ route('deputy_head_of_faculty.annual-budget.index') }}"
                        class="{{ request()->routeIs('deputy_head_of_faculty.annual-budget.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        ກວດສອບແຜນງົບປະມານ
                    </a>
                </div>
                @endif
                @endcan

                @can ("head_of_faculty")
                <div class="space-y-1 mt-4">
                    <a href="{{ route('head_of_faculty.annual-budget.index') }}"
                        class="{{ request()->routeIs('head_of_faculty.annual-budget.*') ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-gray-900' : 'flex items-center px-3 py-2 rounded-md text-sm text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        ອະນຸມັດແຜນງົບປະມານ
                    </a>
                </div>
                @endcan
    </nav>
</aside>
