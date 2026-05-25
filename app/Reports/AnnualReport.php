<?php

namespace App\Reports;

use App\Reports\Sections\IncomeSection;
use App\Reports\Sections\ExpenseSection;
use App\Reports\Sections\AssetSection;
use App\Reports\Sections\StudentSection;
use App\Reports\Sections\SummarySection;

class AnnualReport
{
    /**
     * Register sections here — order = print order.
     * To add a new module: create the class, add it to this list.
     */
    private array $registry = [
        IncomeSection::class,
        ExpenseSection::class,
        AssetSection::class,
        StudentSection::class,
        SummarySection::class,
    ];

    public function build(int $year): array
    {
        $sections   = [];
        $allTotals  = [];

        // First pass: collect data + totals from all non-summary sections
        foreach ($this->registry as $class) {
            if ($class === SummarySection::class) continue;

            $sec = new $class;
            $allTotals[$sec->id] = [
                'title'  => $sec->title,
                'totals' => $sec->totals($year),
            ];
        }

        // Second pass: build full section list (summary receives allTotals)
        foreach ($this->registry as $class) {
            $sec  = new $class;
            $data = $sec->data($year);

            if ($class === SummarySection::class) {
                $data['allTotals'] = $allTotals;
                $data['year']      = $year;
            }

            $sections[] = [
                'id'    => $sec->id,
                'title' => $sec->title,
                'view'  => $sec->view,
                'data'  => $data,
            ];
        }

        return $sections;
    }
}
