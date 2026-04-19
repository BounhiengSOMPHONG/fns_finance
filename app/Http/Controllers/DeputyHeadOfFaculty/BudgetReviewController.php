<?php

namespace App\Http\Controllers\DeputyHeadOfFaculty;

use App\Http\Controllers\Controller;
use App\Models\BudgetPlan;
use Illuminate\Http\Request;

class BudgetReviewController extends Controller
{
    public function index()
    {
        // Show only plans where the current deputy is explicitly assigned as reviewer
        $plans = BudgetPlan::where('status', 'PENDING_REVIEW')
            ->whereHas('reviewers', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderByDesc('fiscal_year')
            ->get();

        return view('deputy_head_of_faculty.annual-budget.index', compact('plans'));
    }

    public function show(BudgetPlan $annualBudget)
    {
        // Block direct URL access if this deputy is not assigned on the plan
        $isReviewer = $annualBudget->reviewers()->where('user_id', auth()->id())->exists();
        if (!$isReviewer) {
            abort(403, 'ທ່ານບໍ່ໄດ້ຮັບມອບໝາຍໃຫ້ກວດສອບແຜນນີ້');
        }

        $annualBudget->load(['lineItems.account', 'comments.user.role', 'comments.markedBy']);
        $annualBudget->setRelation('lineItems', $this->sortLineItemsHierarchically($annualBudget->lineItems));

        return view('deputy_head_of_faculty.annual-budget.show', compact('annualBudget'));
    }

    public function review(Request $request, BudgetPlan $annualBudget)
    {
        // Check that the current user is assigned as a reviewer
        $isReviewer = $annualBudget->reviewers()->where('user_id', auth()->id())->exists();
        if (!$isReviewer) {
            abort(403, 'ທ່ານບໍ່ໄດ້ຮັບມອບໝາຍໃຫ້ກວດສອບແຜນນີ້');
        }

        if ($annualBudget->status === 'MODIFYING') {
            return back()->with('error', 'ບໍ່ສາມາດໃຫ້ຄຳເຫັນໃນຂະນະທີ່ແຜນກຳລັງຖືກແກ້ໄຂ');
        }

        $request->validate([
            'action' => 'required|in:comment',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($request->filled('comment')) {
            $annualBudget->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $request->comment,
                'submission_round' => $annualBudget->submission_round,
            ]);
        }

        return back()->with('success', 'ເພີ່ມຄວາມຄິດເຫັນສຳເລັດ!');
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
