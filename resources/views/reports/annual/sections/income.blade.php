{{--
    Variables:
      $plan        – AcademicIncomePlan|null
      $grouped     – Collection keyed by section_code
      $feeYear2_4  – RegistrationFeeSetting (year2_4) with items|null
      $feeYear1    – RegistrationFeeSetting (year1)    with items|null
--}}

@if(! $plan)
<div class="placeholder">
    <div class="ph-icon">📋</div>
    <div class="ph-text">ບໍ່ມີຂໍ້ມູນລາຍຮັບວິຊາການ</div>
</div>
@else

@php
/* ─ helpers ─────────────────────────────────────── */
$fmt = fn($v) => number_format((float)$v, 0);
$pct = fn($v) => number_format((float)$v * 100, 0).'%';

/* ─ raw item collections ─────────────────────────── */
$sec11 = $grouped->get('1.1', collect());  // credit yr2-4 + master/phd yr2+
$sec12 = $grouped->get('1.2', collect());  // yr2-4 registration (aggregate)
$sec13 = $grouped->get('1.3', collect());  // credit yr1 (bachelor + master yr1)
$sec14 = $grouped->get('1.4', collect());  // yr1 registration (aggregate)
$sec4  = $grouped->get('4',   collect());  // ຄ່າບູລະນະຫ້ອງທົດລອງຄອມ
$sec5  = $grouped->get('5',   collect());  // ຄ່າບຳລຸງອຸປະກອນຫ້ອງທົດລອງ

/* ─ sec13 split bachelor / master ───────────────── */
$sec13_bach = $sec13->filter(fn($it) => $it->degreeProgram?->level === 'bachelor');
$sec13_mast = $sec13->filter(fn($it) => in_array($it->degreeProgram?->level, ['master','phd']));

/* ─ gross helper (credit rows) ──────────────────── */
$creditGross = fn($it) => $it->student_count * $it->snap_course_credit_unit * $it->snap_credit_unit_price;

/* ─ summary group helper ─────────────────────────── */
$sumGrp = function($items) use ($creditGross) {
    $cnt   = $items->sum('student_count');
    $gross = $items->sum($creditGross);
    $fac   = (float) $items->sum('total_income');
    $nuol  = $gross - $fac;
    return compact('cnt','gross','fac','nuol');
};

/* ─ Section 1.1 yr2/3/4 bachelor sub-totals ─────── */
$grpBach = fn($yr) => $sec11->filter(fn($it) =>
    $it->degreeProgram?->level === 'bachelor' && $it->degreeProgram?->study_year == $yr
);
$s11y2 = $sumGrp($grpBach(2));
$s11y3 = $sumGrp($grpBach(3));
$s11y4 = $sumGrp($grpBach(4));

$t11 = [
    'cnt'   => $sec11->sum('student_count'),
    'gross' => $sec11->sum($creditGross),
    'fac'   => (float) $sec11->sum('total_income'),
];
$t11['nuol'] = $t11['gross'] - $t11['fac'];

/* ─ Section 2.5: ALL masters (sec11 yr2+ + sec13 yr1) ── */
$allMasters = $sec11->filter(fn($it) => in_array($it->degreeProgram?->level, ['master','phd']))
                    ->concat($sec13_mast);
$s25 = $sumGrp($allMasters);

/* ─ Section 2.1: bachelor yr1 gross; Excel student count = all sec13 ─ */
$t13_bach    = $sumGrp($sec13_bach);
$cnt13_all   = $sec13->sum('student_count'); // 280 (includes yr1 masters)

/* ─ Section 1.3 full total (for detail table footer) */
$t13 = [
    'cnt'   => $sec13->sum('student_count'),
    'gross' => $sec13->sum($creditGross),
    'fac'   => (float) $sec13->sum('total_income'),
];
$t13['nuol'] = $t13['gross'] - $t13['fac'];

/* ─ Registration sections ────────────────────────── */
$r12item = $sec12->first();
$cnt12   = $r12item?->student_count ?? 0;
$r14item = $sec14->first();
$cnt14   = $r14item?->student_count ?? 0;

$regFeeTotal = function($setting, $count) {
    if (! $setting || ! $count) return ['gross'=>0,'nuol'=>0,'fac'=>0];
    $gross = 0; $nuol = 0;
    foreach ($setting->items as $fi) {
        $g = $fi->amount * $count;
        $gross += $g;
        $nuol  += $g * $fi->nuol_pct;
    }
    return ['gross' => $gross, 'nuol' => $nuol, 'fac' => $gross - $nuol];
};
$rt12 = $regFeeTotal($feeYear2_4, $cnt12);
$rt14 = $regFeeTotal($feeYear1,   $cnt14);

/* ─ Sections 4 & 5 ──────────────────────────────── */
$s4item = $sec4->first();
$s5item = $sec5->first();
$s4 = [
    'cnt'   => $s4item?->student_count ?? 0,
    'gross' => ($s4item?->student_count ?? 0) * ($s4item?->snap_registration_fee_rate ?? 0),
    'fac'   => (float)($s4item?->total_income ?? 0),
    'nuol'  => 0,
    'rate'  => $s4item?->snap_registration_fee_rate ?? 0,
];
$s5 = [
    'cnt'   => $s5item?->student_count ?? 0,
    'gross' => ($s5item?->student_count ?? 0) * ($s5item?->snap_registration_fee_rate ?? 0),
    'fac'   => (float)($s5item?->total_income ?? 0),
    'nuol'  => 0,
    'rate'  => $s5item?->snap_registration_fee_rate ?? 0,
];

/* ─ Grand totals (all sections) ─────────────────── */
$grandGross = $t11['gross'] + $rt12['gross'] + $t13['gross'] + $rt14['gross'] + $s4['gross'] + $s5['gross'];
$grandNuol  = $t11['nuol']  + $rt12['nuol']  + $t13['nuol']  + $rt14['nuol'];
$grandFac   = $t11['fac']   + $rt12['fac']   + $t13['fac']   + $rt14['fac']   + $s4['fac']  + $s5['fac'];
@endphp

{{-- ═══════════════════════════════════════════════════════════════════
     SUMMARY TABLE  (matches Excel R006-R023)
     Columns: ລ/ດ | ເນື້ອໃນ | ຈຳ ພົນ | ອັດຕາ/ໜ່ວຍ | ລວມ | ມຊ | ຄວທ | ຄ່າສອນ | ເຫຼືອ
     ═══════════════════════════════════════════════════════════════════ --}}
<table class="rpt-table avoid-break" style="margin-top:4pt;">
    <thead>
        <tr>
            <th style="width:5%">ລ/ດ</th>
            <th style="width:28%">ເນື້ອໃນເອກະສານ</th>
            <th style="width:7%">ຈຳນວນ<br>ພົນ</th>
            <th style="width:8%">ອັດຕາ<br>ຕໍ່ໜ່ວຍ</th>
            <th style="width:11%">ຈຳນວນເງິນລວມ<br>(ກີບ)</th>
            <th style="width:10%">ມອບພັນທະ ມຊ<br>(ກີບ)</th>
            <th style="width:11%">ລາຍຮັບຂອງ ຄວທ<br>(ກີບ)</th>
            <th style="width:10%">ຄ່າສິດສອນ<br>(ກີບ)</th>
            <th style="width:10%">ເຫຼືອຈາກ<br>ຄ່າສອນ (ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        {{-- Grand total row --}}
        <tr style="font-weight:700; background:#e8edf5;">
            <td class="c">—</td>
            <td>ລວມລາຍຮັບທັງໝົດ</td>
            <td class="r"></td>
            <td class="r"></td>
            <td class="r">{{ $fmt($grandGross) }}</td>
            <td class="r">{{ $fmt($grandNuol) }}</td>
            <td class="r">{{ $fmt($grandFac) }}</td>
            <td class="r">—</td>
            <td class="r">—</td>
        </tr>
        {{-- 1. Registration fees --}}
        <tr style="font-weight:600;">
            <td class="c">1</td>
            <td>ຄ່າລົງທະບຽນນັກສຶກສາ</td>
            <td class="r"></td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt12['gross'] + $rt14['gross']) }}</td>
            <td class="r">{{ $fmt($rt12['nuol'] + $rt14['nuol']) }}</td>
            <td class="r">{{ $fmt($rt12['fac'] + $rt14['fac']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt12['fac'] + $rt14['fac']) }}</td>
        </tr>
        <tr>
            <td class="c dim">1.1</td>
            <td class="dim" style="padding-left:14pt;">ນັກສຶກສາ ປີທີ 1</td>
            <td class="r">{{ $fmt($cnt14) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt14['gross']) }}</td>
            <td class="r">{{ $fmt($rt14['nuol']) }}</td>
            <td class="r">{{ $fmt($rt14['fac']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt14['fac']) }}</td>
        </tr>
        <tr>
            <td class="c dim">1.2</td>
            <td class="dim" style="padding-left:14pt;">ນັກສຶກສາ ປີທີ 2, 3, 4</td>
            <td class="r">{{ $fmt($cnt12) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt12['gross']) }}</td>
            <td class="r">{{ $fmt($rt12['nuol']) }}</td>
            <td class="r">{{ $fmt($rt12['fac']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($rt12['fac']) }}</td>
        </tr>
        {{-- 2. Credit fees --}}
        <tr style="font-weight:600;">
            <td class="c">2</td>
            <td>ຄ່າໜ່ວຍກິດລະບົບຈ່າຍເງິນ</td>
            <td class="r"></td>
            <td class="r"></td>
            <td class="r">{{ $fmt($t11['gross'] + $t13['gross']) }}</td>
            <td class="r">{{ $fmt($t11['nuol'] + $t13['nuol']) }}</td>
            <td class="r">{{ $fmt($t11['fac'] + $t13['fac']) }}</td>
            <td class="r">—</td>
            <td class="r">—</td>
        </tr>
        <tr>
            <td class="c dim">2.1</td>
            <td class="dim" style="padding-left:14pt;">ນັກສຶກສາ ປີທີ 1</td>
            <td class="r">{{ $fmt($cnt13_all) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($t13_bach['gross']) }}</td>
            <td class="r">{{ $fmt($t13_bach['nuol']) }}</td>
            <td class="r">{{ $fmt($t13_bach['fac']) }}</td>
            <td class="r"></td>
            <td class="r"></td>
        </tr>
        @foreach([2=>$s11y2, 3=>$s11y3, 4=>$s11y4] as $yr => $sg)
        <tr>
            <td class="c dim">2.{{ $yr }}</td>
            <td class="dim" style="padding-left:14pt;">ນັກສຶກສາ ປີທີ {{ $yr }}</td>
            <td class="r">{{ $fmt($sg['cnt']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($sg['gross']) }}</td>
            <td class="r">{{ $fmt($sg['nuol']) }}</td>
            <td class="r">{{ $fmt($sg['fac']) }}</td>
            <td class="r"></td>
            <td class="r"></td>
        </tr>
        @endforeach
        <tr>
            <td class="c dim">2.5</td>
            <td class="dim" style="padding-left:14pt;">ນັກສຶກສາ ປ. ໂທ + ເອກ</td>
            <td class="r">{{ $fmt($s25['cnt']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($s25['gross']) }}</td>
            <td class="r">{{ $fmt($s25['nuol']) }}</td>
            <td class="r">{{ $fmt($s25['fac']) }}</td>
            <td class="r"></td>
            <td class="r"></td>
        </tr>
        {{-- rows 3-6 --}}
        <tr><td class="c dim">3</td><td class="dim">ຄ່າລົງທະບຽນເທີມສາມ</td><td class="r">0</td><td class="r">90,000</td><td class="r">0</td><td class="r">0</td><td class="r">0</td><td class="r"></td><td class="r">0</td></tr>
        <tr>
            <td class="c dim">4</td>
            <td class="dim">ຄ່າບູລະນະຫ້ອງທົດລອງຄອມພິວເຕີ</td>
            <td class="r">{{ $s4['cnt'] ?: '—' }}</td>
            <td class="r">{{ $s4['rate'] ? $fmt($s4['rate']) : '—' }}</td>
            <td class="r">{{ $fmt($s4['gross']) }}</td>
            <td class="r">0</td>
            <td class="r">{{ $fmt($s4['fac']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($s4['fac']) }}</td>
        </tr>
        <tr>
            <td class="c dim">5</td>
            <td class="dim">ຄ່າບຳລຸງອຸປະກອນຫ້ອງທົດລອງ</td>
            <td class="r">{{ $s5['cnt'] ?: '—' }}</td>
            <td class="r">{{ $s5['rate'] ? $fmt($s5['rate']) : '—' }}</td>
            <td class="r">{{ $fmt($s5['gross']) }}</td>
            <td class="r">0</td>
            <td class="r">{{ $fmt($s5['fac']) }}</td>
            <td class="r"></td>
            <td class="r">{{ $fmt($s5['fac']) }}</td>
        </tr>
        <tr><td class="c dim">6</td><td class="dim">ຄ່າບໍລິການວິຊາການ ແລະ ຄ່າບໍລິການອື່ນໆ</td><td class="r">—</td><td class="r">—</td><td class="r">0</td><td class="r">0</td><td class="r">0</td><td class="r"></td><td class="r">0</td></tr>
    </tbody>
</table>

{{-- ── Summary signature block (4 persons) ── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12pt; margin-top:14pt; margin-bottom:4pt; text-align:center; font-size:7.5pt;">
    @foreach(['ວັນທີ','ວັນທີ','ວັນທີ','ວັນທີ'] as $i => $lbl)
    <div>
        <div style="color:#9ca3af;margin-bottom:1pt;">{{ $lbl }}</div>
        <div style="height:30pt;"></div>
        <div style="border-top:0.5pt solid #555;padding-top:2pt;">
            @if($i === 0) ຄະນະບໍດີ
            @elseif($i === 1) ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ
            @elseif($i === 2) ຫົວໜ້າພະແນກວິຊາການ
            @else ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 1.1 — Credit fees, Year 2-4 bachelor + Master/PhD
     Columns: ລ/ດ | ຫຼັກສູດ | ອັດຕາ/ຄົນ | ຈຳ ຄົນ | ລວມ | %ມຊ | ພັນທະ ມຊ | %ຄວທ | ລາຍຮັບ ຄວທ
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="subsec-hd keep-with-next" style="margin-top:10pt;">
    1.1. &nbsp; ລາຍຮັບຄ່າໜ່ວຍກິດນັກຮຽນແຕ່ປີ 2-4 ລະບົບຈ່າຍເງິນ ແລະ ປະລິນຍາໂທ
</div>
<table class="rpt-table">
    <thead>
        <tr>
            <th style="width:4%">ລ/ດ</th>
            <th style="width:24%">ລາຍການ / ຫຼັກສູດ</th>
            <th style="width:11%">ອັດຕາຄ່າຮຽນ<br>ຕໍ່ຄົນ (ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>ຄົນ</th>
            <th style="width:11%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ມຊ</th>
            <th style="width:11%">ພັນທະ ມຊ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ຄວທ</th>
            <th style="width:11%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec11 as $idx => $it)
        @php
            $fpp   = $it->snap_course_credit_unit * $it->snap_credit_unit_price;
            $gr    = $it->student_count * $fpp;
            $nuolV = $it->snap_nuol_pct;
            $facV  = 1 - $nuolV;
            // Format program name: "ປີ X name" for bachelor, plain for master/phd
            $progName = $it->degreeProgram
                ? ($it->degreeProgram->level === 'bachelor'
                    ? 'ປີ '.$it->degreeProgram->study_year.' '.$it->degreeProgram->name
                    : $it->degreeProgram->name)
                : '—';
        @endphp
        <tr>
            <td class="c dim">{{ $idx + 1 }}</td>
            <td class="prog">{{ $progName }}</td>
            <td class="r">{{ $fpp ? $fmt($fpp) : '—' }}</td>
            <td class="r">{{ $fmt($it->student_count) }}</td>
            <td class="r">{{ $fmt($gr) }}</td>
            <td class="c">{{ $pct($nuolV) }}</td>
            <td class="r dim">{{ $fmt($gr * $nuolV) }}</td>
            <td class="c">{{ $pct($facV) }}</td>
            <td class="r">{{ $fmt($it->total_income) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="c dim" style="padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="3" class="c">ລວມ</td>
            <td class="r">{{ $fmt($t11['cnt']) }}</td>
            <td class="r">{{ $fmt($t11['gross']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($t11['nuol']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($t11['fac']) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 1.2 — Registration fees, Year 2-4 (per fee item)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="subsec-hd keep-with-next" style="margin-top:8pt;">
    1.2. &nbsp; ລາຍຮັບຄ່າລົງທະບຽນນັກສຶກສາປີທີ 2-4 ຂອງ ຄວທ
</div>
<table class="rpt-table">
    <thead>
        <tr>
            <th style="width:4%">ລ/ດ</th>
            <th style="width:24%">ລາຍການ</th>
            <th style="width:11%">ອັດຕາຄ່າທຳນຽມ<br>ຕໍ່ຄົນ (ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>ຄົນ</th>
            <th style="width:11%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ມຊ</th>
            <th style="width:11%">ພັນທະ ມຊ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ຄວທ</th>
            <th style="width:11%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @if($feeYear2_4 && $cnt12 > 0)
            @foreach($feeYear2_4->items->sortBy('sort_order') as $idx => $fi)
            @php
                $gr    = $fi->amount * $cnt12;
                $nSh   = $gr * $fi->nuol_pct;
                $fSh   = $gr - $nSh;
                $facPct = 1 - $fi->nuol_pct;
            @endphp
            <tr>
                <td class="c dim">{{ $idx + 1 }}</td>
                <td class="prog">{{ $fi->name }}</td>
                <td class="r">{{ $fmt($fi->amount) }}</td>
                <td class="r">{{ $fmt($cnt12) }}</td>
                <td class="r">{{ $fmt($gr) }}</td>
                <td class="c">{{ $pct($fi->nuol_pct) }}</td>
                <td class="r dim">{{ $fmt($nSh) }}</td>
                <td class="c">{{ $pct($facPct) }}</td>
                <td class="r">{{ $fmt($fSh) }}</td>
            </tr>
            @endforeach
        @else
            <tr><td colspan="9" class="c dim" style="padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td></td>
            <td class="c">ລວມ</td>
            <td class="r">{{ $feeYear2_4 ? $fmt($feeYear2_4->total_rate) : '—' }}</td>
            <td class="r">{{ $fmt($cnt12) }}</td>
            <td class="r">{{ $fmt($rt12['gross']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($rt12['nuol']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($rt12['fac']) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 1.3 — Credit fees, Year 1 bachelor
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="subsec-hd keep-with-next" style="margin-top:8pt;">
    1.3. &nbsp; ລາຍຮັບຄ່າໜ່ວຍກິດປີ 1 ລະບົບຈ່າຍເງິນ
</div>
<table class="rpt-table">
    <thead>
        <tr>
            <th style="width:4%">ລ/ດ</th>
            <th style="width:24%">ລາຍການ / ຫຼັກສູດ</th>
            <th style="width:11%">ອັດຕາຄ່າຮຽນ<br>ຕໍ່ຄົນ (ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>ຄົນ</th>
            <th style="width:11%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ມຊ</th>
            <th style="width:11%">ພັນທະ ມຊ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ຄວທ</th>
            <th style="width:11%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sec13 as $idx => $it)
        @php
            $fpp   = $it->snap_course_credit_unit * $it->snap_credit_unit_price;
            $gr    = $it->student_count * $fpp;
            $nuolV = $it->snap_nuol_pct;
            $facV  = 1 - $nuolV;
            $progName = $it->degreeProgram?->name ?? '—';
        @endphp
        <tr>
            <td class="c dim">{{ $idx + 1 }}</td>
            <td class="prog">{{ $progName }}</td>
            <td class="r">{{ $fpp ? $fmt($fpp) : '—' }}</td>
            <td class="r">{{ $fmt($it->student_count) }}</td>
            <td class="r">{{ $fmt($gr) }}</td>
            <td class="c">{{ $pct($nuolV) }}</td>
            <td class="r dim">{{ $fmt($gr * $nuolV) }}</td>
            <td class="c">{{ $pct($facV) }}</td>
            <td class="r">{{ $fmt($it->total_income) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="c dim" style="padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="3" class="c">ລວມ</td>
            <td class="r">{{ $fmt($t13['cnt']) }}</td>
            <td class="r">{{ $fmt($t13['gross']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($t13['nuol']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($t13['fac']) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════════════════════════════════════════════════════════
     SECTION 1.4 — Registration fees, Year 1 (per fee item)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="subsec-hd keep-with-next" style="margin-top:8pt;">
    1.4. &nbsp; ຄ່າລົງທະບຽນນັກສຶກສາປີທີ 1 ລະບົບຈ່າຍເງິນຂອງ ຄວທ
</div>
<table class="rpt-table">
    <thead>
        <tr>
            <th style="width:4%">ລ/ດ</th>
            <th style="width:24%">ລາຍການ</th>
            <th style="width:11%">ອັດຕາຄ່າທຳນຽມ<br>ຕໍ່ຄົນ (ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>ຄົນ</th>
            <th style="width:11%">ລາຍຮັບລວມ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ມຊ</th>
            <th style="width:11%">ພັນທະ ມຊ<br>(ກີບ)</th>
            <th style="width:7%">ຈຳນວນ<br>% ຄວທ</th>
            <th style="width:11%">ລາຍຮັບ ຄວທ<br>(ກີບ)</th>
        </tr>
    </thead>
    <tbody>
        @if($feeYear1 && $cnt14 > 0)
            @foreach($feeYear1->items->sortBy('sort_order') as $idx => $fi)
            @php
                $gr    = $fi->amount * $cnt14;
                $nSh   = $gr * $fi->nuol_pct;
                $fSh   = $gr - $nSh;
                $facPct = 1 - $fi->nuol_pct;
            @endphp
            <tr>
                <td class="c dim">{{ $idx + 1 }}</td>
                <td class="prog">{{ $fi->name }}</td>
                <td class="r">{{ $fmt($fi->amount) }}</td>
                <td class="r">{{ $fmt($cnt14) }}</td>
                <td class="r">{{ $fmt($gr) }}</td>
                <td class="c">{{ $pct($fi->nuol_pct) }}</td>
                <td class="r dim">{{ $fmt($nSh) }}</td>
                <td class="c">{{ $pct($facPct) }}</td>
                <td class="r">{{ $fmt($fSh) }}</td>
            </tr>
            @endforeach
        @else
            <tr><td colspan="9" class="c dim" style="padding:6pt;">ບໍ່ມີຂໍ້ມູນ</td></tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td></td>
            <td class="c">ລວມ</td>
            <td class="r">{{ $feeYear1 ? $fmt($feeYear1->total_rate) : '—' }}</td>
            <td class="r">{{ $fmt($cnt14) }}</td>
            <td class="r">{{ $fmt($rt14['gross']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($rt14['nuol']) }}</td>
            <td></td>
            <td class="r">{{ $fmt($rt14['fac']) }}</td>
        </tr>
    </tfoot>
</table>

@endif
