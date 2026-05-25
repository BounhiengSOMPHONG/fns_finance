@php $useYear1Unit = $useYear1Unit ?? false; @endphp
<table class="fns-table">
    <thead>
        <tr>
            <th style="width:5rem;">ຊັ້ນປີ</th>
            <th>ສາຂາວິຊາ</th>
            <th style="width:6rem;">ລະດັບ</th>
            <th class="col-num" style="width:8rem;">ຈຳນວນ ນ/ສ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($programs as $p)
        @php
            $creditUnit = $useYear1Unit
                ? ($p->latestCourseCredit?->year1_credit_unit ?? null)
                : ($p->latestCourseCredit?->course_credit_unit ?? null);
            $price    = $creditPrices[$p->level]?->credit_unit_price ?? null;
            $warn     = !$creditUnit || !$price;
            $existing = $existingItems->get($section . '_' . $p->id);
        @endphp
        <tr @if($warn) style="background:rgba(245,158,11,0.04);" @endif>
            <td style="font-size:0.83rem; color:var(--fns-gray-600); text-align:center;">
                {{ $p->study_year ? 'ປີ '.$p->study_year : '—' }}
            </td>
            <td style="font-size:0.85rem;">
                {{ $p->name }}
                @if($warn)
                    <span title="ຍັງບໍ່ຕັ້ງຄ່າໜ່ວຍກິດ/ລາຄາ" style="color:#d97706; font-size:0.7rem; margin-left:0.3rem;">⚠</span>
                @endif
            </td>
            <td>
                <span class="fns-badge {{ $p->level==='bachelor'?'fns-badge-blue':($p->level==='master'?'fns-badge-green':'fns-badge-purple') }}" style="font-size:0.68rem;">
                    {{ $p->level_label }}
                </span>
            </td>
            <td>
                <input type="number" name="{{ $inputPrefix }}[{{ $p->id }}]" min="0"
                    value="{{ old($inputPrefix.'.'.$p->id, $existing?->student_count ?? 0) }}"
                    class="fns-input"
                    style="padding:0.35rem 0.5rem; font-size:0.85rem; text-align:right;">
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align:center; padding:1.5rem; color:var(--fns-gray-400);">ບໍ່ມີສາຂາວິຊາ — ກະລຸນາຕັ້ງຄ່າ</td>
        </tr>
        @endforelse
    </tbody>
</table>
