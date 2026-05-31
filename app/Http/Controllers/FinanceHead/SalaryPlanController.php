<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\SalaryBudgetCode;
use App\Models\SalaryEntry;
use App\Models\SalaryPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($data): void {
            $plan = SalaryPlan::create([
                'fiscal_year' => (string) $data['fiscal_year'],
                'month'       => (int) $data['month'],
                'notes'       => $data['notes'] ?? null,
                'created_by'  => Auth::id(),
            ]);

            // Pre-create zero entries for all leaf budget codes
            $leaves = SalaryBudgetCode::where('is_leaf', true)->get();
            $now = now();
            $rows = $leaves->map(fn ($code) => [
                'plan_id'        => $plan->id,
                'budget_code_id' => $code->id,
                'person_count'   => 0,
                'atm_amount'     => 0,
                'cash_amount'    => 0,
                'monthly_total'  => 0,
                'annual_amount'  => 0,
                'remark'         => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ])->toArray();

            SalaryEntry::insert($rows);
        });

        $plan = SalaryPlan::where('fiscal_year', $data['fiscal_year'])
            ->where('month', $data['month'])
            ->first();

        return redirect()->route('head_of_finance.salary.manage', $plan)
            ->with('success', 'ສ້າງແຜນເງິນເດືອນ ເດືອນ ' . $plan->monthLabel() . ' ສຳເລັດ');
    }

    public function manage(SalaryPlan $salaryPlan)
    {
        $entries = $salaryPlan->entries()
            ->get()
            ->keyBy('budget_code_id');

        $roots = SalaryBudgetCode::with('children.children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        // Pre-compute aggregates for every node so the view needs no PHP function
        $nodeAgg = [];
        $allCodes = SalaryBudgetCode::all()->keyBy('id');
        $this->buildAggregates($roots, $entries, $allCodes, $nodeAgg);

        return view('dashboards.finance_head.salary.manage', compact('salaryPlan', 'roots', 'entries', 'nodeAgg'));
    }

    private function buildAggregates(
        \Illuminate\Database\Eloquent\Collection $nodes,
        \Illuminate\Support\Collection $entries,
        \Illuminate\Database\Eloquent\Collection $allCodes,
        array &$nodeAgg
    ): array {
        $totals = ['persons' => 0, 'atm' => 0, 'cash' => 0, 'total' => 0, 'annual' => 0];

        foreach ($nodes as $node) {
            if ($node->is_leaf) {
                $entry = $entries->get($node->id);
                $agg = [
                    'persons' => (int)   ($entry?->person_count  ?? 0),
                    'atm'     => (float) ($entry?->atm_amount    ?? 0),
                    'cash'    => (float) ($entry?->cash_amount   ?? 0),
                    'total'   => (float) ($entry?->monthly_total ?? 0),
                    'annual'  => (float) ($entry?->annual_amount ?? 0),
                ];
            } else {
                $agg = $this->buildAggregates($node->children, $entries, $allCodes, $nodeAgg);
            }

            $nodeAgg[$node->id] = $agg;

            foreach (['persons', 'atm', 'cash', 'total', 'annual'] as $k) {
                $totals[$k] += $agg[$k];
            }
        }

        return $totals;
    }

    public function destroy(SalaryPlan $salaryPlan)
    {
        if ($salaryPlan->isApproved()) {
            return back()->with('error', 'ບໍ່ສາມາດລຶບແຜນທີ່ອະນຸມັດແລ້ວ');
        }

        $salaryPlan->delete();

        return redirect()->route('head_of_finance.salary.index')
            ->with('success', 'ລຶບແຜນເງິນເດືອນສຳເລັດ');
    }

}
