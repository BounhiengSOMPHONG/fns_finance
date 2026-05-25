@php $useYear1Unit = $useYear1Unit ?? false; @endphp
<div class="ai-pg-grid">
@forelse($programs as $p)
    @php
        $creditUnit = $useYear1Unit
            ? ($p->latestCourseCredit?->year1_credit_unit ?? null)
            : ($p->latestCourseCredit?->course_credit_unit ?? null);
        $price    = $creditPrices[$p->level]?->credit_unit_price ?? null;
        $warn     = !$creditUnit || !$price;
        $existing = $existingItems->get($section . '_' . $p->id);
    @endphp
    <label class="ai-pg @if($warn) ai-pg-warn @endif" title="{{ $p->name }}">
        <div class="ai-pg-info">
            <div class="ai-pg-name">{{ $p->name }}@if($warn) <span style="color:#d97706;" title="ຍັງບໍ່ຕັ້ງຄ່າໜ່ວຍກິດ/ລາຄາ">⚠</span>@endif</div>
            <div class="ai-pg-sub">
                <span class="fns-badge {{ $p->level==='bachelor'?'fns-badge-blue':($p->level==='master'?'fns-badge-green':'fns-badge-purple') }}" style="font-size:0.58rem; padding:0.08rem 0.4rem;">{{ $p->level_label }}</span>
                <span>{{ $p->study_year ? 'ປີ '.$p->study_year : '—' }}</span>
            </div>
        </div>
        <input type="number" name="{{ $inputPrefix }}[{{ $p->id }}]" min="0"
            value="{{ old($inputPrefix.'.'.$p->id, $existing?->student_count ?? 0) }}"
            class="fns-input ai-pg-input">
    </label>
@empty
    <div style="grid-column:1/-1; text-align:center; padding:1.25rem; color:var(--fns-gray-400); border:1px dashed var(--fns-gray-200); border-radius:8px;">ບໍ່ມີສາຂາວິຊາ — ກະລຸນາຕັ້ງຄ່າ</div>
@endforelse
</div>
