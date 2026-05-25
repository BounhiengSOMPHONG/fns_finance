<?php

namespace App\Reports\Sections;

class AssetSection
{
    public string $id    = 'asset';
    public string $title = 'ຊັບສິນ';
    public string $view  = 'reports.annual.sections.asset';

    public function data(int $year): array
    {
        return ['year' => $year];
    }

    public function totals(int $year): array
    {
        return ['gross' => 0, 'faculty' => 0, 'p1' => 0, 'p2' => 0];
    }
}
