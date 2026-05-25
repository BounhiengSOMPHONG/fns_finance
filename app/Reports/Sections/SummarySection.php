<?php

namespace App\Reports\Sections;

class SummarySection
{
    public string $id    = 'summary';
    public string $title = 'ສັງລວມທັງໝົດ';
    public string $view  = 'reports.annual.sections.summary';

    public function data(int $year): array
    {
        // receives $allTotals injected by AnnualReport::build()
        return [];
    }

    public function totals(int $year): array
    {
        return [];
    }
}
