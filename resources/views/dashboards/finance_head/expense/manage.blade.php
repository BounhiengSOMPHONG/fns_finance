@extends('layouts.admin')

@section('title', 'Expense planning ' . $planningYear->year)
@section('page-title', 'Expense planning ' . $planningYear->year)

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
            'subsections' => $section->subsections->map(fn ($subsection) => [
                'id' => $subsection->id,
                'parent_id' => $subsection->parent_id,
                'code' => $subsection->code,
                'name' => $subsection->name,
                'default_pattern_id' => $subsection->default_pattern_id,
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
@endphp

<div class="excel-plan">
    <div class="excel-toolbar">
        <a href="{{ route('head_of_finance.expense.index') }}" class="excel-back">
            <span>&larr;</span>
            Back
        </a>
        <div class="excel-title">
            <span>ແຜນລາຍຈ່າຍປະຈຳປີ</span>
            <strong>{{ $planningYear->year }}</strong>
        </div>
        <div class="excel-toolbar-actions">
            <button type="button" class="excel-structure-btn excel-total-shortcut" id="openTotalPage">ໜ້າສະຫຼຸບ</button>
            <button type="button" class="excel-structure-btn is-hidden" id="backFromTotalPage">ກັບໄປໜ້າປ້ອນ</button>
        </div>
        <div class="excel-grand">
            <span>ລວມທັງໝົດ</span>
            <strong id="grandTotal">0</strong>
        </div>
    </div>

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
        <button type="button" id="prevSection" class="excel-nav-btn">Previous</button>
        <div id="sectionTabs" class="excel-tabs"></div>
        <button type="button" id="nextSection" class="excel-nav-btn">Next</button>
    </div>
    <section class="excel-sheet">
        <div class="excel-section-head">
            <div>
                <h2 id="sectionTitle">-</h2>
                <p id="sectionMeta">-</p>
            </div>
            <div class="excel-section-actions">
                <button type="button" class="excel-structure-btn" id="openSectionModal">+ ເພີ່ມຫົວຂໍ້ຫຼັກ</button>
                <button type="button" class="excel-structure-btn" id="openSubsectionModal">+ ເພີ່ມຫົວຂໍ້ຍ່ອຍ</button>
            </div>
            <div class="excel-section-total">
                <span>ລວມພາກນີ້</span>
                <strong id="sectionTotal">0</strong>
            </div>
        </div>

        <div id="subsectionSheets" class="excel-subsections"></div>
    </section>
</div>

<div class="excel-modal" id="sectionModal" aria-hidden="true">
    <div class="excel-modal-panel" role="dialog" aria-modal="true" aria-labelledby="sectionModalTitle">
        <div class="excel-modal-head">
            <h2 id="sectionModalTitle">ເພີ່ມພາກ</h2>
            <button type="button" class="excel-modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.store') }}" class="excel-modal-body">
            @csrf
            <input type="hidden" name="planning_year_id" value="{{ $planningYear->id }}">
            <input type="hidden" name="description" value="">
            <input type="hidden" name="is_active" value="1">

            <div class="excel-modal-grid">
                <label>
                    <span>ລະຫັດ</span>
                    <input name="code" class="fns-input" placeholder="2.7" required>
                </label>
                <label>
                    <span>ລຳດັບ</span>
                    <input type="number" name="display_order" class="fns-input" min="0" max="999" value="{{ ($sections->max('display_order') ?? 0) + 1 }}" required>
                </label>
                <label class="excel-modal-wide">
                    <span>ຊື່ພາກ</span>
                    <input name="name" class="fns-input" placeholder="ຊື່ພາກ" required>
                </label>
            </div>

            <div class="excel-modal-actions">
                <button type="button" class="fns-btn fns-btn-secondary" data-close-modal>ຍົກເລີກ</button>
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<div class="excel-modal" id="subsectionModal" aria-hidden="true">
    <div class="excel-modal-panel" role="dialog" aria-modal="true" aria-labelledby="subsectionModalTitle">
        <div class="excel-modal-head">
            <h2 id="subsectionModalTitle">ເພີ່ມຫົວຂໍ້ຍ່ອຍ</h2>
            <button type="button" class="excel-modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" id="subsectionForm" class="excel-modal-body">
            @csrf
            <input type="hidden" name="description" value="">
            <input type="hidden" name="is_active" value="1">

            <div class="excel-modal-grid">
                <label class="excel-modal-wide">
                    <span>ພາກ</span>
                    <select id="subsectionSection" class="fns-input" required>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" data-url="{{ route('head_of_finance.settings.expense-structure.subsections.store', $section) }}">
                                {{ $section->code }} - {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>ລະຫັດ</span>
                    <input name="code" class="fns-input" placeholder="2.1.12" required>
                </label>
                <label>
                    <span>ລຳດັບ</span>
                    <input type="number" name="display_order" id="subsectionOrder" class="fns-input" min="0" max="999" required>
                </label>
                <label class="excel-modal-wide">
                    <span>ຊື່ຫົວຂໍ້ຍ່ອຍ</span>
                    <input name="name" class="fns-input" placeholder="ຊື່ຫົວຂໍ້ຍ່ອຍ" required>
                </label>
                <label>
                    <span>ຫົວຂໍ້ແມ່</span>
                    <select name="parent_id" id="subsectionParent" class="fns-input">
                        <option value="">No parent</option>
                    </select>
                </label>
                <label>
                    <span>Pattern</span>
                    <select name="default_pattern_id" class="fns-input">
                        <option value="">No pattern</option>
                        @foreach($patterns as $pattern)
                            <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="excel-modal-actions">
                <button type="button" class="fns-btn fns-btn-secondary" data-close-modal>ຍົກເລີກ</button>
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<style>
    .excel-plan { display:flex; flex-direction:column; gap:1rem; }
    .excel-toolbar {
        display:grid; grid-template-columns:auto 1fr auto auto; align-items:center; gap:1rem;
        background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px; padding:.8rem 1rem;
        box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .excel-back { display:inline-flex; align-items:center; gap:.45rem; color:var(--fns-navy); font-weight:800; font-size:.82rem; }
    .excel-back span { font-size:1.1rem; }
    .excel-title span, .excel-grand span, .excel-section-total span { display:block; color:var(--fns-gray-400); font-size:.7rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .excel-title strong { color:var(--fns-navy); font-size:1.05rem; }
    .excel-toolbar-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.55rem; }
    .excel-grand { min-width:180px; text-align:right; padding:.55rem .8rem; border-radius:8px; background:var(--fns-navy); color:#fff; }
    .excel-grand strong { display:block; color:var(--fns-gold-light); font-family:'Cinzel',serif; font-size:1.35rem; line-height:1.1; }
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
        align-items:stretch;
        gap:.65rem;
    }
    .excel-nav-btn {
        border:1px solid var(--fns-gray-200);
        border-radius:8px;
        background:#fff;
        color:var(--fns-navy);
        padding:0 .85rem;
        min-width:92px;
        font-family:inherit;
        font-size:.78rem;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 2px 10px rgba(26,39,68,.04);
    }
    .excel-nav-btn:hover:not(:disabled) { border-color:var(--fns-gold); color:#111b33; }
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
        grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
        gap:.55rem;
        padding-bottom:.2rem;
    }
    .excel-tab {
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:center;
        gap:.55rem;
        min-height:52px;
        border:1px solid var(--fns-gray-200); background:#fff; color:var(--fns-navy); border-radius:8px;
        padding:.55rem .65rem; font-family:inherit; text-align:left; cursor:pointer;
    }
    .excel-tab.active { background:var(--fns-gold); border-color:var(--fns-gold); color:#111b33; box-shadow:0 8px 18px rgba(201,153,26,.24); }
    .excel-tab-code {
        display:inline-flex; align-items:center; justify-content:center; min-width:2.7rem;
        border-radius:6px; background:#eef2f7; padding:.28rem .45rem; font-weight:900; font-size:.8rem;
    }
    .excel-tab-name { min-width:0; font-weight:900; font-size:.78rem; line-height:1.3; }
    .excel-tab-name small {
        display:block; margin-top:.15rem; color:var(--fns-gray-500); font-size:.68rem; font-weight:800;
    }
    .excel-tab-total { color:var(--fns-navy); font-variant-numeric:tabular-nums; font-weight:900; font-size:.78rem; white-space:nowrap; }
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
    .excel-subsections { padding:1rem; display:flex; flex-direction:column; gap:1.3rem; }
    .excel-parent-block { display:flex; flex-direction:column; gap:.85rem; }
    .excel-parent-title { padding:.2rem .1rem; }
    .excel-parent-title h3 { margin:0; color:#061226; font-size:1.15rem; font-weight:900; line-height:1.35; }
    .excel-block { border:1px solid #d8dce5; border-radius:6px; overflow:hidden; background:#fff; }
    .excel-block-title { padding:.8rem .95rem; border-bottom:1px solid #d8dce5; background:#fff; }
    .excel-block-title h3 { margin:0; color:#061226; font-size:1rem; line-height:1.35; font-weight:900; }
    .excel-block-title p { margin:.3rem 0 0; color:var(--fns-gray-500); font-size:.75rem; }
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
    .excel-add-row td { background:#fffdf7; }
    .excel-add { border:0; border-radius:6px; background:var(--fns-gold); color:#111b33; font-weight:900; padding:.45rem .75rem; cursor:pointer; white-space:nowrap; }
    .excel-save-line { border:0; border-radius:6px; background:var(--fns-navy); color:#fff; font-weight:900; padding:.42rem .6rem; cursor:pointer; white-space:nowrap; margin-right:.25rem; }
    .excel-delete { border:0; background:transparent; color:#dc2626; font-size:1rem; cursor:pointer; line-height:1; }
    .excel-empty { color:var(--fns-gray-400); text-align:center; padding:.8rem; }
    .excel-unit { text-align:right; color:#111b33; font-weight:800; padding:.45rem .65rem; background:#f7f8fa; border-bottom:1px solid #d8dce5; }
    .excel-toast { position:fixed; right:1rem; bottom:1rem; z-index:10000; background:var(--fns-navy); color:#fff; border-radius:8px; padding:.75rem .9rem; box-shadow:0 18px 38px rgba(17,27,51,.22); font-size:.82rem; }
    .excel-modal {
        position:fixed;
        inset:0;
        z-index:1000;
        display:none;
        align-items:center;
        justify-content:center;
        padding:1rem;
        background:rgba(15,23,42,.48);
    }
    .excel-modal.is-open { display:flex; }
    .excel-modal-panel {
        width:min(620px, 100%);
        max-height:calc(100vh - 2rem);
        overflow:auto;
        border:1px solid var(--fns-gray-200);
        border-radius:12px;
        background:#fff;
        box-shadow:0 24px 70px rgba(15,23,42,.28);
    }
    .excel-modal-head {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1rem 1.15rem;
        border-bottom:1px solid var(--fns-gray-200);
        background:#fbfbfc;
    }
    .excel-modal-head h2 { margin:0; color:var(--fns-navy); font-size:1rem; font-weight:900; }
    .excel-modal-close {
        border:0;
        background:transparent;
        color:var(--fns-gray-500);
        font-size:1.45rem;
        line-height:1;
        cursor:pointer;
    }
    .excel-modal-body { padding:1.15rem; }
    .excel-modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:.9rem; }
    .excel-modal-grid label span {
        display:block;
        margin-bottom:.35rem;
        color:var(--fns-gray-500);
        font-size:.76rem;
        font-weight:900;
    }
    .excel-modal-wide { grid-column:1 / -1; }
    .excel-modal-actions { display:flex; justify-content:flex-end; gap:.55rem; margin-top:1.25rem; }
    @media (max-width:760px) {
        .excel-toolbar, .excel-section-head { grid-template-columns:1fr; display:flex; flex-direction:column; }
        .excel-toolbar-actions { width:100%; justify-content:flex-start; }
        .excel-section-actions { width:100%; justify-content:flex-start; margin-left:0; }
        .excel-grand, .excel-section-total { width:100%; text-align:left; }
        .excel-overview-head { flex-direction:column; }
        .excel-section-nav { grid-template-columns:1fr 1fr; }
        .excel-tabs { grid-column:1 / -1; order:2; }
        .excel-nav-btn { min-height:40px; }
        .excel-modal-grid { grid-template-columns:1fr; }
        .excel-modal-wide { grid-column:auto; }
    }
</style>

<script>
const PLANNING_YEAR_ID = @json($planningYear->id);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const SECTIONS = @json($sectionsPayload);
const PATTERNS = @json($patternsPayload);
const RULES = @json($rulesPayload);
const CHART_ACCOUNTS = @json($chartAccountsPayload);
let ROWS = @json($rowsPayload);
let selectedSectionId = SECTIONS[0]?.id || null;
let lastInputSectionId = SECTIONS[0]?.id || null;
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

function rowsFor(sectionId, subsectionId = null) {
    return ROWS.filter(row => Number(row.section_id) === Number(sectionId) && (subsectionId === null || Number(row.subsection_id) === Number(subsectionId)));
}

function totalFor(sectionId, subsectionId = null) {
    return rowsFor(sectionId, subsectionId).reduce((sum, row) => sum + numberValue(row.total), 0);
}

function renderOverviewSummary() {
    const grandTotal = ROWS.reduce((sum, row) => sum + numberValue(row.total), 0);
    const monthlyGrandTotal = SECTIONS.reduce((sum, section) => sum + summaryValue(rowsFor(section.id), ['amount_per_month', 'unit_price']), 0);

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
                    const sectionRows = rowsFor(section.id);
                    const monthly = summaryValue(sectionRows, ['amount_per_month', 'unit_price']);
                    const months = summaryValue(sectionRows, ['month_count']);
                    const total = totalFor(section.id);

                    return `
                        <tr>
                            <td class="excel-seq">${index + 1}</td>
                            <td>${esc(section.name)}</td>
                            <td style="text-align:center;">${esc(section.code)}</td>
                            <td class="excel-number">${fmt.format(monthly)}</td>
                            <td class="excel-number">${fmt.format(months || 12)}</td>
                            <td class="excel-number excel-summary-highlight">${fmt.format(total)}</td>
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
        const finalSubsections = section.subsections.filter(subsection =>
            !section.subsections.some(child => Number(child.parent_id) === Number(subsection.id))
        );

        return `
            <div class="excel-preview-block">
                <h3 class="excel-preview-title">${esc(section.code)} ${esc(section.name)}</h3>
                ${renderSectionSummary(section, finalSubsections)}
            </div>
        `;
    }).join('');
}

function renderTabs() {
    document.getElementById('sectionTabs').innerHTML = SECTIONS.map(section => `
        <button type="button" class="excel-tab ${Number(section.id) === Number(selectedSectionId) ? 'active' : ''}" data-section="${section.id}">
            <span class="excel-tab-code">${esc(section.code)}</span>
            <span class="excel-tab-name">
                ${esc(section.name)}
                <small>${section.subsections.filter(subsection => !section.subsections.some(child => Number(child.parent_id) === Number(subsection.id))).length} ຫົວຂໍ້ຍ່ອຍ</small>
            </span>
            <span class="excel-tab-total">${fmt.format(totalFor(section.id))}</span>
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

function visibleFields(pattern) {
    return (pattern?.fields || []).filter(field => field.key !== 'reference');
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
    document.getElementById('backFromTotalPage').classList.toggle('is-hidden', !isOverview);

    if (isOverview) {
        renderOverviewSummary();
        document.getElementById('grandTotal').textContent = fmt.format(ROWS.reduce((sum, row) => sum + numberValue(row.total), 0));
        syncSectionNavButtons();
        return;
    }

    const section = SECTIONS.find(item => Number(item.id) === Number(selectedSectionId));
    if (!section) return;
    lastInputSectionId = Number(section.id);

    renderOverviewSummary();
    document.getElementById('sectionTitle').textContent = `${section.code} ${section.name}`;
    const finalSubsections = section.subsections.filter(subsection =>
        !section.subsections.some(child => Number(child.parent_id) === Number(subsection.id))
    );
    document.getElementById('sectionMeta').textContent = `${finalSubsections.length} ຫົວຂໍ້ຍ່ອຍ`;
    document.getElementById('sectionTotal').textContent = fmt.format(totalFor(section.id));
    document.getElementById('grandTotal').textContent = fmt.format(ROWS.reduce((sum, row) => sum + numberValue(row.total), 0));
    document.getElementById('subsectionSheets').innerHTML = section.subsections
        .filter(subsection => subsection.parent_id === null)
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

function renderSectionSummary(section, subsections) {
    const subtotal = totalFor(section.id);
    const monthlyTotal = summaryValue(rowsFor(section.id), ['amount_per_month', 'unit_price']);

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
                    const rows = rowsFor(section.id, subsection.id);
                    const monthly = summaryValue(rows, ['amount_per_month', 'unit_price']);
                    const qty = summaryValue(rows, ['month_count', 'quantity', 'times_per_year', 'frequency_count', 'event_count']);
                    const total = totalFor(section.id, subsection.id);
                    const note = rows.map(row => row.values?.note || row.detail).filter(Boolean).join(', ');

                    return `
                        <tr>
                            <td class="excel-seq">${index + 1}</td>
                            <td>${esc(subsection.name)}</td>
                            <td style="text-align:center;">${esc(subsection.code)}</td>
                            <td class="excel-number">${fmt.format(monthly)}</td>
                            <td class="excel-number">${fmt.format(qty)}</td>
                            <td class="excel-number excel-summary-highlight">${fmt.format(total)}</td>
                            <td>${esc(note)}</td>
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
    const children = section.subsections.filter(child => Number(child.parent_id) === Number(subsection.id));
    if (!children.length) {
        return renderSubsection(section, subsection);
    }

    return `
        <div class="excel-parent-block">
            <div class="excel-parent-title">
                <h3>${esc(subsection.code)} &nbsp;${esc(subsection.name)}</h3>
            </div>
            ${children.map(child => renderSubsection(section, child)).join('')}
        </div>
    `;
}

function renderSubsection(section, subsection) {
    const pattern = PATTERNS[subsection.default_pattern_id] || Object.values(PATTERNS)[0];
    const fields = visibleFields(pattern);
    const normalFields = fields.filter(field => field.key !== 'yearly_total');
    const totalField = fields.find(field => field.key === 'yearly_total') || fields.find(field => field.calculated);
    const rows = rowsFor(section.id, subsection.id);
    const subtotal = totalFor(section.id, subsection.id);
    const inputRow = Object.fromEntries(fields.map(field => [field.key, field.default_value ?? '']));
    if (!inputRow.item_name) inputRow.item_name = '';

    return `
        <article class="excel-block" data-section="${section.id}" data-subsection="${subsection.id}" data-pattern="${pattern?.id || ''}">
            <div class="excel-block-title">
                <h3>${esc(subsection.code)} &nbsp;${esc(subsection.name)}</h3>
                <p>${esc(pattern?.name || 'No pattern')} ${activeRule(section.id, subsection.id, pattern?.id)?.formula ? '· ' + esc(activeRule(section.id, subsection.id, pattern?.id).formula) : ''}</p>
            </div>
            <div class="excel-unit">ໜ່ວຍ: ກີບ</div>
            <div class="excel-table-wrap">
                <table class="excel-table">
                    <thead>
                        <tr>
                            <th class="excel-seq">ລ/ດ</th>
                            ${normalFields.map(field => `<th>${esc(field.label)}</th>`).join('')}
                            ${totalField ? `<th>${esc(totalField.label)}</th>` : ''}
                            <th>ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.length ? rows.map((row, index) => renderSavedRow(row, index + 1, normalFields, totalField)).join('') : `
                            <tr><td colspan="${normalFields.length + (totalField ? 3 : 2)}" class="excel-empty">ຍັງບໍ່ມີລາຍການ</td></tr>
                        `}
                        ${renderInputRow(inputRow, normalFields, totalField)}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="${normalFields.length + 1}" class="excel-number">ລວມ</td>
                            ${totalField ? `<td class="excel-number">${fmt.format(subtotal)}</td>` : ''}
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
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
                    ${field.key === 'item_name'
                        ? renderChartAccountSelect(field, row.values?.reference || '', rowDisplayValue(row, field))
                        : `<input class="excel-input ${field.type === 'number' ? 'excel-money-input' : ''}" name="${esc(field.key)}" data-type="${esc(field.type)}" type="${field.type === 'number' ? 'text' : field.type === 'date' ? 'date' : 'text'}"
                                  inputmode="${field.type === 'number' ? 'decimal' : 'text'}" value="${esc(field.type === 'number' ? moneyInputValue(rowDisplayValue(row, field)) : rowDisplayValue(row, field))}" placeholder="${esc(field.label)}" ${field.required ? 'required' : ''}>`}
                </td>
            `).join('')}
            ${totalField ? `<td><input class="excel-input excel-money-input" name="${esc(totalField.key)}" data-type="number" data-calculated="1" type="text" inputmode="decimal" value="${moneyInputValue(row.total || rowDisplayValue(row, totalField))}" readonly></td>` : ''}
            <td style="text-align:center;">
                <button type="button" class="excel-save-line" data-update="${row.id}" title="Save">Save</button>
                <button type="button" class="excel-delete" data-delete="${row.id}" title="Delete">&times;</button>
            </td>
        </tr>
    `;
}

function renderInputRow(values, normalFields, totalField) {
    return `
        <tr class="excel-add-row">
            <td class="excel-seq">+</td>
            ${normalFields.map(field => `
                <td class="${field.key === 'item_name' ? 'excel-name' : ''}">
                    ${field.key === 'item_name' ? renderChartAccountSelect(field) : `
                        <input class="excel-input ${field.type === 'number' ? 'excel-money-input' : ''}" name="${esc(field.key)}" data-type="${esc(field.type)}" type="${field.type === 'number' ? 'text' : field.type === 'date' ? 'date' : 'text'}"
                               inputmode="${field.type === 'number' ? 'decimal' : 'text'}" value="${esc(field.type === 'number' ? moneyInputValue(values[field.key]) : values[field.key])}" placeholder="${esc(field.label)}" ${field.required ? 'required' : ''}>
                    `}
                </td>
            `).join('')}
            ${totalField ? `<td><input class="excel-input excel-money-input" name="${esc(totalField.key)}" data-type="number" data-calculated="1" type="text" inputmode="decimal" value="0" readonly></td>` : ''}
            <td style="text-align:center;"><button type="button" class="excel-add">ເພີ່ມ</button></td>
        </tr>
    `;
}

function renderChartAccountSelect(field, selectedCode = '', selectedName = '') {
    const listId = `chartAccounts-${Math.random().toString(36).slice(2)}`;
    const selectedValue = selectedCode || selectedName ? `${selectedCode} - ${selectedName}`.replace(/^ - /, '').trim() : '';

    return `
        <input class="excel-input excel-account-search" name="chart_account_search" data-type="text"
               list="${listId}" placeholder="${esc(field.label)}" autocomplete="off" value="${esc(selectedValue)}" ${field.required ? 'required' : ''}>
        <datalist id="${listId}">
            ${CHART_ACCOUNTS.map(account => `
                <option value="${esc(account.code)} - ${esc(account.name)}"></option>
            `).join('')}
        </datalist>
        <input type="hidden" name="chart_account_id" data-type="text" value="">
        <input type="hidden" name="item_name" data-type="text" value="${esc(selectedName)}">
        <input type="hidden" name="reference" data-type="text" value="${esc(selectedCode)}">
    `;
}

function bindSheetEvents() {
    document.querySelectorAll('.excel-block').forEach(block => updateBlockTotal(block));

    document.querySelectorAll('.excel-add-row input, .excel-saved-row input').forEach(input => {
        input.addEventListener('input', event => {
            if (event.target.dataset.type === 'number' && !event.target.readOnly) {
                event.target.value = moneyInputValue(event.target.value);
            }
            updateLineTotal(event.target.closest('tr'));
        });
    });

    document.querySelectorAll('.excel-account-search').forEach(input => {
        input.addEventListener('input', event => {
            syncChartAccount(event.target);
        });
        input.addEventListener('change', event => syncChartAccount(event.target));
    });

    document.querySelectorAll('.excel-add').forEach(button => {
        button.addEventListener('click', event => saveBlockRow(event.target.closest('.excel-block')));
    });

    document.querySelectorAll('.excel-save-line').forEach(button => {
        button.addEventListener('click', event => updateSavedRow(event.target.closest('.excel-saved-row')));
    });

    document.querySelectorAll('.excel-delete').forEach(button => {
        button.addEventListener('click', event => deleteRow(event.target.dataset.delete));
    });
}

function inputValues(block) {
    return lineValues(block.querySelector('.excel-add-row'));
}

function lineValues(row) {
    const values = {};
    row.querySelectorAll('input, select').forEach(input => {
        if (!input.name || input.name === 'chart_account_id' || input.name === 'chart_account_search') return;
        values[input.name] = input.dataset.type === 'number' ? numberValue(input.value) : input.value;
    });
    return values;
}

function syncChartAccount(searchInput) {
    const row = searchInput.closest('tr');
    const value = searchInput.value.trim().toLowerCase();
    const account = CHART_ACCOUNTS.find(item =>
        `${item.code} - ${item.name}`.toLowerCase() === value ||
        String(item.code).toLowerCase() === value
    );

    row.querySelector('input[name="chart_account_id"]').value = account?.id || '';
    row.querySelector('input[name="item_name"]').value = account?.name || '';
    row.querySelector('input[name="reference"]').value = account?.code || '';
}

function updateBlockTotal(block) {
    updateLineTotal(block.querySelector('.excel-add-row'));
}

function updateLineTotal(row) {
    if (!row) return;
    const block = row.closest('.excel-block');
    const sectionId = Number(block.dataset.section);
    const subsectionId = Number(row.dataset.subsection || block.dataset.subsection);
    const patternId = Number(row.dataset.pattern || block.dataset.pattern);
    const rule = activeRule(sectionId, subsectionId, patternId);
    const values = lineValues(row);
    const total = rule ? calculateFormula(rule.formula, values) : numberValue(values.yearly_total);
    const totalInput = row.querySelector('input[name="yearly_total"]');
    if (totalInput) totalInput.value = moneyInputValue(total);
}

async function saveBlockRow(block) {
    updateBlockTotal(block);
    const accountSearch = block.querySelector('.excel-account-search');
    if (accountSearch && accountSearch.value.trim() && !block.querySelector('input[name="chart_account_id"]').value) {
        toast('Choose an account from the search list');
        accountSearch.focus();
        return;
    }

    const sectionId = Number(block.dataset.section);
    const subsectionId = Number(block.dataset.subsection);
    const patternId = Number(block.dataset.pattern);
    const subsection = SECTIONS.find(section => Number(section.id) === sectionId)?.subsections.find(sub => Number(sub.id) === subsectionId);
    const values = inputValues(block);
    const planDetail = values.item_name || subsection?.name || 'Expense row';

    const response = await fetch(@json(route('head_of_finance.expense-plan-rows.store')), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({
            planning_year_id: PLANNING_YEAR_ID,
            section_id: sectionId,
            subsection_id: subsectionId,
            pattern_id: patternId,
            plan_detail: planDetail,
            detail: values.note || null,
            values,
        }),
    });

    const data = await response.json();
    if (!response.ok || !data.success) {
        toast('Could not save row');
        return;
    }

    ROWS.push({
        ...data.entry,
        code: subsection?.code,
        label: subsection?.name,
    });
    renderTabs();
    renderSheet();
    toast('Saved');
}

async function updateSavedRow(row) {
    updateLineTotal(row);
    const accountSearch = row.querySelector('.excel-account-search');
    if (accountSearch && accountSearch.value.trim() && !row.querySelector('input[name="reference"]').value) {
        toast('Choose an account from the search list');
        accountSearch.focus();
        return;
    }

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

async function deleteRow(id) {
    if (!confirm('Delete this row?')) return;
    const response = await fetch(`/head-of-finance/expense-plan-rows/${id}`, {
        method: 'DELETE',
        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
    });
    if (!response.ok) {
        toast('Could not delete');
        return;
    }
    ROWS = ROWS.filter(row => Number(row.id) !== Number(id));
    renderTabs();
    renderSheet();
    toast('Deleted');
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
document.getElementById('openTotalPage').addEventListener('click', () => {
    if (selectedSectionId !== 'overview') lastInputSectionId = selectedSectionId;
    selectedSectionId = 'overview';
    renderTabs();
    renderSheet();
    document.querySelector('.excel-plan')?.scrollIntoView({behavior: 'smooth', block: 'start'});
});
document.getElementById('backFromTotalPage').addEventListener('click', () => {
    selectedSectionId = lastInputSectionId || SECTIONS[0]?.id || null;
    renderTabs();
    renderSheet();
    document.querySelector('.excel-plan')?.scrollIntoView({behavior: 'smooth', block: 'start'});
});

const sectionModal = document.getElementById('sectionModal');
const subsectionModal = document.getElementById('subsectionModal');
const subsectionForm = document.getElementById('subsectionForm');
const subsectionSection = document.getElementById('subsectionSection');
const subsectionParent = document.getElementById('subsectionParent');
const subsectionOrder = document.getElementById('subsectionOrder');

function openModal(modal) {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(() => modal.querySelector('input, select, textarea')?.focus(), 50);
}

function closeModal(modal) {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function syncSubsectionModal() {
    if (!subsectionSection) return;

    const selected = subsectionSection.options[subsectionSection.selectedIndex];
    const section = SECTIONS.find(item => Number(item.id) === Number(subsectionSection.value));
    subsectionForm.action = selected?.dataset.url || '';
    subsectionParent.innerHTML = '<option value="">No parent</option>';

    if (section) {
        section.subsections
            .filter(subsection => subsection.parent_id === null)
            .forEach(subsection => {
                const option = document.createElement('option');
                option.value = subsection.id;
                option.textContent = `${subsection.code} - ${subsection.name}`;
                subsectionParent.appendChild(option);
            });

        subsectionOrder.value = section.subsections.length + 1;
    }
}

document.getElementById('openSectionModal').addEventListener('click', () => openModal(sectionModal));
document.getElementById('openSubsectionModal').addEventListener('click', () => {
    if (subsectionSection && selectedSectionId !== 'overview') subsectionSection.value = String(selectedSectionId);
    syncSubsectionModal();
    openModal(subsectionModal);
});

subsectionSection?.addEventListener('change', syncSubsectionModal);

document.addEventListener('click', event => {
    if (!event.target.matches('[data-close-modal]') && !event.target.classList.contains('excel-modal')) return;

    closeModal(sectionModal);
    closeModal(subsectionModal);
});

document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;

    closeModal(sectionModal);
    closeModal(subsectionModal);
});

renderTabs();
renderSheet();
</script>
@endsection
