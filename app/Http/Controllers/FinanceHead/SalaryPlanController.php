<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\PlanningYear;
use App\Models\SalaryPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SalaryPlanController extends Controller
{
    public function index()
    {
        $plans = SalaryPlan::with('creator')
            ->orderByDesc('fiscal_year')
            ->orderByDesc('month')
            ->paginate(15);

        return view('dashboards.finance_head.salary.index', compact('plans'));
    }

    public function create()
    {
        return view('dashboards.finance_head.salary.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'month'       => 'required|integer|min:1|max:12',
            'notes'       => 'nullable|string|max:500',
        ]);

        $exists = SalaryPlan::where('fiscal_year', $data['fiscal_year'])
            ->where('month', $data['month'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'ມີຂໍ້ມູນເງິນເດືອນເດືອນ ' . str_pad($data['month'], 2, '0', STR_PAD_LEFT) . '/' . $data['fiscal_year'] . ' ແລ້ວ');
        }

        $planningYear = PlanningYear::firstOrCreate(
            ['year' => (int) $data['fiscal_year']],
            [
                'name' => 'Planning ' . $data['fiscal_year'],
                'is_active' => true,
            ]
        );

        $plan = SalaryPlan::create([
            'planning_year_id' => $planningYear->id,
            'fiscal_year' => (int) $data['fiscal_year'],
            'month'       => (int) $data['month'],
            'notes'       => $data['notes'] ?? null,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('head_of_finance.salary.manage', $plan)
            ->with('success', 'ສ້າງແຜນເງິນເດືອນ ເດືອນ ' . $plan->monthLabel() . ' ສຳເລັດ');
    }

    public function manage(SalaryPlan $salaryPlan)
    {
        $entries = $salaryPlan->entries()
            ->with('chartOfAccount')
            ->orderBy('id')
            ->get();

        // Load the whole COA tree once so we can compute each leaf's top-level (main) ancestor
        // without round-tripping per row.
        $all = ChartOfAccount::orderBy('account_code')->get(['id', 'account_code', 'account_name', 'parent_id']);
        $byId = $all->keyBy('id');

        $mainAccounts = $all->whereNull('parent_id')->values();

        $childParentIds = $all->pluck('parent_id')->filter()->unique()->all();
        $leaves         = $all->reject(fn ($a) => in_array($a->id, $childParentIds, true))->values();

        $coa = $leaves->map(function ($a) use ($byId) {
            // Walk up to find the top-level (parent_id IS NULL) ancestor.
            $mainId = $a->id;
            $node   = $a;
            $guard  = 0;
            while ($node && $node->parent_id && $guard++ < 10) {
                $node = $byId->get($node->parent_id);
                if ($node) $mainId = $node->id;
            }

            return [
                'id'      => $a->id,
                'code'    => $a->account_code,
                'name'    => $a->account_name,
                'main_id' => $mainId,
            ];
        });

        return view('dashboards.finance_head.salary.manage', compact('salaryPlan', 'entries', 'coa', 'mainAccounts'));
    }

    public function destroy(SalaryPlan $salaryPlan)
    {
        $salaryPlan->delete();

        return redirect()->route('head_of_finance.salary.index')
            ->with('success', 'ລຶບແຜນເງິນເດືອນສຳເລັດ');
    }
}
