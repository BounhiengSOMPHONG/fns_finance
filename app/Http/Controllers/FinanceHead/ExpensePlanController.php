<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpensePlan;
use App\Models\ExpenseRefCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensePlanController extends Controller
{
    public function index()
    {
        $plans = ExpensePlan::with(['creator', 'entries'])
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

        return redirect()->route('head_of_finance.expense.manage', $plan)
            ->with('success', 'ສ້າງແຜນງົບປະມານສຳເລັດ');
    }

    public function manage(ExpensePlan $expensePlan)
    {
        $expensePlan->load('entries');

        $coaMap       = $this->buildCoaMap();
        $mainAccounts = ChartOfAccount::whereNull('parent_id')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);
        $refCodes     = ExpenseRefCode::orderBy('sort_order')->orderBy('code')->get();

        return view('dashboards.finance_head.expense.manage', compact('expensePlan', 'coaMap', 'mainAccounts', 'refCodes'));
    }

    public function destroy(ExpensePlan $expensePlan)
    {
        if ($expensePlan->isApproved()) {
            return back()->with('error', 'ບໍ່ສາມາດລຶບແຜນທີ່ອະນຸມັດແລ້ວ');
        }

        // Flat entries cascade cleanly via FK — no deep self-referential tree.
        $expensePlan->delete();

        return redirect()->route('head_of_finance.expense.index')
            ->with('success', 'ລຶບແຜນງົບປະມານສຳເລັດ');
    }

    public function approve(ExpensePlan $expensePlan)
    {
        $expensePlan->update(['status' => 'APPROVED']);

        return back()->with('success', 'ອະນຸມັດແຜນງົບປະມານສຳເລັດ');
    }

    /**
     * Map of account_code => {id, name, main_cat, main_item} for the manage grid.
     * Main Cat / Main Item are derived from the COA parent chain (resolved in
     * memory to avoid per-row queries).
     */
    private function buildCoaMap(): array
    {
        // All accounts needed to walk parent chains for main_cat / main_item.
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        $byId     = $accounts->keyBy('id');

        // Picker only exposes leaf accounts (nodes with no children).
        $childParentIds = $accounts->pluck('parent_id')->filter()->unique()->all();
        $leaves         = $accounts->reject(fn ($a) => in_array($a->id, $childParentIds, true));

        $map = [];
        foreach ($leaves as $account) {
            $chain   = [];
            $node    = $account;
            $mainId  = $account->id;
            $guard   = 0;
            while ($node && $guard++ < 10) {
                array_unshift($chain, $node->account_name);
                $mainId = $node->id;
                $node   = $node->parent_id ? $byId->get($node->parent_id) : null;
            }

            $map[$account->account_code] = [
                'id'        => $account->id,
                'name'      => $account->account_name,
                'main_cat'  => $chain[0] ?? '',
                'main_item' => $chain[1] ?? '',
                'main_id'   => $mainId,
            ];
        }

        return $map;
    }
}
