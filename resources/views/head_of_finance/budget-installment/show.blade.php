@extends('layouts.admin')

@section('title', 'ແຜນງວດງົບປະມານ ' . $budgetPlan->fiscal_year)
@section('page-title', 'ແຜນງວດງົບປະມານປະຈຳປີ ' . $budgetPlan->fiscal_year)

@section('content')

    <div class="flex items-center justify-between gap-3 mb-4">
        <a href="{{ route('head_of_finance.budget-installment.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            ກັບຄືນພາກສ່ວນຈັດສັນແຜນງວດ
        </a>
        <a href="{{ route('head_of_finance.budget-installment.preview', $budgetPlan) }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            ພາບລວມ 1-2
        </a>
    </div>

    {{-- Tabs ─────────────────────────────────────────────────────────── --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <a href="{{ route('head_of_finance.budget-installment.show', $budgetPlan) }}"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium border-blue-500 text-blue-600">
                ແຜນງວດ 1 ແລະ 2
            </a>
            <a href="{{ route('head_of_finance.budget-installment-34.show', $budgetPlan) }}"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
                ແຜນງວດ 3 ແລະ 4
            </a>
        </nav>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden p-6 w-full max-w-[1400px] mx-auto">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">
                ຕາຕະລາງແຜນງວດງົບປະມານປະຈຳປີ {{ $budgetPlan->fiscal_year }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">ກະລຸນາໃສ່ຈຳນວນເງິນແຜນງວດ 1 ແລະ ແຜນງວດ 2 (ແຜນງວດ = ງົບປົກກະຕິ + ງົບວິຊາການ)</p>
        </div>

        @php
            // Calculate Grand Total for the whole plan
            $totalAnnual = 0;
            $totalP1 = 0;
            $totalP2 = 0;

            foreach ($budgetPlan->lineItems as $item) {
                // Sum only the root nodes to prevent double counting
                if (str_ends_with($item->account->account_code ?? '', '000000')) {
                    $totalAnnual += ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                    $totalP1 += $item->period_1_amount ?? 0;
                    $totalP2 += $item->period_2_amount ?? 0;
                }
            }

            $total6Months = $totalP1 + $totalP2;
            $total6MonthsEnd = $totalAnnual - $total6Months;
        @endphp

        <form action="{{ route('head_of_finance.budget-installment.save', $budgetPlan) }}" method="POST">
            @csrf
            
            <div class="overflow-x-auto border rounded-sm mb-6">
                <table class="w-full text-sm border-collapse" id="installmentTable">
                    <thead>
                        <tr class="bg-blue-600 text-white text-center">
                            <th class="border border-blue-500 px-2 py-2" style="width: 50px;">ພາກ</th>
                            <th class="border border-blue-500 px-2 py-2" style="width: 50px;">ພາກສ່ວນ</th>
                            <th class="border border-blue-500 px-2 py-2" style="width: 50px;">ຮ່ວງ</th>
                            <th class="border border-blue-500 px-2 py-2" style="width: 50px;">ລູກຮ່ວງ</th>
                            <th class="border border-blue-500 px-3 py-2 text-left" style="min-width: 200px;">ເນື້ອໃນລາຍຈ່າຍ</th>
                            <th class="border border-blue-500 px-3 py-2 w-32 font-medium text-xs">ແຜນງົບປະມານ<br>ປະຈຳປີ</th>
                            <th class="border border-blue-500 px-3 py-2 w-32">ແຜນງວດ 1</th>
                            <th class="border border-blue-500 px-3 py-2 w-32">ແຜນງວດ 2</th>
                            <th class="border border-blue-500 px-3 py-2 w-32 font-medium text-xs">ແຜນ 6 ເດືອນຕົ້ນປີ<br>(ງວດ 1 + ງວດ 2)</th>
                            <th class="border border-blue-500 px-3 py-2 w-32 font-medium text-xs">ແຜນ 6 ເດືອນທ້າຍປີ<br>(ປະຈຳປີ - 6 ເດືອນຕົ້ນປີ)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $rootParts = [];
                            foreach ($budgetPlan->lineItems as $item) {
                                if (str_ends_with($item->account->account_code ?? '', '000000')) {
                                    $rootParts[] = substr($item->account->account_code, 0, 2);
                                }
                            }
                            $rootEquation = count($rootParts) > 0 ? implode('+', array_unique($rootParts)) : '';
                        @endphp
                        {{-- Top Total Row --}}
                        <tr class="bg-green-100 font-bold text-gray-900 relative z-10 transition-colors">
                            <td colspan="5" class="border border-green-200 px-4 py-2 text-right">ລວມຍອດສ່ວນ({{ $rootEquation }})=</td>
                            <td class="border border-green-200 px-3 py-2 text-right tabular-nums">{{ number_format($totalAnnual, 2) }}</td>
                            <td class="border border-green-200 px-3 py-2 text-right tabular-nums text-green-700" id="grand_p1">{{ number_format($totalP1, 2) }}</td>
                            <td class="border border-green-200 px-3 py-2 text-right tabular-nums text-green-700" id="grand_p2">{{ number_format($totalP2, 2) }}</td>
                            <td class="border border-green-200 px-3 py-2 text-right tabular-nums text-blue-700" id="grand_6m">{{ number_format($total6Months, 2) }}</td>
                            <td class="border border-green-200 px-3 py-2 text-right tabular-nums text-purple-700" id="grand_6me">{{ number_format($total6MonthsEnd, 2) }}</td>
                        </tr>

                        @forelse ($budgetPlan->lineItems as $item)
                            @php
                                $code = $item->account->account_code ?? '';
                                $part1 = substr($code, 0, 2);
                                $part2 = substr($code, 2, 2);
                                $part3 = substr($code, 4, 2);
                                $part4 = substr($code, 6, 2);

                                $isParent = $item->is_parent ?? false;
                                
                                $annualAmount = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                                $p1Amount = $item->period_1_amount ?? 0;
                                $p2Amount = $item->period_2_amount ?? 0;
                                $m6Amount = $p1Amount + $p2Amount;
                                $m6endAmount = $annualAmount - $m6Amount;

                                $trClass = $isParent ? 'bg-blue-50 font-semibold' : 'bg-white hover:bg-gray-50';
                                
                                // To easily rebuild the tree for calculations, dataset attributes
                                $parentId = $item->account->parent_id ?? '';
                            @endphp
                            <tr class="{{ $trClass }}" 
                                data-id="{{ $item->account_id }}" 
                                data-parent-id="{{ $parentId }}"
                                data-is-parent="{{ $isParent ? '1' : '0' }}"
                                data-annual="{{ $annualAmount }}">
                                
                                <td class="border border-gray-200 px-2 py-2 text-center text-xs font-mono text-gray-500">{{ $part1 }}</td>
                                <td class="border border-gray-200 px-2 py-2 text-center text-xs font-mono text-gray-500">{{ $part2 }}</td>
                                <td class="border border-gray-200 px-2 py-2 text-center text-xs font-mono text-gray-500">{{ $part3 }}</td>
                                <td class="border border-gray-200 px-2 py-2 text-center text-xs font-mono text-gray-500">{{ $part4 }}</td>
                                
                                <td class="border border-gray-200 px-3 py-2 text-gray-800">
                                    @if(!$isParent) - @endif {{ $item->account->account_name ?? '-' }}
                                </td>
                                
                                <td class="border border-gray-200 px-3 py-2 text-right tabular-nums text-gray-700">
                                    {{ number_format($annualAmount, 2) }}
                                </td>
                                
                                <td class="border border-gray-200 px-2 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-2 py-1 text-right tabular-nums text-gray-800" id="parent_p1_{{ $item->account_id }}">{{ number_format($p1Amount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][period_1]" 
                                            value="{{ rtrim(rtrim(number_format($p1Amount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="p1-input w-full px-2 py-1 border border-gray-300 rounded text-right text-sm focus:ring-1 focus:ring-blue-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>
                                
                                <td class="border border-gray-200 px-2 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-2 py-1 text-right tabular-nums text-gray-800" id="parent_p2_{{ $item->account_id }}">{{ number_format($p2Amount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][period_2]" 
                                            value="{{ rtrim(rtrim(number_format($p2Amount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="p2-input w-full px-2 py-1 border border-gray-300 rounded text-right text-sm focus:ring-1 focus:ring-blue-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>

                                <td class="border border-gray-200 px-3 py-2 text-right tabular-nums font-semibold text-blue-800" id="m6_{{ $item->account_id }}">
                                    {{ number_format($m6Amount, 2) }}
                                </td>
                                
                                <td class="border border-gray-200 px-3 py-2 text-right tabular-nums font-semibold text-purple-800" id="m6end_{{ $item->account_id }}">
                                    {{ number_format($m6endAmount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-gray-500">ບໍ່ມີລາຍການງົບປະມານ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ບັນທຶກແຜນງວດ
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Formatting helper
        function formatLaoCurrency(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Main function to recalculate parent sums, row sums, and the grand total
        function recalculate() {
            const table = document.getElementById('installmentTable');
            const rows = Array.from(table.querySelectorAll('tbody tr[data-id]'));
            
            // 1. Gather raw data from inputs for leaf nodes
            let nodeData = {};
            rows.forEach(tr => {
                const id = tr.dataset.id;
                const pId = tr.dataset.parentId;
                const isParent = tr.dataset.isParent === '1';
                const annual = parseFloat(tr.dataset.annual) || 0;
                
                let p1 = 0, p2 = 0;
                if (!isParent) {
                    const p1Input = tr.querySelector('.p1-input');
                    const p2Input = tr.querySelector('.p2-input');
                    p1 = parseFloat(p1Input.value) || 0;
                    p2 = parseFloat(p2Input.value) || 0;
                }
                
                nodeData[id] = { id, pId, isParent, annual, p1, p2, originalRow: tr };
            });

            // 2. Build adjacency list for tree
            let childrenMap = {};
            for (const key in nodeData) {
                const pId = nodeData[key].pId;
                if (pId) {
                    if (!childrenMap[pId]) childrenMap[pId] = [];
                    childrenMap[pId].push(key);
                }
            }

            // 3. Recursive rollup function
            function computeSum(nodeId) {
                let node = nodeData[nodeId];
                if (!node) return { p1: 0, p2: 0 };

                if (node.isParent && childrenMap[nodeId]) {
                    let sumP1 = 0;
                    let sumP2 = 0;
                    for (const childId of childrenMap[nodeId]) {
                        let res = computeSum(childId);
                        sumP1 += res.p1;
                        sumP2 += res.p2;
                    }
                    node.p1 = sumP1;
                    node.p2 = sumP2;

                    // Update parent display
                    let p1Display = document.getElementById('parent_p1_' + nodeId);
                    let p2Display = document.getElementById('parent_p2_' + nodeId);
                    if(p1Display) p1Display.textContent = formatLaoCurrency(sumP1);
                    if(p2Display) p2Display.textContent = formatLaoCurrency(sumP2);
                }

                // Update 6m / 6m-end row displays
                let m6 = node.p1 + node.p2;
                let m6end = node.annual - m6;
                
                let m6Cell = document.getElementById('m6_' + nodeId);
                let m6endCell = document.getElementById('m6end_' + nodeId);
                
                if (m6Cell) m6Cell.textContent = formatLaoCurrency(m6);
                if (m6endCell) m6endCell.textContent = formatLaoCurrency(m6end);
                
                // Add a small visual warning if 6-month budget exceeds annual budget (negative end of year)
                if (m6end < 0 && m6endCell) {
                    m6endCell.classList.add('text-red-600', 'bg-red-50');
                    m6endCell.classList.remove('text-purple-800');
                } else if (m6endCell) {
                    m6endCell.classList.remove('text-red-600', 'bg-red-50');
                    m6endCell.classList.add('text-purple-800');
                }

                return { p1: node.p1, p2: node.p2 };
            }

            // 4. Calculate for all root nodes and accumulate Grand Totals
            let grandP1 = 0;
            let grandP2 = 0;
            
            for (const key in nodeData) {
                if (!nodeData[key].pId) { // root node empty parent
                    let res = computeSum(key);
                    grandP1 += res.p1;
                    grandP2 += res.p2;
                }
            }

            // 5. Update Grand Totals row
            let grand6M = grandP1 + grandP2;
            let grandAnnualAttr = document.getElementById('installmentTable').querySelector('tbody tr').querySelector('td:nth-child(2)');
            // Get annual from dataset of root elements or just read text of the static grand total cell...
            // It's easier just to re-sum roots:
            let grandAnnual = 0;
            for (const key in nodeData) {
                if (!nodeData[key].pId) {
                    grandAnnual += nodeData[key].annual;
                }
            }
            let grand6MEnd = grandAnnual - grand6M;

            document.getElementById('grand_p1').textContent = formatLaoCurrency(grandP1);
            document.getElementById('grand_p2').textContent = formatLaoCurrency(grandP2);
            document.getElementById('grand_6m').textContent = formatLaoCurrency(grand6M);
            let gEndCell = document.getElementById('grand_6me');
            gEndCell.textContent = formatLaoCurrency(grand6MEnd);
            
            if (grand6MEnd < 0) {
                gEndCell.classList.add('text-red-600');
                gEndCell.classList.remove('text-purple-700');
            } else {
                gEndCell.classList.remove('text-red-600');
                gEndCell.classList.add('text-purple-700');
            }
        }
    </script>
    @endpush
@endsection
