@extends('layouts.admin')

@section('title', 'Expense structure')
@section('page-title', 'Expense structure')

@section('content')
@php
    $accountOptionsById = $accountOptions->keyBy('id');
    $defaultRowTotal = $defaultRowsByCode->reduce(fn ($total, $rows) => $total + $rows->count(), 0);
    $linkedDefaultRowTotal = $defaultRowsByCode->reduce(fn ($total, $rows) => $total + $rows->whereNotNull('chart_of_account_id')->count(), 0);
@endphp

<div class="es-page">
    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="es-hero">
        <div>
            <h2>Expense structure & account links</h2>
            <p>Set the workbook structure first, then connect each default row to the right chart account.</p>
            <div class="es-stats">
                <span>{{ $sections->count() }} sections</span>
                <span>{{ $sections->sum(fn ($section) => $section->subsections->count()) }} subsections</span>
                <span>{{ $defaultRowTotal }} default rows</span>
                <span class="{{ $defaultRowTotal > $linkedDefaultRowTotal ? 'is-warn' : 'is-ok' }}">
                    {{ $linkedDefaultRowTotal }}/{{ $defaultRowTotal }} linked
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('head_of_finance.settings.expense-structure.index') }}" class="es-year-form">
            <label for="planning_year_id">Planning year</label>
            <select id="planning_year_id" name="planning_year_id" class="fns-input" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected($planningYear?->id === $year->id)>
                        {{ $year->year }} - {{ $year->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($planningYear)
        <details class="es-add-panel">
            <summary>
                <span>
                    <strong>Add section</strong>
                    <small>Create a new top-level expense group.</small>
                </span>
                <span class="es-summary-action">Open</span>
            </summary>
            <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.store') }}" class="grid gap-3 px-5 py-4 md:grid-cols-[110px_1fr_120px_auto] md:items-end">
                @csrf
                <input type="hidden" name="planning_year_id" value="{{ $planningYear->id }}">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
                    <input name="code" class="fns-input" placeholder="2.7" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input name="name" class="fns-input" placeholder="Section name" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Order</label>
                    <input type="number" name="display_order" class="fns-input" min="0" max="999" value="{{ ($sections->max('display_order') ?? 0) + 1 }}" required>
                </div>
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                    Active
                </label>
                <div class="md:col-span-4">
                    <textarea name="description" class="fns-input" rows="2" placeholder="Description"></textarea>
                    <button type="submit" class="fns-btn fns-btn-primary mt-3">Add section</button>
                </div>
            </form>
        </details>

        @if($sections->isNotEmpty())
            <div class="es-section-nav" aria-label="Expense sections">
                <button type="button" class="es-nav-btn" id="esPrevSection">
                    <span>&larr;</span>
                    <span>ກ່ອນ</span>
                </button>

                <div class="es-section-tabs" id="esSectionTabs">
                    @foreach($sections as $navSection)
                        @php
                            $navDefaultRows = $navSection->subsections->flatMap(fn ($subsection) => $defaultRowsByCode->get($subsection->code, collect()));
                            $navLinkedRows = $navDefaultRows->whereNotNull('chart_of_account_id')->count();
                        @endphp
                        <button type="button"
                                class="es-section-tab {{ $loop->first ? 'is-active' : '' }}"
                                data-section-target="{{ $navSection->id }}">
                            <span class="es-section-tab-code">{{ $navSection->code }}</span>
                            <span class="es-section-tab-copy">
                                <strong>{{ $navSection->name }}</strong>
                                <small>{{ $navSection->subsections->count() }} subsections</small>
                            </span>
                            <span class="es-section-tab-total">{{ $navDefaultRows->isNotEmpty() ? $navLinkedRows . '/' . $navDefaultRows->count() : '0' }}</span>
                        </button>
                    @endforeach
                </div>

                <button type="button" class="es-nav-btn" id="esNextSection">
                    <span>ຕໍ່ໄປ</span>
                    <span>&rarr;</span>
                </button>
            </div>
        @endif

        @forelse($sections as $section)
            @php
                $parentOptions = $section->subsections->whereNull('parent_id');
                $sectionDefaultRows = $section->subsections->flatMap(fn ($subsection) => $defaultRowsByCode->get($subsection->code, collect()));
                $sectionLinkedDefaultRows = $sectionDefaultRows->whereNotNull('chart_of_account_id')->count();
            @endphp
            <section class="es-section-card js-section-panel {{ $loop->first ? 'is-active' : '' }}" data-section-panel="{{ $section->id }}">
                <div class="es-section-title">
                    <div class="es-section-code">{{ $section->code }}</div>
                    <div class="min-w-0">
                        <h3>{{ $section->name }}</h3>
                        <p>{{ $section->subsections->count() }} subsections · {{ $sectionLinkedDefaultRows }}/{{ $sectionDefaultRows->count() }} accounts linked</p>
                    </div>
                    @if($sectionDefaultRows->isNotEmpty())
                        <span class="{{ $sectionDefaultRows->count() > $sectionLinkedDefaultRows ? 'es-pill is-warn' : 'es-pill is-ok' }}">
                            {{ $sectionLinkedDefaultRows }}/{{ $sectionDefaultRows->count() }} linked
                        </span>
                    @else
                        <span class="es-pill">No default rows</span>
                    @endif
                </div>

                <details class="es-edit-section">
                    <summary>
                        <span>Edit section settings</span>
                        <span class="es-summary-action">Open</span>
                    </summary>
                    <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.update', $section) }}" class="js-autosave-form grid gap-3 md:grid-cols-[110px_1fr_120px_auto] md:items-end">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
                            <input name="code" value="{{ $section->code }}" class="fns-input" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Section name</label>
                            <input name="name" value="{{ $section->name }}" class="fns-input" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Order</label>
                            <input type="number" name="display_order" value="{{ $section->display_order }}" class="fns-input" min="0" max="999" required>
                        </div>
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" @checked($section->is_active) class="rounded border-slate-300">
                            Active
                        </label>
                        <div class="md:col-span-4">
                            <textarea name="description" class="fns-input" rows="2" placeholder="Description">{{ $section->description }}</textarea>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button"
                                        class="fns-btn fns-btn-danger js-delete-setting"
                                        data-url="{{ route('head_of_finance.settings.expense-structure.sections.destroy', $section) }}"
                                        data-message="Delete this section?">
                                    Delete section
                                </button>
                            </div>
                        </div>
                    </form>
                </details>

                <div class="es-table-wrap">
                    <table class="es-table">
                        <thead>
                            <tr>
                                <th class="py-2 pr-3">Code</th>
                                <th class="py-2 pr-3">Subsection name</th>
                                <th class="py-2 pr-3">Parent</th>
                                <th class="py-2 pr-3">Pattern</th>
                                <th class="py-2 pr-3">Accounts</th>
                                <th class="py-2 pr-3">Order</th>
                                <th class="py-2 pr-3">Active</th>
                                <th class="py-2 pr-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->subsections as $subsection)
                                @php
                                    $defaultRowsForSubsection = $defaultRowsByCode->get($subsection->code, collect());
                                    $linkedRowsForSubsection = $defaultRowsForSubsection->whereNotNull('chart_of_account_id')->count();
                                    $missingLinksForSubsection = $linkedRowsForSubsection < $defaultRowsForSubsection->count();
                                @endphp
                                <tr class="js-autosave-row" data-url="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}">
                                    <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}" class="js-autosave-source-form">
                                        @csrf
                                        @method('PATCH')
                                        <td class="py-2 pr-3">
                                            <input name="code" value="{{ $subsection->code }}" class="fns-input min-w-28" required>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input name="name" value="{{ $subsection->name }}" class="fns-input min-w-80" required>
                                            <input type="hidden" name="description" value="{{ $subsection->description }}">
                                        </td>
                                        <td class="py-2 pr-3">
                                            <select name="parent_id" class="fns-input min-w-44">
                                                <option value="">No parent</option>
                                                @foreach($parentOptions as $parent)
                                                    @if($parent->id !== $subsection->id)
                                                        <option value="{{ $parent->id }}" @selected($subsection->parent_id === $parent->id)>
                                                            {{ $parent->code }} - {{ $parent->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <select name="default_pattern_id" class="fns-input min-w-44">
                                                <option value="">No pattern</option>
                                                @foreach($patterns as $pattern)
                                                    <option value="{{ $pattern->id }}" @selected($subsection->default_pattern_id === $pattern->id)>
                                                        {{ $pattern->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 pr-3">
                                            @if($defaultRowsForSubsection->isNotEmpty())
                                                <span class="js-default-subsection-badge es-pill {{ $missingLinksForSubsection ? 'is-warn' : 'is-ok' }}">
                                                    {{ $linkedRowsForSubsection }}/{{ $defaultRowsForSubsection->count() }} linked
                                                </span>
                                            @else
                                                <span class="es-pill">No defaults</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input type="number" name="display_order" value="{{ $subsection->display_order }}" min="0" max="999" class="fns-input w-24" required>
                                        </td>
                                        <td class="py-2 pr-3 text-center">
                                            <input type="checkbox" name="is_active" value="1" @checked($subsection->is_active) class="rounded border-slate-300">
                                        </td>
                                        <td class="py-2 pr-3 whitespace-nowrap">
                                            <button type="button"
                                                    class="fns-btn fns-btn-danger fns-btn-sm js-delete-setting"
                                                    data-url="{{ route('head_of_finance.settings.expense-structure.subsections.destroy', $subsection) }}"
                                                    data-message="Delete this subsection?">
                                                Delete
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                <tr class="es-account-row">
                                    <td colspan="8" class="px-3 pb-4 pt-0">
                                        <details class="es-account-panel">
                                            <summary>
                                                <span class="min-w-0 truncate">
                                                    Manage default rows for {{ $subsection->code }} - {{ $subsection->name }}
                                                </span>
                                                <span class="flex shrink-0 items-center gap-2 text-xs">
                                                    <span class="js-default-group-badge es-pill {{ $defaultRowsForSubsection->isNotEmpty() && ! $missingLinksForSubsection ? 'is-ok' : 'is-warn' }}">
                                                        {{ $defaultRowsForSubsection->isNotEmpty() ? $linkedRowsForSubsection . '/' . $defaultRowsForSubsection->count() . ' linked' : 'No defaults' }}
                                                    </span>
                                                    <span class="es-summary-action">Open</span>
                                                </span>
                                            </summary>

                                            <div class="es-default-list">
                                                @forelse($defaultRowsForSubsection as $defaultRow)
                                                    @php
                                                        $selectedAccount = $accountOptionsById->get($defaultRow->chart_of_account_id);
                                                        $selectedLabel = $selectedAccount['label'] ?? '';
                                                    @endphp
                                                    <form method="POST"
                                                          action="{{ route('head_of_finance.settings.expense-default-rows.update', $defaultRow) }}"
                                                          class="js-default-account-row es-default-row"
                                                          data-row="{{ $defaultRow->id }}"
                                                          data-url="{{ route('head_of_finance.settings.expense-default-rows.account.update', $defaultRow) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="min-w-0">
                                                            <label class="es-default-field">
                                                                <span>Row name</span>
                                                                <input name="item_name" value="{{ $defaultRow->item_name }}" class="fns-input" required>
                                                            </label>
                                                            <div class="mt-1 flex flex-wrap gap-1.5 text-xs">
                                                                <span class="rounded bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">
                                                                    Ref: <span class="js-default-reference">{{ $defaultRow->reference ?: '-' }}</span>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="es-default-fields">
                                                            <label class="es-default-field">
                                                                <span>Account</span>
                                                                <input class="fns-input js-default-account-search"
                                                                       list="expense-structure-account-options"
                                                                       value="{{ $selectedLabel }}"
                                                                       placeholder="Type account code or name"
                                                                       autocomplete="off">
                                                                <input type="hidden" name="chart_of_account_id" value="{{ $defaultRow->chart_of_account_id }}">
                                                            </label>
                                                            <label class="es-default-field es-default-order">
                                                                <span>Order</span>
                                                                <input type="number" name="sort_order" value="{{ $defaultRow->sort_order }}" class="fns-input" min="1" max="999" required>
                                                            </label>
                                                        </div>

                                                        <div class="es-default-actions">
                                                            <span class="js-default-row-status es-pill {{ $defaultRow->chart_of_account_id ? 'is-ok' : '' }}">
                                                                {{ $defaultRow->chart_of_account_id ? 'Linked' : 'No link' }}
                                                            </span>
                                                            <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">Save</button>
                                                            <button type="button" class="fns-btn fns-btn-secondary fns-btn-sm js-default-clear-account">Clear account</button>
                                                            <button type="button"
                                                                    class="fns-btn fns-btn-danger fns-btn-sm js-delete-setting"
                                                                    data-url="{{ route('head_of_finance.settings.expense-default-rows.destroy', $defaultRow) }}"
                                                                    data-message="Delete this default row?">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </form>
                                                @empty
                                                    <div class="es-default-empty">No default rows yet.</div>
                                                @endforelse

                                                <form method="POST" action="{{ route('head_of_finance.settings.expense-default-rows.store') }}" class="es-default-add-form">
                                                    @csrf
                                                    <input type="hidden" name="subsection_code" value="{{ $subsection->code }}">
                                                    <label>
                                                        <span>Row name</span>
                                                        <input name="item_name" class="fns-input" placeholder="Default row name" required>
                                                    </label>
                                                    <label>
                                                        <span>Account</span>
                                                        <select name="chart_of_account_id" class="fns-input">
                                                            <option value="">No account</option>
                                                            @foreach($accountOptions as $account)
                                                                <option value="{{ $account['id'] }}">{{ $account['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>Order</span>
                                                        <input type="number" name="sort_order" class="fns-input" min="1" max="999" value="{{ ($defaultRowsForSubsection->max('sort_order') ?? 0) + 1 }}" required>
                                                    </label>
                                                    <button type="submit" class="fns-btn fns-btn-primary fns-btn-sm">Add default row</button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="es-add-subsection-row">
                                <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.store', $section) }}">
                                    @csrf
                                    <td class="py-3 pr-3">
                                        <input name="code" class="fns-input min-w-28" placeholder="{{ $section->code }}.1" required>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input name="name" class="fns-input min-w-80" placeholder="Subsection name" required>
                                        <input type="hidden" name="description" value="">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="parent_id" class="fns-input min-w-44">
                                            <option value="">No parent</option>
                                            @foreach($parentOptions as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="default_pattern_id" class="fns-input min-w-44">
                                            <option value="">No pattern</option>
                                            @foreach($patterns as $pattern)
                                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <span class="text-xs font-semibold text-slate-400">After save</span>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input type="number" name="display_order" value="{{ ($section->subsections->max('display_order') ?? 0) + 1 }}" min="0" max="999" class="fns-input w-24" required>
                                    </td>
                                    <td class="py-3 pr-3 text-center">
                                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">Add</button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
                No sections yet for this planning year.
            </div>
        @endforelse
    @else
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
            Create a planning year first.
        </div>
    @endif

    <datalist id="expense-structure-account-options">
        @foreach($accountOptions as $account)
            <option value="{{ $account['label'] }}"></option>
        @endforeach
    </datalist>
</div>

<style>
    .es-page { display:flex; flex-direction:column; gap:1rem; }
    .es-hero {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        padding:1rem 1.15rem;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-hero h2 { margin:0; color:var(--fns-navy); font-size:1.05rem; font-weight:900; }
    .es-hero p { margin:.25rem 0 0; color:var(--fns-gray-500); font-size:.82rem; }
    .es-stats { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.7rem; }
    .es-stats span, .es-pill {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:999px;
        background:#f1f5f9;
        color:#475569;
        padding:.25rem .55rem;
        font-size:.7rem;
        font-weight:900;
        white-space:nowrap;
    }
    .es-stats .is-ok, .es-pill.is-ok { background:#e9f8ef; color:#047857; }
    .es-stats .is-warn, .es-pill.is-warn { background:#fff7df; color:#a16207; }
    .es-year-form { display:flex; flex-direction:column; gap:.25rem; min-width:240px; }
    .es-year-form label {
        color:var(--fns-gray-500);
        font-size:.68rem;
        font-weight:900;
        text-transform:uppercase;
    }
    .es-add-panel,
    .es-edit-section,
    .es-account-panel {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        overflow:hidden;
    }
    .es-add-panel > summary,
    .es-edit-section > summary,
    .es-account-panel > summary {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        list-style:none;
        cursor:pointer;
        padding:.75rem 1rem;
        color:var(--fns-navy);
        font-size:.82rem;
        font-weight:900;
    }
    .es-add-panel > summary::-webkit-details-marker,
    .es-edit-section > summary::-webkit-details-marker,
    .es-account-panel > summary::-webkit-details-marker { display:none; }
    .es-add-panel small { display:block; margin-top:.1rem; color:var(--fns-gray-500); font-size:.72rem; font-weight:700; }
    .es-summary-action {
        border-radius:999px;
        background:#f1f5f9;
        color:#475569;
        padding:.25rem .55rem;
        font-size:.68rem;
        font-weight:900;
        white-space:nowrap;
    }
    details[open] > summary .es-summary-action { background:#fff7df; color:#a16207; }
    .es-section-nav {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:stretch;
        gap:.5rem;
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        padding:.45rem;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-section-tabs {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));
        gap:.45rem;
        min-width:0;
    }
    .es-nav-btn,
    .es-section-tab {
        border:1px solid #e2e8f0;
        border-radius:7px;
        background:#fff;
        color:var(--fns-navy);
        font-family:inherit;
        cursor:pointer;
    }
    .es-nav-btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.35rem;
        min-width:92px;
        padding:0 .7rem;
        color:#64748b;
        font-size:.72rem;
        font-weight:900;
    }
    .es-nav-btn:hover:not(:disabled) { border-color:#d2a112; background:#fffdf4; color:var(--fns-navy); }
    .es-nav-btn:disabled { cursor:not-allowed; opacity:.45; }
    .es-section-tab {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:center;
        gap:.55rem;
        min-height:46px;
        padding:.42rem .55rem;
        text-align:left;
        overflow:hidden;
    }
    .es-section-tab:hover { border-color:#d4d9e4; background:#fbfcfe; }
    .es-section-tab.is-active {
        border-color:#d2a112;
        background:#d2a112;
        color:#061226;
        box-shadow:0 3px 10px rgba(201,153,26,.18);
    }
    .es-section-tab-code {
        display:grid;
        place-items:center;
        min-width:2.65rem;
        height:2.15rem;
        border-radius:7px;
        background:#eef2f7;
        color:var(--fns-navy);
        font-size:.78rem;
        font-weight:900;
    }
    .es-section-tab.is-active .es-section-tab-code { background:rgba(255,255,255,.45); }
    .es-section-tab-copy { min-width:0; }
    .es-section-tab-copy strong {
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
        color:inherit;
        font-size:.72rem;
        font-weight:900;
        line-height:1.25;
    }
    .es-section-tab-copy small {
        display:block;
        margin-top:.08rem;
        color:#64748b;
        font-size:.62rem;
        font-weight:900;
    }
    .es-section-tab.is-active .es-section-tab-copy small { color:#3d3418; }
    .es-section-tab-total {
        color:inherit;
        font-size:.72rem;
        font-weight:900;
        white-space:nowrap;
    }
    .es-section-card {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        overflow:hidden;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-section-card.js-section-panel { display:none; }
    .es-section-card.js-section-panel.is-active { display:block; }
    .es-section-title {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:center;
        gap:.75rem;
        padding:.85rem 1rem;
        border-bottom:1px solid var(--fns-gray-200);
        background:#fbfcfe;
    }
    .es-section-code {
        display:grid;
        place-items:center;
        min-width:3rem;
        height:2.25rem;
        border-radius:7px;
        background:var(--fns-navy);
        color:#fff;
        font-weight:900;
        font-size:.85rem;
    }
    .es-section-title h3 { margin:0; color:var(--fns-navy); font-size:.95rem; font-weight:900; line-height:1.25; }
    .es-section-title p { margin:.15rem 0 0; color:var(--fns-gray-500); font-size:.74rem; }
    .es-edit-section { margin:.75rem 1rem 0; background:#fcfdff; }
    .es-edit-section form { padding:1rem; border-top:1px solid var(--fns-gray-200); }
    .es-table-wrap { overflow:auto; padding:1rem; }
    .es-table { width:100%; min-width:1120px; border-collapse:separate; border-spacing:0; font-size:.8rem; }
    .es-table thead th {
        border-bottom:1px solid #dbe2ec;
        color:#64748b;
        padding:.45rem .55rem;
        text-align:left;
        font-size:.66rem;
        font-weight:900;
        text-transform:uppercase;
    }
    .es-table tbody td { border-bottom:1px solid #eef2f7; padding:.42rem .55rem; vertical-align:middle; }
    .es-table tbody tr:hover td { background:#fffdf6; }
    .es-account-row td { border-bottom:0 !important; background:#f8fafc; }
    .es-add-subsection-row td { background:#fbfcfe; }
    .es-account-panel { border-color:#dbe2ec; }
    .es-account-panel > summary { padding:.6rem .8rem; background:#fff; }
    .es-default-list { display:grid; gap:.55rem; border-top:1px solid var(--fns-gray-200); padding:.75rem; background:#fbfcfe; }
    .es-default-empty {
        border:1px dashed #cbd5e1;
        border-radius:7px;
        background:#fff;
        color:#64748b;
        padding:.75rem;
        font-size:.78rem;
        font-weight:800;
        text-align:center;
    }
    .es-default-row {
        display:grid;
        grid-template-columns:minmax(13rem,1fr) minmax(24rem,2fr) minmax(8rem,auto);
        align-items:start;
        gap:.75rem;
        border:1px solid #e2e8f0;
        border-radius:7px;
        background:#fff;
        padding:.7rem;
    }
    .es-default-row .fns-input { padding:.48rem .6rem; font-size:.78rem; }
    .es-default-fields {
        display:grid;
        grid-template-columns:minmax(14rem,1fr) 5.5rem;
        gap:.55rem;
        min-width:0;
    }
    .es-default-field { display:block; min-width:0; }
    .es-default-field span {
        display:block;
        margin-bottom:.2rem;
        color:#64748b;
        font-size:.66rem;
        font-weight:900;
        text-transform:uppercase;
    }
    .es-default-actions {
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:.4rem;
    }
    .es-default-add-form {
        display:grid;
        grid-template-columns:minmax(14rem,1.2fr) minmax(20rem,2fr) 5.5rem auto;
        align-items:end;
        gap:.6rem;
        border:1px solid #e2e8f0;
        border-radius:7px;
        background:#fff;
        padding:.75rem;
    }
    .es-default-add-form label { min-width:0; }
    .es-default-add-form label span {
        display:block;
        margin-bottom:.2rem;
        color:#64748b;
        font-size:.66rem;
        font-weight:900;
        text-transform:uppercase;
    }
    .es-default-add-form .fns-input { padding:.48rem .6rem; font-size:.78rem; }
    @media (max-width:900px) {
        .es-hero { flex-direction:column; }
        .es-year-form { width:100%; }
        .es-section-nav { grid-template-columns:auto auto; }
        .es-section-tabs { grid-column:1 / -1; order:2; grid-template-columns:1fr; }
        .es-nav-btn { min-height:36px; }
        .es-section-title { grid-template-columns:auto 1fr; }
        .es-section-title > .es-pill { grid-column:1 / -1; justify-self:start; }
        .es-default-row { grid-template-columns:1fr; }
        .es-default-fields { grid-template-columns:1fr; }
        .es-default-actions { justify-content:flex-start; }
        .es-default-add-form { grid-template-columns:1fr; }
    }
</style>

@push('scripts')
<script>
const EXPENSE_STRUCTURE_ACCOUNT_OPTIONS = @json($accountOptions->values());
const EXPENSE_STRUCTURE_CSRF = document.querySelector('meta[name="csrf-token"]').content;
let activeExpenseStructureSection = document.querySelector('.es-section-tab.is-active')?.dataset.sectionTarget || null;

function syncExpenseStructureSectionNav() {
    const tabs = Array.from(document.querySelectorAll('.es-section-tab'));
    const panels = Array.from(document.querySelectorAll('.js-section-panel'));
    const activeIndex = tabs.findIndex(tab => String(tab.dataset.sectionTarget) === String(activeExpenseStructureSection));

    tabs.forEach(tab => {
        const isActive = String(tab.dataset.sectionTarget) === String(activeExpenseStructureSection);
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    panels.forEach(panel => {
        panel.classList.toggle('is-active', String(panel.dataset.sectionPanel) === String(activeExpenseStructureSection));
    });

    const prev = document.getElementById('esPrevSection');
    const next = document.getElementById('esNextSection');
    if (prev) prev.disabled = activeIndex <= 0;
    if (next) next.disabled = activeIndex < 0 || activeIndex >= tabs.length - 1;
}

function selectExpenseStructureSection(sectionId) {
    if (!sectionId) return;
    activeExpenseStructureSection = sectionId;
    syncExpenseStructureSectionNav();
}

function moveExpenseStructureSection(direction) {
    const tabs = Array.from(document.querySelectorAll('.es-section-tab'));
    const index = tabs.findIndex(tab => String(tab.dataset.sectionTarget) === String(activeExpenseStructureSection));
    const nextTab = tabs[index + direction];
    if (!nextTab) return;
    selectExpenseStructureSection(nextTab.dataset.sectionTarget);
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-delete-setting');
    if (!button) return;
    if (!confirm(button.dataset.message || 'Delete this row?')) return;

    const response = await fetch(button.dataset.url, {
        method: 'DELETE',
        headers: {
            'Accept': 'text/html',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });

    if (response.redirected) {
        window.location.href = response.url;
        return;
    }

    window.location.reload();
});

const autosaveTimers = new WeakMap();

function showAutosaveStatus(message, isOk = true) {
    const status = document.createElement('div');
    status.textContent = message;
    status.className = [
        'fixed bottom-4 right-4 z-50 rounded-md px-3 py-2 text-sm font-medium shadow-lg',
        isOk ? 'bg-slate-900 text-white' : 'bg-red-600 text-white',
    ].join(' ');

    document.body.appendChild(status);
    setTimeout(() => status.remove(), 1400);
}

function findExpenseStructureAccount(value) {
    const normalized = String(value || '').trim().toLowerCase();
    if (!normalized) return null;

    return EXPENSE_STRUCTURE_ACCOUNT_OPTIONS.find(account =>
        String(account.label).toLowerCase() === normalized ||
        String(account.code).toLowerCase() === normalized ||
        String(account.name).toLowerCase() === normalized
    ) || null;
}

function setDefaultAccountStatus(row, message, state = 'idle') {
    const status = row.querySelector('.js-default-row-status');
    if (!status) return;

    const tone = {
        linked: 'is-ok',
        idle: '',
        warning: 'is-warn',
        error: 'is-warn',
    }[state] || '';

    status.textContent = message;
    status.className = `js-default-row-status es-pill ${tone}`;
}

function updateDefaultAccountBadge(badge, linked, total) {
    if (!badge) return;

    const complete = linked === total;
    badge.textContent = `${linked}/${total} linked`;
    badge.className = [
        badge.classList.contains('js-default-subsection-badge')
            ? 'js-default-subsection-badge es-pill'
            : 'js-default-group-badge es-pill',
        complete ? 'is-ok' : 'is-warn',
    ].join(' ');
}

function refreshDefaultAccountSummary(row) {
    const details = row.closest('details');
    if (!details) return;

    const rows = Array.from(details.querySelectorAll('.js-default-account-row'));
    const linked = rows.filter(item => item.querySelector('input[name="chart_of_account_id"]')?.value).length;
    const total = rows.length;

    updateDefaultAccountBadge(details.querySelector('.js-default-group-badge'), linked, total);
    updateDefaultAccountBadge(details.closest('tr')?.previousElementSibling?.querySelector('.js-default-subsection-badge'), linked, total);
}

async function saveDefaultAccountRow(row) {
    const input = row.querySelector('.js-default-account-search');
    const hidden = row.querySelector('input[name="chart_of_account_id"]');
    const account = findExpenseStructureAccount(input.value);

    if (input.value.trim() && !account) {
        setDefaultAccountStatus(row, 'Choose from list', 'error');
        input.focus();
        return;
    }

    hidden.value = account?.id || '';
    setDefaultAccountStatus(row, 'Saving...', 'warning');

    try {
        const response = await fetch(row.dataset.url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': EXPENSE_STRUCTURE_CSRF,
            },
            body: JSON.stringify({chart_of_account_id: hidden.value || null}),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error('Account link was not saved');
        }

        row.querySelector('.js-default-reference').textContent = data.row.reference || '-';
        input.value = data.row.account_label || '';
        hidden.value = data.row.chart_of_account_id || '';
        setDefaultAccountStatus(row, data.row.chart_of_account_id ? 'Linked' : 'No link', data.row.chart_of_account_id ? 'linked' : 'idle');
        refreshDefaultAccountSummary(row);
    } catch (error) {
        setDefaultAccountStatus(row, 'Not saved', 'error');
        refreshDefaultAccountSummary(row);
    }
}

function getAutosaveControls(scope) {
    return Array.from(scope.querySelectorAll('input, select, textarea'))
        .filter((input) => input.name && input.type !== 'hidden');
}

function snapshotAutosaveForm(form) {
    getAutosaveControls(form).forEach((input) => {
        input.dataset.originalValue = input.type === 'checkbox'
            ? (input.checked ? '1' : '0')
            : input.value;
    });
}

function hasAutosaveChange(input) {
    const value = input.type === 'checkbox'
        ? (input.checked ? '1' : '0')
        : input.value;

    return input.dataset.originalValue !== value;
}

function formHasAutosaveChanges(form) {
    return getAutosaveControls(form).some(hasAutosaveChange);
}

async function autosaveForm(form) {
    if (!form || form.dataset.saving === '1') return;

    form.dataset.saving = '1';

    try {
        const body = form.matches('form') ? new FormData(form) : new FormData();
        const action = form.matches('form') ? form.action : form.dataset.url;

        if (!form.matches('form')) {
            body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            body.append('_method', 'PATCH');

            getAutosaveControls(form).forEach((input) => {
                if (input.type === 'checkbox') {
                    if (input.checked) body.append(input.name, input.value || '1');
                    return;
                }

                body.append(input.name, input.value);
            });
        }

        const response = await fetch(action, {
            method: 'POST',
            body,
            headers: { 'Accept': 'text/html' },
        });

        if (!response.ok) throw new Error('Autosave failed');

        snapshotAutosaveForm(form);
    } catch (error) {
        throw error;
    } finally {
        form.dataset.saving = '0';
    }
}

async function autosaveDirtyForms() {
    const forms = Array.from(document.querySelectorAll('.js-autosave-form, .js-autosave-row'))
        .filter((form) => form.dataset.saving !== '1' && formHasAutosaveChanges(form));

    if (!forms.length) return;

    try {
        await Promise.all(forms.map((form) => autosaveForm(form)));
        showAutosaveStatus(forms.length === 1 ? 'Saved' : `Saved ${forms.length} changes`);
    } catch (error) {
        showAutosaveStatus('Could not autosave all changes', false);
    }
}

function queueAutosave(input) {
    const form = input.closest('.js-autosave-form, .js-autosave-row');
    if (!form || !hasAutosaveChange(input)) return;

    clearTimeout(autosaveTimers.get(document));
    autosaveTimers.set(document, setTimeout(autosaveDirtyForms, 450));
}

document.querySelectorAll('.js-autosave-form, .js-autosave-row').forEach(snapshotAutosaveForm);

document.addEventListener('submit', (event) => {
    const form = event.target.closest('.js-autosave-form, .js-autosave-source-form');
    if (!form) return;

    event.preventDefault();
    autosaveDirtyForms();
});

document.addEventListener('keydown', (event) => {
    const input = event.target.closest('.js-autosave-form input, .js-autosave-form select, .js-autosave-form textarea, .js-autosave-row input, .js-autosave-row select, .js-autosave-row textarea');
    if (!input || event.key !== 'Enter') return;

    event.preventDefault();
    autosaveDirtyForms();
});

document.addEventListener('blur', (event) => {
    const input = event.target.closest('.js-autosave-form input, .js-autosave-form textarea, .js-autosave-row input, .js-autosave-row textarea');
    if (input) queueAutosave(input);
}, true);

document.addEventListener('change', (event) => {
    const input = event.target.closest('.js-autosave-form select, .js-autosave-form input[type="checkbox"], .js-autosave-row select, .js-autosave-row input[type="checkbox"]');
    if (input) queueAutosave(input);
});

document.addEventListener('change', (event) => {
    const input = event.target.closest('.js-default-account-search');
    if (!input) return;
    saveDefaultAccountRow(input.closest('.js-default-account-row'));
});

document.addEventListener('keydown', (event) => {
    const input = event.target.closest('.js-default-account-search');
    if (!input || event.key !== 'Enter') return;

    event.preventDefault();
    saveDefaultAccountRow(input.closest('.js-default-account-row'));
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-default-clear-account');
    if (!button) return;

    const row = button.closest('.js-default-account-row');
    row.querySelector('.js-default-account-search').value = '';
    row.querySelector('input[name="chart_of_account_id"]').value = '';
    saveDefaultAccountRow(row);
});

document.addEventListener('click', (event) => {
    const tab = event.target.closest('.es-section-tab');
    if (!tab) return;
    selectExpenseStructureSection(tab.dataset.sectionTarget);
});

document.getElementById('esPrevSection')?.addEventListener('click', () => moveExpenseStructureSection(-1));
document.getElementById('esNextSection')?.addEventListener('click', () => moveExpenseStructureSection(1));
syncExpenseStructureSectionNav();
</script>
@endpush
@endsection
