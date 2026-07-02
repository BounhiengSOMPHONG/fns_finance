@extends('layouts.admin')

@section('title', 'ຕັ້ງຄ່າຫຼັກສູດ')
@section('page-title', 'ຕັ້ງຄ່າຫຼັກສູດ')

@section('content')
@php
    $levelMeta = [
        'bachelor' => ['label' => 'ປ.ຕີ', 'full' => 'ປະລິນຍາຕີ'],
        'master' => ['label' => 'ປ.ໂທ', 'full' => 'ປະລິນຍາໂທ'],
        'phd' => ['label' => 'ປ.ເອກ', 'full' => 'ປະລິນຍາເອກ'],
    ];
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value * 100, 4, '.', ''), '0'), '.');
    $splitPct = fn ($value) => rtrim(rtrim(number_format((float) $value * 100, 2, '.', ''), '0'), '.');
    $displayProgramName = function (?string $name): string {
        $name = (string) ($name ?? '');
        $display = trim((string) preg_replace('/\s*(?:ປີ|\x{0E1B}\x{0E35})\s*1\s*$/u', '', $name));

        return $display !== '' ? $display : $name;
    };
    $creditsByLevel = $displayCourseCredits->groupBy(fn ($setting) => $setting->degreeProgram?->level ?? 'unknown');
@endphp

<section class="erp-shell">
    <div class="erp-topbar">
        <div>
            <span class="erp-kicker">ຕັ້ງຄ່າ</span>
            <h2>ຕັ້ງຄ່າຫຼັກສູດ</h2>
            <p>ກຳນົດລາຄາຕໍ່ໜ່ວຍກິດ, ອັດຕາຫັກ ມຊ, ສັດສ່ວນຄິດໜ່ວຍກິດ ແລະ ໜ່ວຍກິດລວມຂອງແຕ່ລະຫຼັກສູດ.</p>
        </div>
        <div class="erp-settings-actions">
            <button type="button" class="erp-settings-btn erp-setting-trigger" data-settings-modal="price-settings-modal">
                ລາຄາຕໍ່ໜ່ວຍກິດ
            </button>
            <button type="button" class="erp-settings-btn erp-setting-trigger" data-settings-modal="nuol-settings-modal">
                ອັດຕາຫັກ ມຊ
            </button>
            <button type="button" class="erp-settings-btn erp-setting-trigger" data-settings-modal="split-settings-modal">
                ສັດສ່ວນຄິດໜ່ວຍກິດ
            </button>
            <a href="{{ route('head_of_finance.settings.registration-fee.index') }}" class="erp-settings-btn erp-settings-link">
                ຄ່າລົງທະບຽນ
            </a>
        </div>
    </div>

    <section class="erp-summary-grid" aria-label="Program setting summary">
        <article class="erp-summary-card">
            <div class="erp-summary-head">
                <span>01</span>
                <strong>ລາຄາຕໍ່ໜ່ວຍກິດ</strong>
            </div>
            <div class="erp-summary-values">
                @foreach($levelMeta as $key => $meta)
                    @php $price = $prices->get($key); @endphp
                    <span>{{ $meta['label'] }} <b>{{ $price ? number_format((float) $price->credit_unit_price, 0) : '-' }}</b></span>
                @endforeach
            </div>
            <button type="button" class="erp-summary-action erp-setting-trigger" data-settings-modal="price-settings-modal">ແກ້ໄຂລາຄາ</button>
        </article>

        <article class="erp-summary-card">
            <div class="erp-summary-head">
                <span>02</span>
                <strong>ອັດຕາຫັກ ມຊ</strong>
            </div>
            <div class="erp-summary-values">
                @foreach($levelMeta as $key => $meta)
                    @php $nuol = $nuolPcts->get($key); @endphp
                    <span>{{ $meta['label'] }} <b>{{ $nuol ? $pct($nuol->percentage).'%' : '-' }}</b></span>
                @endforeach
            </div>
            <button type="button" class="erp-summary-action erp-setting-trigger" data-settings-modal="nuol-settings-modal">ແກ້ໄຂ ມຊ</button>
        </article>

        <article class="erp-summary-card">
            <div class="erp-summary-head">
                <span>03</span>
                <strong>ສັດສ່ວນຄິດໜ່ວຍກິດ</strong>
            </div>
            <div class="erp-summary-values">
                @foreach(['master', 'phd'] as $level)
                    @php $split = $creditSplits->get($level); @endphp
                    <span>{{ $levelMeta[$level]['label'] }} <b>{{ $split ? $splitPct($split->year1_percentage).'/'.$splitPct($split->year2_percentage) : '60/40' }}</b></span>
                @endforeach
            </div>
            <button type="button" class="erp-summary-action erp-setting-trigger" data-settings-modal="split-settings-modal">ແກ້ໄຂສັດສ່ວນ</button>
        </article>

        <article class="erp-summary-card erp-summary-card-link">
            <div class="erp-summary-head">
                <span>04</span>
                <strong>ຄ່າລົງທະບຽນ</strong>
            </div>
            <p>ຄ່າລົງທະບຽນປີ 1 ແລະ ປີ 2-4 ຍັງຈັດການໃນໜ້າແຍກ.</p>
            <a href="{{ route('head_of_finance.settings.registration-fee.index') }}" class="erp-summary-action">ໄປໜ້າຄ່າລົງທະບຽນ</a>
        </article>
    </section>

    <section class="erp-panel">
        <div class="erp-panel-head erp-table-head">
            <div>
                <h3>ໜ່ວຍກິດຕາມຫຼັກສູດ</h3>
                <p>ຄົ້ນຫາ ແລະ ແກ້ໄຂຈຳນວນໜ່ວຍກິດລວມຂອງແຕ່ລະຫຼັກສູດ.</p>
            </div>
            <div class="erp-toolbar">
                <div class="erp-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="text" id="cc-filter" placeholder="ຄົ້ນຫາຫຼັກສູດ..." autocomplete="off">
                </div>
                <select id="cc-level-filter" class="erp-select" aria-label="Filter by level">
                    <option value="">ທຸກລະດັບ</option>
                    @foreach($levelMeta as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <span class="erp-count"><b id="cc-visible-count">{{ $displayCourseCredits->count() }}</b> / {{ $displayCourseCredits->count() }}</span>
                <button type="button" id="cc-open-create" class="erp-btn erp-btn-primary">
                    <span aria-hidden="true">+</span>
                    ເພີ່ມໃໝ່
                </button>
            </div>
        </div>

        <div class="cc-credit-groups">
            @forelse($levelMeta as $levelKey => $level)
                @php $items = $creditsByLevel->get($levelKey); @endphp
                @if($items && $items->count())
                    <section class="cc-credit-card" data-level-card="{{ $levelKey }}">
                        <div class="cc-credit-head">
                            <div>
                                <span class="erp-badge">{{ $level['label'] }}</span>
                                <strong>{{ $level['full'] }}</strong>
                            </div>
                            <span class="erp-count"><b>{{ $items->count() }}</b> ຫຼັກສູດ</span>
                        </div>

                        <div class="cc-credit-rows">
                            @foreach($items as $s)
                                @php
                                    $level = $s->degreeProgram?->level;
                                    $meta = $levelMeta[$level] ?? ['label' => $level ?: '-', 'full' => $level ?: '-'];
                                    $programName = $s->degreeProgram?->name ?? '-';
                                    $shownProgramName = $displayProgramName($programName);
                                    $displayYears = $s->display_years ?? collect();
                                    $displayCodes = $s->display_codes ?? '';
                                    $departmentName = $s->degreeProgram?->academic_department_label;
                                @endphp
                                <div class="cc-credit-row cc-row" data-level="{{ $level }}" data-name="{{ \Illuminate\Support\Str::lower($shownProgramName.' '.$programName.' '.$displayCodes.' '.$departmentName) }}">
                                    <div class="cc-credit-main">
                                        <strong class="erp-program">{{ $shownProgramName }}</strong>
                                        <span>{{ $departmentName ?: $meta['full'] }}</span>
                                    </div>

                                    <div class="erp-year-chip-list">
                                        @if($displayYears->count() > 1)
                                            @foreach($displayYears as $row)
                                                <span class="erp-year-chip">ປີ {{ $row['year'] }}: {{ $row['unit'] }}</span>
                                            @endforeach
                                        @else
                                            <span class="erp-year-chip">
                                                {{ $s->degreeProgram?->study_year ? 'ປີ '.$s->degreeProgram?->study_year.': ' : '' }}{{ (float) $s->course_credit_unit }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="cc-credit-meta">
                                        <span>ປີ {{ $s->start_year }}</span>
                                        <span>{{ $s->gov_doc_id ?: '-' }}</span>
                                    </div>

                                    <div class="erp-actions">
                                        <button type="button"
                                            class="erp-icon-btn js-cc-edit"
                                            title="ແກ້ໄຂ"
                                            data-url="{{ route('head_of_finance.settings.course-credits.update', $s) }}"
                                            data-degree-program-id="{{ $s->degree_program_id }}"
                                            data-level="{{ $level }}"
                                            data-course-credit-unit="{{ (float) $s->course_credit_unit }}"
                                            data-gov-doc-id="{{ $s->gov_doc_id }}"
                                            data-start-year="{{ $s->start_year }}"
                                            data-display-years='@json($displayYears)'>
                                            ✎
                                        </button>
                                        <form method="POST" action="{{ route('head_of_finance.settings.course-credits.destroy', $s) }}" onsubmit="return confirm('ທ່ານຕ້ອງການລຶບຂໍ້ມູນນີ້ແທ້ບໍ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-icon-btn erp-icon-danger" title="ລຶບ">×</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @empty
            @endforelse

            @if($displayCourseCredits->isEmpty())
                <div class="erp-empty">ຍັງບໍ່ມີຂໍ້ມູນໜ່ວຍກິດ</div>
            @endif
        </div>

        <div id="cc-nores" class="erp-empty erp-empty-hidden">
            ບໍ່ພົບຫຼັກສູດທີ່ກົງກັບການຄົ້ນຫາ
        </div>
    </section>
</section>

<div id="price-settings-modal" class="cc-modal settings-modal" aria-hidden="true">
    <div class="cc-modal-panel settings-modal-panel" role="dialog" aria-modal="true" aria-labelledby="price-settings-title">
        <div class="cc-modal-head">
            <div>
                <h2 id="price-settings-title">ລາຄາຕໍ່ໜ່ວຍກິດ</h2>
                <p>ຕັ້ງລາຄາຕໍ່ໜ່ວຍກິດຕາມລະດັບການສຶກສາ.</p>
            </div>
            <button type="button" class="cc-modal-close" data-settings-close>&times;</button>
        </div>
        <div class="settings-modal-body">
            @foreach($levelMeta as $key => $meta)
                @php $price = $prices->get($key); @endphp
                <div class="erp-settings-row">
                    <div class="erp-level-cell">
                        <strong>{{ $meta['label'] }}</strong>
                        <span>{{ $meta['full'] }}</span>
                    </div>
                    @if($price)
                        <form method="POST" action="{{ route('head_of_finance.settings.credit-unit-price.update', $price) }}" class="erp-inline-form dirty-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="level" value="{{ $key }}">
                            <label>
                                <span>ລາຄາຕໍ່ໜ່ວຍກິດ</span>
                                <input type="number" name="credit_unit_price" step="0.01" min="0" required value="{{ (float) $price->credit_unit_price }}" class="erp-input erp-input-num data-dirty">
                            </label>
                            <label>
                                <span>ເອກະສານ</span>
                                <input type="text" name="gov_doc_id" value="{{ $price->gov_doc_id }}" class="erp-input data-dirty">
                            </label>
                            <label>
                                <span>ປີ</span>
                                <input type="number" name="start_year" min="2000" max="2100" required value="{{ $price->start_year }}" class="erp-input erp-input-year data-dirty">
                            </label>
                            <button type="submit" class="erp-btn erp-btn-save btn-save">ບັນທຶກ</button>
                        </form>
                    @else
                        <div class="erp-missing">ຍັງບໍ່ມີລາຄາຂອງລະດັບນີ້</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="nuol-settings-modal" class="cc-modal settings-modal" aria-hidden="true">
    <div class="cc-modal-panel settings-modal-panel" role="dialog" aria-modal="true" aria-labelledby="nuol-settings-title">
        <div class="cc-modal-head">
            <div>
                <h2 id="nuol-settings-title">ອັດຕາຫັກ ມຊ</h2>
                <p>ກຳນົດເປີເຊັນສ່ວນແບ່ງຂອງ ມຊ ຕາມລະດັບ.</p>
            </div>
            <button type="button" class="cc-modal-close" data-settings-close>&times;</button>
        </div>
        <div class="settings-modal-body">
            @foreach($levelMeta as $key => $meta)
                @php $n = $nuolPcts->get($key); @endphp
                <div class="erp-settings-row">
                    <div class="erp-level-cell">
                        <strong>{{ $meta['label'] }}</strong>
                        <span>{{ $meta['full'] }}</span>
                    </div>
                    @if($n)
                        <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.update', $n) }}" class="erp-inline-form dirty-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="level" value="{{ $key }}">
                            <label>
                                <span>ອັດຕາຫັກ ມຊ (%)</span>
                                <input type="number" name="percentage" step="0.01" min="0" max="100" required value="{{ $pct($n->percentage) }}" class="erp-input erp-input-num data-dirty">
                            </label>
                            <label>
                                <span>ເອກະສານ</span>
                                <input type="text" name="gov_doc_id" value="{{ $n->gov_doc_id }}" class="erp-input data-dirty">
                            </label>
                            <label>
                                <span>ປີ</span>
                                <input type="number" name="start_year" min="2000" max="2100" required value="{{ $n->start_year }}" class="erp-input erp-input-year data-dirty">
                            </label>
                            <button type="submit" class="erp-btn erp-btn-save btn-save">ບັນທຶກ</button>
                        </form>
                    @else
                        <div class="erp-missing">ຍັງບໍ່ມີອັດຕາຫັກ ມຊ ຂອງລະດັບນີ້</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="split-settings-modal" class="cc-modal settings-modal" aria-hidden="true">
    <div class="cc-modal-panel settings-modal-panel" role="dialog" aria-modal="true" aria-labelledby="split-settings-title">
        <div class="cc-modal-head">
            <div>
                <h2 id="split-settings-title">ສັດສ່ວນຄິດໜ່ວຍກິດ ປ.ໂທ / ປ.ເອກ</h2>
                <p>ກຳນົດວ່າໜ່ວຍກິດລວມຂອງ ປ.ໂທ/ປ.ເອກ ຈະຄິດເຂົ້າປີ 1 ແລະ ປີ 2+ ຢ່າງໃດ.</p>
            </div>
            <button type="button" class="cc-modal-close" data-settings-close>&times;</button>
        </div>
        <div class="settings-modal-body">
            <form method="POST" action="{{ route('head_of_finance.settings.course-credit-splits.reset-defaults') }}" class="settings-reset-form">
                @csrf
                <button type="submit" class="erp-btn erp-btn-save">ຕັ້ງຄ່າ ປ.ໂທ/ປ.ເອກ 60/40</button>
            </form>
            <div class="erp-split-table">
                <div class="erp-split-head">
                    <span>ລະດັບ</span>
                    <span>ປີ 1 (%)</span>
                    <span>ປີ 2+ (%)</span>
                    <span>ເອກະສານ</span>
                    <span>ປີ</span>
                    <span>ຈັດການ</span>
                </div>
                @foreach(['master', 'phd'] as $level)
                    @php
                        $split = $creditSplits->get($level);
                        $year1Pct = $split ? $splitPct($split->year1_percentage) : '60';
                        $year2Pct = $split ? $splitPct($split->year2_percentage) : '40';
                        $meta = $levelMeta[$level];
                    @endphp
                    <form method="POST" action="{{ route('head_of_finance.settings.course-credit-splits.update', $level) }}" class="erp-split-row dirty-form">
                        @csrf
                        @method('PATCH')
                        <div class="erp-level-cell">
                            <strong>{{ $meta['label'] }}</strong>
                            <span>{{ $meta['full'] }}</span>
                        </div>
                        <label>
                            <span>ປີ 1 (%)</span>
                            <input type="number" name="year1_percentage" step="0.01" min="0" max="100" required value="{{ old('year1_percentage', $year1Pct) }}" class="erp-input erp-input-num data-dirty js-split-year1">
                        </label>
                        <label>
                            <span>ປີ 2+ (%)</span>
                            <input type="number" name="year2_percentage" step="0.01" min="0" max="100" required value="{{ old('year2_percentage', $year2Pct) }}" class="erp-input erp-input-num data-dirty js-split-year2">
                        </label>
                        <label>
                            <span>ເອກະສານ</span>
                            <input type="text" name="gov_doc_id" value="{{ old('gov_doc_id', $split->gov_doc_id ?? '') }}" class="erp-input data-dirty">
                        </label>
                        <label>
                            <span>ປີ</span>
                            <input type="number" name="start_year" min="2000" max="2100" required value="{{ old('start_year', $split->start_year ?? date('Y')) }}" class="erp-input erp-input-year data-dirty">
                        </label>
                        <button type="submit" class="erp-btn erp-btn-save btn-save">ບັນທຶກ</button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div id="cc-modal" class="cc-modal" aria-hidden="true">
    <div class="cc-modal-panel" role="dialog" aria-modal="true" aria-labelledby="cc-modal-title">
        <div class="cc-modal-head">
            <h2 id="cc-modal-title">ເພີ່ມໃໝ່</h2>
            <button type="button" class="cc-modal-close" data-cc-close>&times;</button>
        </div>
        <form method="POST" action="{{ route('head_of_finance.settings.course-credits.store') }}" id="cc-modal-form" class="cc-modal-body">
            @csrf
            <input type="hidden" name="_method" id="cc-form-method" value="PUT" disabled>

            <div class="erp-modal-grid erp-modal-grid-wide">
                <label class="erp-field">
                    <span>ຫຼັກສູດ <b>*</b></span>
                    <div class="cc-program-picker" id="cc-program-picker">
                        <input type="text" id="cc-program-search" class="erp-input" placeholder="ພິມລະຫັດ ຫຼື ຊື່ຫຼັກສູດ..." autocomplete="off" aria-expanded="false" aria-controls="cc-program-list">
                        <input type="hidden" name="degree_program_id" id="cc-degree-program" required>
                        <div class="cc-program-list" id="cc-program-list" role="listbox"></div>
                    </div>
                    <select id="cc-program-source" class="cc-program-source" tabindex="-1" aria-hidden="true">
                        @foreach($displayPrograms as $p)
                            <option value="{{ $p->id }}"
                                data-level="{{ $p->level }}"
                                data-display-years='@json($p->display_years ?? collect())'>
                                [{{ $p->level_label }}{{ ($p->display_rows ?? collect())->count() > 1 ? '' : ($p->study_year ? ' ປີ '.$p->study_year : '') }}] {{ $displayProgramName($p->name) }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="erp-modal-grid erp-modal-grid-wide" id="cc-single-unit-wrap">
                <label class="erp-field">
                    <span>ໜ່ວຍກິດລວມຂອງຫຼັກສູດ <b>*</b></span>
                    <input type="number" id="cc-unit-bach" name="course_credit_unit" min="1" max="999" step="0.5" class="erp-input" required>
                </label>
            </div>

            <div class="erp-modal-grid erp-modal-grid-wide cc-year-units" id="cc-year-units"></div>

            <div class="erp-modal-grid">
                <label class="erp-field">
                    <span>ເລກທີເອກະສານອ້າງອີງ</span>
                    <input type="text" name="gov_doc_id" id="cc-gov-doc" class="erp-input">
                </label>
                <label class="erp-field">
                    <span>ປີທີ່ເລີ່ມໃຊ້ <b>*</b></span>
                    <input type="number" name="start_year" id="cc-start-year" min="2000" max="2100" value="{{ date('Y') }}" class="erp-input" required>
                </label>
            </div>

            <div class="cc-modal-actions">
                <button type="button" class="erp-btn erp-btn-secondary" data-cc-close>ຍົກເລີກ</button>
                <button type="submit" class="erp-btn erp-btn-primary" id="cc-submit">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<style>
    .erp-shell { display:flex; flex-direction:column; gap:.85rem; padding-bottom:1.25rem; }
    .erp-topbar, .erp-panel {
        background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 1px 3px rgba(15,23,42,.05);
    }
    .erp-topbar {
        display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.9rem 1rem;
    }
    .erp-kicker { color:#64748b; font-size:.68rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .erp-topbar h2 { margin:.12rem 0; color:#172642; font-size:1.08rem; font-weight:900; }
    .erp-topbar p { margin:0; color:#64748b; font-size:.78rem; }
    .erp-settings-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.45rem; max-width:680px; }
    .erp-settings-btn {
        display:inline-flex; align-items:center; justify-content:center; min-height:36px;
        border:1px solid #bfdbfe; border-radius:6px; background:#eff6ff; color:#1d4ed8;
        padding:0 .75rem; font-family:inherit; font-size:.78rem; font-weight:900;
        cursor:pointer; white-space:nowrap; text-decoration:none;
    }
    .erp-settings-btn:hover { background:#2563eb; border-color:#2563eb; color:#fff; }
    .erp-settings-btn::after { content:"›"; margin-left:.38rem; font-size:1rem; line-height:1; }
    .erp-summary-grid {
        display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem;
    }
    .erp-summary-card {
        display:grid; align-content:space-between; gap:.65rem;
        min-height:148px; padding:.85rem; border:1px solid #e5e7eb; border-radius:8px;
        background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.05);
    }
    .erp-summary-head { display:flex; align-items:center; gap:.55rem; min-width:0; }
    .erp-summary-head span {
        display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto;
        width:28px; height:28px; border-radius:999px; background:#172642; color:#fff;
        font-size:.68rem; font-weight:900; font-variant-numeric:tabular-nums;
    }
    .erp-summary-head strong { color:#172642; font-size:.86rem; font-weight:900; line-height:1.35; }
    .erp-summary-values { display:grid; gap:.28rem; }
    .erp-summary-values span {
        display:flex; align-items:center; justify-content:space-between; gap:.5rem;
        color:#64748b; font-size:.73rem; font-weight:800;
    }
    .erp-summary-values b { color:#172642; font-variant-numeric:tabular-nums; }
    .erp-summary-card p { margin:0; color:#64748b; font-size:.74rem; line-height:1.6; }
    .erp-summary-action {
        display:inline-flex; align-items:center; justify-content:center; justify-self:start;
        min-height:32px; border:1px solid #f5d58b; border-radius:999px;
        background:#fff8e5; color:#8a5a00; padding:0 .72rem;
        font-family:inherit; font-size:.72rem; font-weight:900; text-decoration:none; cursor:pointer;
    }
    .erp-summary-action:hover { background:#f7c948; border-color:#f7c948; color:#172642; }
    .erp-summary-card-link { background:#fffdf7; border-color:#fde7b0; }
    .erp-panel { overflow:hidden; }
    .erp-panel-head {
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; background:#f8fafc;
    }
    .erp-panel-head h3 { margin:0; color:#172642; font-size:.95rem; font-weight:900; }
    .erp-panel-head p { margin:.15rem 0 0; color:#64748b; font-size:.72rem; }
    .erp-status { border-radius:999px; padding:.22rem .55rem; font-size:.68rem; font-weight:800; white-space:nowrap; }
    .erp-status-warning { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
    .erp-rate-table { overflow-x:auto; }
    .erp-rate-header, .erp-rate-row {
        display:grid; grid-template-columns:130px minmax(430px, 1.2fr) minmax(430px, 1.2fr);
        gap:.7rem; min-width:1010px; align-items:center;
    }
    .erp-rate-header {
        padding:.55rem .85rem; background:#fff; border-bottom:1px solid #e5e7eb;
        color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase;
    }
    .erp-rate-header span:nth-child(4), .erp-rate-header span:nth-child(5) { display:none; }
    .erp-rate-row { padding:.7rem .85rem; border-bottom:1px solid #eef2f7; }
    .erp-rate-row:last-child { border-bottom:0; }
    .erp-level-cell strong { display:block; color:#172642; font-size:.88rem; }
    .erp-level-cell span, .erp-inline-form label span, .erp-field span {
        display:block; color:#64748b; font-size:.68rem; font-weight:800; margin-bottom:.18rem;
    }
    .erp-inline-form { display:grid; grid-template-columns:1fr 1fr 86px auto; gap:.45rem; align-items:end; }
    .erp-split-table { display:grid; gap:.45rem; }
    .erp-split-head,
    .erp-split-row {
        display:grid; grid-template-columns:130px 92px 92px minmax(120px, 1fr) 92px 96px;
        gap:.5rem; align-items:end;
    }
    .erp-split-head {
        color:#64748b; font-size:.68rem; font-weight:900; padding:0 .15rem;
    }
    .erp-split-row {
        border:1px solid #e5e7eb; border-radius:8px; padding:.7rem; background:#fff;
    }
    .erp-split-row label span {
        display:block; color:#64748b; font-size:.68rem; font-weight:800; margin-bottom:.18rem;
    }
    .erp-input, .erp-select, .erp-search input {
        width:100%; height:42px; border:1px solid #cbd5e1; border-radius:6px; background:#fff;
        color:#172642; font-size:.82rem; padding:0 .65rem; outline:none;
    }
    .erp-input:focus, .erp-select:focus, .erp-search input:focus {
        border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12);
    }
    .cc-program-source { display:none; }
    .cc-program-picker { position:relative; }
    .cc-program-list {
        position:absolute; z-index:1020; left:0; right:0; top:calc(100% + 5px);
        display:none; max-height:260px; overflow:auto;
        border:1px solid #cbd5e1; border-radius:8px; background:#fff;
        box-shadow:0 18px 40px rgba(15,23,42,.16);
    }
    .cc-program-list.is-open { display:block; }
    .cc-program-option {
        width:100%; border:0; border-bottom:1px solid #eef2f7; background:#fff;
        padding:.55rem .7rem; color:#172642; text-align:left; font-family:inherit;
        font-size:.8rem; font-weight:800; cursor:pointer;
    }
    .cc-program-option:last-child { border-bottom:0; }
    .cc-program-option:hover, .cc-program-option.is-active { background:#eff6ff; color:#1d4ed8; }
    .cc-program-empty { padding:.7rem; color:#94a3b8; font-size:.78rem; }
    .erp-input-num { text-align:right; font-variant-numeric:tabular-nums; }
    .erp-input-year { text-align:center; }
    .erp-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:.35rem; height:40px;
        border-radius:6px; border:1px solid transparent; padding:0 .75rem;
        font-size:.78rem; font-weight:900; text-decoration:none; cursor:pointer; white-space:nowrap;
    }
    .erp-btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
    .erp-btn-primary:hover { background:#1d4ed8; border-color:#1d4ed8; }
    .erp-btn-secondary { background:#fff; border-color:#cbd5e1; color:#172642; }
    .erp-btn-save { background:#f8fafc; border-color:#cbd5e1; color:#334155; }
    .dirty-form.is-dirty .erp-btn-save { background:#16a34a; border-color:#16a34a; color:#fff; }
    .erp-missing { color:#94a3b8; border:1px dashed #cbd5e1; border-radius:6px; padding:.7rem; font-size:.78rem; }
    .erp-table-head { align-items:flex-end; }
    .erp-toolbar { display:flex; align-items:center; gap:.5rem; }
    .erp-search { position:relative; width:260px; }
    .erp-search span { position:absolute; left:.65rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-weight:900; }
    .erp-search input { padding-left:1.8rem; }
    .erp-select { width:132px; }
    .erp-count { color:#64748b; font-size:.76rem; white-space:nowrap; }
    .erp-count b { color:#172642; font-variant-numeric:tabular-nums; }
    .erp-table-wrap { overflow-x:auto; }
    .erp-data-table { width:100%; min-width:840px; border-collapse:collapse; }
    .erp-data-table th, .erp-data-table td {
        padding:.55rem .7rem; border-bottom:1px solid #e5e7eb; color:#172642; font-size:.8rem; text-align:left;
        vertical-align:middle;
    }
    .erp-data-table th { background:#f8fafc; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; white-space:nowrap; }
    .erp-program { display:block; max-width:330px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .cc-credit-groups { display:grid; gap:.8rem; padding:.85rem; }
    .cc-credit-card {
        border:1px solid #e5e7eb; border-radius:8px; background:#fff;
        overflow:hidden;
    }
    .cc-credit-head {
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        padding:.65rem .75rem; border-bottom:1px solid #e5e7eb; background:#f8fafc;
    }
    .cc-credit-head > div { display:flex; align-items:center; gap:.45rem; min-width:0; }
    .cc-credit-head strong { color:#172642; font-size:.86rem; font-weight:900; }
    .cc-credit-rows {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(min(100%, 520px), 1fr));
        column-gap:1.2rem; padding:0 .75rem .35rem;
    }
    .cc-credit-row {
        display:grid; grid-template-columns:minmax(0, 1.1fr) minmax(190px, .9fr) 112px 78px;
        gap:.7rem; align-items:center; padding:.62rem .15rem;
        border-bottom:1px solid #eef2f7;
    }
    .cc-credit-main { min-width:0; }
    .cc-credit-main .erp-program {
        max-width:none; white-space:normal; overflow:visible; text-overflow:clip;
        color:#172642; line-height:1.45;
    }
    .cc-credit-main span, .cc-credit-meta span {
        display:block; color:#64748b; font-size:.68rem; font-weight:800;
    }
    .cc-credit-meta { display:grid; gap:.15rem; justify-items:start; }
    .erp-data-table td.erp-year-cell { text-align:right; }
    .erp-year-chip-list { display:flex; flex-wrap:wrap; gap:.25rem; justify-content:flex-end; }
    .erp-year-chip {
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:999px; background:#eef2f7; color:#334155;
        padding:.12rem .48rem; font-size:.68rem; font-weight:900; white-space:nowrap;
    }
    .cc-year-units { display:none; }
    .cc-year-units.is-open { display:grid; }
    .cc-year-unit-row {
        display:grid; grid-template-columns:90px minmax(0,1fr); gap:.5rem; align-items:center;
        border:1px solid #e5e7eb; border-radius:6px; padding:.55rem; background:#fbfdff;
    }
    .cc-year-unit-row strong { color:#172642; font-size:.78rem; }
    .erp-num { text-align:right !important; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .erp-badge { display:inline-flex; border:1px solid #cbd5e1; border-radius:999px; padding:.12rem .45rem; color:#334155; background:#f8fafc; font-size:.72rem; font-weight:900; }
    .erp-actions-col { width:92px; text-align:center !important; }
    .erp-actions { display:flex; align-items:center; justify-content:center; gap:.3rem; }
    .erp-actions form { margin:0; }
    .erp-icon-btn {
        width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #cbd5e1; border-radius:6px; background:#fff; color:#2563eb;
        font-size:.9rem; font-weight:900; cursor:pointer;
    }
    .erp-icon-btn:hover { background:#eff6ff; border-color:#93c5fd; }
    .erp-icon-danger { color:#dc2626; }
    .erp-icon-danger:hover { background:#fef2f2; border-color:#fecaca; }
    .erp-empty { padding:1rem; text-align:center; color:#94a3b8; font-size:.82rem; }
    .erp-empty-hidden { display:none; border-top:1px solid #e5e7eb; }
    .cc-modal {
        position:fixed; inset:0; z-index:1000; display:none; align-items:center; justify-content:center;
        padding:1rem; background:rgba(15,23,42,.48);
    }
    .cc-modal.is-open { display:flex; }
    .cc-modal-panel {
        width:min(680px, 100%); max-height:calc(100vh - 2rem); overflow:auto;
        border-radius:8px; border:1px solid #e5e7eb; background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.28);
    }
    .cc-modal-head {
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; background:#f8fafc;
    }
    .cc-modal-head h2 { margin:0; color:#172642; font-size:.98rem; font-weight:900; }
    .cc-modal-head p { margin:.15rem 0 0; color:#64748b; font-size:.72rem; }
    .cc-modal-close { border:0; background:transparent; color:#64748b; font-size:1.35rem; line-height:1; cursor:pointer; }
    .cc-modal-body { display:grid; gap:.75rem; padding:.9rem; }
    .settings-modal-panel { width:min(980px, 100%); }
    .settings-modal-body { display:grid; gap:.7rem; padding:.9rem; }
    .erp-settings-row {
        display:grid; grid-template-columns:130px minmax(0, 1fr);
        gap:.75rem; align-items:center; border:1px solid #e5e7eb; border-radius:8px; padding:.7rem; background:#fff;
    }
    .settings-reset-form { display:flex; justify-content:flex-end; }
    .erp-modal-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.7rem; }
    .erp-modal-grid-wide { grid-template-columns:1fr; }
    .erp-field b { color:#dc2626; }
    .erp-field em { color:#f97316; font-style:normal; font-size:.68rem; }
    .erp-calc-panel { border:1px solid #bfdbfe; background:#eff6ff; border-radius:8px; padding:.75rem; }
    .cc-modal-actions { display:flex; justify-content:flex-end; gap:.5rem; padding-top:.2rem; }
    @media (max-width:900px) {
        .erp-topbar, .erp-panel-head { align-items:stretch; flex-direction:column; }
        .erp-summary-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        .erp-settings-actions { justify-content:flex-start; max-width:none; }
        .erp-split-head { display:none; }
        .erp-split-row { grid-template-columns:1fr; }
        .erp-settings-row { grid-template-columns:1fr; }
        .erp-toolbar { width:100%; align-items:stretch; flex-direction:column; }
        .erp-search, .erp-select { width:100%; }
        .cc-credit-row { grid-template-columns:1fr; align-items:start; }
        .cc-credit-row .erp-year-chip-list { justify-content:flex-start; }
        .cc-credit-meta { justify-items:start; }
        .cc-credit-row .erp-actions { justify-content:flex-start; }
    }
    @media (max-width:640px) {
        .erp-summary-grid { grid-template-columns:1fr; }
        .erp-modal-grid { grid-template-columns:1fr; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dirty-form').forEach(form => {
        const mark = () => form.classList.add('is-dirty');
        form.querySelectorAll('.data-dirty').forEach(el => {
            el.addEventListener('input', mark);
            el.addEventListener('change', mark);
        });
    });

    const filter = document.getElementById('cc-filter');
    const levelFilter = document.getElementById('cc-level-filter');
    const nores = document.getElementById('cc-nores');
    const visibleCount = document.getElementById('cc-visible-count');

    function applyFilters() {
        const q = (filter?.value || '').trim().toLowerCase();
        const level = levelFilter?.value || '';
        let visible = 0;

        document.querySelectorAll('.cc-row').forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const rowLevel = row.dataset.level || '';
            const hit = (!q || name.includes(q)) && (!level || rowLevel === level);
            row.style.display = hit ? '' : 'none';
            if (hit) visible++;
        });

        document.querySelectorAll('.cc-credit-card').forEach(card => {
            const hasVisibleRow = Array.from(card.querySelectorAll('.cc-row'))
                .some(row => row.style.display !== 'none');
            card.style.display = hasVisibleRow ? '' : 'none';
        });

        if (visibleCount) visibleCount.textContent = visible;
        if (nores) nores.style.display = visible === 0 ? 'block' : 'none';
    }

    filter?.addEventListener('input', applyFilters);
    levelFilter?.addEventListener('change', applyFilters);

    const settingsModals = Array.from(document.querySelectorAll('.settings-modal'));

    function openSettingsModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeSettingsModal(modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function closeAllSettingsModals() {
        settingsModals.forEach(closeSettingsModal);
    }

    document.querySelectorAll('.erp-setting-trigger').forEach(button => {
        button.addEventListener('click', () => openSettingsModal(button.dataset.settingsModal));
    });

    const modal = document.getElementById('cc-modal');
    const modalTitle = document.getElementById('cc-modal-title');
    const form = document.getElementById('cc-modal-form');
    const method = document.getElementById('cc-form-method');
    const degreeProgram = document.getElementById('cc-degree-program');
    const programSource = document.getElementById('cc-program-source');
    const programSearch = document.getElementById('cc-program-search');
    const programList = document.getElementById('cc-program-list');
    const unitBach = document.getElementById('cc-unit-bach');
    const singleUnitWrap = document.getElementById('cc-single-unit-wrap');
    const yearUnits = document.getElementById('cc-year-units');
    const govDoc = document.getElementById('cc-gov-doc');
    const startYear = document.getElementById('cc-start-year');
    const submit = document.getElementById('cc-submit');
    const createUrl = @json(route('head_of_finance.settings.course-credits.store'));
    const programChoices = Array.from(programSource?.options || []).map(option => ({
        id: option.value,
        label: option.textContent.trim(),
        level: option.dataset.level || '',
        displayYears: jsonData(option.dataset.displayYears),
        search: `${option.value} ${option.textContent}`.toLowerCase(),
    }));

    function jsonData(value, fallback = []) {
        try {
            return value ? JSON.parse(value) : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function setSingleUnitMode(enabled) {
        singleUnitWrap.style.display = enabled ? '' : 'none';
        unitBach.disabled = !enabled;
        unitBach.required = enabled;
        yearUnits.classList.toggle('is-open', !enabled);
        if (enabled) yearUnits.innerHTML = '';
    }

    function renderYearUnits(rows, mode) {
        if (rows.length <= 1) {
            setSingleUnitMode(true);
            return;
        }

        setSingleUnitMode(false);
        yearUnits.innerHTML = rows.map(row => {
            const id = mode === 'edit' ? row.id : row.program_id;
            const name = mode === 'edit' ? `setting_units[${id}]` : `course_credit_units[${id}]`;
            const value = row.unit ?? '';
            const year = row.year ? `ປີ ${row.year}` : 'ບໍ່ລະບຸປີ';

            return `
                <label class="cc-year-unit-row">
                    <strong>${year}</strong>
                    <input type="number" name="${name}" min="1" max="999" step="0.5" value="${value}" class="erp-input erp-input-num" required>
                </label>
            `;
        }).join('');
    }

    function selectedProgramRows() {
        return programChoices.find(program => program.id === degreeProgram.value)?.displayYears || [];
    }

    function closeProgramList() {
        programList?.classList.remove('is-open');
        programSearch?.setAttribute('aria-expanded', 'false');
    }

    function renderProgramList(query = '') {
        if (!programList) return;

        const q = query.trim().toLowerCase();
        const matches = programChoices
            .filter(program => !q || program.search.includes(q))
            .slice(0, 80);

        if (!matches.length) {
            programList.innerHTML = '<div class="cc-program-empty">ບໍ່ພົບຫຼັກສູດ</div>';
        } else {
            programList.innerHTML = matches.map(program => `
                <button type="button" class="cc-program-option" data-program-id="${program.id}" role="option">
                    ${escapeHtml(program.label)}
                </button>
            `).join('');
        }

        programList.classList.add('is-open');
        programSearch?.setAttribute('aria-expanded', 'true');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function selectProgram(id, mode = 'create') {
        const program = programChoices.find(item => item.id === String(id));
        degreeProgram.value = program?.id || '';
        programSearch.value = program?.label || '';
        closeProgramList();
        renderYearUnits(selectedProgramRows(), mode);
    }

    function openModal(mode, data = {}) {
        form.action = mode === 'edit' ? data.url : createUrl;
        method.disabled = mode !== 'edit';
        modalTitle.textContent = mode === 'edit' ? 'ແກ້ໄຂໜ່ວຍກິດ' : 'ເພີ່ມໜ່ວຍກິດ';
        submit.textContent = mode === 'edit' ? 'ອັບເດດ' : 'ບັນທຶກ';

        form.reset();
        selectProgram(data.degreeProgramId || '', mode);
        unitBach.value = data.courseCreditUnit || '';
        govDoc.value = data.govDocId || '';
        startYear.value = data.startYear || @json(date('Y'));
        renderYearUnits(data.displayYears || selectedProgramRows(), mode);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        setTimeout(() => programSearch.focus(), 50);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        form.reset();
        degreeProgram.value = '';
        programSearch.value = '';
        closeProgramList();
        method.disabled = true;
        setSingleUnitMode(true);
    }

    document.getElementById('cc-open-create')?.addEventListener('click', () => openModal('create'));
    programSearch?.addEventListener('click', () => renderProgramList(programSearch.value));
    programSearch?.addEventListener('input', () => {
        degreeProgram.value = '';
        renderProgramList(programSearch.value);
        setSingleUnitMode(true);
    });
    programSearch?.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeProgramList();
    });
    programList?.addEventListener('click', event => {
        const option = event.target.closest('.cc-program-option');
        if (!option) return;
        selectProgram(option.dataset.programId);
    });
    form?.addEventListener('submit', event => {
        if (degreeProgram.value) return;
        event.preventDefault();
        programSearch.focus();
        programSearch.setCustomValidity('ກະລຸນາເລືອກຫຼັກສູດຈາກລາຍການ');
        programSearch.reportValidity();
        setTimeout(() => programSearch.setCustomValidity(''), 0);
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('#cc-program-picker')) {
            closeProgramList();
        }

        const settingsClose = event.target.closest('[data-settings-close]');
        if (settingsClose) {
            closeSettingsModal(settingsClose.closest('.settings-modal'));
            return;
        }

        if (event.target.classList.contains('settings-modal')) {
            closeSettingsModal(event.target);
            return;
        }

        const edit = event.target.closest('.js-cc-edit');
        if (edit) {
            openModal('edit', {
                url: edit.dataset.url,
                degreeProgramId: edit.dataset.degreeProgramId,
                courseCreditUnit: edit.dataset.courseCreditUnit,
                govDocId: edit.dataset.govDocId,
                startYear: edit.dataset.startYear,
                displayYears: jsonData(edit.dataset.displayYears),
            });
            return;
        }

        if (event.target.matches('[data-cc-close]') || event.target === modal) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;

        const activeSettingsModal = settingsModals.find(item => item.classList.contains('is-open'));
        if (activeSettingsModal) {
            closeAllSettingsModals();
            return;
        }

        if (modal.classList.contains('is-open')) closeModal();
    });
    document.querySelectorAll('.erp-split-row').forEach(card => {
        const year1 = card.querySelector('.js-split-year1');
        const year2 = card.querySelector('.js-split-year2');
        const sync = (source, target) => {
            const value = Math.max(0, Math.min(100, Number(source.value) || 0));
            source.value = value;
            target.value = Math.round((100 - value) * 100) / 100;
        };

        year1?.addEventListener('input', () => sync(year1, year2));
        year2?.addEventListener('input', () => sync(year2, year1));
    });
});
</script>
@endpush
@endsection
