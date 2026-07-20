@extends('layouts.admin')

@section('title', 'ລາຍການ & ບັນຊີ')
@section('page-title', 'ລາຍການ & ບັນຊີ')

@section('content')
@php
    $accountOptionsById = $accountOptions->keyBy('id');
    $groupedRows = $rows->groupBy('subsection_code');
@endphp

<div class="space-y-5">
    @include('dashboards.finance_head.settings.expense-setup-tabs')

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">ລາຍການລິ້ງບັນຊີ</h2>
                <p class="mt-1 text-sm text-slate-500">
                    ຄົ້ນຫາລາຍການລາຍຈ່າຍ ແລະ ເລືອກ Chart of Account ໃຫ້ຖືກ.
                    @if($planningYear)
                        <span class="font-semibold text-slate-700">ສົກປີ {{ $planningYear->year }}</span>
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}" class="flex min-w-72 flex-1 justify-end gap-2">
                <select name="planning_year_id" class="fns-input max-w-56" onchange="this.form.submit()">
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" @selected($planningYear?->id === $year->id)>
                            {{ $year->year }} - {{ $year->name }}
                        </option>
                    @endforeach
                </select>
                <input name="q" value="{{ $query }}" class="fns-input max-w-md" placeholder="ຄົ້ນຫາລາຍການ, ກຸ່ມ ຫຼື ລະຫັດບັນຊີ...">
                <button type="submit" class="fns-btn fns-btn-primary">ຄົ້ນຫາ</button>
                @if($query !== '')
                    <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index', $planningYear ? ['planning_year_id' => $planningYear->id] : []) }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
                @endif
            </form>
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button type="button" class="fns-btn fns-btn-secondary" id="account-link-show-all">ສະແດງທັງໝົດ</button>
            <button type="button" class="fns-btn fns-btn-secondary" id="account-link-show-unlinked">ສະແດງທີ່ຍັງບໍ່ເຊື່ອມ</button>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">ກຸ່ມລາຍການ</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $rows->count() }} ລາຍການ ໃນ {{ $groupedRows->count() }} ກຸ່ມຍ່ອຍ.
                    </p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">ເລືອກແລ້ວບັນທຶກອັດຕະໂນມັດ</span>
            </div>
        </div>

        <div class="space-y-3 bg-slate-50 p-4">
            @forelse($groupedRows as $code => $groupRows)
                @php
                    $subsection = $subsectionLabels->get($code);
                    $linkedCount = $groupRows->whereNotNull('chart_of_account_id')->count();
                    $hasMissingLinks = $linkedCount < $groupRows->count();
                @endphp
                <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/80 ring-1 ring-white transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-xl hover:shadow-slate-300/70"
                         data-account-group>
                    <summary class="flex cursor-pointer list-none items-center gap-4 bg-white px-5 py-4 hover:bg-amber-50/45">
                        <span class="grid h-12 w-16 shrink-0 place-items-center rounded-lg bg-slate-900 text-sm font-black text-white shadow-md shadow-slate-300">
                            {{ $code }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-slate-950">
                                {{ $subsection?->name ?? 'ບໍ່ພົບກຸ່ມຍ່ອຍ' }}
                            </span>
                            <span class="mt-1 block truncate text-xs text-slate-500">
                                {{ trim(($subsection?->section?->code ?? '') . ' ' . ($subsection?->section?->name ?? '')) ?: 'ລາຍການມາດຕະຖານ' }}
                            </span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            {{ $groupRows->count() }} ລາຍການ
                        </span>
                        <span class="rounded-full {{ $hasMissingLinks ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-xs font-bold">
                            {{ $linkedCount }}/{{ $groupRows->count() }} ເຊື່ອມແລ້ວ
                        </span>
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-sm font-black text-slate-500 transition group-open:rotate-180 group-hover:bg-amber-100 group-hover:text-amber-700">v</span>
                    </summary>

                    <div class="border-t border-slate-200 bg-white p-4">
                        <div class="grid gap-3">
                            @foreach($groupRows as $row)
                                @php
                                    $selectedAccount = $accountOptionsById->get($row->chart_of_account_id);
                                    $selectedLabel = $selectedAccount['label'] ?? '';
                                    $selectedReference = $selectedAccount['code'] ?? null;
                                @endphp
                                <div class="js-account-row rounded-lg border border-slate-200 p-4"
                                     data-row="{{ strtolower($row->item_name.' '.$code.' '.($subsection?->name ?? '').' '.$selectedLabel.' '.($selectedReference ?? '')) }}"
                                     data-linked="{{ $row->chart_of_account_id ? 'true' : 'false' }}">
                                    <div class="grid gap-3 xl:grid-cols-[minmax(18rem,1fr)_minmax(34rem,2fr)_8rem] xl:items-start">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-950">{{ $row->item_name }}</div>
                                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                                <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-700">
                                                    ບັນຊີ: <span class="js-reference">{{ $selectedReference ?: '-' }}</span>
                                                </span>
                                                <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-700">
                                                    ລຳດັບ: {{ $row->sort_order }}
                                                </span>
                                            </div>
                                        </div>

                                        <form method="POST"
                                              action="{{ route('head_of_finance.settings.expense-default-rows.account.update', $row) }}"
                                              class="js-account-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <input class="fns-input js-account-search !py-3"
                                                   value="{{ $selectedLabel }}"
                                                   title="{{ $selectedLabel }}"
                                                   placeholder="ພິມລະຫັດ ຫຼື ຊື່ບັນຊີ..."
                                                   autocomplete="off"
                                                   role="combobox"
                                                   aria-expanded="false"
                                                   aria-autocomplete="list">
                                                    <input type="hidden" name="chart_of_account_id" value="{{ $row->chart_of_account_id }}">
                                                </div>
                                                <button type="button" class="fns-btn fns-btn-secondary js-clear-account">ລ້າງ</button>
                                            </div>
                                        </form>

                                        <div>
                                            <span class="js-row-status inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">
                                                {{ $row->chart_of_account_id ? 'ເຊື່ອມແລ້ວ' : 'ຍັງບໍ່ເຊື່ອມ' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white px-5 py-12 text-center text-slate-500">
                    ບໍ່ພົບລາຍການ.
                </div>
            @endforelse
        </div>

    </section>
</div>

<style>
    .account-combobox-menu {
        position: fixed;
        z-index: 80;
        max-height: min(34rem, calc(100vh - 2rem));
        overflow: auto;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
        padding: .3rem;
    }
    .account-combobox-option {
        display: grid;
        grid-template-columns: 6.4rem minmax(0, 1fr);
        gap: .85rem;
        width: 100%;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #0f172a;
        padding: .46rem .64rem;
        text-align: left;
        font: inherit;
        cursor: pointer;
    }
    .account-combobox-option + .account-combobox-option {
        border-top: 1px solid #eef2f7;
    }
    .account-combobox-option:hover,
    .account-combobox-option.is-active {
        background: #fff7e6;
    }
    .account-combobox-code {
        align-self: start;
        border-radius: 6px;
        background: #0f172a;
        color: #ffffff;
        padding: .24rem .45rem;
        text-align: center;
        font-size: .86rem;
        font-weight: 900;
        white-space: nowrap;
    }
    .account-combobox-text {
        min-width: 0;
        display: grid;
        gap: .22rem;
    }
    .account-combobox-name {
        color: #111827;
        font-size: .9rem;
        font-weight: 900;
        line-height: 1.35;
        overflow-wrap: anywhere;
        white-space: normal;
    }
    .account-combobox-path {
        color: #64748b;
        font-size: .74rem;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
        white-space: normal;
    }
    .account-combobox-path span + span::before {
        content: "/";
        color: #c29014;
        margin: 0 .38rem;
        font-weight: 900;
    }
    .account-combobox-empty {
        color: #64748b;
        padding: .65rem .75rem;
        font-size: .85rem;
        font-weight: 700;
    }
</style>

@push('scripts')
<script>
const ACCOUNT_OPTIONS = @json($accountOptions->values());
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let accountLinkFilter = 'all';
let activeAccountInput = null;
let activeAccountOptionIndex = -1;
const accountComboboxMenu = document.createElement('div');
accountComboboxMenu.className = 'account-combobox-menu';
accountComboboxMenu.hidden = true;
document.body.appendChild(accountComboboxMenu);

function applyAccountLinkFilters() {
    document.querySelectorAll('details[data-account-group]').forEach(group => {
        const rows = Array.from(group.querySelectorAll('.js-account-row'));
        rows.forEach(row => {
            const matchesLink = accountLinkFilter === 'all' || row.dataset.linked === 'false';
            row.hidden = !matchesLink;
        });

        group.hidden = rows.length > 0 && rows.every(row => row.hidden);
    });
}

function findAccount(value) {
    const normalized = String(value || '').trim().toLowerCase();
    if (!normalized) return null;

    return ACCOUNT_OPTIONS.find(account =>
        String(account.label).toLowerCase() === normalized ||
        String(account.code).toLowerCase() === normalized ||
        String(account.name).toLowerCase() === normalized
    ) || null;
}

function matchingAccounts(value) {
    const normalized = String(value || '').trim().toLowerCase();
    const source = normalized
        ? ACCOUNT_OPTIONS.filter(account => [
            account.label,
            account.code,
            account.name,
        ].some(part => String(part || '').toLowerCase().includes(normalized)))
        : ACCOUNT_OPTIONS;

    return source;
}

function positionAccountCombobox() {
    if (!activeAccountInput || accountComboboxMenu.hidden) return;

    const rect = activeAccountInput.getBoundingClientRect();
    const menuWidth = Math.min(Math.max(rect.width, 720), window.innerWidth - 24);
    const left = Math.min(Math.max(12, rect.left), window.innerWidth - menuWidth - 12);
    const viewportPadding = 12;
    const gap = 6;
    const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
    const spaceAbove = rect.top - viewportPadding;
    const openAbove = spaceBelow < 260 && spaceAbove > spaceBelow;
    const availableHeight = Math.max(180, Math.min(544, openAbove ? spaceAbove - gap : spaceBelow - gap));

    accountComboboxMenu.style.width = `${menuWidth}px`;
    accountComboboxMenu.style.left = `${left}px`;
    accountComboboxMenu.style.maxHeight = `${availableHeight}px`;
    accountComboboxMenu.style.top = openAbove
        ? `${Math.max(viewportPadding, rect.top - availableHeight - gap)}px`
        : `${rect.bottom + gap}px`;
}

function hideAccountCombobox() {
    if (activeAccountInput) {
        activeAccountInput.setAttribute('aria-expanded', 'false');
    }

    accountComboboxMenu.hidden = true;
    activeAccountInput = null;
    activeAccountOptionIndex = -1;
}

function renderAccountCombobox(input) {
    activeAccountInput = input;
    const matches = matchingAccounts(input.value);
    activeAccountOptionIndex = -1;

    accountComboboxMenu.innerHTML = matches.length
        ? matches.map((account, index) => {
            const parts = accountParts(account);
            return `
            <button type="button"
                    class="account-combobox-option"
                    data-account-index="${index}"
                    title="${escapeHtml(account.label)}">
                <span class="account-combobox-code">${escapeHtml(account.code)}</span>
                <span class="account-combobox-text">
                    <span class="account-combobox-name">${escapeHtml(parts.current)}</span>
                    <span class="account-combobox-path">${parts.parents.map(part => `<span>${escapeHtml(part)}</span>`).join('')}</span>
                </span>
            </button>
        `;
        }).join('')
        : '<div class="account-combobox-empty">ບໍ່ພົບບັນຊີທີ່ຄົ້ນຫາ</div>';

    accountComboboxMenu.hidden = false;
    accountComboboxMenu.dataset.matches = JSON.stringify(matches.map(account => account.id));
    input.setAttribute('aria-expanded', 'true');
    positionAccountCombobox();
}

function accountParts(account) {
    const rawLabel = String(account.label || '');
    const withoutCode = rawLabel.replace(new RegExp(`^${escapeRegExp(String(account.code || ''))}\\s*-\\s*`), '');
    const parts = withoutCode.split('/').map(part => part.trim()).filter(Boolean);

    return {
        current: parts.at(-1) || account.name || withoutCode || account.code,
        parents: parts.length > 1 ? parts.slice(0, -1) : [],
    };
}

function escapeRegExp(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));
}

function selectComboboxAccount(index) {
    if (!activeAccountInput) return;

    const ids = JSON.parse(accountComboboxMenu.dataset.matches || '[]');
    const account = ACCOUNT_OPTIONS.find(item => Number(item.id) === Number(ids[index]));
    if (!account) return;

    activeAccountInput.value = account.label;
    activeAccountInput.title = account.label;
    const form = activeAccountInput.closest('.js-account-form');
    form.querySelector('input[name="chart_of_account_id"]').value = account.id;
    hideAccountCombobox();
    saveAccountForm(form);
}

function setActiveComboboxOption(nextIndex) {
    const options = Array.from(accountComboboxMenu.querySelectorAll('.account-combobox-option'));
    if (!options.length) return;

    activeAccountOptionIndex = (nextIndex + options.length) % options.length;
    options.forEach((option, index) => option.classList.toggle('is-active', index === activeAccountOptionIndex));
    options[activeAccountOptionIndex].scrollIntoView({block: 'nearest'});
}

function setRowStatus(row, message, ok = true) {
    const status = row.querySelector('.js-row-status');
    status.textContent = message;
    status.className = [
        'js-row-status inline-flex rounded-full px-2 py-1 text-xs font-semibold',
        ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700',
    ].join(' ');
}

function refreshGroupSummary(row) {
    const details = row.closest('details');
    if (!details) return;

    const rows = Array.from(details.querySelectorAll('.js-account-row'));
    const linked = rows.filter(item => item.querySelector('input[name="chart_of_account_id"]')?.value).length;
    const badges = details.querySelectorAll('summary > span');
    const linkedBadge = badges[3];
    if (!linkedBadge) return;

    linkedBadge.textContent = `${linked}/${rows.length} ເຊື່ອມແລ້ວ`;
    linkedBadge.className = [
        'rounded-full px-3 py-1 text-xs font-bold',
        linked === rows.length ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
    ].join(' ');
}

async function saveAccountForm(form) {
    const row = form.closest('.js-account-row');
    const input = form.querySelector('.js-account-search');
    const hidden = form.querySelector('input[name="chart_of_account_id"]');
    const account = findAccount(input.value);

    if (input.value.trim() && !account) {
        hidden.value = '';
        setRowStatus(row, 'ເລືອກຈາກລາຍຊື່', false);
        input.focus();
        refreshGroupSummary(row);
        return;
    }

    hidden.value = account?.id || '';
    setRowStatus(row, 'ກຳລັງບັນທຶກ...');

    const response = await fetch(form.action, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({chart_of_account_id: hidden.value || null}),
    });
    const data = await response.json();

    if (!response.ok || !data.success) {
        setRowStatus(row, 'ບັນທຶກບໍ່ສຳເລັດ', false);
        refreshGroupSummary(row);
        return;
    }

    row.querySelector('.js-reference').textContent = data.row.reference || '-';
    input.value = data.row.account_label || '';
    input.title = data.row.account_label || '';
    hidden.value = data.row.chart_of_account_id || '';
    row.dataset.linked = data.row.chart_of_account_id ? 'true' : 'false';
    setRowStatus(row, data.row.chart_of_account_id ? 'ເຊື່ອມແລ້ວ' : 'ຍັງບໍ່ເຊື່ອມ');
    refreshGroupSummary(row);
    applyAccountLinkFilters();
}

document.addEventListener('change', event => {
    const input = event.target.closest('.js-account-search');
    if (!input) return;
    saveAccountForm(input.closest('.js-account-form'));
});

document.addEventListener('input', event => {
    const input = event.target.closest('.js-account-search');
    if (!input) return;
    renderAccountCombobox(input);
});

document.addEventListener('focusin', event => {
    const input = event.target.closest('.js-account-search');
    if (!input) return;
    renderAccountCombobox(input);
});

document.addEventListener('keydown', event => {
    const input = event.target.closest('.js-account-search');
    if (!input) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        renderAccountCombobox(input);
        setActiveComboboxOption(activeAccountOptionIndex + 1);
        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        setActiveComboboxOption(activeAccountOptionIndex - 1);
        return;
    }

    if (event.key === 'Escape') {
        hideAccountCombobox();
        return;
    }

    if (event.key !== 'Enter') return;

    event.preventDefault();
    if (!accountComboboxMenu.hidden && activeAccountOptionIndex >= 0) {
        selectComboboxAccount(activeAccountOptionIndex);
        return;
    }

    saveAccountForm(input.closest('.js-account-form'));
});

document.addEventListener('click', event => {
    const option = event.target.closest('.account-combobox-option');
    if (option) {
        selectComboboxAccount(Number(option.dataset.accountIndex));
        return;
    }

    if (!event.target.closest('.js-account-search') && !event.target.closest('.account-combobox-menu')) {
        hideAccountCombobox();
    }

    const button = event.target.closest('.js-clear-account');
    if (!button) return;
    const form = button.closest('.js-account-form');
    form.querySelector('.js-account-search').value = '';
    form.querySelector('input[name="chart_of_account_id"]').value = '';
    saveAccountForm(form);
});

window.addEventListener('resize', positionAccountCombobox);
window.addEventListener('scroll', positionAccountCombobox, true);

document.getElementById('account-link-show-all')?.addEventListener('click', () => {
    accountLinkFilter = 'all';
    applyAccountLinkFilters();
});
document.getElementById('account-link-show-unlinked')?.addEventListener('click', () => {
    accountLinkFilter = 'unlinked';
    applyAccountLinkFilters();
});
</script>
@endpush
@endsection
