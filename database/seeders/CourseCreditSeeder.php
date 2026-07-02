<?php

namespace Database\Seeders;

use App\Models\CourseCreditSetting;
use App\Models\CourseCreditSplitSetting;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use Illuminate\Database\Seeder;

class CourseCreditSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Fix credit-unit prices for master & phd ──────────────────
        // Derived from Planning 2026.xls: price = fee_per_student / credit_units
        // master: 11,040,000 / 46 = 240,000 | phd: 22,800,000 / 38 = 600,000
        CreditUnitPriceSetting::where('level', 'master')->update(['credit_unit_price' => 240000]);
        CreditUnitPriceSetting::where('level', 'phd')->update(['credit_unit_price' => 600000]);

        // ── 2. Add bachelor year-1 programs (section 1.3) ───────────────
        // Mirroring the year-2 set; names use the same short form as Y2 partners.
        $year1Programs = [
            ['B-CS-Y1',   'ວິທະຍາສາດຄອມ'],
            ['B-PD-Y1',   'ພັດທະນາໂປຣແກຣມ'],
            ['B-WD-Y1',   'ພັດທະນາເວບໄຊ້'],
            ['B-CSC-Y1',  'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ'],
            ['B-CSC-EVE-Y1', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-CS-EVE-Y1', 'ວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ'],
            ['B-PD-EVE-Y1', 'ການພັດທະນາໂປຣແກຣມ ພາກຄໍ່າ'],
            ['B-WD-EVE-Y1', 'ການພັດທະນາເວບໄຊ້ ພາກຄໍ່າ'],
            ['B-AI-Y1', 'ເອໄອ ແລະ ນະວັດຕະກໍາ'],
            ['B-MAA-Y1',  'ຄະນິດສາດນໍາໃຊ້'],
            ['B-MAE-Y1',  'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ'],
            ['B-STAT-Y1', 'ຄະນິດສາດສະຖິຕິ'],
            ['B-BIO-Y1',  'ຊີວະທົ່ວໄປ'],
            ['B-BT-Y1',   'ເທັກໂນໂລຍີ່ຊີວະພາບ'],
            ['B-CHEM-Y1', 'ເຄມີທົ່ວໄປ'],
            ['B-ECHE-Y1', 'ເຄມີສິ່ງແວດລ້ອມ'],
            ['B-PHYS-Y1', 'ຟີຊິກທົ່ວໄປ'],
            ['B-GPHY-Y1', 'ທໍລະນີຟີຊິກ'],
            ['B-MATS-Y1', 'ວັດສະດຸສາດ'],
            ['B-NPHY-Y1', 'ຟິຊິກນິວເຄຣຍ'],
        ];

        foreach ($year1Programs as [$code, $name]) {
            [$department, $sortOrder] = $this->departmentForCode($code);
            DegreeProgram::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'level' => 'bachelor',
                    'study_year' => 1,
                    'include_in_planning' => true,
                    'academic_department' => $department,
                    'department_sort_order' => $sortOrder,
                ]
            );
        }

        CourseCreditSplitSetting::updateOrCreate(
            ['level' => 'master', 'start_year' => 2026],
            ['year1_percentage' => 0.60, 'year2_percentage' => 0.40]
        );
        CourseCreditSplitSetting::updateOrCreate(
            ['level' => 'phd', 'start_year' => 2026],
            ['year1_percentage' => 0.60, 'year2_percentage' => 0.40]
        );

        // ── 3. Seed course credit units ──────────────────────────────────
        // Formula verified: fee_per_student (from Planning 2026.xls) / credit_unit_price = credit_units
        // Bachelor price = 35,000 | Master price = 240,000 | PhD price = 600,000
        $credits = [
            // ── Year 1 (section 1.3) ──
            'B-CS-Y1' => 37,  // 1,295,000 / 35,000
            'B-PD-Y1' => 38,  // 1,330,000 / 35,000
            'B-WD-Y1' => 38,  // 1,330,000 / 35,000
            'B-CSC-Y1' => 37,  // 1,295,000 / 35,000
            'B-CSC-EVE-Y1' => 37,
            'B-CS-EVE-Y1' => 37,
            'B-PD-EVE-Y1' => 38,
            'B-WD-EVE-Y1' => 38,
            'B-MAA-Y1' => 37,  // 1,295,000 / 35,000
            'B-MAE-Y1' => 36,  // 1,260,000 / 35,000
            'B-STAT-Y1' => 36,  // 1,260,000 / 35,000
            'B-BIO-Y1' => 37,  // 1,295,000 / 35,000
            'B-BT-Y1' => 37,  // 1,295,000 / 35,000
            'B-CHEM-Y1' => 39,  // 1,365,000 / 35,000
            'B-ECHE-Y1' => 39,  // 1,365,000 / 35,000
            'B-PHYS-Y1' => 35,  // 1,225,000 / 35,000
            'B-GPHY-Y1' => 35,  // 1,225,000 / 35,000
            'B-MATS-Y1' => 35,  // 1,225,000 / 35,000
            'B-NPHY-Y1' => 35,  // 1,225,000 / 35,000

            // ── Year 2 (section 1.1) ──
            'B-CS-Y2' => 37,  // 1,295,000 / 35,000
            'B-PD-Y2' => 37,  // 1,295,000 / 35,000
            'B-WD-Y2' => 37,  // 1,295,000 / 35,000
            'B-CSC-Y2' => 27,  //   945,000 / 35,000
            'B-CSC-EVE-Y2' => 27,
            'B-CS-EVE-Y2' => 37,
            'B-PD-EVE-Y2' => 37,
            'B-WD-EVE-Y2' => 37,
            'B-MAA-Y2' => 37,  // 1,295,000 / 35,000
            'B-MAE-Y2' => 37,  // 1,295,000 / 35,000
            'B-STAT-Y2' => 37,  // 1,295,000 / 35,000
            'B-BIO-Y2' => 31,  // 1,085,000 / 35,000
            'B-BT-Y2' => 31,  // 1,085,000 / 35,000
            'B-CHEM-Y2' => 35,  // 1,225,000 / 35,000
            'B-ECHE-Y2' => 35,  // 1,225,000 / 35,000
            'B-PHYS-Y2' => 36,  // 1,260,000 / 35,000
            'B-GPHY-Y2' => 36,  // 1,260,000 / 35,000
            'B-MATS-Y2' => 37,  // 1,295,000 / 35,000
            'B-NPHY-Y2' => 36,  // 1,260,000 / 35,000

            // ── Year 3 (section 1.1) ──
            'B-CS-Y3' => 33,  // 1,155,000 / 35,000
            'B-PD-Y3' => 38,  // 1,330,000 / 35,000
            'B-WD-Y3' => 42,  // 1,470,000 / 35,000
            'B-CS-EVE-Y3' => 33,
            'B-PD-EVE-Y3' => 38,
            'B-WD-EVE-Y3' => 42,
            'B-MATH-Y3' => 39,  // 1,365,000 / 35,000
            'B-MAE-Y3' => 39,  // 1,365,000 / 35,000
            'B-STAT-Y3' => 37,  // 1,295,000 / 35,000
            'B-BIO-Y3' => 36,  // 1,260,000 / 35,000
            'B-BT-Y3' => 33,  // 1,155,000 / 35,000
            'B-CHEM-Y3' => 35,  // 1,225,000 / 35,000
            'B-ECHE-Y3' => 36,  // 1,260,000 / 35,000
            'B-PHYS-Y3' => 34,  // 1,190,000 / 35,000
            'B-GPHY-Y3' => 36,  // 1,260,000 / 35,000
            'B-MATS-Y3' => 36,  // 1,260,000 / 35,000
            'B-NPHY-Y3' => 36,  // 1,260,000 / 35,000

            // ── Year 4 (section 1.1) ──
            'B-CS-Y4' => 27,  //   945,000 / 35,000
            'B-PD-Y4' => 30,  // 1,050,000 / 35,000
            'B-WD-Y4' => 27,  //   945,000 / 35,000
            'B-CS-EVE-Y4' => 27,
            'B-PD-EVE-Y4' => 30,
            'B-WD-EVE-Y4' => 27,
            'B-MATH-Y4' => 24,  //   840,000 / 35,000
            'B-MAE-Y4' => 27,  //   945,000 / 35,000
            'B-STAT-Y4' => 27,  //   945,000 / 35,000
            'B-BIO-Y4' => 25,  //   875,000 / 35,000
            'B-BT-Y4' => 23,  //   805,000 / 35,000
            'B-CHEM-Y4' => 23,  //   805,000 / 35,000
            'B-ECHE-Y4' => 22,  //   770,000 / 35,000
            'B-PHYS-Y4' => 27,  //   945,000 / 35,000
            'B-GPHY-Y4' => 30,  // 1,050,000 / 35,000
            'B-MATS-Y4' => 27,  //   945,000 / 35,000
            'B-NPHY-Y4' => 28,  //   980,000 / 35,000

            // ── Master — full program credits (old year-2+ + old year-1) ──
            'M-PHYS' => 45,  // 10,800,000 / 240,000
            'M-MATH' => 46,  // 11,040,000 / 240,000
            'M-BIO' => 41,  //  9,840,000 / 240,000
            'M-CHEM' => 46,  // 11,040,000 / 240,000
            'M-CS' => 46,  // 11,040,000 / 240,000
            'MR-PHYS' => 46,  // 11,040,000 / 240,000
            'MR-CHEM' => 46,  // 11,040,000 / 240,000
            'MR-BIO' => 46,  // 11,040,000 / 240,000

            // ── PhD — full program credits (old year-2+ + old year-1) ──
            'D-PHYS' => 38,  // 22,800,000 / 600,000
            'D-BIO' => 40,  // 24,000,000 / 600,000
        ];

        // Legacy year-1 units for master/phd, used here only to reconstruct
        // full program credits before the configurable 60/40 split is applied.
        // Principle: year1 = 60% of total program credits, year2+ = 40%.
        // Formula: legacy_year1_units = year1_rate_from_excel / price_per_unit
        //   master (240,000/unit): M-PHYS=66, M-MATH=69, M-BIO=62, M-CHEM=69, M-CS=69 …
        //   phd    (600,000/unit): D-PHYS=57, D-BIO=60
        $year1CreditUnits = [
            'M-PHYS' => 66,    // 15,840,000 / 240,000
            'M-MATH' => 69,    // 16,560,000 / 240,000
            'M-BIO' => 62,    // 14,760,000 / 240,000 = 61.5 → ປັດຂຶ້ນ 62
            'M-CHEM' => 69,    // 16,560,000 / 240,000
            'M-CS' => 69,    // 16,560,000 / 240,000
            'MR-PHYS' => 69,    // 16,560,000 / 240,000
            'MR-CHEM' => 69,    // 16,560,000 / 240,000
            'MR-BIO' => 69,    // 16,560,000 / 240,000
            'D-PHYS' => 57,    // 34,200,000 / 600,000
            'D-BIO' => 60,    // 36,000,000 / 600,000
        ];

        $programIds = DegreeProgram::whereIn('code', array_keys($credits))
            ->pluck('id', 'code');

        $now = now();
        foreach ($credits as $code => $units) {
            $programId = $programIds[$code] ?? null;
            if (! $programId) {
                continue;
            }

            $totalUnits = $units + ($year1CreditUnits[$code] ?? 0);

            CourseCreditSetting::updateOrCreate(
                ['degree_program_id' => $programId],
                [
                    'course_credit_unit' => $totalUnits,
                    'start_year' => 2026,
                    'updated_at' => $now,
                ]
            );
        }
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
