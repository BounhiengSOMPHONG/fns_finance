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
                                                   list="chart-account-options"
                                                   value="{{ $selectedLabel }}"
                                                   placeholder="ພິມລະຫັດ ຫຼື ຊື່ບັນຊີ..."
                                                   autocomplete="off">
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

        <datalist id="chart-account-options">
            @foreach($accountOptions as $account)
                <option value="{{ $account['label'] }}"></option>
            @endforeach
        </datalist>
    </section>
</div>

@push('scripts')
<script>
const ACCOUNT_OPTIONS = @json($accountOptions->values());
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let accountLinkFilter = 'all';

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

document.addEventListener('keydown', event => {
    const input = event.target.closest('.js-account-search');
    if (!input || event.key !== 'Enter') return;
    event.preventDefault();
    saveAccountForm(input.closest('.js-account-form'));
});

document.addEventListener('click', event => {
    const button = event.target.closest('.js-clear-account');
    if (!button) return;
    const form = button.closest('.js-account-form');
    form.querySelector('.js-account-search').value = '';
    form.querySelector('input[name="chart_of_account_id"]').value = '';
    saveAccountForm(form);
});

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
