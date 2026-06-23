@php
    $expenseSetupTabs = [
        [
            'label' => 'DEF ແຕ່ລະປີ',
            'description' => 'ໝວດ, ກຸ່ມ, ລາຍການຕາມສົກປີ',
            'route' => route('head_of_finance.settings.expense-setup.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-setup.*') || request()->routeIs('head_of_finance.settings.expense-structure.*'),
        ],
        [
            'label' => 'ລາຍການລິ້ງບັນຊີ',
            'description' => 'ຄົ້ນຫາ ແລະ ເຊື່ອມ Chart of Account',
            'route' => route('head_of_finance.settings.expense-default-rows.accounts.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-default-rows.*'),
        ],
        [
            'label' => 'ສູດຄຳນວນ',
            'description' => 'ຊ່ອງກອກ ແລະ ຕົວຄູນ',
            'route' => route('head_of_finance.settings.expense-patterns.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-patterns.*'),
        ],
    ];
@endphp

<nav class="mb-5 grid gap-2 md:grid-cols-3" aria-label="Expense setup">
    @foreach($expenseSetupTabs as $tab)
        <a href="{{ $tab['route'] }}"
           class="rounded-lg border px-4 py-3 shadow-sm transition {{ $tab['active'] ? 'border-amber-400 bg-amber-50 text-slate-950' : 'border-slate-200 bg-white text-slate-700 hover:border-amber-300 hover:bg-amber-50/40' }}">
            <span class="block text-sm font-black">{{ $tab['label'] }}</span>
            <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $tab['description'] }}</span>
        </a>
    @endforeach
</nav>
