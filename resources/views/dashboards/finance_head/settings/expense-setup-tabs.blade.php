@php
    $expenseSetupTabs = [
        [
            'step' => '01',
            'label' => 'DEF ແຕ່ລະປີ',
            'description' => 'ໝວດ, ກຸ່ມ, ລາຍການຕາມສົກປີ',
            'route' => route('head_of_finance.settings.expense-setup.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-setup.*') || request()->routeIs('head_of_finance.settings.expense-structure.*'),
        ],
        [
            'step' => '02',
            'label' => 'ລາຍການລິ້ງບັນຊີ',
            'description' => 'ຄົ້ນຫາ ແລະ ເຊື່ອມ Chart of Account',
            'route' => route('head_of_finance.settings.expense-default-rows.accounts.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-default-rows.*'),
        ],
        [
            'step' => '03',
            'label' => 'ສູດຄຳນວນ',
            'description' => 'ຊ່ອງກອກ ແລະ ຕົວຄູນ',
            'route' => route('head_of_finance.settings.expense-patterns.index'),
            'active' => request()->routeIs('head_of_finance.settings.expense-patterns.*'),
        ],
    ];
@endphp

<nav class="ex-stepnav" aria-label="Expense setup">
    @foreach($expenseSetupTabs as $tab)
        <a href="{{ $tab['route'] }}"
           class="ex-stepnav-item {{ $tab['active'] ? 'is-active' : '' }}"
           @if($tab['active']) aria-current="page" @endif>
            <span class="ex-stepnav-no">{{ $tab['step'] }}</span>
            <span class="ex-stepnav-copy">
                <span>{{ $tab['label'] }}</span>
                <small>{{ $tab['description'] }}</small>
            </span>
        </a>
    @endforeach
</nav>

@once
    <style>
        .ex-stepnav {
            display:flex;
            align-items:stretch;
            gap:.35rem;
            margin-bottom:1rem;
            overflow-x:auto;
            border:1px solid #dbe2ec;
            border-radius:8px;
            background:#fff;
            padding:.35rem;
            box-shadow:0 1px 8px rgba(15,23,42,.04);
        }
        .ex-stepnav-item {
            position:relative;
            display:flex;
            align-items:center;
            gap:.65rem;
            min-width:13rem;
            flex:1 0 0;
            border-radius:6px;
            padding:.55rem .75rem;
            color:#53627a;
            text-decoration:none;
            transition:background .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .ex-stepnav-item:hover {
            background:#f8fafc;
            color:#13213b;
        }
        .ex-stepnav-item.is-active {
            background:#13213b;
            color:#fff;
            box-shadow:0 8px 18px rgba(19,33,59,.16);
        }
        .ex-stepnav-no {
            display:grid;
            place-items:center;
            width:2.05rem;
            height:2.05rem;
            flex:0 0 auto;
            border-radius:999px;
            background:#eef2f7;
            color:#13213b;
            font-size:.72rem;
            font-weight:900;
            font-variant-numeric:tabular-nums;
        }
        .ex-stepnav-item.is-active .ex-stepnav-no {
            background:#f2c94c;
            color:#13213b;
        }
        .ex-stepnav-copy {
            display:grid;
            min-width:0;
            gap:.05rem;
        }
        .ex-stepnav-copy span {
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            color:inherit;
            font-size:.86rem;
            font-weight:900;
        }
        .ex-stepnav-copy small {
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            color:currentColor;
            font-size:.68rem;
            font-weight:700;
            opacity:.68;
        }
        @media (max-width:720px) {
            .ex-stepnav { margin-left:-.25rem; margin-right:-.25rem; }
            .ex-stepnav-item { min-width:11.5rem; flex-basis:auto; }
        }
    </style>
@endonce
