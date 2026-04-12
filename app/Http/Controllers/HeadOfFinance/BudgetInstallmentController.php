<?php

namespace App\Http\Controllers\HeadOfFinance;

use App\Http\Controllers\Controller;
use App\Models\BudgetPlan;
use App\Models\BudgetPeriodAllocation;
use App\Models\BudgetLineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetInstallmentController extends Controller
{
    /**
     * Display a listing of approved budget plans ready for installment allocation.
     */
    public function index()
    {
        $plans = BudgetPlan::where('status', 'APPROVED')->orderByDesc('fiscal_year')->get();
        return view('head_of_finance.budget-installment.index', compact('plans'));
    }

    /**
     * Show the detailed allocation table for a specific approved budget plan.
     */
    public function show(BudgetPlan $budgetPlan)
    {
        if ($budgetPlan->status !== 'APPROVED') {
            return redirect()->route('head_of_finance.budget-installment.index')
                ->with('error', 'ສາມາດຈັດການແຜນງວດໄດ້ສະເພາະແຜນທີ່ອະນຸມັດແລ້ວເທົ່ານັ້ນ');
        }

        $budgetPlan->load(['lineItems.account', 'lineItems.periodAllocations']);
        
        // We reuse the synthesizeTreeAndRollUp logic to get the hierarchical breakdown
        $synthesizedItems = $this->synthesizeTreeAndRollUp($budgetPlan->lineItems);
        $budgetPlan->setRelation('lineItems', $synthesizedItems);

        return view('head_of_finance.budget-installment.show', compact('budgetPlan'));
    }

    /**
     * Save the period allocations.
     */
    public function save(Request $request, BudgetPlan $budgetPlan)
    {
        if ($budgetPlan->status !== 'APPROVED') {
            return back()->with('error', 'ສາມາດຈັດການແຜນງວດໄດ້ສະເພາະແຜນທີ່ອະນຸມັດແລ້ວເທົ່ານັ້ນ');
        }

        $request->validate([
            'allocations' => 'array',
            'allocations.*.period_1' => 'nullable|numeric|min:0',
            'allocations.*.period_2' => 'nullable|numeric|min:0',
        ]);

        $allocationsList = $request->input('allocations', []);

        DB::transaction(function () use ($allocationsList) {
            foreach ($allocationsList as $lineItemId => $amounts) {
                $p1 = empty($amounts['period_1']) ? 0 : (float)$amounts['period_1'];
                $p2 = empty($amounts['period_2']) ? 0 : (float)$amounts['period_2'];

                // Update or Create period 1
                BudgetPeriodAllocation::updateOrCreate(
                    [
                        'budget_line_item_id' => $lineItemId,
                        'period_name' => 'ງວດ1',
                    ],
                    [
                        'allocated_amount' => $p1,
                    ]
                );

                // Update or Create period 2
                BudgetPeriodAllocation::updateOrCreate(
                    [
                        'budget_line_item_id' => $lineItemId,
                        'period_name' => 'ງວດ2',
                    ],
                    [
                        'allocated_amount' => $p2,
                    ]
                );
            }
        });

        return back()->with('success', 'ບັນທຶກແຜນງວດງົບປະມານສຳເລັດ!');
    }

    /**
     * Copied from AnnualBudgetPlanController to ensure consistency.
     */
    protected function synthesizeTreeAndRollUp($lineItems)
    {
        $allAccounts = \App\Models\ChartOfAccount::orderBy('account_code')->get();
        $accountMap = $allAccounts->keyBy('id');

        $childrenMap = [];
        foreach ($allAccounts as $acc) {
            if ($acc->parent_id) {
                $childrenMap[$acc->parent_id][] = $acc->id;
            }
        }

        $aggregated = [];
        foreach ($lineItems as $item) {
            $period1 = $item->periodAllocations->where('period_name', 'ງວດ1')->first()->allocated_amount ?? 0;
            $period2 = $item->periodAllocations->where('period_name', 'ງວດ2')->first()->allocated_amount ?? 0;

            $aggregated[$item->account_id] = [
                'amount_regular' => $item->amount_regular,
                'amount_academic' => $item->amount_academic,
                'period_1' => $period1,
                'period_2' => $period2,
                'original_item' => $item,
            ];
        }

        $computeSum = function ($accountId) use (&$computeSum, &$aggregated, $childrenMap) {
            $reg = $aggregated[$accountId]['amount_regular'] ?? 0;
            $acad = $aggregated[$accountId]['amount_academic'] ?? 0;
            $p1 = $aggregated[$accountId]['period_1'] ?? 0;
            $p2 = $aggregated[$accountId]['period_2'] ?? 0;
            $hasItems = isset($aggregated[$accountId]['original_item']);

            if (isset($childrenMap[$accountId])) {
                $reg = 0;
                $acad = 0;
                $p1 = 0;
                $p2 = 0;
                foreach ($childrenMap[$accountId] as $childId) {
                    $childSums = $computeSum($childId);
                    $reg += $childSums['reg'];
                    $acad += $childSums['acad'];
                    $p1 += $childSums['p1'];
                    $p2 += $childSums['p2'];
                    if ($childSums['hasItems']) {
                        $hasItems = true;
                    }
                }
                $aggregated[$accountId]['amount_regular'] = $reg;
                $aggregated[$accountId]['amount_academic'] = $acad;
                $aggregated[$accountId]['period_1'] = $p1;
                $aggregated[$accountId]['period_2'] = $p2;
            }

            $aggregated[$accountId]['should_render'] = $hasItems || $reg > 0 || $acad > 0;
            return ['reg' => $reg, 'acad' => $acad, 'p1' => $p1, 'p2' => $p2, 'hasItems' => $hasItems];
        };

        $roots = $allAccounts->whereNull('parent_id');
        foreach ($roots as $root) {
            $computeSum($root->id);
        }

        $syntheticItems = collect();
        foreach ($allAccounts as $acc) {
            $shouldRender = $aggregated[$acc->id]['should_render'] ?? false;

            if ($shouldRender) {
                $reg = $aggregated[$acc->id]['amount_regular'] ?? 0;
                $acad = $aggregated[$acc->id]['amount_academic'] ?? 0;
                $p1 = $aggregated[$acc->id]['period_1'] ?? 0;
                $p2 = $aggregated[$acc->id]['period_2'] ?? 0;
                
                // Fallback for period reads below
                $p1Value = $aggregated[$acc->id]['period_1'] ?? 0;
                $p2Value = $aggregated[$acc->id]['period_2'] ?? 0;
                
                $isParent = isset($childrenMap[$acc->id]);

                if (isset($aggregated[$acc->id]['original_item'])) {
                    $item = $aggregated[$acc->id]['original_item'];
                    $item->amount_regular = $reg;
                    $item->amount_academic = $acad;
                    $item->period_1_amount = $p1Value;
                    $item->period_2_amount = $p2Value;
                    $item->is_parent = $isParent;
                    $item->setRelation('account', $acc);
                    $syntheticItems->push($item);
                } else {
                    $syntheticItem = new \App\Models\BudgetLineItem([
                        'account_id' => $acc->id,
                        'amount_regular' => $reg,
                        'amount_academic' => $acad,
                    ]);
                    $syntheticItem->period_1_amount = $p1Value;
                    $syntheticItem->period_2_amount = $p2Value;
                    $syntheticItem->is_parent = $isParent;
                    $syntheticItem->setRelation('account', $acc);
                    $syntheticItems->push($syntheticItem);
                }
            }
        }

        return $syntheticItems;
    }
}
