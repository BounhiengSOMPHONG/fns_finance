<?php

use App\Models\DegreeProgram;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DegreeProgram::query()
            ->where('level', 'bachelor')
            ->where('study_year', 1)
            ->where(function ($query): void {
                $query->where('name', 'like', '% ປີ 1')
                    ->orWhere('name', 'like', '% ปี 1');
            })
            ->get()
            ->each(function (DegreeProgram $program): void {
                $program->forceFill([
                    'name' => trim((string) preg_replace('/\s*(?:ປີ|ปี)\s*1\s*$/u', '', (string) $program->name)),
                ])->save();
            });
    }

    public function down(): void
    {
        DegreeProgram::query()
            ->where('level', 'bachelor')
            ->where('study_year', 1)
            ->where('code', 'like', 'B-%-Y1')
            ->where('name', 'not like', '% ປີ 1')
            ->get()
            ->each(function (DegreeProgram $program): void {
                $program->forceFill([
                    'name' => trim((string) $program->name).' ປີ 1',
                ])->save();
            });
    }
};
