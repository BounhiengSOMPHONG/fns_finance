@php
    $items = $feeSetting?->items ?? collect();
    $totals = ['rate' => 0.0, 'gross' => 0.0, 'nuol' => 0.0, 'fns' => 0.0];
@endphp

<table class="report-table">
    <thead>
        <tr>
            <th>ລ/ດ</th>
            <th>ລາຍການ</th>
            <th>ອັດຕາຄ່າທະບຽນຕໍ່ຄົນ</th>
            <th>ຈຳນວນຄົນ</th>
            <th>ລາຍຮັບລວມ</th>
            <th>ຈຳນວນເປີເຊັນ ມຊ</th>
            <th>ພັນທະມຊ</th>
            <th>ຈຳນວນເປີເຊັນ ຄວທ</th>
            <th>ລາຍຮັບຄວທ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                $amount = (float) $item->amount;
                $nuolPct = (float) $item->nuol_pct;
                $gross = $studentCount * $amount;
                $nuol = $gross * $nuolPct;
                $fns = $gross - $nuol;
                $totals['rate'] += $amount;
                $totals['gross'] += $gross;
                $totals['nuol'] += $nuol;
                $totals['fns'] += $fns;
            @endphp
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td class="num">{{ $money($amount) }}</td>
                <td class="num">{{ $studentCount }}</td>
                <td class="num">{{ $money($gross) }}</td>
                <td class="num">{{ $pct($nuolPct) }}</td>
                <td class="num">{{ $money($nuol) }}</td>
                <td class="num">{{ $pct(1 - $nuolPct) }}</td>
                <td class="num">{{ $money($fns) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td></td>
            <td>ລວມ</td>
            <td class="num">{{ $money($feeSetting?->total_rate ?? $totals['rate']) }}</td>
            <td class="num">{{ $studentCount }}</td>
            <td class="num">{{ $money($totals['gross']) }}</td>
            <td></td>
            <td class="num">{{ $money($totals['nuol']) }}</td>
            <td></td>
            <td class="num">{{ $money($totals['fns']) }}</td>
        </tr>
    </tbody>
</table>
