<?php

namespace App\Reports\Sections;

use App\Models\ExpensePlan;

class ExpenseSection
{
    public string $id    = 'expense';
    public string $title = 'ປະເມີນລາຍຈ່າຍ';
    public string $view  = 'reports.annual.sections.expense';

    public function data(int $year): array
    {
        $plan = ExpensePlan::with([
            'topCategories.children.items',
            'topCategories.items',
            'allCategories',
        ])->where('fiscal_year', $year)->first();

        $topCategories = $plan ? $plan->topCategories : collect();

        return compact('plan', 'topCategories', 'year');
    }

    public function totals(int $year): array
    {
        $plan = ExpensePlan::with(['allCategories.items'])
            ->where('fiscal_year', $year)->first();

        $total = $plan ? (float) $plan->allCategories->flatMap->items->sum('annual_amount') : 0;

        return ['gross' => $total, 'faculty' => $total, 'p1' => $total, 'p2' => 0];
    }
}
