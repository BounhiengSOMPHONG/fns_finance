@extends('layouts.admin')

@section('title', 'ລາຍຈ່າຍ ' . $planningYear->year)
@section('page-title', 'ລາຍຈ່າຍ ' . $planningYear->year)

@section('content')
@php
    $fieldSettingsById = $fieldSettings;

    $patternsPayload = $patterns->mapWithKeys(function ($pattern) use ($fieldSettingsById) {
        $fields = $pattern->fields->map(function ($field) use ($fieldSettingsById) {
            $setting = $fieldSettingsById->get($field->id);

            return [
                'id' => $field->id,
                'key' => $field->field_key,
                'label' => $setting?->label ?? $field->default_label,
                'type' => $field->data_type,
                'order' => $setting?->display_order ?? $field->display_order,
                'required' => (bool) ($setting?->is_required ?? $field->is_required),
                'calculated' => (bool) $field->is_calculated,
                'active' => (bool) ($setting?->is_active ?? true),
                'default_value' => $setting?->default_value ?? $field->default_value,
            ];
        })->filter(fn ($field) => $field['active'])
          ->sortBy('order')
          ->values();

        return [$pattern->id => [
            'id' => $pattern->id,
            'key' => $pattern->key,
            'name' => $pattern->name,
            'description' => $pattern->description,
            'fields' => $fields,
        ]];
    });

    $rulesPayload = $rules->map(fn ($rule) => [
        'pattern_id' => $rule->pattern_id,
        'section_id' => $rule->section_id,
        'subsection_id' => $rule->subsection_id,
        'target_field_key' => $rule->target_field_key,
        'formula' => $rule->formula,
    ])->values();

    $sectionsPayload = $sections->map(function ($section) {
        return [
            'id' => $section->id,
            'code' => $section->code,
            'name' => $section->name,
            'summary_period_count' => $section->summary_period_count ?? 12,
            'subsections' => $section->subsections->map(fn ($subsection) => [
                'id' => $subsection->id,
                'parent_id' => $subsection->parent_id,
                'code' => $subsection->code,
                'name' => $subsection->name,
                'default_pattern_id' => $subsection->default_pattern_id,
                'summary_period_count' => $subsection->summary_period_count ?? 12,
            ])->values(),
        ];
    })->values();

    $rowsPayload = $expenseRows->map(fn ($row) => [
        'id' => $row->id,
        'section_id' => $row->section_id,
        'subsection_id' => $row->subsection_id,
        'pattern_id' => $row->pattern_id,
        'pattern_key' => $row->pattern?->key,
        'code' => $row->subsection?->code ?? $row->section?->code,
        'label' => $row->subsection?->name ?? $row->section?->name,
        'plan_detail' => $row->plan_detail,
        'detail' => $row->detail,
        'total' => $row->yearlyTotal(),
        'values' => $row->values->mapWithKeys(fn ($value) => [
            $value->field_key => $value->value_number ?? $value->value_text ?? $value->value_date ?? $value->value_boolean
        ]),
    ])->values();

    $chartAccountsPayload = $chartAccounts->map(fn ($account) => [
        'id' => $account->id,
        'code' => $account->account_code,
        'name' => $account->account_name,
    ])->values();

    $defaultRowsPayload = $defaultRows ?? collect();
@endphp

<div class="excel-plan">
    <section class="excel-overview" id="overviewPage">
        <div class="excel-overview-head">
            <div>
                <h2>ສະຫຼຸບລວມລາຍຈ່າຍ</h2>
                <p>ລວມລາຍຈ່າຍແຕ່ລະພາກ ປະຈຳປີ {{ $planningYear->year }}</p>
            </div>
            <strong id="overviewGrandTotal">0</strong>
        </div>
        <div id="overviewSummary" class="excel-summary-wrap"></div>
        <div id="overviewSectionSummaries" class="excel-preview-sections"></div>
    </section>

    <div class="excel-section-nav">
        <button type="button" id="prevSection" class="excel-nav-btn">
            <span>&larr;</span>
            <span>ກ່ອນ</span>
        </button>
        <div id="sectionTabs" class="excel-tabs"></div>
        <button type="button" id="nextSection" class="excel-nav-btn">
            <span>ຕໍ່ໄປ</span>
            <span>&rarr;</span>
        </button>
    </div>
    <section class="excel-sheet">
        <div class="excel-section-head">
            <div>
                <h2 id="sectionTitle">-</h2>
                <p id="sectionMeta">-</p>
            </div>
            <div class="excel-section-total">
                <span>ລວມພາກນີ້</span>
                <strong id="sectionTotal">0</strong>
            </div>
        </div>

        <div id="subsectionSheets" class="excel-subsections"></div>
    </section>
</div>

<style>
    .excel-plan { display:flex; flex-direction:column; gap:1rem; }
    .excel-section-total span { display:block; color:var(--fns-gray-400); font-size:.7rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .excel-overview {
        background:#fff;
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        overflow:hidden;
        box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .excel-overview-head {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        padding:1rem 1.15rem;
        border-bottom:1px solid var(--fns-gray-200);
        background:#fbfbfc;
    }
    .excel-overview-head h2 { margin:0; color:var(--fns-navy); font-size:1.1rem; font-weight:900; }
    .excel-overview-head p { margin:.25rem 0 0; color:var(--fns-gray-500); font-size:.82rem; }
    .excel-overview-head strong {
        color:var(--fns-navy);
        font-family:'Cinzel',serif;
        font-size:1.25rem;
        white-space:nowrap;
    }
    .excel-preview-sections { padding:1rem; display:flex; flex-direction:column; gap:1.1rem; }
    .excel-preview-block { overflow:auto; }
    .excel-preview-title {
        margin:.2rem 0 .55rem;
        color:#061226;
        font-size:1rem;
        font-weight:900;
    }
    .is-hidden { display:none !important; }
    .excel-section-nav {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:start;
        gap:.45rem;
        padding:.35rem;
        background:#fff;
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        box-shadow:0 1px 8px rgba(26,39,68,.04);
    }
    .excel-nav-btn {
        border:1px solid var(--fns-gray-200);
        border-radius:6px;
        background:#f8fafc;
        color:var(--fns-navy);
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.35rem;
        min-width:74px;
        min-height:36px;
        padding:0 .65rem;
        font-family:inherit;
        font-size:.72rem;
        font-weight:900;
        cursor:pointer;
    }
    .excel-nav-btn:hover:not(:disabled) { border-color:var(--fns-gold); background:#fffdf4; color:#111b33; }
    .excel-nav-btn:disabled { cursor:not-allowed; opacity:.45; }
    .excel-structure-btn {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        color:var(--fns-navy);
        padding:.55rem .8rem;
        font-family:inherit;
        font-size:.8rem;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 2px 10px rgba(26,39,68,.04);
    }
    .excel-structure-btn:hover { border-color:var(--fns-gold); color:#111b33; }
    .excel-total-shortcut { background:var(--fns-gold); border-color:var(--fns-gold); color:#111b33; }
    .excel-total-shortcut:hover { background:#dcb236; border-color:#dcb236; }
    .excel-tabs {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(190px, 1fr));
        gap:.4rem;
    }
    .excel-tab {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:center;
        gap:.45rem;
        min-height:42px;
        border:1px solid #e4e8f0; background:#fff; color:var(--fns-navy); border-radius:7px;
        padding:.42rem .52rem; font-family:inherit; text-align:left; cursor:pointer;
        overflow:hidden;
    }
    .excel-tab:hover { border-color:#d4d9e4; background:#fbfcfe; }
    .excel-tab.active { background:#d2a112; border-color:#d2a112; color:#061226; box-shadow:0 3px 10px rgba(201,153,26,.18); }
    .excel-tab-code {
        display:inline-flex; align-items:center; justify-content:center; min-width:2.25rem;
        border-radius:6px; background:#eef2f7; padding:.22rem .36rem; font-weight:900; font-size:.76rem;
    }
    .excel-tab-name {
        min-width:0;
        font-weight:900;
        font-size:.72rem;
        line-height:1.25;
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
    .excel-tab-name small {
        display:block; margin-top:.08rem; color:var(--fns-gray-500); font-size:.63rem; font-weight:800;
    }
    .excel-tab-total { color:var(--fns-navy); font-variant-numeric:tabular-nums; font-weight:900; font-size:.72rem; white-space:nowrap; }
    .excel-tab.active .excel-tab-code { background:rgba(255,255,255,.42); }
    .excel-tab.active .excel-tab-name small { color:#3b3218; }
    .excel-sheet { background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px; overflow:hidden; box-shadow:0 2px 12px rgba(26,39,68,.05); }
    .excel-section-head {
        display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;
        padding:1rem 1.15rem; border-bottom:1px solid var(--fns-gray-200); background:#fbfbfc;
    }
    .excel-section-head h2 { margin:0; color:var(--fns-navy); font-size:1.15rem; }
    .excel-section-head p { margin:.25rem 0 0; color:var(--fns-gray-500); font-size:.82rem; }
    .excel-section-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.55rem; margin-left:auto; }
    .excel-section-total { text-align:right; min-width:160px; }
    .excel-section-total strong { color:var(--fns-navy); font-family:'Cinzel',serif; font-size:1.2rem; }
    .excel-summary-wrap { padding:1rem 1rem 0; overflow:auto; }
    .excel-summary-table { width:100%; min-width:820px; border-collapse:collapse; font-size:.82rem; }
    .excel-summary-table th { background:#172642; color:#fff; border:1px solid #172642; padding:.45rem .55rem; text-align:center; font-weight:900; }
    .excel-summary-table td { border:1px solid #111827; padding:.35rem .45rem; background:#fff; }
    .excel-summary-table tfoot td { background:#f7f8fa; font-weight:900; }
    .excel-summary-highlight { background:#fffb00 !important; font-weight:900; }
    .excel-summary-count-input {
        width:4.5rem;
        border:1px solid transparent;
        background:transparent;
        color:inherit;
        font:inherit;
        font-variant-numeric:tabular-nums;
        text-align:right;
        padding:.2rem .3rem;
    }
    .excel-summary-count-input:focus {
        border-color:#2563eb;
        background:#fff;
        outline:0;
    }
    .excel-subsections { padding:1rem; display:flex; flex-direction:column; gap:1.3rem; }
    .excel-parent-block { display:flex; flex-direction:column; gap:.85rem; }
    .excel-parent-title { padding:.2rem .1rem; }
    .excel-parent-title h3 { margin:0; color:#061226; font-size:1.15rem; font-weight:900; line-height:1.35; }
    .excel-block { border:1px solid #d8dce5; border-radius:6px; overflow:hidden; background:#fff; }
    .excel-block.is-collapsed .excel-block-title { border-bottom:0; }
    .excel-block.is-collapsed .excel-block-body { display:none; }
    .excel-block-title { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:.8rem .95rem; border-bottom:1px solid #d8dce5; background:#fff; }
    .excel-block-title-main { display:flex; align-items:flex-start; gap:.65rem; min-width:0; }
    .excel-block-title h3 { margin:0; color:#061226; font-size:1rem; line-height:1.35; font-weight:900; }
    .excel-block-title p { margin:.3rem 0 0; color:var(--fns-gray-500); font-size:.75rem; }
    .excel-collapse-btn {
        flex:0 0 auto;
        width:2rem;
        height:2rem;
        border:1px solid var(--fns-gray-200);
        border-radius:6px;
        background:#f8fafc;
        color:var(--fns-navy);
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-family:inherit;
        font-size:1rem;
        font-weight:900;
        line-height:1;
        cursor:pointer;
    }
    .excel-collapse-btn:hover { border-color:var(--fns-gold); background:#fffdf4; color:#111b33; }
    .excel-collapse-icon { transform:rotate(90deg); transition:transform .16s ease; }
    .excel-block.is-collapsed .excel-collapse-icon { transform:rotate(0deg); }
    .excel-table-wrap { overflow:auto; }
    .excel-table { width:100%; min-width:880px; border-collapse:collapse; font-size:.82rem; }
    .excel-table th {
        background:#172642; color:#fff; border:1px solid #172642; padding:.5rem .55rem;
        text-align:center; font-weight:900; white-space:nowrap;
    }
    .excel-table td { border:1px solid #d8dce5; padding:.38rem .45rem; vertical-align:middle; background:#fff; }
    .excel-table tfoot td { background:#f7f8fa; font-weight:900; }
    .excel-seq { width:54px; text-align:center; font-weight:800; color:var(--fns-navy); }
    .excel-name { min-width:260px; }
    .excel-number { text-align:right; font-variant-numeric:tabular-nums; }
    .excel-input {
        width:100%; min-width:90px; border:0; outline:0; background:transparent; padding:.2rem .1rem;
        font:inherit; color:var(--fns-navy);
    }
    .excel-account-search { min-width:340px; }
    .excel-money-input { text-align:right; font-variant-numeric:tabular-nums; }
    .excel-delete { border:0; background:transparent; color:#dc2626; font-size:1rem; cursor:pointer; line-height:1; }
    .excel-readonly-value { color:#111827; font-weight:800; line-height:1.35; }
    .excel-readonly-muted { color:#64748b; font-size:.76rem; line-height:1.35; }
    .excel-empty { color:var(--fns-gray-400); text-align:center; padding:.8rem; }
    .excel-unit { text-align:right; color:#111b33; font-weight:800; padding:.45rem .65rem; background:#f7f8fa; border-bottom:1px solid #d8dce5; }
    .excel-toast { position:fixed; right:1rem; bottom:1rem; z-index:10000; background:var(--fns-navy); color:#fff; border-radius:8px; padding:.75rem .9rem; box-shadow:0 18px 38px rgba(17,27,51,.22); font-size:.82rem; }
    @media (max-width:760px) {
        .excel-section-head { grid-template-columns:1fr; display:flex; flex-direction:column; }
        .excel-section-actions { width:100%; justify-content:flex-start; margin-left:0; }
        .excel-section-total { width:100%; text-align:left; }
        .excel-overview-head { flex-direction:column; }
        .excel-section-nav { grid-template-columns:auto auto; }
        .excel-tabs { grid-column:1 / -1; order:2; }
        .excel-nav-btn { min-height:34px; }
    }
</style>

<script>
const PLANNING_YEAR_ID = @json($planningYear->id);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const SECTIONS = @json($sectionsPayload);
const PATTERNS = @json($patternsPayload);
const RULES = @json($rulesPayload);
const CHART_ACCOUNTS = @json($chartAccountsPayload);
const DEFAULT_ROWS = @json($defaultRowsPayload);
let ROWS = @json($rowsPayload);
let selectedSectionId = SECTIONS[0]?.id || null;
let lastInputSectionId = SECTIONS[0]?.id || null;
const collapsedSubsections = new Set(SECTIONS.flatMap(section => section.subsections.map(subsection => Number(subsection.id))));
const fmt = new Intl.NumberFormat('de-DE', { maximumFractionDigits: 0 });

function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[char]));
}

function numberValue(value) {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : 0;
    }

    const raw = String(value ?? '').trim().replace(/\s/g, '');
    if (raw === '') return 0;

    const normalized = /^[+-]?\d+\.\d{1,2}$/.test(raw)
        ? raw
        : raw.replace(/[.,]/g, '');
    const n = Number(normalized);
    return Number.isFinite(n) ? n : 0;
}

function moneyInputValue(value) {
    if (value === null || value === undefined || value === '') return '';
    const numeric = numberValue(value);
    return numeric ? fmt.format(numeric) : '0';
}

function codeSortKey(code) {
    return String(code || '').split('.').map(part => String(Number(part) || 0).padStart(4, '0')).join('.');
}

function sortSubsections(subsections) {
    return [...subsections].sort((a, b) => codeSortKey(a.code).localeCompare(codeSortKey(b.code)));
}

function rowsFor(sectionId, subsectionId = null) {
    return ROWS.filter(row => Number(row.section_id) === Number(sectionId) && (subsectionId === null || Number(row.subsection_id) === Number(subsectionId)));
}

function totalFor(sectionId, subsectionId = null) {
    return rowsFor(sectionId, subsectionId).reduce((sum, row) => sum + rowTotal(row), 0);
}

function childSubsections(section, subsectionId) {
    const children = sortSubsections(section.subsections.filter(subsection => Number(subsection.parent_id) === Number(subsectionId)));
    return children.flatMap(child => [child, ...childSubsections(section, child.id)]);
}

function summaryRowsFor(section, subsection) {
    const ids = [subsection.id, ...childSubsections(section, subsection.id).map(child => child.id)];
    return ROWS.filter(row => Number(row.section_id) === Number(section.id) && ids.some(id => Number(id) === Number(row.subsection_id)));
}

function topLevelSubsections(section) {
    return sortSubsections(section.subsections.filter(subsection => subsection.parent_id === null));
}

function summaryPeriodCount(item) {
    const count = numberValue(item?.summary_period_count);
    return count > 0 ? count : 12;
}

function subsectionSummaryValues(section, subsection) {
    const directRows = rowsFor(section.id, subsection.id);
    const rows = summaryRowsFor(section, subsection);
    const rowTotalValue = rows.reduce((sum, row) => sum + rowTotal(row), 0);
    const periodCount = summaryPeriodCount(subsection);
    const rowMonthly = periodCount ? rowTotalValue / periodCount : rowTotalValue;

    return {
        monthly: rowMonthly,
        qty: periodCount,
        total: rowTotalValue,
        monthlyText: fmt.format(rowMonthly),
        qtyText: fmt.format(periodCount),
        note: summaryNote(rows),
    };
}

function sectionSummaryValues(section) {
    return topLevelSubsections(section).reduce((summary, subsection) => {
        const values = subsectionSummaryValues(section, subsection);
        summary.monthly += values.monthly;
        summary.total += values.total;
        return summary;
    }, {monthly: 0, total: 0});
}

function renderOverviewSummary() {
    const overviewValues = SECTIONS.reduce((summary, section) => {
        const values = sectionSummaryValues(section);
        summary.total += values.total;
        summary.monthly += values.total / summaryPeriodCount(section);
        return summary;
    }, {monthly: 0, total: 0});
    const grandTotal = overviewValues.total;
    const monthlyGrandTotal = overviewValues.monthly;

    document.getElementById('overviewGrandTotal').textContent = fmt.format(grandTotal);
    document.getElementById('overviewSummary').innerHTML = `
        <table class="excel-summary-table">
            <thead>
                <tr>
                    <th style="width:54px;">ລ/ດ</th>
                    <th>ລາຍການ</th>
                    <th style="width:95px;">ອ້າງອີງ</th>
                    <th style="width:140px;">ຕໍ່ເດືອນ</th>
                    <th style="width:95px;">ຈ/ນເດືອນ</th>
                    <th style="width:155px;">ຕໍ່ປີ</th>
                    <th style="width:150px;">ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                ${SECTIONS.map((section, index) => {
                    const values = sectionSummaryValues(section);
                    const periodCount = summaryPeriodCount(section);
                    const monthly = values.total / periodCount;

                    return `
                        <tr>
                            <td class="excel-seq">${index + 1}</td>
                            <td>${esc(section.name)}</td>
                            <td style="text-align:center;">${esc(section.code)}</td>
                            <td class="excel-number">${fmt.format(monthly)}</td>
                            <td class="excel-number">
                                <input class="excel-summary-count-input" type="text" inputmode="decimal" value="${moneyInputValue(periodCount)}" data-summary-section="${section.id}">
                            </td>
                            <td class="excel-number excel-summary-highlight">${fmt.format(values.total)}</td>
                            <td></td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="excel-number">${fmt.format(monthlyGrandTotal)}</td>
                    <td></td>
                    <td class="excel-number excel-summary-highlight">${fmt.format(grandTotal)}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    `;
    document.getElementById('overviewSectionSummaries').innerHTML = SECTIONS.map(section => {
        return `
            <div class="excel-preview-block">
                <h3 class="excel-preview-title">${esc(section.code)} ${esc(section.name)}</h3>
                ${renderSectionSummary(section, topLevelSubsections(section))}
            </div>
        `;
    }).join('');
    bindSummaryCountEvents();
}

function renderTabs() {
    document.getElementById('sectionTabs').innerHTML = SECTIONS.map(section => `
        <button type="button" class="excel-tab ${Number(section.id) === Number(selectedSectionId) ? 'active' : ''}" data-section="${section.id}">
            <span class="excel-tab-code">${esc(section.code)}</span>
            <span class="excel-tab-name">
                ${esc(section.name)}
                <small>${section.subsections.filter(subsection => !section.subsections.some(child => Number(child.parent_id) === Number(subsection.id))).length} ຫົວຂໍ້ຍ່ອຍ</small>
            </span>
            <span class="excel-tab-total">${fmt.format(sectionSummaryValues(section).total)}</span>
        </button>
    `).join('');
    syncSectionNavButtons();
}

function selectedSectionIndex() {
    if (selectedSectionId === 'overview') return SECTIONS.length;
    return SECTIONS.findIndex(section => Number(section.id) === Number(selectedSectionId));
}

function syncSectionNavButtons() {
    const index = selectedSectionIndex();
    document.getElementById('prevSection').disabled = index <= 0;
    document.getElementById('nextSection').disabled = index < 0 || index >= SECTIONS.length;
}

function moveSection(direction) {
    const index = selectedSectionIndex();
    const pages = [...SECTIONS.map(section => Number(section.id)), 'overview'];
    const next = pages[index + direction];
    if (next === undefined) return;

    if (selectedSectionId !== 'overview') lastInputSectionId = selectedSectionId;
    selectedSectionId = next;
    renderTabs();
    renderSheet();
}

function activeRule(sectionId, subsectionId, patternId) {
    return RULES.find(rule =>
        Number(rule.pattern_id) === Number(patternId) &&
        (rule.section_id === null || Number(rule.section_id) === Number(sectionId)) &&
        (rule.subsection_id === null || Number(rule.subsection_id) === Number(subsectionId))
    );
}

function calculateFormula(formula, values) {
    return String(formula || '').split('+').reduce((sum, addend) => {
        const product = addend.split('*').reduce((result, token) => {
            const key = token.trim();
            if (!key) return result;
            return result * (Number.isFinite(Number(key)) ? Number(key) : numberValue(values[key]));
        }, 1);
        return sum + product;
    }, 0);
}

function calculatedTotalForPattern(patternKey, values = {}) {
    const number = key => numberValue(values[key]);

    switch (patternKey) {
        case 'monthly':
            return number('amount_per_month') * number('month_count');
        case 'unit_quantity':
            return number('unit_price') * number('quantity');
        case 'unit_quantity_frequency':
            return number('unit_price') * number('quantity') * number('times_per_year');
        case 'frequency_based':
            return number('unit_price') * number('quantity') * number('frequency_count');
        case 'event_based':
            return number('unit_price') * number('event_count') * number('people_count');
        default:
            return 0;
    }
}

function rowTotal(row) {
    const rule = activeRule(row.section_id, row.subsection_id, row.pattern_id);

    if (rule) {
        return calculateFormula(rule.formula, row.values || {});
    }

    const storedTotal = numberValue(row.values?.yearly_total ?? row.total);
    return storedTotal > 0 ? storedTotal : calculatedTotalForPattern(row.pattern_key, row.values || {});
}

function fieldsForPattern(pattern) {
    return (pattern?.fields || []).filter(field => field.active)
      .sort((a, b) => Number(a.order || 0) - Number(b.order || 0));
}

function visibleFields(pattern, subsection) {
    return fieldsForPattern(pattern).filter(field => field.key !== 'reference');
}

function rowDisplayValue(row, field) {
    if (field.key === 'item_name') return row.values?.item_name ?? row.plan_detail;
    if (field.key === 'note') return row.values?.note ?? row.detail;
    return row.values?.[field.key] ?? '';
}

function renderSheet() {
    const isOverview = selectedSectionId === 'overview';
    document.getElementById('overviewPage').classList.toggle('is-hidden', !isOverview);
    document.querySelector('.excel-section-nav').classList.toggle('is-hidden', isOverview);
    document.querySelector('.excel-sheet').classList.toggle('is-hidden', isOverview);
    document.getElementById('backFromTotalPage')?.classList.toggle('is-hidden', !isOverview);

    if (isOverview) {
        renderOverviewSummary();
        const grandTotalEl = document.getElementById('grandTotal');
        if (grandTotalEl) {
            grandTotalEl.textContent = fmt.format(SECTIONS.reduce((sum, section) => sum + sectionSummaryValues(section).total, 0));
        }
        syncSectionNavButtons();
        return;
    }

    const section = SECTIONS.find(item => Number(item.id) === Number(selectedSectionId));
    if (!section) return;
    lastInputSectionId = Number(section.id);

    renderOverviewSummary();
    document.getElementById('sectionTitle').textContent = `${section.code} ${section.name}`;
    const finalSubsections = sortSubsections(section.subsections.filter(subsection =>
        !section.subsections.some(child => Number(child.parent_id) === Number(subsection.id))
    ));
    document.getElementById('sectionMeta').textContent = `${finalSubsections.length} ຫົວຂໍ້ຍ່ອຍ`;
    document.getElementById('sectionTotal').textContent = fmt.format(sectionSummaryValues(section).total);
    const grandTotalEl = document.getElementById('grandTotal');
    if (grandTotalEl) {
        grandTotalEl.textContent = fmt.format(SECTIONS.reduce((sum, item) => sum + sectionSummaryValues(item).total, 0));
    }
    document.getElementById('subsectionSheets').innerHTML = topLevelSubsections(section)
        .map(subsection => renderSubsectionGroup(section, subsection))
        .join('');
    bindSheetEvents();
}

function summaryValue(rows, keys) {
    return rows.reduce((sum, row) => {
        const firstValue = keys.map(key => row.values?.[key]).find(value => value !== undefined && value !== null && value !== '');
        return sum + numberValue(firstValue);
    }, 0);
}

function summaryNote(rows) {
    const notes = [...new Set(rows
        .map(row => String(row.values?.note || row.detail || '').trim())
        .filter(Boolean)
    )];

    if (notes.length <= 2) {
        return notes.join(', ');
    }

    return `${notes.slice(0, 2).join(', ')} +${notes.length - 2}`;
}

function renderSectionSummary(section, subsections) {
    const sectionValues = sectionSummaryValues(section);
    const subtotal = sectionValues.total;
    const monthlyTotal = subsections.reduce((sum, subsection) => {
        return sum + subsectionSummaryValues(section, subsection).monthly;
    }, 0);

    return `
        <table class="excel-summary-table">
            <thead>
                <tr>
                    <th style="width:54px;">ລ/ດ</th>
                    <th>ລາຍການ</th>
                    <th style="width:95px;">ອ້າງອີງ</th>
                    <th style="width:130px;">ຕໍ່ເດືອນ</th>
                    <th style="width:90px;">ຈ/ນ</th>
                    <th style="width:150px;">ໝົດປີ</th>
                    <th style="width:130px;">ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                ${subsections.map((subsection, index) => {
                    const summary = subsectionSummaryValues(section, subsection);

                    return `
                        <tr>
                            <td class="excel-seq">${index + 1}</td>
                            <td>${esc(subsection.name)}</td>
                            <td style="text-align:center;">${esc(subsection.code)}</td>
                            <td class="excel-number">${summary.monthlyText}</td>
                            <td class="excel-number">
                                <input class="excel-summary-count-input" type="text" inputmode="decimal" value="${esc(summary.qtyText)}" data-summary-subsection="${subsection.id}">
                            </td>
                            <td class="excel-number excel-summary-highlight">${fmt.format(summary.total)}</td>
                            <td>${esc(summary.note)}</td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="excel-number">${fmt.format(monthlyTotal)}</td>
                    <td></td>
                    <td class="excel-number excel-summary-highlight">${fmt.format(subtotal)}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    `;
}

function renderSubsectionGroup(section, subsection) {
    const children = sortSubsections(section.subsections.filter(child => Number(child.parent_id) === Number(subsection.id)));
    if (!children.length) {
        if (!rowsFor(section.id, subsection.id).length && !(DEFAULT_ROWS[subsection.code] || []).length) {
            return '';
        }

        return renderSubsection(section, subsection);
    }

    const hasOwnRows = rowsFor(section.id, subsection.id).length || (DEFAULT_ROWS[subsection.code] || []).length;

    return `
        <div class="excel-parent-block">
            <div class="excel-parent-title">
                <h3>${esc(subsection.code)} &nbsp;${esc(subsection.name)}</h3>
            </div>
            ${hasOwnRows ? renderSubsection(section, subsection) : ''}
            ${children.map(child => renderSubsection(section, child)).join('')}
        </div>
    `;
}

function renderSubsection(section, subsection) {
    const pattern = PATTERNS[subsection.default_pattern_id] || Object.values(PATTERNS)[0];
    const fields = visibleFields(pattern, subsection);
    const normalFields = fields.filter(field => field.key !== 'yearly_total');
    const totalField = fields.find(field => field.key === 'yearly_total') || fields.find(field => field.calculated);
    const rows = rowsFor(section.id, subsection.id);
    const subtotal = totalFor(section.id, subsection.id);
    const isCollapsed = collapsedSubsections.has(Number(subsection.id));
    return `
        <article class="excel-block ${isCollapsed ? 'is-collapsed' : ''}" data-section="${section.id}" data-subsection="${subsection.id}" data-pattern="${pattern?.id || ''}">
            <div class="excel-block-title">
                <div class="excel-block-title-main">
                    <button type="button" class="excel-collapse-btn" data-collapse-subsection="${subsection.id}" aria-expanded="${isCollapsed ? 'false' : 'true'}" aria-label="${isCollapsed ? 'ເປີດລາຍລະອຽດ' : 'ພັບລາຍລະອຽດ'} ${esc(subsection.code)} ${esc(subsection.name)}">
                        <span class="excel-collapse-icon">›</span>
                    </button>
                    <div>
                        <h3>${esc(subsection.code)} &nbsp;${esc(subsection.name)}</h3>
                        <p>${esc(pattern?.name || 'No pattern')} ${activeRule(section.id, subsection.id, pattern?.id)?.formula ? '· ' + esc(activeRule(section.id, subsection.id, pattern?.id).formula) : ''}</p>
                    </div>
                </div>
            </div>
            <div class="excel-block-body">
                <div class="excel-unit">ໜ່ວຍ: ກີບ</div>
                <div class="excel-table-wrap">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th class="excel-seq">ລ/ດ</th>
                                ${normalFields.map(field => `<th>${esc(field.label)}</th>`).join('')}
                                ${totalField ? `<th>${esc(totalField.label)}</th>` : ''}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.length ? rows.map((row, index) => renderSavedRow(row, index + 1, normalFields, totalField)).join('') : `
                                <tr><td colspan="${normalFields.length + (totalField ? 2 : 1)}" class="excel-empty">ຍັງບໍ່ມີລາຍການ</td></tr>
                            `}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="${normalFields.length + 1}" class="excel-number">ລວມ</td>
                                ${totalField ? `<td class="excel-number">${fmt.format(subtotal)}</td>` : ''}
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </article>
    `;
}

function renderSavedRow(row, index, normalFields, totalField) {
    return `
        <tr class="excel-saved-row" data-row="${row.id}" data-section="${row.section_id}" data-subsection="${row.subsection_id}" data-pattern="${row.pattern_id}">
            <td class="excel-seq">${index}</td>
            ${normalFields.map(field => `
                <td class="${field.type === 'number' ? 'excel-number' : field.key === 'item_name' ? 'excel-name' : ''}">
                    ${['item_name', 'reference'].includes(field.key)
                        ? renderLockedField(field, rowDisplayValue(row, field))
                        : `<input class="excel-input ${field.type === 'number' ? 'excel-money-input' : ''}" name="${esc(field.key)}" data-type="${esc(field.type)}" type="${field.type === 'number' ? 'text' : field.type === 'date' ? 'date' : 'text'}"
                                  inputmode="${field.type === 'number' ? 'decimal' : 'text'}" value="${esc(field.type === 'number' ? moneyInputValue(rowDisplayValue(row, field)) : rowDisplayValue(row, field))}" placeholder="${esc(field.label)}" ${field.required ? 'required' : ''}>`}
                </td>
            `).join('')}
            ${totalField ? `<td><input class="excel-input excel-money-input" name="${esc(totalField.key)}" data-type="number" data-calculated="1" type="text" inputmode="decimal" value="${moneyInputValue(rowTotal(row))}" readonly></td>` : ''}
        </tr>
    `;
}

function renderLockedField(field, value = '') {
    return `
        <div class="${field.key === 'item_name' ? 'excel-readonly-value' : 'excel-readonly-muted'}">${esc(value || '-')}</div>
        <input type="hidden" name="${esc(field.key)}" data-type="${esc(field.type)}" value="${esc(value)}">
    `;
}

function findSubsection(subsectionId) {
    return SECTIONS.flatMap(section => section.subsections).find(subsection => Number(subsection.id) === Number(subsectionId));
}

function bindSheetEvents() {
    document.querySelectorAll('.excel-block').forEach(block => updateBlockTotal(block));

    document.querySelectorAll('.excel-saved-row input').forEach(input => {
        input.addEventListener('input', event => {
            if (event.target.dataset.type === 'number' && !event.target.readOnly) {
                event.target.value = moneyInputValue(event.target.value);
            }
            updateLineTotal(event.target.closest('tr'));
        });
    });

    document.querySelectorAll('.excel-saved-row input').forEach(input => {
        input.addEventListener('change', event => updateSavedRow(event.target.closest('.excel-saved-row')));
        input.addEventListener('keydown', event => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            event.target.blur();
        });
    });

    document.querySelectorAll('.excel-collapse-btn').forEach(button => {
        button.addEventListener('click', event => {
            const subsectionId = Number(event.currentTarget.dataset.collapseSubsection);

            if (collapsedSubsections.has(subsectionId)) {
                collapsedSubsections.delete(subsectionId);
            } else {
                collapsedSubsections.add(subsectionId);
            }

            renderSheet();
        });
    });
}

function lineValues(row) {
    const values = {};
    row.querySelectorAll('input, select').forEach(input => {
        if (input.name === '_token' || input.name === '_method') return;
        if (!input.name || input.name === 'chart_account_id' || input.name === 'chart_account_search') return;
        values[input.name] = input.dataset.type === 'number' ? numberValue(input.value) : input.value;
    });
    return values;
}

function updateSourceTotal(block, row) {
    if (!block || !row) return;
    const sectionId = Number(block.dataset.section);
    const subsectionId = Number(row.dataset.subsection || block.dataset.subsection);
    const patternId = Number(row.dataset.pattern || block.dataset.pattern);
    const rule = activeRule(sectionId, subsectionId, patternId);
    const values = lineValues(row);
    const pattern = PATTERNS[patternId];
    const total = rule
        ? calculateFormula(rule.formula, values)
        : calculatedTotalForPattern(pattern?.key, values);
    const totalInput = row.querySelector('input[name="yearly_total"]');
    if (totalInput) totalInput.value = moneyInputValue(total);
}

function updateBlockTotal(block) {
    block.querySelectorAll('.excel-saved-row').forEach(row => updateSourceTotal(block, row));
}

function updateLineTotal(row) {
    if (!row) return;
    const block = row.closest('.excel-block');
    updateSourceTotal(block, row);
}

async function updateSavedRow(row) {
    updateLineTotal(row);

    const rowId = Number(row.dataset.row);
    const values = lineValues(row);
    const planDetail = values.item_name || 'Expense row';

    const response = await fetch(`/head-of-finance/expense-plan-rows/${rowId}`, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({
            plan_detail: planDetail,
            detail: values.note || null,
            values,
        }),
    });

    const data = await response.json();
    if (!response.ok || !data.success) {
        toast('Could not update row');
        return;
    }

    ROWS = ROWS.map(item => Number(item.id) === rowId ? {
        ...item,
        ...data.entry,
        code: item.code,
        label: item.label,
    } : item);
    renderTabs();
    renderSheet();
    toast('Updated');
}

function bindSummaryCountEvents() {
    document.querySelectorAll('[data-summary-section], [data-summary-subsection]').forEach(input => {
        input.addEventListener('change', event => saveSummaryCount(event.target));
        input.addEventListener('keydown', event => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            event.target.blur();
        });
    });
}

async function saveSummaryCount(input) {
    const count = numberValue(input.value);
    if (count <= 0) {
        input.value = '12';
        toast('ຈ/ນ must be more than 0');
        return;
    }

    const sectionId = input.dataset.summarySection;
    const subsectionId = input.dataset.summarySubsection;
    const url = sectionId
        ? `/head-of-finance/expense/${PLANNING_YEAR_ID}/sections/${sectionId}/summary-settings`
        : `/head-of-finance/expense/${PLANNING_YEAR_ID}/subsections/${subsectionId}/summary-settings`;

    const response = await fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({summary_period_count: count}),
    });
    const data = await response.json();

    if (!response.ok || !data.success) {
        toast('Could not save ຈ/ນ');
        return;
    }

    if (sectionId) {
        const section = SECTIONS.find(item => Number(item.id) === Number(sectionId));
        if (section) section.summary_period_count = data.section.summary_period_count;
    } else {
        const subsection = SECTIONS.flatMap(section => section.subsections).find(item => Number(item.id) === Number(subsectionId));
        if (subsection) subsection.summary_period_count = data.subsection.summary_period_count;
    }

    renderTabs();
    renderSheet();
    toast('Saved');
}

function toast(message) {
    const el = document.createElement('div');
    el.className = 'excel-toast';
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 1800);
}

document.getElementById('sectionTabs').addEventListener('click', event => {
    const tab = event.target.closest('.excel-tab');
    if (!tab) return;
    selectedSectionId = Number(tab.dataset.section);
    renderTabs();
    renderSheet();
});

document.getElementById('prevSection').addEventListener('click', () => moveSection(-1));
document.getElementById('nextSection').addEventListener('click', () => moveSection(1));
document.getElementById('openTotalPage')?.addEventListener('click', () => {
    if (selectedSectionId !== 'overview') lastInputSectionId = selectedSectionId;
    selectedSectionId = 'overview';
    renderTabs();
    renderSheet();
    document.querySelector('.excel-plan')?.scrollIntoView({behavior: 'smooth', block: 'start'});
});
document.getElementById('backFromTotalPage')?.addEventListener('click', () => {
    selectedSectionId = lastInputSectionId || SECTIONS[0]?.id || null;
    renderTabs();
    renderSheet();
    document.querySelector('.excel-plan')?.scrollIntoView({behavior: 'smooth', block: 'start'});
});

renderTabs();
renderSheet();
</script>
@endsection
