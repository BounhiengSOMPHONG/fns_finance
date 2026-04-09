<?php

namespace App\Http\Controllers\HeadOfFaculty;

use App\Http\Controllers\Controller;
use App\Models\BudgetPlan;
use App\Models\User;
use App\Notifications\BudgetPlanStatusChanged;
use Illuminate\Http\Request;

class BudgetApprovalController extends Controller
{
    public function index()
    {
        $plans = BudgetPlan::whereIn('status', ['PENDING_FINAL_APPROVAL', 'APPROVED'])
            ->orderByDesc('fiscal_year')
            ->get();

        return view('head_of_faculty.annual-budget.index', compact('plans'));
    }

    public function show(BudgetPlan $annualBudget)
    {
        $annualBudget->load(['lineItems.account', 'comments.user.role', 'comments.markedBy']);
        $annualBudget->setRelation('lineItems', $this->sortLineItemsHierarchically($annualBudget->lineItems));

        return view('head_of_faculty.annual-budget.show', compact('annualBudget'));
    }

    public function review(Request $request, BudgetPlan $annualBudget)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,comment',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($request->action !== 'comment' && $annualBudget->status !== 'PENDING_FINAL_APPROVAL') {
            return back()->with('error', 'ສະຖານະບໍ່ຖືກຕ້ອງສຳລັບການກວດສອບນີ້');
        }

        // Save comment if provided
        if ($request->filled('comment')) {
            $annualBudget->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $request->comment,
                'submission_round' => $annualBudget->submission_round,
            ]);
        }

        if ($request->action === 'approve') {
            $annualBudget->update(['status' => 'APPROVED']);
            $msg = 'ອະນຸມັດແຜນງົບປະມານສຳເລັດ!';

            // Notify the HoF who created this plan
            $creator = User::find($annualBudget->created_by);
            if ($creator) {
                $creator->notify(new BudgetPlanStatusChanged(
                    $annualBudget,
                    'ແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year . ' ໄດ້ຮັບການອະນຸມັດແລ້ວ',
                    route('head_of_finance.annual-budget.show', $annualBudget->id)
                ));
            }

            return redirect()->route('head_of_faculty.annual-budget.index')->with('success', $msg);

        } elseif ($request->action === 'reject') {
            if (!$request->filled('comment')) {
                return back()->with('error', 'ກະລຸນາໃສ່ຄຳເຫັນກ່ອນສົ່ງກັບໃຫ້ແກ້ໄຂ');
            }

            $annualBudget->update(['status' => 'MODIFYING']);
            $msg = 'ສົ່ງແຜນກັບໃຫ້ແກ້ໄຂສຳເລັດ!';

            // Notify the HoF who created this plan
            $creator = User::find($annualBudget->created_by);
            if ($creator) {
                $creator->notify(new BudgetPlanStatusChanged(
                    $annualBudget,
                    'ແຜນງົບປະມານປະຈຳປີ ' . $annualBudget->fiscal_year . ' ຖືກສົ່ງກັບໃຫ້ປັບປຸງ — ກະລຸນາກວດສອບຄຳເຫັນ',
                    route('head_of_finance.annual-budget.show', $annualBudget->id)
                ));
            }

            return redirect()->route('head_of_faculty.annual-budget.index')->with('success', $msg);

        } else {
            return back()->with('success', 'ເພີ່ມຄວາມຄິດເຫັນສຳເລັດ!');
        }
    }

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
            $aggregated[$item->account_id] = [
                'amount_regular' => $item->amount_regular,
                'amount_academic' => $item->amount_academic,
                'original_item' => $item,
            ];
        }

        $computeSum = function ($accountId) use (&$computeSum, &$aggregated, $childrenMap) {
            $reg = $aggregated[$accountId]['amount_regular'] ?? 0;
            $acad = $aggregated[$accountId]['amount_academic'] ?? 0;
            $hasItems = isset($aggregated[$accountId]['original_item']);

            if (isset($childrenMap[$accountId])) {
                $reg = 0;
                $acad = 0;
                foreach ($childrenMap[$accountId] as $childId) {
                    $childSums = $computeSum($childId);
                    $reg += $childSums['reg'];
                    $acad += $childSums['acad'];
                    if ($childSums['hasItems']) {
                        $hasItems = true;
                    }
                }
                $aggregated[$accountId]['amount_regular'] = $reg;
                $aggregated[$accountId]['amount_academic'] = $acad;
            }

            $aggregated[$accountId]['should_render'] = $hasItems || $reg > 0 || $acad > 0;
            return ['reg' => $reg, 'acad' => $acad, 'hasItems' => $hasItems];
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
                $isParent = isset($childrenMap[$acc->id]);

                if (isset($aggregated[$acc->id]['original_item'])) {
                    $item = $aggregated[$acc->id]['original_item'];
                    $item->amount_regular = $reg;
                    $item->amount_academic = $acad;
                    $item->is_parent = $isParent;
                    $item->setRelation('account', $acc);
                    $syntheticItems->push($item);
                } else {
                    $syntheticItem = new \App\Models\BudgetLineItem([
                        'account_id' => $acc->id,
                        'amount_regular' => $reg,
                        'amount_academic' => $acad,
                    ]);
                    $syntheticItem->is_parent = $isParent;
                    $syntheticItem->setRelation('account', $acc);
                    $syntheticItems->push($syntheticItem);
                }
            }
        }

        return $syntheticItems;
    }

    protected function sortLineItemsHierarchically($lineItems)
    {
        return $this->synthesizeTreeAndRollUp($lineItems);
    }
}
