@extends('layouts.admin')

@section('title', 'Default row accounts')
@section('page-title', 'Default row accounts')

@section('content')
@php
    $accountOptionsById = $accountOptions->keyBy('id');
    $groupedRows = $rows->groupBy('subsection_code');
@endphp

<div class="space-y-5">
    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Connect default expense rows to chart accounts</h2>
                <p class="mt-1 text-sm text-slate-500">
                    The default row name can be different from the chart account name, but still stay connected.
                </p>
            </div>
            <form method="GET" action="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}" class="flex min-w-72 flex-1 justify-end gap-2">
                <input name="q" value="{{ $query }}" class="fns-input max-w-md" placeholder="Search row, subsection, or ref code...">
                <button type="submit" class="fns-btn fns-btn-primary">Search</button>
                @if($query !== '')
                    <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}" class="fns-btn fns-btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Default row groups</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $rows->count() }} rows in {{ $groupedRows->count() }} subsections. Open a subsection to connect its rows.
                    </p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Autosaves on selection</span>
            </div>
        </div>

        <div class="space-y-3 bg-slate-50 p-4">
            @forelse($groupedRows as $code => $groupRows)
                @php
                    $subsection = $subsectionLabels->get($code);
                    $linkedCount = $groupRows->whereNotNull('chart_of_account_id')->count();
                    $hasMissingLinks = $linkedCount < $groupRows->count();
                @endphp
                <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/80 ring-1 ring-white transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-xl hover:shadow-slate-300/70">
                    <summary class="flex cursor-pointer list-none items-center gap-4 bg-white px-5 py-4 hover:bg-amber-50/45">
                        <span class="grid h-12 w-16 shrink-0 place-items-center rounded-lg bg-slate-900 text-sm font-black text-white shadow-md shadow-slate-300">
                            {{ $code }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-slate-950">
                                {{ $subsection?->name ?? 'No matching subsection' }}
                            </span>
                            <span class="mt-1 block truncate text-xs text-slate-500">
                                {{ trim(($subsection?->section?->code ?? '') . ' ' . ($subsection?->section?->name ?? '')) ?: 'Default rows only' }}
                            </span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            {{ $groupRows->count() }} rows
                        </span>
                        <span class="rounded-full {{ $hasMissingLinks ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-xs font-bold">
                            {{ $linkedCount }}/{{ $groupRows->count() }} linked
                        </span>
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-sm font-black text-slate-500 transition group-open:rotate-180 group-hover:bg-amber-100 group-hover:text-amber-700">v</span>
                    </summary>

                    <div class="border-t border-slate-200 bg-white p-4">
                        <div class="grid gap-3">
                            @foreach($groupRows as $row)
                                @php
                                    $selectedAccount = $accountOptionsById->get($row->chart_of_account_id);
                                    $selectedLabel = $selectedAccount['label'] ?? '';
                                @endphp
                                <div class="js-account-row rounded-lg border border-slate-200 p-4" data-row="{{ $row->id }}">
                                    <div class="grid gap-3 xl:grid-cols-[minmax(18rem,1fr)_minmax(34rem,2fr)_8rem] xl:items-start">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-950">{{ $row->item_name }}</div>
                                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                                <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-700">
                                                    Ref: <span class="js-reference">{{ $row->reference ?: '-' }}</span>
                                                </span>
                                                <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-700">
                                                    Order: {{ $row->sort_order }}
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
                                                           placeholder="Type chart account code or name..."
                                                           autocomplete="off">
                                                    <input type="hidden" name="chart_of_account_id" value="{{ $row->chart_of_account_id }}">
                                                </div>
                                                <button type="button" class="fns-btn fns-btn-secondary js-clear-account">Clear</button>
                                            </div>
                                        </form>

                                        <div>
                                            <span class="js-row-status inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">
                                                {{ $row->chart_of_account_id ? 'Linked' : 'No link' }}
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
                    No default rows found.
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

    linkedBadge.textContent = `${linked}/${rows.length} linked`;
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
        setRowStatus(row, 'Choose from list', false);
        input.focus();
        refreshGroupSummary(row);
        return;
    }

    hidden.value = account?.id || '';
    setRowStatus(row, 'Saving...');

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
        setRowStatus(row, 'Not saved', false);
        refreshGroupSummary(row);
        return;
    }

    row.querySelector('.js-reference').textContent = data.row.reference || '-';
    input.value = data.row.account_label || '';
    hidden.value = data.row.chart_of_account_id || '';
    setRowStatus(row, data.row.chart_of_account_id ? 'Linked' : 'No link');
    refreshGroupSummary(row);
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
</script>
@endpush
@endsection
