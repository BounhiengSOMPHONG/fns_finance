<?php

namespace Database\Seeders;

use App\Models\CourseCreditSetting;
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
            ['B-CS-Y1',   'ວິທະຍາສາດຄອມ ປີ 1'],
            ['B-PD-Y1',   'ພັດທະນາໂປຣແກຣມ ປີ 1'],
            ['B-WD-Y1',   'ພັດທະນາເວບໄຊ້ ປີ 1'],
            ['B-CSC-Y1',  'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມ ປີ 1'],
            ['B-MAA-Y1',  'ຄະນິດສາດນໍາໃຊ້ ປີ 1'],
            ['B-MAE-Y1',  'ຄະນິດສາດສໍາຫຼັບເສດຖະສາດ ປີ 1'],
            ['B-STAT-Y1', 'ຄະນິດສາດສະຖິຕິ ປີ 1'],
            ['B-BIO-Y1',  'ຊີວະທົ່ວໄປ ປີ 1'],
            ['B-BT-Y1',   'ເທັກໂນໂລຍີ່ຊີວະພາບ ປີ 1'],
            ['B-CHEM-Y1', 'ເຄມີທົ່ວໄປ ປີ 1'],
            ['B-ECHE-Y1', 'ເຄມີສິ່ງແວດລ້ອມ ປີ 1'],
            ['B-PHYS-Y1', 'ຟີຊິກທົ່ວໄປ ປີ 1'],
            ['B-GPHY-Y1', 'ທໍລະນີຟີຊິກ ປີ 1'],
            ['B-MATS-Y1', 'ວັດສະດຸສາດ ປີ 1'],
            ['B-NPHY-Y1', 'ຟິຊິກນິວເຄຣຍ ປີ 1'],
        ];

        foreach ($year1Programs as [$code, $name]) {
            DegreeProgram::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'level' => 'bachelor', 'study_year' => 1, 'is_active' => true]
            );
        }

        // ── 3. Seed course credit units ──────────────────────────────────
        // Formula verified: fee_per_student (from Planning 2026.xls) / credit_unit_price = credit_units
        // Bachelor price = 35,000 | Master price = 240,000 | PhD price = 600,000
        $credits = [
            // ── Year 1 (section 1.3) ──
            'B-CS-Y1'   => 37,  // 1,295,000 / 35,000
            'B-PD-Y1'   => 38,  // 1,330,000 / 35,000
            'B-WD-Y1'   => 38,  // 1,330,000 / 35,000
            'B-CSC-Y1'  => 37,  // 1,295,000 / 35,000
            'B-MAA-Y1'  => 37,  // 1,295,000 / 35,000
            'B-MAE-Y1'  => 36,  // 1,260,000 / 35,000
            'B-STAT-Y1' => 36,  // 1,260,000 / 35,000
            'B-BIO-Y1'  => 37,  // 1,295,000 / 35,000
            'B-BT-Y1'   => 37,  // 1,295,000 / 35,000
            'B-CHEM-Y1' => 39,  // 1,365,000 / 35,000
            'B-ECHE-Y1' => 39,  // 1,365,000 / 35,000
            'B-PHYS-Y1' => 35,  // 1,225,000 / 35,000
            'B-GPHY-Y1' => 35,  // 1,225,000 / 35,000
            'B-MATS-Y1' => 35,  // 1,225,000 / 35,000
            'B-NPHY-Y1' => 35,  // 1,225,000 / 35,000

            // ── Year 2 (section 1.1) ──
            'B-CS-Y2'   => 37,  // 1,295,000 / 35,000
            'B-PD-Y2'   => 37,  // 1,295,000 / 35,000
            'B-WD-Y2'   => 37,  // 1,295,000 / 35,000
            'B-CSC-Y2'  => 27,  //   945,000 / 35,000
            'B-MAA-Y2'  => 37,  // 1,295,000 / 35,000
            'B-MAE-Y2'  => 37,  // 1,295,000 / 35,000
            'B-STAT-Y2' => 37,  // 1,295,000 / 35,000
            'B-BIO-Y2'  => 31,  // 1,085,000 / 35,000
            'B-BT-Y2'   => 31,  // 1,085,000 / 35,000
            'B-CHEM-Y2' => 35,  // 1,225,000 / 35,000
            'B-ECHE-Y2' => 35,  // 1,225,000 / 35,000
            'B-PHYS-Y2' => 36,  // 1,260,000 / 35,000
            'B-GPHY-Y2' => 36,  // 1,260,000 / 35,000
            'B-MATS-Y2' => 37,  // 1,295,000 / 35,000
            'B-NPHY-Y2' => 36,  // 1,260,000 / 35,000

            // ── Year 3 (section 1.1) ──
            'B-CS-Y3'   => 33,  // 1,155,000 / 35,000
            'B-PD-Y3'   => 38,  // 1,330,000 / 35,000
            'B-WD-Y3'   => 42,  // 1,470,000 / 35,000
            'B-MATH-Y3' => 39,  // 1,365,000 / 35,000
            'B-MAE-Y3'  => 39,  // 1,365,000 / 35,000
            'B-STAT-Y3' => 37,  // 1,295,000 / 35,000
            'B-BIO-Y3'  => 36,  // 1,260,000 / 35,000
            'B-BT-Y3'   => 33,  // 1,155,000 / 35,000
            'B-CHEM-Y3' => 35,  // 1,225,000 / 35,000
            'B-ECHE-Y3' => 36,  // 1,260,000 / 35,000
            'B-PHYS-Y3' => 34,  // 1,190,000 / 35,000
            'B-GPHY-Y3' => 36,  // 1,260,000 / 35,000
            'B-MATS-Y3' => 36,  // 1,260,000 / 35,000
            'B-NPHY-Y3' => 36,  // 1,260,000 / 35,000

            // ── Year 4 (section 1.1) ──
            'B-CS-Y4'   => 27,  //   945,000 / 35,000
            'B-PD-Y4'   => 30,  // 1,050,000 / 35,000
            'B-WD-Y4'   => 27,  //   945,000 / 35,000
            'B-MATH-Y4' => 24,  //   840,000 / 35,000
            'B-MAE-Y4'  => 27,  //   945,000 / 35,000
            'B-STAT-Y4' => 27,  //   945,000 / 35,000
            'B-BIO-Y4'  => 25,  //   875,000 / 35,000
            'B-BT-Y4'   => 23,  //   805,000 / 35,000
            'B-CHEM-Y4' => 23,  //   805,000 / 35,000
            'B-ECHE-Y4' => 22,  //   770,000 / 35,000
            'B-PHYS-Y4' => 27,  //   945,000 / 35,000
            'B-GPHY-Y4' => 30,  // 1,050,000 / 35,000
            'B-MATS-Y4' => 27,  //   945,000 / 35,000
            'B-NPHY-Y4' => 28,  //   980,000 / 35,000

            // ── Master — section 1.1 year-2+ rate / 240,000 ──
            'M-PHYS'    => 45,  // 10,800,000 / 240,000
            'M-MATH'    => 46,  // 11,040,000 / 240,000
            'M-BIO'     => 41,  //  9,840,000 / 240,000
            'M-CHEM'    => 46,  // 11,040,000 / 240,000
            'M-CS'      => 46,  // 11,040,000 / 240,000
            'MR-PHYS'   => 46,  // 11,040,000 / 240,000
            'MR-CHEM'   => 46,  // 11,040,000 / 240,000
            'MR-BIO'    => 46,  // 11,040,000 / 240,000

            // ── PhD — section 1.1 rate / 600,000 ──
            'D-PHYS'    => 38,  // 22,800,000 / 600,000
            'D-BIO'     => 40,  // 24,000,000 / 600,000
        ];

        // Year-1 credit units for master/phd (section 1.3).
        // Principle: year1 = 60% of total program credits, year2+ = 40%.
        // So year1_credit_unit = year2+_credit_unit × 1.5  (60/40 ratio).
        // Formula: year1_credit_unit = year1_rate_from_excel / price_per_unit
        //   master (240,000/unit): M-PHYS=66, M-MATH=69, M-BIO=62, M-CHEM=69, M-CS=69 …
        //   phd    (600,000/unit): D-PHYS=57, D-BIO=60
        $year1CreditUnits = [
            'M-PHYS'  => 66,    // 15,840,000 / 240,000
            'M-MATH'  => 69,    // 16,560,000 / 240,000
            'M-BIO'   => 62,    // 14,760,000 / 240,000 = 61.5 → ປັດຂຶ້ນ 62
            'M-CHEM'  => 69,    // 16,560,000 / 240,000
            'M-CS'    => 69,    // 16,560,000 / 240,000
            'MR-PHYS' => 69,    // 16,560,000 / 240,000
            'MR-CHEM' => 69,    // 16,560,000 / 240,000
            'MR-BIO'  => 69,    // 16,560,000 / 240,000
            'D-PHYS'  => 57,    // 34,200,000 / 600,000
            'D-BIO'   => 60,    // 36,000,000 / 600,000
        ];

        $programIds = DegreeProgram::whereIn('code', array_keys($credits))
            ->pluck('id', 'code');

        $now = now();
        foreach ($credits as $code => $units) {
            $programId = $programIds[$code] ?? null;
            if (!$programId) continue;

            CourseCreditSetting::updateOrCreate(
                ['degree_program_id' => $programId],
                [
                    'course_credit_unit' => $units,
                    'year1_credit_unit'  => $year1CreditUnits[$code] ?? null,
                    'start_year'         => 2026,
                    'updated_at'         => $now,
                ]
            );
        }
    }
}
