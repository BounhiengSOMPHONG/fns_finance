<?php

namespace App\Reports\Sections;

class StudentSection
{
    public string $id    = 'student';
    public string $title = 'ຈຳນວນນັກສຶກສາ';
    public string $view  = 'reports.annual.sections.student';

    public function data(int $year): array
    {
        return ['year' => $year];
    }

    public function totals(int $year): array
    {
        return ['gross' => 0, 'faculty' => 0, 'p1' => 0, 'p2' => 0];
    }
}
