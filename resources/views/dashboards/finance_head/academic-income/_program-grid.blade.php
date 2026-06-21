@php $useYear1Unit = $useYear1Unit ?? false; @endphp
<div class="ai-rows">
@forelse($programs as $p)
    @php
        $totalCreditUnit = $p->latestCourseCredit?->course_credit_unit;
        $creditUnit = $totalCreditUnit;
        if ($totalCreditUnit && in_array($p->level, ['master', 'phd'], true)) {
            $creditUnit = (float) $totalCreditUnit * ($useYear1Unit
                ? \App\Models\CourseCreditSplitSetting::year1For($p->level)
                : \App\Models\CourseCreditSplitSetting::year2For($p->level));
        }
        $price    = $creditPrices[$p->level]?->credit_unit_price ?? null;
        $warn     = !$creditUnit || !$price;
        $existing = $existingItems->get($section . '_' . $p->id);
        $val      = (int) old($inputPrefix . '.' . $p->id, $existing?->student_count ?? 0);
    @endphp
    <label class="ai-row @if($warn) is-warn @endif @if($val<=0) is-zero @endif"
           data-name="{{ \Illuminate\Support\Str::lower($p->name) }}"
           data-save-kind="count"
           data-input-prefix="{{ $inputPrefix }}"
           data-program-id="{{ $p->id }}">
        <span class="ai-row-name">
            @if($warn)<span class="ai-warn-dot" title="ຍັງບໍ່ໄດ້ຕັ້ງຄ່າໜ່ວຍກິດ / ລາຄາ"></span>@endif
            <span class="ai-row-txt" title="{{ $p->name }}">{{ $p->name }}</span>
        </span>
        <input type="number" name="{{ $inputPrefix }}[{{ $p->id }}]" min="0" inputmode="numeric"
            value="{{ $val }}" class="ai-num" data-sec="{{ $section }}">
    </label>
@empty
    <div class="ai-empty">ບໍ່ມີສາຂາວິຊາ — ກະລຸນາຕັ້ງຄ່າກ່ອນ</div>
@endforelse
</div>
