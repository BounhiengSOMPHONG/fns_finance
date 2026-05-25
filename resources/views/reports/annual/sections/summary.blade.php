{{-- Variables: $allTotals (array keyed by section id), $year --}}

<div class="subsec-hd keep-with-next">ສັງລວມທຸກ Module</div>

@php
$grandFac = collect($allTotals)->sum(fn($s) => $s['totals']['faculty'] ?? 0);
$grandP1  = collect($allTotals)->sum(fn($s) => $s['totals']['p1']      ?? 0);
$grandP2  = collect($allTotals)->sum(fn($s) => $s['totals']['p2']      ?? 0);
$grandGross = collect($allTotals)->sum(fn($s) => $s['totals']['gross'] ?? 0);
@endphp

<table class="rpt-table avoid-break" style="margin-top:6pt;">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:35%">Module / ໝວດ</th>
            <th>ລາຍຮັບລວມ (ກີບ)</th>
            <th>ລາຍຮັບ ຄວທ (ກີບ)</th>
            <th>ງວດ 1 (ກີບ)</th>
            <th>ງວດ 2 (ກີບ)</th>
            <th style="width:10%">ສະຖານະ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allTotals as $id => $sec)
        @php $t = $sec['totals']; $hasData = ($t['faculty'] ?? 0) > 0; @endphp
        <tr>
            <td class="c dim">{{ $loop->iteration }}</td>
            <td>{{ $sec['title'] }}</td>
            <td class="r">{{ number_format($t['gross'] ?? 0, 0) }}</td>
            <td class="r">{{ number_format($t['faculty'] ?? 0, 0) }}</td>
            <td class="r">{{ number_format($t['p1'] ?? 0, 0) }}</td>
            <td class="r">{{ number_format($t['p2'] ?? 0, 0) }}</td>
            <td class="c">
                @if($hasData)
                    <span style="color:#16a34a;font-size:7pt;font-weight:700;">✓ ມີຂໍ້ມູນ</span>
                @else
                    <span style="color:#d97706;font-size:7pt;">⏳ ລໍຖ້າ</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="subtotal">
            <td colspan="2" class="c">ລວມທັງໝົດ</td>
            <td class="r">{{ number_format($grandGross, 0) }}</td>
            <td class="r">{{ number_format($grandFac, 0) }}</td>
            <td class="r">{{ number_format($grandP1, 0) }}</td>
            <td class="r">{{ number_format($grandP2, 0) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

{{-- Grand total bar --}}
<div class="grand-bar avoid-break" style="margin-top:10pt;">
    <div class="grand-bar-head">ລວມລາຍຮັບທຸກ Module ສົກ {{ $year }}</div>
    <table class="rpt-table grand-table">
        <thead>
            <tr>
                <th style="width:40%">ລາຍຮັບ ຄວທ ທັງໝົດ (ກີບ)</th>
                <th>ງວດ 1 (ກີບ)</th>
                <th>ງວດ 2 (ກີບ)</th>
                <th>ຍອດລວມງວດ (ກີບ)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="grand-row">
                <td class="r" style="font-size:11pt;">{{ number_format($grandFac, 0) }}</td>
                <td class="r" style="font-size:11pt;">{{ number_format($grandP1, 0) }}</td>
                <td class="r" style="font-size:11pt;">{{ number_format($grandP2, 0) }}</td>
                <td class="r" style="font-size:11pt;">{{ number_format($grandP1 + $grandP2, 0) }}</td>
            </tr>
        </tbody>
    </table>
</div>
