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
        return redirect()->route('head_of_finance.manage-plan.index');
    }

    public function create()
    {
        return view('dashboards.finance_head.salary.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        $exists = SalaryPlan::where('fiscal_year', $data['fiscal_year'])
            ->where('month', $data['month'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'ມີຂໍ້ມູນເງິນເດືອນເດືອນ '.str_pad($data['month'], 2, '0', STR_PAD_LEFT).'/'.$data['fiscal_year'].' ແລ້ວ');
        }

        $planningYear = PlanningYear::firstOrCreate(
            ['year' => (int) $data['fiscal_year']],
            [
                'name' => 'Planning '.$data['fiscal_year'],
                'is_active' => true,
            ]
        );

        abort_if(
            $planningYear->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        $plan = SalaryPlan::create([
            'planning_year_id' => $planningYear->id,
            'fiscal_year' => (int) $data['fiscal_year'],
            'month' => (int) $data['month'],
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('head_of_finance.salary.manage', $plan)
            ->with('success', 'ສ້າງແຜນເງິນເດືອນ ເດືອນ '.$plan->monthLabel().' ສຳເລັດ');
    }

    public function manage(SalaryPlan $salaryPlan)
    {
        abort_if(
            $salaryPlan->planningYear?->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        $entries = $salaryPlan->entries()
            ->with('chartOfAccount')
            ->orderBy('id')
            ->get();

        $all = ChartOfAccount::orderBy('account_code')->get(['id', 'account_code', 'account_name', 'parent_id']);
        $rootIds = $all
            ->whereNull('parent_id')
            ->filter(fn ($a) => str_starts_with((string) $a->account_code, '60')
                || str_starts_with((string) $a->account_code, '61'))
            ->pluck('id');

        $subAccounts = collect();
        $parentIds = $rootIds;

        while ($parentIds->isNotEmpty()) {
            $children = $all->whereIn('parent_id', $parentIds->all())->values();

            if ($children->isEmpty()) {
                break;
            }

            $subAccounts = $subAccounts->merge($children);
            $parentIds = $children->pluck('id');
        }

        $parentAccountIds = $all->pluck('parent_id')->filter()->unique();
        $leafAccounts = $subAccounts->reject(fn ($a) => $parentAccountIds->contains($a->id));
        $accountsById = $all->keyBy('id');
        $rootIdsLookup = $rootIds->flip();
        $accountsByCode = $all->keyBy('account_code');

        $coa = $leafAccounts->sortBy('account_code')->values()->map(function ($a) use ($accountsByCode, $accountsById, $rootIdsLookup) {
            $groupCode = substr((string) $a->account_code, 0, 3).'00000';
            $group = $accountsByCode->get($groupCode);
            $topic = $accountsById->get($a->parent_id);

            if (! $group) {
                $group = $a;

                while ($group?->parent_id && ! $rootIdsLookup->has($group->parent_id)) {
                    $group = $accountsById->get($group->parent_id);
                }
            }

            return [
                'id' => $a->id,
                'code' => $a->account_code,
                'name' => $a->account_name,
                'topic_code' => $topic?->account_code,
                'topic_name' => $topic?->account_name,
                'group_code' => $group?->account_code,
                'group_name' => $group?->account_name,
            ];
        });

        return view('dashboards.finance_head.salary.manage', compact('salaryPlan', 'entries', 'coa'));
    }
}
