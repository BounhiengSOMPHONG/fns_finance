@extends('layouts.admin')

@section('title', 'ແຜນງວດງົບປະມານ ' . $budgetPlan->fiscal_year)
@section('page-title', 'ແຜນງວດງົບປະມານປະຈຳປີ (ງວດ 3 ແລະ 4) ' . $budgetPlan->fiscal_year)

@section('content')

    <div class="flex items-center justify-between gap-3 mb-4">
        <a href="{{ route('head_of_finance.budget-installment.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            ກັບຄືນພາກສ່ວນຈັດສັນແຜນງວດ
        </a>
        <a href="{{ route('head_of_finance.budget-installment-34.preview', $budgetPlan) }}"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            ພາບລວມ 3-4
        </a>
    </div>

    {{-- Tabs ─────────────────────────────────────────────────────────── --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <a href="{{ route('head_of_finance.budget-installment.show', $budgetPlan) }}"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
                ແຜນງວດ 1 ແລະ 2
            </a>
            <a href="{{ route('head_of_finance.budget-installment-34.show', $budgetPlan) }}"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium border-purple-500 text-purple-600">
                ແຜນງວດ 3 ແລະ 4 + ການດັດແກ້
            </a>
        </nav>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden p-6 w-full max-w-[1600px] mx-auto">
        <form action="{{ route('head_of_finance.budget-installment-34.save', $budgetPlan) }}" method="POST" id="installmentForm">
            @csrf
            
            <div id="validation-error" class="hidden mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                <strong>ຂໍ້ຜິດພາດ:</strong> ຜົນລວມຂອງ ແຜນງວດ 3 + ແຜນງວດ 4 ຕ້ອງເທົ່າກັບ ແຜນດັດແກ້ 6 ເດືອນທ້າຍປີ ສຳລັບທຸກລາຍການ!
            </div>

            <div class="overflow-x-auto pb-4">
                <table class="w-full text-xs border-collapse" id="installmentTable">
                    <thead>
                        <tr class="bg-purple-100 text-gray-800 text-center border border-gray-300">
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 w-8">ພາກ</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 w-8">ພ/ສ</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 w-8">ຮ່ວງ</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 w-8">ລູກຮ່ວງ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2">ເນື້ອໃນລາຍຈ່າຍ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24">ແຜນອະນຸມັດປີ 2025</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24">ແຜນ 6 ເດືອນທ້າຍປີ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24 text-red-700">ແຜນດັດແກ້ຫຼຸດ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24 text-blue-700">ແຜນດັດແກ້ເພີ່ມ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24">ແຜນດັດແກ້ 6 ເດືອນທ້າຍປີ</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24">ແຜນງວດ 3</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24">ແຜນງວດ 4</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 w-24 bg-green-50">ແຜນປະຕິບັດໝົດປີ</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 w-16">ທຽບເປີເຊັນ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 table-group-divider border border-gray-300">
                        @php
                            $totalAnnual = 0;
                            $totalPlan6M = 0;
                            $totalReduce = 0;
                            $totalIncrease = 0;
                            $totalRevised6M = 0;
                            $totalP3 = 0;
                            $totalP4 = 0;
                            $totalExecute = 0;

                            foreach ($budgetPlan->lineItems as $item) {
                                if (str_ends_with($item->account->formatted_code ?? '', '-00-00-00')) {
                                    $annualAmount = ($item->amount_regular ?? 0) + ($item->amount_academic ?? 0);
                                    $p1 = $item->period_1_amount ?? 0;
                                    $p2 = $item->period_2_amount ?? 0;
                                    $plan6M = $annualAmount - $p1 - $p2;
                                    
                                    $reduce = $item->reduce_amount ?? 0;
                                    $incr = $item->increase_amount ?? 0;
                                    $revised6M = $plan6M - $reduce + $incr;
                                    
                                    $p3 = $item->period_3_amount ?? 0;
                                    $p4 = $item->period_4_amount ?? 0;
                                    $execute = $annualAmount - $reduce + $incr;

                                    $totalAnnual += $annualAmount;
                                    $totalPlan6M += $plan6M;
                                    $totalReduce += $reduce;
                                    $totalIncrease += $incr;
                                    $totalRevised6M += $revised6M;
                                    $totalP3 += $p3;
                                    $totalP4 += $p4;
                                    $totalExecute += $execute;
                                }
                            }
                            $gPercent = $totalAnnual > 0 ? ($totalExecute / $totalAnnual) * 100 : 0;
                        @endphp
                        {{-- Grand Total Line --}}
                        <tr class="bg-purple-50 font-bold">
                            <td colspan="5" class="border border-purple-200 px-3 py-2 text-right">ລວມຍອດເງິນທຸກພາກສ່ວນ:</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-purple-800" id="grand_annual">{{ number_format($totalAnnual, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-gray-700" id="grand_plan6m">{{ number_format($totalPlan6M, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-red-700" id="grand_reduce">{{ number_format($totalReduce, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-blue-700" id="grand_increase">{{ number_format($totalIncrease, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-purple-700" id="grand_revised6m">{{ number_format($totalRevised6M, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-green-700" id="grand_p3">{{ number_format($totalP3, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-green-700" id="grand_p4">{{ number_format($totalP4, 2) }}</td>
                            <td class="border border-purple-200 px-2 py-2 text-right tabular-nums text-green-800" id="grand_execute">{{ number_format($totalExecute, 2) }}</td>
                            <td class="border border-purple-200 px-1 py-2 text-center tabular-nums text-blue-800" id="grand_percent">{{ number_format($gPercent, 2) }}%</td>
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
                                $p1 = $item->period_1_amount ?? 0;
                                $p2 = $item->period_2_amount ?? 0;
                                $plan6M = $annualAmount - $p1 - $p2;

                                $reduceAmount = $item->reduce_amount ?? 0;
                                $increaseAmount = $item->increase_amount ?? 0;
                                $revised6M = $plan6M - $reduceAmount + $increaseAmount;

                                $p3Amount = $item->period_3_amount ?? 0;
                                $p4Amount = $item->period_4_amount ?? 0;
                                $executeAmount = $annualAmount - $reduceAmount + $increaseAmount;
                                $percent = $annualAmount > 0 ? ($executeAmount / $annualAmount) * 100 : 0;

                                $trClass = $isParent ? 'bg-purple-50 font-semibold' : 'bg-white hover:bg-gray-50';
                                $parentId = $item->account->parent_id ?? '';
                            @endphp
                            <tr class="{{ $trClass }}" 
                                data-id="{{ $item->account_id }}" 
                                data-parent-id="{{ $parentId }}"
                                data-is-parent="{{ $isParent ? '1' : '0' }}"
                                data-annual="{{ $annualAmount }}"
                                data-plan6m="{{ $plan6M }}">
                                
                                <td class="border border-gray-200 px-1 py-2 text-center text-xs font-mono text-gray-500">{{ $part1 }}</td>
                                <td class="border border-gray-200 px-1 py-2 text-center text-xs font-mono text-gray-500">{{ $part2 }}</td>
                                <td class="border border-gray-200 px-1 py-2 text-center text-xs font-mono text-gray-500">{{ $part3 }}</td>
                                <td class="border border-gray-200 px-1 py-2 text-center text-xs font-mono text-gray-500">{{ $part4 }}</td>
                                
                                <td class="border border-gray-200 px-2 py-2 text-gray-800">
                                    @if(!$isParent) - @endif {{ $item->account->account_name ?? '-' }}
                                </td>
                                
                                <td class="border border-gray-200 px-2 py-2 text-right tabular-nums text-gray-700">
                                    {{ number_format($annualAmount, 2) }}
                                </td>
                                
                                <td class="border border-gray-200 px-2 py-2 text-right tabular-nums text-gray-700">
                                    {{ number_format($plan6M, 2) }}
                                </td>

                                {{-- ແຜນດັດແກ້ຫຼຸດ --}}
                                <td class="border border-gray-200 px-1 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-1 py-1 text-right tabular-nums text-red-700" id="parent_reduce_{{ $item->account_id }}">{{ number_format($reduceAmount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][reduce]" 
                                            value="{{ rtrim(rtrim(number_format($reduceAmount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="reduce-input w-full px-1 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-purple-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>

                                {{-- ແຜນດັດແກ້ເພີ່ມ --}}
                                <td class="border border-gray-200 px-1 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-1 py-1 text-right tabular-nums text-blue-700" id="parent_increase_{{ $item->account_id }}">{{ number_format($increaseAmount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][increase]" 
                                            value="{{ rtrim(rtrim(number_format($increaseAmount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="increase-input w-full px-1 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-purple-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>

                                <td class="border border-gray-200 px-2 py-2 text-right tabular-nums font-medium text-gray-800" id="revised6m_{{ $item->account_id }}">
                                    {{ number_format($revised6M, 2) }}
                                </td>
                                
                                {{-- ງວດ 3 --}}
                                <td class="border border-gray-200 px-1 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-1 py-1 text-right tabular-nums text-gray-800" id="parent_p3_{{ $item->account_id }}">{{ number_format($p3Amount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][period_3]" 
                                            value="{{ rtrim(rtrim(number_format($p3Amount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="p3-input w-full px-1 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-purple-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>
                                
                                {{-- ງວດ 4 --}}
                                <td class="border border-gray-200 px-1 py-1 text-right">
                                    @if($isParent)
                                        <div class="px-1 py-1 text-right tabular-nums text-gray-800" id="parent_p4_{{ $item->account_id }}">{{ number_format($p4Amount, 2) }}</div>
                                    @else
                                        <input type="number" name="allocations[{{ $item->id }}][period_4]" 
                                            value="{{ rtrim(rtrim(number_format($p4Amount, 2, '.', ''), '0'), '.') }}" 
                                            min="0" step="0.01"
                                            class="p4-input w-full px-1 py-1 border border-gray-300 rounded text-right text-xs focus:ring-1 focus:ring-purple-500" 
                                            oninput="recalculate()">
                                    @endif
                                </td>

                                <td class="border border-gray-200 px-2 py-2 bg-green-50 text-right tabular-nums font-semibold text-green-800" id="execute_{{ $item->account_id }}">
                                    {{ number_format($executeAmount, 2) }}
                                </td>
                                
                                <td class="border border-gray-200 px-1 py-2 text-center tabular-nums text-blue-700" id="percent_{{ $item->account_id }}">
                                    {{ number_format($percent, 2) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-6 py-10 text-center text-gray-500">ບໍ່ມີລາຍການງົບປະມານ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-4 mb-2">
                <button type="submit" id="saveBtn" class="inline-flex items-center px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ບັນທຶກແຜນງວດ ແລະ ການດັດແກ້
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function formatLaoCurrency(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recalculate() {
            const table = document.getElementById('installmentTable');
            const rows = Array.from(table.querySelectorAll('tbody tr[data-id]'));
            
            let nodeData = {};
            rows.forEach(tr => {
                const id = tr.dataset.id;
                const pId = tr.dataset.parentId;
                const isParent = tr.dataset.isParent === '1';
                const annual = parseFloat(tr.dataset.annual) || 0;
                const plan6m = parseFloat(tr.dataset.plan6m) || 0;
                
                let reduce = 0, increase = 0, p3 = 0, p4 = 0;
                if (!isParent) {
                    reduce = parseFloat(tr.querySelector('.reduce-input').value) || 0;
                    increase = parseFloat(tr.querySelector('.increase-input').value) || 0;
                    p3 = parseFloat(tr.querySelector('.p3-input').value) || 0;
                    p4 = parseFloat(tr.querySelector('.p4-input').value) || 0;
                }
                
                nodeData[id] = { id, pId, isParent, annual, plan6m, reduce, increase, p3, p4, originalRow: tr };
            });

            let childrenMap = {};
            for (const key in nodeData) {
                const pId = nodeData[key].pId;
                if (pId) {
                    if (!childrenMap[pId]) childrenMap[pId] = [];
                    childrenMap[pId].push(key);
                }
            }

            function computeSum(nodeId) {
                let node = nodeData[nodeId];
                if (!node) return { reduce: 0, increase: 0, p3: 0, p4: 0 };

                if (node.isParent && childrenMap[nodeId]) {
                    let s_reduce = 0, s_increase = 0, s_p3 = 0, s_p4 = 0;
                    for (const childId of childrenMap[nodeId]) {
                        let res = computeSum(childId);
                        s_reduce += res.reduce;
                        s_increase += res.increase;
                        s_p3 += res.p3;
                        s_p4 += res.p4;
                    }
                    node.reduce = s_reduce;
                    node.increase = s_increase;
                    node.p3 = s_p3;
                    node.p4 = s_p4;
                }
                return { reduce: node.reduce, increase: node.increase, p3: node.p3, p4: node.p4 };
            }

            // Find roots and trigger roll-up
            for (const key in nodeData) {
                if (!nodeData[key].pId) {
                    computeSum(key);
                }
            }

            let grandAnnual = 0, grandPlan6m = 0, grandReduce = 0, grandIncrease = 0;
            let grandRevised6m = 0, grandP3 = 0, grandP4 = 0, grandExecute = 0;
            let isValid = true;

            for (const key in nodeData) {
                const node = nodeData[key];
                const tr = node.originalRow;

                // Required derived fields
                const revised6m = node.plan6m - node.reduce + node.increase;
                const execute = node.annual - node.reduce + node.increase;
                const percent = node.annual > 0 ? (execute / node.annual) * 100 : 0;

                // Validate (skip validation on completely empty or zeroed out leaf lines if they aren't used? We check all)
                if (!node.isParent) {
                    // Precision buffer 0.05 logic
                    const diff = Math.abs(node.p3 + node.p4 - revised6m);
                    if (diff > 0.05 && (node.annual > 0 || revised6m !== 0)) {
                        isValid = false;
                        tr.classList.add('bg-red-50');
                    } else {
                        tr.classList.remove('bg-red-50');
                    }
                }

                if (node.isParent) {
                    document.getElementById(`parent_reduce_${key}`).textContent = formatLaoCurrency(node.reduce);
                    document.getElementById(`parent_increase_${key}`).textContent = formatLaoCurrency(node.increase);
                    document.getElementById(`parent_p3_${key}`).textContent = formatLaoCurrency(node.p3);
                    document.getElementById(`parent_p4_${key}`).textContent = formatLaoCurrency(node.p4);
                }

                document.getElementById(`revised6m_${key}`).textContent = formatLaoCurrency(revised6m);
                document.getElementById(`execute_${key}`).textContent = formatLaoCurrency(execute);
                document.getElementById(`percent_${key}`).textContent = formatLaoCurrency(percent) + '%';

                // Grand totals (Roots only)
                if (!node.pId) {
                    grandAnnual += node.annual;
                    grandPlan6m += node.plan6m;
                    grandReduce += node.reduce;
                    grandIncrease += node.increase;
                    grandRevised6m += revised6m;
                    grandP3 += node.p3;
                    grandP4 += node.p4;
                    grandExecute += execute;
                }
            }

            let gPercent = grandAnnual > 0 ? (grandExecute / grandAnnual) * 100 : 0;

            document.getElementById('grand_annual').textContent = formatLaoCurrency(grandAnnual);
            document.getElementById('grand_plan6m').textContent = formatLaoCurrency(grandPlan6m);
            document.getElementById('grand_reduce').textContent = formatLaoCurrency(grandReduce);
            document.getElementById('grand_increase').textContent = formatLaoCurrency(grandIncrease);
            document.getElementById('grand_revised6m').textContent = formatLaoCurrency(grandRevised6m);
            document.getElementById('grand_p3').textContent = formatLaoCurrency(grandP3);
            document.getElementById('grand_p4').textContent = formatLaoCurrency(grandP4);
            document.getElementById('grand_execute').textContent = formatLaoCurrency(grandExecute);
            document.getElementById('grand_percent').textContent = formatLaoCurrency(gPercent) + '%';

            // Show hide error logic
            const errBox = document.getElementById('validation-error');
            const saveBtn = document.getElementById('saveBtn');
            if (!isValid) {
                errBox.classList.remove('hidden');
                saveBtn.disabled = true;
                saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                errBox.classList.add('hidden');
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Setup initial validation state on load
        recalculate();
    </script>
    @endpush
@endsection
