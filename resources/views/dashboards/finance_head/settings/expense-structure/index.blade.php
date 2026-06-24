@extends('layouts.admin')

@section('title', 'ຕັ້ງຄ່າລາຍຈ່າຍ')
@section('page-title', 'ຕັ້ງຄ່າລາຍຈ່າຍ')

@section('content')
@php
    $accountOptionsById = $accountOptions->keyBy('id');
    $defaultRowTotal = $defaultRowsByCode->reduce(fn ($total, $rows) => $total + $rows->count(), 0);
    $linkedDefaultRowTotal = $defaultRowsByCode->reduce(fn ($total, $rows) => $total + $rows->whereNotNull('chart_of_account_id')->count(), 0);
    $unlinkedAccountWarnings = $accountWarnings->filter(fn ($row) => $row->chart_of_account_id === null)->values();
    $unlinkedDefaultRowTotal = max($defaultRowTotal - $linkedDefaultRowTotal, 0);
@endphp

<div class="es-page">
    @include('dashboards.finance_head.settings.expense-setup-tabs')

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="es-hero">
        <div>
            <h2>DEF ແຕ່ລະປີ</h2>
            <p>ຈັດໝວດ, ກຸ່ມ ແລະ ລາຍການລາຍຈ່າຍຂອງສົກປີທີ່ເລືອກ.</p>
            <div class="es-stats">
                <span>{{ $sections->count() }} ໝວດຫຼັກ</span>
                <span>{{ $sections->sum(fn ($section) => $section->subsections->count()) }} ກຸ່ມຍ່ອຍ</span>
                <span>{{ $defaultRowTotal }} ລາຍການ</span>
                <span class="{{ $defaultRowTotal > $linkedDefaultRowTotal ? 'is-warn' : 'is-ok' }}">
                    {{ $linkedDefaultRowTotal }}/{{ $defaultRowTotal }} ເຊື່ອມບັນຊີ
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('head_of_finance.settings.expense-structure.index') }}" class="es-year-form">
            <label for="planning_year_id">ສົກປີທີ່ເບິ່ງ</label>
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
        <section class="{{ $unlinkedDefaultRowTotal > 0 ? 'es-link-banner is-warn' : 'es-link-banner is-ok' }}">
            <div>
                <strong>{{ $linkedDefaultRowTotal }}/{{ $defaultRowTotal }} ລາຍການເຊື່ອມບັນຊີແລ້ວ</strong>
                <span>
                    @if($unlinkedDefaultRowTotal > 0)
                        {{ $unlinkedDefaultRowTotal }} ລາຍການຍັງບໍ່ເຊື່ອມ. ການລິ້ງບັນຊີຖືກແຍກໄປໜ້າຂອງມັນເພື່ອບໍ່ໃຫ້ DEF ປົນກັນ.
                    @else
                        ລາຍການຂອງສົກປີນີ້ມີບັນຊີຄົບແລ້ວ.
                    @endif
                </span>
            </div>
            <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}" class="fns-btn fns-btn-secondary">
                ໄປລິ້ງບັນຊີ
            </a>
        </section>
    @endif

    @if($planningYear)
        <section class="es-add-panel">
            <div class="es-add-panel-head">
                <span class="es-add-summary-copy">
                    <strong>ໂຄງສ້າງໝວດລາຍຈ່າຍ</strong>
                    <small>ເພີ່ມໝວດໃຫຍ່ໃໝ່ໃນສົກປີນີ້</small>
                </span>
                <button type="button" class="es-add-button" data-open-section-modal>
                    <span class="es-add-button-icon">+</span>
                    <span>ເພີ່ມໝວດຫຼັກ</span>
                </button>
            </div>
        </section>

        <div class="es-modal-backdrop" data-section-modal hidden>
            <div class="es-modal" role="dialog" aria-modal="true" aria-labelledby="esSectionModalTitle">
                <div class="es-modal-head">
                    <div>
                        <span>Expense Section</span>
                        <h3 id="esSectionModalTitle">ເພີ່ມໝວດຫຼັກ</h3>
                    </div>
                    <button type="button" class="es-modal-close" data-close-section-modal aria-label="Close">&times;</button>
                </div>

                <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.store') }}" class="es-section-modal-form">
                    @csrf
                    <input type="hidden" name="planning_year_id" value="{{ $planningYear->id }}">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">ລະຫັດ</label>
                        <input name="code" class="fns-input" placeholder="2.7" required data-section-modal-first>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">ຊື່ໝວດ</label>
                        <input name="name" class="fns-input" placeholder="ຊື່ໝວດລາຍຈ່າຍ" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">ລຳດັບ</label>
                        <input type="number" name="display_order" class="fns-input" min="0" max="999" value="{{ ($sections->max('display_order') ?? 0) + 1 }}" required>
                    </div>
                    <label class="es-modal-check">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                        ໃຊ້ງານ
                    </label>
                    <div class="es-modal-wide">
                        <label class="mb-1 block text-sm font-medium text-slate-700">ລາຍລະອຽດ</label>
                        <textarea name="description" class="fns-input" rows="3" placeholder="ລາຍລະອຽດ"></textarea>
                    </div>
                    <div class="es-modal-actions">
                        <button type="button" class="fns-btn fns-btn-secondary" data-close-section-modal>ຍົກເລີກ</button>
                        <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກໝວດ</button>
                    </div>
                </form>
            </div>
        </div>

        @if($sections->isNotEmpty())
            <div class="es-section-nav" aria-label="ໝວດລາຍຈ່າຍ">
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
                                <small>{{ $navSection->subsections->count() }} ກຸ່ມຍ່ອຍ</small>
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
                        <p>{{ $section->subsections->count() }} ກຸ່ມຍ່ອຍ · {{ $sectionLinkedDefaultRows }}/{{ $sectionDefaultRows->count() }} ເຊື່ອມບັນຊີ</p>
                    </div>
                    <div class="es-section-title-actions">
                        @if($sectionDefaultRows->isNotEmpty())
                            <span class="{{ $sectionDefaultRows->count() > $sectionLinkedDefaultRows ? 'es-pill is-warn' : 'es-pill is-ok' }}">
                                {{ $sectionLinkedDefaultRows }}/{{ $sectionDefaultRows->count() }} ເຊື່ອມແລ້ວ
                            </span>
                        @else
                            <span class="es-pill">ຍັງບໍ່ມີລາຍການ</span>
                        @endif

                        <button type="button" class="es-section-edit-btn" data-open-section-edit-modal="{{ $section->id }}" aria-haspopup="dialog">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" />
                            </svg>
                            <span>ແກ້ໄຂ</span>
                        </button>
                    </div>
                </div>

                <div class="es-modal-backdrop" data-section-edit-modal="{{ $section->id }}" hidden>
                    <div class="es-modal" role="dialog" aria-modal="true" aria-labelledby="esEditSectionModalTitle{{ $section->id }}">
                        <div class="es-modal-head">
                            <div>
                                <span>Expense Section</span>
                                <h3 id="esEditSectionModalTitle{{ $section->id }}">ແກ້ໄຂໝວດຫຼັກ {{ $section->code }}</h3>
                            </div>
                            <button type="button" class="es-modal-close" data-close-section-edit-modal aria-label="Close">&times;</button>
                        </div>

                        <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.update', $section) }}" class="js-autosave-form es-section-modal-form">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">ລະຫັດ</label>
                                <input name="code" value="{{ $section->code }}" class="fns-input" required data-section-edit-first>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">ຊື່ໝວດ</label>
                                <input name="name" value="{{ $section->name }}" class="fns-input" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">ລຳດັບ</label>
                                <input type="number" name="display_order" value="{{ $section->display_order }}" class="fns-input" min="0" max="999" required>
                            </div>
                            <label class="es-modal-check">
                                <input type="checkbox" name="is_active" value="1" @checked($section->is_active) class="rounded border-slate-300">
                                ໃຊ້ງານ
                            </label>
                            <div class="es-modal-wide">
                                <label class="mb-1 block text-sm font-medium text-slate-700">ລາຍລະອຽດ</label>
                                <textarea name="description" class="fns-input" rows="3" placeholder="ລາຍລະອຽດ">{{ $section->description }}</textarea>
                            </div>
                            <div class="es-modal-actions">
                                <button type="button"
                                        class="fns-btn fns-btn-danger js-delete-setting"
                                        data-url="{{ route('head_of_finance.settings.expense-structure.sections.destroy', $section) }}"
                                        data-message="ລຶບໝວດນີ້?">
                                    ລຶບໝວດ
                                </button>
                                <button type="button" class="fns-btn fns-btn-secondary" data-close-section-edit-modal>ປິດ</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="es-table-wrap">
                    <table class="es-table">
                        <thead>
                            <tr>
                                <th class="py-2 pr-3">ລະຫັດ</th>
                                <th class="py-2 pr-3">ຊື່ກຸ່ມຍ່ອຍ</th>
                                <th class="py-2 pr-3">ຢູ່ໃຕ້</th>
                                <th class="py-2 pr-3">ແບບຄຳນວນ</th>
                                <th class="py-2 pr-3">ບັນຊີ</th>
                                <th class="py-2 pr-3">ລຳດັບ</th>
                                <th class="py-2 pr-3">ໃຊ້</th>
                                <th class="py-2 pr-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->subsections as $subsection)
                                @php
                                    $defaultRowsForSubsection = $defaultRowsByCode->get($subsection->code, collect());
                                    $linkedRowsForSubsection = $defaultRowsForSubsection->whereNotNull('chart_of_account_id')->count();
                                    $missingLinksForSubsection = $linkedRowsForSubsection < $defaultRowsForSubsection->count();
                                    $childSubsectionsCount = $subsection->children->count();
                                    $hasChildSubsections = $childSubsectionsCount > 0;
                                @endphp
                                <tr class="js-autosave-row es-subsection-row {{ $hasChildSubsections ? 'is-parent' : '' }} {{ $subsection->parent_id ? 'is-child' : '' }}" data-url="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}">
                                    <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}" class="js-autosave-source-form">
                                        @csrf
                                        @method('PATCH')
                                        <td class="py-2 pr-3">
                                            <input name="code" value="{{ $subsection->code }}" class="fns-input min-w-28" required>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input name="name" value="{{ $subsection->name }}" class="fns-input min-w-80" required>
                                            <input type="hidden" name="description" value="{{ $subsection->description }}">
                                            @if($hasChildSubsections)
                                                <span class="es-parent-marker">ຫົວຂໍ້ໃຫຍ່ · {{ $childSubsectionsCount }} ຫົວຂໍ້ຍ່ອຍ</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-3">
                                            <select name="parent_id" class="fns-input min-w-44">
                                                <option value="">ບໍ່ມີກຸ່ມແມ່</option>
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
                                                <option value="">ບໍ່ມີແບບຄຳນວນ</option>
                                                @foreach($patterns as $pattern)
                                                    <option value="{{ $pattern->id }}" @selected($subsection->default_pattern_id === $pattern->id)>
                                                        {{ $pattern->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 pr-3">
                                            @if($hasChildSubsections)
                                                <span class="es-pill is-parent">ບໍ່ມີ DEF</span>
                                            @elseif($defaultRowsForSubsection->isNotEmpty())
                                                <span class="js-default-subsection-badge es-pill {{ $missingLinksForSubsection ? 'is-warn' : 'is-ok' }}">
                                                    {{ $linkedRowsForSubsection }}/{{ $defaultRowsForSubsection->count() }} ເຊື່ອມແລ້ວ
                                                </span>
                                            @else
                                                <span class="es-pill">ຍັງບໍ່ມີລາຍການ</span>
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
                                                    data-message="ລຶບກຸ່ມຍ່ອຍນີ້?">
                                                ລຶບ
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                @unless($hasChildSubsections)
                                <tr class="es-account-row">
                                    <td colspan="8" class="px-3 pb-4 pt-0">
                                        <details class="es-account-panel">
                                            <summary>
                                                <span class="min-w-0 truncate">
                                                    DEF ຂອງ {{ $subsection->code }} - {{ $subsection->name }}
                                                </span>
                                                <span class="flex shrink-0 items-center gap-2 text-xs">
                                                    <span class="js-default-group-badge es-pill {{ $defaultRowsForSubsection->isNotEmpty() && ! $missingLinksForSubsection ? 'is-ok' : 'is-warn' }}">
                                                        {{ $defaultRowsForSubsection->isNotEmpty() ? $linkedRowsForSubsection . '/' . $defaultRowsForSubsection->count() . ' ເຊື່ອມແລ້ວ' : 'ຍັງບໍ່ມີລາຍການ' }}
                                                    </span>
                                                    <span class="es-summary-action">ເປີດ</span>
                                                </span>
                                            </summary>

                                            <div class="es-default-list">
                                                @forelse($defaultRowsForSubsection as $defaultRow)
                                                    @php
                                                        $selectedAccount = $accountOptionsById->get($defaultRow->chart_of_account_id);
                                                        $selectedLabel = $selectedAccount['label'] ?? '';
                                                        $selectedReference = $selectedAccount['code'] ?? null;
                                                        $suggestedAccount = $defaultRow->getAttribute('suggested_account');
                                                        $accountState = $defaultRow->chart_of_account_id === null ? 'unlinked' : 'linked';
                                                    @endphp
                                                    <form method="POST"
                                                          action="{{ route('head_of_finance.settings.expense-default-rows.update', $defaultRow) }}"
                                                          class="js-default-account-row es-default-row"
                                                          data-row="{{ $defaultRow->id }}"
                                                          data-account-state="{{ $accountState }}"
                                                          data-url="{{ route('head_of_finance.settings.expense-default-rows.account.update', $defaultRow) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="chart_of_account_id" value="{{ $defaultRow->chart_of_account_id }}">
                                                        <div class="min-w-0">
                                                            <label class="es-default-field">
                                                                <span>ລາຍການລາຍຈ່າຍ</span>
                                                                <input name="item_name" value="{{ $defaultRow->item_name }}" class="fns-input" required>
                                                            </label>
                                                            <div class="mt-1 flex flex-wrap gap-1.5 text-xs">
                                                                <span class="rounded bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">ລຳດັບ: {{ $defaultRow->sort_order }}</span>
                                                                <span class="rounded bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">ບັນຊີ: {{ $selectedReference ?: '-' }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="es-default-fields">
                                                            <label class="es-default-field es-default-order">
                                                                <span>ລຳດັບ</span>
                                                                <input type="number" name="sort_order" value="{{ $defaultRow->sort_order }}" class="fns-input" min="1" max="999" required>
                                                            </label>
                                                            <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index', ['q' => $defaultRow->item_name]) }}"
                                                               class="fns-btn fns-btn-secondary fns-btn-sm">
                                                                ໄປລິ້ງບັນຊີ
                                                            </a>
                                                        </div>

                                                        <div class="es-default-actions">
                                                            <span class="js-default-row-status es-pill {{ $defaultRow->chart_of_account_id ? 'is-ok' : '' }}">
                                                                {{ $defaultRow->chart_of_account_id ? 'ເຊື່ອມແລ້ວ' : 'ຍັງບໍ່ເຊື່ອມ' }}
                                                            </span>
                                                            <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">ບັນທຶກ</button>
                                                            <button type="button"
                                                                    class="fns-btn fns-btn-danger fns-btn-sm js-delete-setting"
                                                                    data-url="{{ route('head_of_finance.settings.expense-default-rows.destroy', $defaultRow) }}"
                                                                    data-message="ລຶບລາຍການນີ້?">
                                                                ລຶບ
                                                            </button>
                                                        </div>
                                                    </form>
                                                @empty
                                                    <div class="es-default-empty">ຍັງບໍ່ມີລາຍການໃນກຸ່ມນີ້.</div>
                                                @endforelse

                                                <form method="POST" action="{{ route('head_of_finance.settings.expense-default-rows.store') }}" class="es-default-add-form">
                                                    @csrf
                                                    <input type="hidden" name="subsection_code" value="{{ $subsection->code }}">
                                                    <input type="hidden" name="subsection_id" value="{{ $subsection->id }}">
                                                    <label>
                                                        <span>ລາຍການລາຍຈ່າຍ</span>
                                                        <input name="item_name" class="fns-input" placeholder="ຊື່ລາຍການລາຍຈ່າຍ" required>
                                                    </label>
                                                    <label>
                                                        <span>ສະຖານະບັນຊີ</span>
                                                        <input class="fns-input" value="ບັນທຶກກ່ອນ ແລ້ວໄປລິ້ງບັນຊີ" disabled>
                                                    </label>
                                                    <label>
                                                        <span>ລຳດັບ</span>
                                                        <input type="number" name="sort_order" class="fns-input" min="1" max="999" value="{{ ($defaultRowsForSubsection->max('sort_order') ?? 0) + 1 }}" required>
                                                    </label>
                                                    <button type="submit" class="fns-btn fns-btn-primary fns-btn-sm">ເພີ່ມລາຍການ</button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                                @endunless
                            @endforeach

                            <tr class="es-add-subsection-row">
                                <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.store', $section) }}">
                                    @csrf
                                    <td class="py-3 pr-3">
                                        <input name="code" class="fns-input min-w-28" placeholder="{{ $section->code }}.1" required>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input name="name" class="fns-input min-w-80" placeholder="ຊື່ກຸ່ມຍ່ອຍ" required>
                                        <input type="hidden" name="description" value="">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="parent_id" class="fns-input min-w-44">
                                            <option value="">ບໍ່ມີກຸ່ມແມ່</option>
                                            @foreach($parentOptions as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="default_pattern_id" class="fns-input min-w-44">
                                            <option value="">ບໍ່ມີແບບຄຳນວນ</option>
                                            @foreach($patterns as $pattern)
                                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <span class="text-xs font-semibold text-slate-400">ບັນທຶກແລ້ວຈຶ່ງເພີ່ມລາຍການ</span>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input type="number" name="display_order" value="{{ ($section->subsections->max('display_order') ?? 0) + 1 }}" min="0" max="999" class="fns-input w-24" required>
                                    </td>
                                    <td class="py-3 pr-3 text-center">
                                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">ເພີ່ມ</button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
                ຍັງບໍ່ມີໝວດລາຍຈ່າຍໃນສົກປີນີ້.
            </div>
        @endforelse
    @else
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
            ສ້າງສົກປີກ່ອນ ແລ້ວຈຶ່ງຕັ້ງຄ່າລາຍຈ່າຍ.
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
    .es-link-banner {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        border:1px solid #d8e1ed;
        border-radius:8px;
        background:#f8fbff;
        padding:.9rem 1rem;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-link-banner.is-warn { border-color:#f1cd73; background:#fffaf0; }
    .es-link-banner.is-ok { border-color:#b9e4c9; background:#f0fbf4; }
    .es-link-banner strong { display:block; color:var(--fns-navy); font-size:.9rem; font-weight:900; }
    .es-link-banner span { display:block; margin-top:.2rem; color:#64748b; font-size:.78rem; line-height:1.45; }
    .es-warning-panel,
    .es-ready-panel {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-warning-panel { border-color:#f1cd73; background:#fffaf0; }
    .es-warning-head {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:.85rem 1rem;
        border-bottom:1px solid #f4ddb0;
    }
    .es-warning-head h3 { margin:0; color:#7a4c05; font-size:.92rem; font-weight:900; }
    .es-warning-head p { margin:.12rem 0 0; color:#94660d; font-size:.74rem; font-weight:800; }
    .es-warning-actions { display:flex; flex-wrap:wrap; gap:.35rem; }
    .es-filter-btn {
        border:1px solid #ead49f;
        border-radius:7px;
        background:#fff;
        color:#7a4c05;
        cursor:pointer;
        font-size:.72rem;
        font-weight:900;
        padding:.42rem .62rem;
    }
    .es-filter-btn.is-active { background:#7a4c05; border-color:#7a4c05; color:#fff; }
    .es-warning-list {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
        gap:.45rem;
        padding:.75rem;
    }
    .es-warning-item {
        display:grid;
        grid-template-columns:auto 1fr;
        gap:.25rem .5rem;
        border:1px solid #f0d89d;
        border-radius:7px;
        background:#fff;
        color:#5f4209;
        cursor:pointer;
        padding:.55rem .65rem;
        text-align:left;
    }
    .es-warning-item:hover { border-color:#d2a112; box-shadow:0 2px 10px rgba(122,76,5,.08); }
    .es-warning-code { font-weight:900; }
    .es-warning-name { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:800; }
    .es-warning-current,
    .es-warning-suggest { color:#8a6413; font-size:.68rem; font-weight:900; }
    .es-ready-panel {
        display:flex;
        align-items:center;
        gap:.6rem;
        padding:.85rem 1rem;
        color:#047857;
        background:#f0fdf4;
        border-color:#bbf7d0;
        font-size:.82rem;
    }
    .es-ready-panel strong { font-weight:900; }
    .es-account-hint {
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        background:#e9f8ef;
        color:#047857;
        padding:.12rem .45rem;
        font-weight:900;
    }
    .es-account-hint.is-review { background:#fff7df; color:#a16207; }
    .es-default-row.is-filter-hidden,
    .es-account-row.is-filter-hidden,
    .js-autosave-row.is-filter-hidden {
        display:none;
    }
    .es-default-row.is-focus-pulse {
        border-color:#d2a112;
        box-shadow:0 0 0 3px rgba(210,161,18,.18);
    }
    .es-add-panel,
    .es-account-panel {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        overflow:hidden;
    }
    .es-add-panel {
        border-color:#d5dfeb;
        background:#fbfcff;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .es-add-panel-head,
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
    .es-add-panel-head {
        padding:.65rem .75rem .65rem 1rem;
    }
    .es-account-panel > summary::-webkit-details-marker { display:none; }
    .es-add-summary-copy {
        display:grid;
        min-width:0;
        gap:.08rem;
    }
    .es-add-summary-copy strong {
        color:var(--fns-navy);
        font-size:.84rem;
        font-weight:900;
    }
    .es-add-panel small { display:block; color:var(--fns-gray-500); font-size:.7rem; font-weight:700; }
    .es-add-button {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.45rem;
        min-height:2.35rem;
        border:1px solid #d2a112;
        border-radius:7px;
        background:#d2a112;
        color:#061226;
        padding:.5rem .8rem;
        font-size:.76rem;
        font-weight:900;
        line-height:1;
        white-space:nowrap;
        box-shadow:0 5px 14px rgba(201,153,26,.18);
        transition:transform .15s ease, box-shadow .15s ease, background .15s ease;
        cursor:pointer;
        font-family:inherit;
    }
    .es-add-button-icon {
        display:grid;
        place-items:center;
        width:1.2rem;
        height:1.2rem;
        border-radius:999px;
        background:rgba(255,255,255,.55);
        font-size:.95rem;
        line-height:1;
    }
    .es-add-button:hover {
        background:#e2b329;
        box-shadow:0 8px 18px rgba(201,153,26,.24);
        transform:translateY(-1px);
    }
    .es-modal-backdrop {
        position:fixed;
        inset:0;
        z-index:80;
        display:grid;
        place-items:center;
        background:rgba(6,18,38,.42);
        padding:1.25rem;
        backdrop-filter:blur(3px);
    }
    .es-modal-backdrop[hidden] { display:none; }
    .es-modal {
        width:min(680px, 100%);
        max-height:min(720px, calc(100vh - 2.5rem));
        overflow:auto;
        border:1px solid #dbe2ec;
        border-radius:8px;
        background:#fff;
        box-shadow:0 24px 70px rgba(6,18,38,.28);
    }
    .es-modal-head {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        border-bottom:1px solid #e2e8f0;
        padding:1rem 1.1rem;
        background:#fbfcff;
    }
    .es-modal-head span {
        display:block;
        color:#a16207;
        font-size:.68rem;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
    }
    .es-modal-head h3 {
        margin:.12rem 0 0;
        color:var(--fns-navy);
        font-size:1rem;
        font-weight:900;
    }
    .es-modal-close {
        display:grid;
        place-items:center;
        width:2rem;
        height:2rem;
        border:1px solid #e2e8f0;
        border-radius:7px;
        background:#fff;
        color:#64748b;
        cursor:pointer;
        font-size:1.25rem;
        line-height:1;
    }
    .es-modal-close:hover { border-color:#d2a112; color:var(--fns-navy); }
    .es-section-modal-form {
        display:grid;
        grid-template-columns:110px 1fr 120px;
        gap:.85rem;
        padding:1.1rem;
    }
    .es-modal-check {
        display:flex;
        align-items:center;
        gap:.45rem;
        min-height:2.55rem;
        align-self:end;
        border:1px solid #e2e8f0;
        border-radius:7px;
        padding:.55rem .7rem;
        color:#475569;
        font-size:.8rem;
        font-weight:800;
    }
    .es-modal-wide,
    .es-modal-actions {
        grid-column:1 / -1;
    }
    .es-modal-actions {
        display:flex;
        justify-content:flex-end;
        gap:.5rem;
        padding-top:.15rem;
    }
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
    .es-section-title-actions {
        display:flex;
        align-items:center;
        justify-content:flex-end;
        flex-wrap:wrap;
        gap:.45rem;
    }
    .es-section-edit-btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.4rem;
        min-height:2rem;
        border:1px solid #d8e0ea;
        border-radius:999px;
        background:#fff;
        color:#13213b;
        padding:.35rem .62rem;
        font-size:.72rem;
        font-weight:900;
        line-height:1;
        white-space:nowrap;
        box-shadow:0 1px 2px rgba(15,23,42,.05);
        transition:border-color .16s ease, box-shadow .16s ease, background .16s ease, transform .16s ease;
    }
    .es-section-edit-btn:hover {
        border-color:#c29014;
        background:#fff9e8;
        box-shadow:0 6px 14px rgba(15,23,42,.08);
        transform:translateY(-1px);
    }
    .es-section-edit-btn:focus-visible {
        outline:3px solid rgba(194,144,20,.22);
        outline-offset:2px;
        border-color:#c29014;
    }
    .es-section-edit-btn svg {
        width:.9rem;
        height:.9rem;
        stroke:currentColor;
        stroke-width:2;
        stroke-linecap:round;
        stroke-linejoin:round;
    }
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
    .es-subsection-row.is-parent td {
        border-top:1px solid #dbe4ef;
        background:#f8fbff;
    }
    .es-subsection-row.is-parent:hover td { background:#f3f8ff; }
    .es-subsection-row.is-parent .fns-input {
        border-color:#cbd8e8;
        background:#fff;
        color:#13213b;
        font-weight:900;
    }
    .es-subsection-row.is-child td:first-child {
        box-shadow:inset 3px 0 0 #d2a112;
    }
    .es-parent-marker {
        display:inline-flex;
        align-items:center;
        width:max-content;
        max-width:100%;
        margin-top:.28rem;
        border-radius:999px;
        background:#e8eef8;
        color:#334155;
        padding:.16rem .48rem;
        font-size:.66rem;
        font-weight:900;
        line-height:1.2;
    }
    .es-pill.is-parent {
        background:#eef2f7;
        color:#475569;
    }
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
        .es-link-banner { align-items:flex-start; flex-direction:column; }
        .es-year-form { width:100%; }
        .es-section-nav { grid-template-columns:auto auto; }
        .es-section-tabs { grid-column:1 / -1; order:2; grid-template-columns:1fr; }
        .es-nav-btn { min-height:36px; }
        .es-section-title { grid-template-columns:auto 1fr; }
        .es-section-title-actions { grid-column:1 / -1; justify-content:flex-start; }
        .es-default-row { grid-template-columns:1fr; }
        .es-default-fields { grid-template-columns:1fr; }
        .es-default-actions { justify-content:flex-start; }
        .es-default-add-form { grid-template-columns:1fr; }
        .es-add-panel-head { align-items:stretch; flex-direction:column; }
        .es-add-button { width:100%; }
        .es-section-modal-form { grid-template-columns:1fr; }
        .es-modal-actions { align-items:stretch; flex-direction:column-reverse; }
        .es-modal-actions .fns-btn { width:100%; }
    }
</style>

@push('scripts')
<script>
const EXPENSE_STRUCTURE_ACCOUNT_OPTIONS = @json($accountOptions->values());
const EXPENSE_STRUCTURE_CSRF = document.querySelector('meta[name="csrf-token"]').content;
let activeExpenseStructureSection = document.querySelector('.es-section-tab.is-active')?.dataset.sectionTarget || null;
let activeAccountFilter = 'all';

function openSectionModal() {
    const modal = document.querySelector('[data-section-modal]');
    if (!modal) return;

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('[data-section-modal-first]')?.focus(), 30);
}

function closeSectionModal() {
    const modal = document.querySelector('[data-section-modal]');
    if (!modal || modal.hidden) return;

    modal.hidden = true;
    document.body.style.overflow = '';
    document.querySelector('[data-open-section-modal]')?.focus();
}

function openSectionEditModal(sectionId) {
    const modal = document.querySelector(`[data-section-edit-modal="${sectionId}"]`);
    if (!modal) return;

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('[data-section-edit-first]')?.focus(), 30);
}

async function closeSectionEditModal(modal = document.querySelector('[data-section-edit-modal]:not([hidden])')) {
    if (!modal || modal.hidden) return;

    const form = modal.querySelector('.js-autosave-form');
    if (form && formHasAutosaveChanges(form)) {
        await autosaveDirtyForms();
    }

    const sectionId = modal.dataset.sectionEditModal;
    modal.hidden = true;
    document.body.style.overflow = '';
    document.querySelector(`[data-open-section-edit-modal="${sectionId}"]`)?.focus();
}

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
    if (!confirm(button.dataset.message || 'ລຶບລາຍການນີ້?')) return;

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

function applyExpenseAccountFilter(filter = activeAccountFilter) {
    activeAccountFilter = filter;

    document.querySelectorAll('.es-filter-btn').forEach(button => {
        button.classList.toggle('is-active', button.dataset.accountFilter === activeAccountFilter);
    });

    document.querySelectorAll('.js-default-account-row').forEach(row => {
        const state = row.dataset.accountState || 'linked';
        row.classList.toggle('is-filter-hidden', activeAccountFilter !== 'all' && state !== activeAccountFilter);
    });

    document.querySelectorAll('.es-account-row').forEach(accountRow => {
        const visibleRows = Array.from(accountRow.querySelectorAll('.js-default-account-row'))
            .filter(row => !row.classList.contains('is-filter-hidden'));
        const hide = activeAccountFilter !== 'all' && visibleRows.length === 0;
        accountRow.classList.toggle('is-filter-hidden', hide);
        accountRow.previousElementSibling?.classList.toggle('is-filter-hidden', hide);
    });
}

function jumpToDefaultAccountRow(rowId) {
    const row = document.querySelector(`.js-default-account-row[data-row="${rowId}"]`);
    if (!row) return;

    const sectionPanel = row.closest('.js-section-panel');
    if (sectionPanel?.dataset.sectionPanel) {
        selectExpenseStructureSection(sectionPanel.dataset.sectionPanel);
    }

    row.closest('details')?.setAttribute('open', 'open');
    row.classList.remove('is-filter-hidden');
    row.scrollIntoView({behavior: 'smooth', block: 'center'});
    row.classList.add('is-focus-pulse');
    setTimeout(() => row.classList.remove('is-focus-pulse'), 1500);
}

function updateDefaultAccountBadge(badge, linked, total) {
    if (!badge) return;

    const complete = linked === total;
    badge.textContent = `${linked}/${total} ເຊື່ອມແລ້ວ`;
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
        setDefaultAccountStatus(row, 'ເລືອກຈາກລາຍຊື່', 'error');
        input.focus();
        return;
    }

    hidden.value = account?.id || '';
    setDefaultAccountStatus(row, 'ກຳລັງບັນທຶກ...', 'warning');

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
        row.dataset.accountState = data.row.chart_of_account_id ? 'linked' : 'unlinked';
        setDefaultAccountStatus(row, data.row.chart_of_account_id ? 'ເຊື່ອມແລ້ວ' : 'ຍັງບໍ່ເຊື່ອມ', data.row.chart_of_account_id ? 'linked' : 'idle');
        refreshDefaultAccountSummary(row);
        applyExpenseAccountFilter();
    } catch (error) {
        setDefaultAccountStatus(row, 'ບັນທຶກບໍ່ສຳເລັດ', 'error');
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
        showAutosaveStatus(forms.length === 1 ? 'ບັນທຶກແລ້ວ' : `ບັນທຶກ ${forms.length} ຈຸດແລ້ວ`);
    } catch (error) {
        showAutosaveStatus('ບັນທຶກບາງຈຸດບໍ່ສຳເລັດ', false);
    }
}

function queueAutosave(input) {
    const form = input.closest('.js-autosave-form, .js-autosave-row');
    if (!form || !hasAutosaveChange(input)) return;

    clearTimeout(autosaveTimers.get(document));
    autosaveTimers.set(document, setTimeout(autosaveDirtyForms, 450));
}

document.querySelectorAll('.js-autosave-form, .js-autosave-row').forEach(snapshotAutosaveForm);

document.addEventListener('click', async (event) => {
    if (event.target.closest('[data-open-section-modal]')) {
        openSectionModal();
        return;
    }

    if (event.target.closest('[data-close-section-modal]')) {
        closeSectionModal();
        return;
    }

    if (event.target.matches('[data-section-modal]')) {
        closeSectionModal();
        return;
    }

    const editButton = event.target.closest('[data-open-section-edit-modal]');
    if (editButton) {
        openSectionEditModal(editButton.dataset.openSectionEditModal);
        return;
    }

    const editCloseButton = event.target.closest('[data-close-section-edit-modal]');
    if (editCloseButton) {
        await closeSectionEditModal(editCloseButton.closest('[data-section-edit-modal]'));
        return;
    }

    if (event.target.matches('[data-section-edit-modal]')) {
        await closeSectionEditModal(event.target);
        return;
    }

    const filterButton = event.target.closest('.es-filter-btn');
    if (filterButton) {
        applyExpenseAccountFilter(filterButton.dataset.accountFilter || 'all');
        return;
    }

    const warningItem = event.target.closest('.es-warning-item');
    if (warningItem) {
        jumpToDefaultAccountRow(warningItem.dataset.jumpDefaultRow);
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('.js-autosave-form, .js-autosave-source-form');
    if (!form) return;

    event.preventDefault();
    autosaveDirtyForms();
});

document.addEventListener('keydown', async (event) => {
    if (event.key === 'Escape') {
        const editModal = document.querySelector('[data-section-edit-modal]:not([hidden])');
        if (editModal) {
            await closeSectionEditModal(editModal);
            return;
        }

        closeSectionModal();
        return;
    }

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
