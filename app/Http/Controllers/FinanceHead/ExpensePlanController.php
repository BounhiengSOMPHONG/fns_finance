<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseCategoryTemplate;
use App\Models\ExpensePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensePlanController extends Controller
{
    public function index()
    {
        $plans = ExpensePlan::with(['creator', 'allCategories.items'])
            ->orderByDesc('fiscal_year')
            ->paginate(15);

        return view('dashboards.finance_head.expense.index', compact('plans'));
    }

    public function create()
    {
        return view('dashboards.finance_head.expense.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100|unique:expense_plans,fiscal_year',
            'notes'       => 'nullable|string|max:500',
        ]);

        $plan = ExpensePlan::create([
            'fiscal_year' => $data['fiscal_year'],
            'notes'       => $data['notes'] ?? null,
            'status'      => 'DRAFT',
            'created_by'  => Auth::id(),
        ]);

        $this->seedFromTemplate($plan);

        return redirect()->route('head_of_finance.expense.manage', $plan)
            ->with('success', 'ສ້າງແຜນງົບປະມານສຳເລັດ');
    }

    /**
     * Copy the global category template into a freshly-created plan so the
     * standard main/sub category tree is ready without rebuilding it by hand.
     */
    private function seedFromTemplate(ExpensePlan $plan): void
    {
        $grouped = ExpenseCategoryTemplate::orderBy('sort_order')
            ->orderBy('ref_code')->get()->groupBy('parent_id');

        if ($grouped->isEmpty()) {
            return;
        }

        $this->copyLevel($plan, $grouped, null, null);
    }

    private function copyLevel(ExpensePlan $plan, $grouped, $tplParentId, $newParentId): void
    {
        foreach ($grouped->get($tplParentId, collect()) as $tpl) {
            $cat = ExpenseCategory::create([
                'plan_id'      => $plan->id,
                'parent_id'    => $newParentId,
                'ref_code'     => $tpl->ref_code,
                'name'         => $tpl->name,
                'sort_order'   => $tpl->sort_order,
                'formula_type' => 'AB',   // per-plan default; adjustable on manage page
            ]);
            $this->copyLevel($plan, $grouped, $tpl->id, $cat->id);
        }
    }

    public function show(ExpensePlan $expensePlan)
    {
        $expensePlan->load([
            'topCategories.children.children.items.chartOfAccount',
            'topCategories.children.items.chartOfAccount',
            'topCategories.items.chartOfAccount',
            'creator',
        ]);

        return view('dashboards.finance_head.expense.show', compact('expensePlan'));
    }

    public function manage(ExpensePlan $expensePlan)
    {
        $expensePlan->load([
            'topCategories.children.children.items.chartOfAccount',
            'topCategories.children.items.chartOfAccount',
            'topCategories.items.chartOfAccount',
        ]);

        $chartOfAccounts = \App\Models\ChartOfAccount::orderBy('account_code')->get();

        return view('dashboards.finance_head.expense.manage', compact('expensePlan', 'chartOfAccounts'));
    }

    public function destroy(ExpensePlan $expensePlan)
    {
        if ($expensePlan->isApproved()) {
            return back()->with('error', 'ບໍ່ສາມາດລຶບແຜນທີ່ອະນຸມັດແລ້ວ');
        }

        // The deep self-referential category tree exceeds MySQL's recursive
        // cascade limit, so clear it bottom-up before deleting the plan.
        foreach ($expensePlan->topCategories as $cat) {
            $this->deleteCategoryTree($cat);
        }

        $expensePlan->delete();

        return redirect()->route('head_of_finance.expense.index')
            ->with('success', 'ລຶບແຜນງົບປະມານສຳເລັດ');
    }

    private function deleteCategoryTree(ExpenseCategory $category): void
    {
        foreach ($category->children as $child) {
            $this->deleteCategoryTree($child);
        }
        $category->items()->delete();
        $category->delete();
    }

    public function approve(ExpensePlan $expensePlan)
    {
        $expensePlan->update(['status' => 'APPROVED']);

        return back()->with('success', 'ອະນຸມັດແຜນງົບປະມານສຳເລັດ');
    }
}
