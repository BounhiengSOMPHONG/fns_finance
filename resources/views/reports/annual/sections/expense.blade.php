{{-- Variables: $plan (ExpensePlan|null), $topCategories (Collection), $year --}}

@if(!$plan || $topCategories->isEmpty())
<div class="placeholder">
    <div class="ph-icon">📋</div>
    <div class="ph-text">ຍັງບໍ່ມີຂໍ້ມູນປະເມີນລາຍຈ່າຍ ສົກ {{ $year }}</div>
    <div class="ph-text" style="margin-top:3pt;font-size:7pt;color:#bbb;">ສ້າງ ExpensePlan ແລ້ວ ເພີ່ມໝວດ/ລາຍການ ກ່ອນ</div>
</div>
@else

@php $grandTotal = (float) $plan->allCategories->flatMap->items->sum('annual_amount'); @endphp

<div class="subsec-hd keep-with-next">ປະເມີນລາຍຈ່າຍ ສົກ {{ $year }}</div>

@foreach($topCategories as $mainCat)
@php $mainTotal = $mainCat->subtotal(); @endphp
<div class="avoid-break" style="margin-bottom:8pt;">

    {{-- Main category header --}}
    <table class="rpt-table" style="margin-bottom:0;">
        <thead>
            <tr>
                <th style="width:4%;text-align:center;">#</th>
                <th colspan="2">{{ $mainCat->ref_code }} — {{ $mainCat->name }}</th>
                <th style="width:13%;text-align:right;">ຕໍ່ເດືອນ (ກີບ)</th>
                <th style="width:6%;text-align:center;">ຈ/ນ</th>
                <th style="width:14%;text-align:right;">ໝົດປີ (ກີບ)</th>
                <th style="width:13%;">ໝາຍເຫດ</th>
            </tr>
        </thead>
        <tbody>

        @foreach($mainCat->children as $sub)
        @php $subTotal = $sub->subtotal(); @endphp

            {{-- Subcategory header row --}}
            <tr style="background:#f1f5f9;">
                <td class="c dim" style="font-size:7.5pt;"></td>
                <td colspan="2" style="font-weight:700;color:#1e3a5f;font-size:8pt;">{{ $sub->ref_code }} {{ $sub->name }}</td>
                <td></td><td></td>
                <td class="r" style="font-weight:700;color:#1e3a5f;">{{ number_format($subTotal, 0) }}</td>
                <td></td>
            </tr>

            {{-- Items --}}
            @foreach($sub->items as $item)
            <tr>
                <td class="c dim" style="font-size:7pt;">{{ $loop->iteration }}</td>
                <td style="padding-left:12pt;">{{ $item->name }}</td>
                <td style="font-size:7pt;color:#64748b;">{{ $item->reference }}</td>
                <td class="r">{{ number_format($item->monthly_amount, 0) }}</td>
                <td class="c">{{ $item->quantity }}</td>
                <td class="r" style="font-weight:600;">{{ number_format($item->annual_amount, 0) }}</td>
                <td style="font-size:7pt;color:#64748b;">{{ $item->remark }}</td>
            </tr>
            @endforeach

        @endforeach

        {{-- Direct items (main cat has no subcategories) --}}
        @if($mainCat->children->isEmpty())
            @foreach($mainCat->items as $item)
            <tr>
                <td class="c dim" style="font-size:7pt;">{{ $loop->iteration }}</td>
                <td style="padding-left:8pt;">{{ $item->name }}</td>
                <td style="font-size:7pt;color:#64748b;">{{ $item->reference }}</td>
                <td class="r">{{ number_format($item->monthly_amount, 0) }}</td>
                <td class="c">{{ $item->quantity }}</td>
                <td class="r" style="font-weight:600;">{{ number_format($item->annual_amount, 0) }}</td>
                <td style="font-size:7pt;color:#64748b;">{{ $item->remark }}</td>
            </tr>
            @endforeach
        @endif

        </tbody>
        <tfoot>
            <tr class="subtotal">
                <td colspan="5" class="c">ລວມ {{ $mainCat->ref_code }}</td>
                <td class="r">{{ number_format($mainTotal, 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endforeach

{{-- Grand total --}}
<div class="grand-bar avoid-break" style="margin-top:10pt;">
    <div class="grand-bar-head">ລວມປະເມີນລາຍຈ່າຍທັງໝົດ ສົກ {{ $year }}</div>
    <table class="rpt-table grand-table">
        <thead>
            <tr>
                <th style="width:60%">ງົບທັງໝົດ (ກີບ)</th>
                <th>ໝາຍເຫດ</th>
            </tr>
        </thead>
        <tbody>
            <tr class="grand-row">
                <td class="r" style="font-size:11pt;">{{ number_format($grandTotal, 0) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

@endif
