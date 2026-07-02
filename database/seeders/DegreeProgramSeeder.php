<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DegreeProgramSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('degree_programs')->truncate();
        Schema::enableForeignKeyConstraints();

        $programs = [];

        // ─── Year 2 ────────────────────────────────────────────────────
        $year2 = [
            ['B-CS-Y2',    'ວິທະຍາສາດຄອມ'],
            ['B-PD-Y2',    'ພັດທະນາໂປຣແກຣມ'],
            ['B-WD-Y2',    'ພັດທະນາເວບໄຊ້'],
            ['B-CSC-Y2',   'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ'],
            ['B-CSC-EVE-Y2', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-CS-EVE-Y2', 'ວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-PD-EVE-Y2', 'ການພັດທະນາໂປຣແກຣມ ພາກຄໍ່າ'],
            ['B-WD-EVE-Y2', 'ການພັດທະນາເວບໄຊ້ ພາກຄໍ່າ'],
            ['B-AI-Y2', 'ເອໄອ ແລະ ນະວັດຕະກໍາ'],
            ['B-MAA-Y2',   'ຄະນິດສາດນໍາໃຊ້'],
            ['B-MAE-Y2',   'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ'],
            ['B-STAT-Y2',  'ຄະນິດສາດສະຖິຕິ'],
            ['B-BIO-Y2',   'ຊີວະທົ່ວໄປ'],
            ['B-BT-Y2',    'ເທັກໂນໂລຍີ່ຊີວະພາບ'],
            ['B-CHEM-Y2',  'ເຄມີທົ່ວໄປ'],
            ['B-ECHE-Y2',  'ເຄມີສິ່ງແວດລ້ອມ'],
            ['B-PHYS-Y2',  'ຟີຊິກທົ່ວໄປ'],
            ['B-GPHY-Y2',  'ທໍລະນີຟີຊິກ'],
            ['B-MATS-Y2',  'ວັດສະດຸສາດ'],
            ['B-NPHY-Y2',  'ຟິຊິກນິວເຄຣຍ'],
        ];
        foreach ($year2 as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'bachelor', 2);
        }

        // ─── Year 3 ────────────────────────────────────────────────────
        $year3 = [
            ['B-CS-Y3',    'ວິທະຍາສາດຄອມ'],
            ['B-PD-Y3',    'ພັດທະນາໂປຣແກຣມ'],
            ['B-WD-Y3',    'ພັດທະນາເວບໄຊ້'],
            ['B-CS-EVE-Y3', 'ວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-PD-EVE-Y3', 'ການພັດທະນາໂປຣແກຣມ ພາກຄໍ່າ'],
            ['B-WD-EVE-Y3', 'ການພັດທະນາເວບໄຊ້ ພາກຄໍ່າ'],
            ['B-AI-Y3', 'ເອໄອ ແລະ ນະວັດຕະກໍາ'],
            ['B-MATH-Y3',  'ຄະນິດທົ່ວໄປ'],
            ['B-MAE-Y3',   'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ'],
            ['B-STAT-Y3',  'ຄະນິດສາດສະຖິຕິ'],
            ['B-BIO-Y3',   'ຊີວະວິທະຍາທົ່ວໄປ'],
            ['B-BT-Y3',    'ເທັກໂນໂລຍີ່ຊີວະພາບ'],
            ['B-CHEM-Y3',  'ເຄມີສາດທົ່ວໄປ'],
            ['B-ECHE-Y3',  'ເຄມີສິ່ງແວດລ້ອມ'],
            ['B-PHYS-Y3',  'ຟີຊິກສາດທົ່ວໄປ'],
            ['B-GPHY-Y3',  'ທໍລະນີຟີຊິກ'],
            ['B-MATS-Y3',  'ວັດສະດຸສາດ'],
            ['B-NPHY-Y3',  'ຟິຊິກນິວເຄຣຍ'],
        ];
        foreach ($year3 as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'bachelor', 3);
        }

        // ─── Year 4 ────────────────────────────────────────────────────
        $year4 = [
            ['B-CS-Y4',    'ວິທະຍາສາດຄອມ'],
            ['B-PD-Y4',    'ພັດທະນາໂປຣແກຣມ'],
            ['B-WD-Y4',    'ພັດທະນາເວບໄຊ້'],
            ['B-CS-EVE-Y4', 'ວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-PD-EVE-Y4', 'ການພັດທະນາໂປຣແກຣມ ພາກຄໍ່າ'],
            ['B-WD-EVE-Y4', 'ການພັດທະນາເວບໄຊ້ ພາກຄໍ່າ'],
            ['B-AI-Y4', 'ເອໄອ ແລະ ນະວັດຕະກໍາ'],
            ['B-MATH-Y4',  'ຄະນິດທົ່ວໄປ'],
            ['B-MAE-Y4',   'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ'],
            ['B-STAT-Y4',  'ຄະນິດສາດສະຖິຕິ'],
            ['B-BIO-Y4',   'ຊີວະວິທະຍາທົ່ວໄປ'],
            ['B-BT-Y4',    'ເທັກໂນໂລຍີ່ຊີວະພາບ'],
            ['B-CHEM-Y4',  'ເຄມີສາດທົ່ວໄປ'],
            ['B-ECHE-Y4',  'ເຄມີສິ່ງແວດລ້ອມ'],
            ['B-PHYS-Y4',  'ຟີຊິກສາດທົ່ວໄປ'],
            ['B-GPHY-Y4',  'ທໍລະນີຟີຊິກ'],
            ['B-MATS-Y4',  'ວັດສະດຸສາດ'],
            ['B-NPHY-Y4',  'ຟິຊິກນິວເຄຣຍ'],
        ];
        foreach ($year4 as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'bachelor', 4);
        }

        // ─── Master (coursework) ───────────────────────────────────────
        $masters = [
            ['M-PHYS',  'ປະລິນຍາໂທຟິຊິກນໍາໃຊ້'],
            ['M-MATH',  'ປະລິນຍາໂທຄະນິດສາດ'],
            ['M-BIO',   'ປິລິນຍາໂທຊີວະວິທະຍາ'],
            ['M-CHEM',  'ປະລິນຍາໂທເຄມີ'],
            ['M-CS',    'ປະລິນຍາໂທວິທະຍາສາດຄອມພິວເຕີ'],
        ];
        foreach ($masters as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'master', null);
        }

        // ─── Master (research format) ──────────────────────────────────
        $masterResearch = [
            ['MR-PHYS',  'ຟີຊິກສາດຮູບແບບຄົ້ນຄວ້າ'],
            ['MR-CHEM',  'ເຄມີສາດຮູບແບບຄົ້ນຄວ້າ'],
            ['MR-BIO',   'ຊີວະວິທະຍາຮູບແບບຄົ້ນຄວ້າ'],
        ];
        foreach ($masterResearch as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'master', null);
        }

        // ─── PhD ───────────────────────────────────────────────────────
        $phd = [
            ['D-PHYS',  'ປະລີິນຍາເອກຟິຊິກ'],
            ['D-BIO',   'ປະລີິນຍາເອກຊີວະວິທະຍາ'],
        ];
        foreach ($phd as [$code, $name]) {
            $programs[] = $this->programRow($code, $name, 'phd', null);
        }

        $now = now();
        foreach ($programs as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('degree_programs')->insert($programs);
    }

    private function programRow(string $code, string $name, string $level, ?int $studyYear): array
    {
        [$department, $sortOrder] = $this->departmentForCode($code);

        return [
            'code' => $code,
            'name' => $name,
            'level' => $level,
            'study_year' => $studyYear,
            'include_in_planning' => true,
            'academic_department' => $department,
            'department_sort_order' => $sortOrder,
        ];
    }

    private function departmentForCode(string $code): array
    {
        $base = strtoupper($code);
        $base = preg_replace('/^MR-/', 'M-', $base);
        $base = preg_replace('/-EVE(?=-Y\d+$)/', '', $base);
        $base = preg_replace('/-Y\d+$/', '', $base);

        $department = match ($base) {
            'B-MAA', 'B-MAE', 'B-STAT', 'B-MATH', 'M-MATH' => 'math_stats',
            'B-PHYS', 'B-GPHY', 'B-MATS', 'B-NPHY', 'M-PHYS', 'D-PHYS' => 'physics',
            'B-CHEM', 'B-ECHE', 'M-CHEM' => 'chemistry',
            'B-BIO', 'B-BT', 'M-BIO', 'D-BIO' => 'biology',
            'B-CS', 'B-PD', 'B-WD', 'B-CSC', 'B-AI', 'M-CS' => 'computer_science',
            default => 'other',
        };

        $orders = [
            'math_stats' => 10,
            'physics' => 20,
            'chemistry' => 30,
            'biology' => 40,
            'computer_science' => 50,
            'other' => 90,
        ];

        return [$department, $orders[$department] ?? 90];
    }
}
