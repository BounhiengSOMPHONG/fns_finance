<?php

namespace Database\Seeders;

use App\Models\AcademicIncomeItem;
use App\Models\AcademicIncomePlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicIncomePlanSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::value('id') ?? 1;

        AcademicIncomePlan::where('fiscal_year', 2026)->delete();

        $plan = AcademicIncomePlan::create([
            'fiscal_year' => 2026,
            'notes' => 'ຂໍ້ມູນຈາກ Planning 2026.xls',
            'created_by' => $adminId,
        ]);

        // ─────────────────────────────────────────────────────────────────
        // Section 1.1 — ຄ່າໜ່ວຍກິດ ປີ 2–4 + ປ.ໂທ/ປ.ເອກ (60% first / 40% second)
        // ─────────────────────────────────────────────────────────────────
        $items11 = [
            // Year 2 — bachelor (nuol 17%)
            ['dp' => 1, 'n' => 60, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 2, 'n' => 70, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 3, 'n' => 60, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 4, 'n' => 8, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 5, 'n' => 0, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 6, 'n' => 6, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 7, 'n' => 0, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 8, 'n' => 0, 'rate' => 1085000, 'nuol' => 0.17],
            ['dp' => 9, 'n' => 0, 'rate' => 1085000, 'nuol' => 0.17],
            ['dp' => 10, 'n' => 7, 'rate' => 1225000, 'nuol' => 0.17],
            ['dp' => 11, 'n' => 6, 'rate' => 1225000, 'nuol' => 0.17],
            ['dp' => 12, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 13, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 14, 'n' => 0, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 15, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            // Year 3 — bachelor (nuol 17%)
            ['dp' => 16, 'n' => 60, 'rate' => 1155000, 'nuol' => 0.17],
            ['dp' => 17, 'n' => 35, 'rate' => 1330000, 'nuol' => 0.17],
            ['dp' => 18, 'n' => 45, 'rate' => 1470000, 'nuol' => 0.17],
            ['dp' => 19, 'n' => 1, 'rate' => 1365000, 'nuol' => 0.17],
            ['dp' => 20, 'n' => 12, 'rate' => 1365000, 'nuol' => 0.17],
            ['dp' => 21, 'n' => 0, 'rate' => 1295000, 'nuol' => 0.17],
            ['dp' => 22, 'n' => 1, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 23, 'n' => 0, 'rate' => 1155000, 'nuol' => 0.17],
            ['dp' => 24, 'n' => 5, 'rate' => 1225000, 'nuol' => 0.17],
            ['dp' => 25, 'n' => 3, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 26, 'n' => 0, 'rate' => 1190000, 'nuol' => 0.17],
            ['dp' => 27, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 28, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            ['dp' => 29, 'n' => 0, 'rate' => 1260000, 'nuol' => 0.17],
            // Year 4 — bachelor (nuol 17%)
            ['dp' => 30, 'n' => 55, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 31, 'n' => 30, 'rate' => 1050000, 'nuol' => 0.17],
            ['dp' => 32, 'n' => 40, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 33, 'n' => 2, 'rate' => 840000, 'nuol' => 0.17],
            ['dp' => 34, 'n' => 21, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 35, 'n' => 11, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 36, 'n' => 3, 'rate' => 875000, 'nuol' => 0.17],
            ['dp' => 37, 'n' => 3, 'rate' => 805000, 'nuol' => 0.17],
            ['dp' => 38, 'n' => 16, 'rate' => 805000, 'nuol' => 0.17],
            ['dp' => 39, 'n' => 5, 'rate' => 770000, 'nuol' => 0.17],
            ['dp' => 40, 'n' => 2, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 41, 'n' => 0, 'rate' => 1050000, 'nuol' => 0.17],
            ['dp' => 42, 'n' => 0, 'rate' => 945000, 'nuol' => 0.17],
            ['dp' => 43, 'n' => 1, 'rate' => 980000, 'nuol' => 0.17],
            // Master/PhD year 2+ (nuol 10%)
            ['dp' => 44, 'n' => 0, 'rate' => 10800000, 'nuol' => 0.10],
            ['dp' => 45, 'n' => 0, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 46, 'n' => 0, 'rate' => 9840000, 'nuol' => 0.10],
            ['dp' => 47, 'n' => 0, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 48, 'n' => 3, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 49, 'n' => 3, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 50, 'n' => 7, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 51, 'n' => 3, 'rate' => 11040000, 'nuol' => 0.10],
            ['dp' => 52, 'n' => 0, 'rate' => 22800000, 'nuol' => 0.10],
            ['dp' => 53, 'n' => 0, 'rate' => 24000000, 'nuol' => 0.10],
        ];

        foreach ($items11 as $r) {
            $total = round($r['n'] * $r['rate'] * (1 - $r['nuol']), 2);
            AcademicIncomeItem::create([
                'plan_id' => $plan->id,
                'section_code' => '1.1',
                'degree_program_id' => $r['dp'],
                'student_count' => $r['n'],
                'total_income' => $total,
            ]);
        }

        // ─────────────────────────────────────────────────────────────────
        // Section 1.2 — ຄ່າລົງທະບຽນ ປີ 2–4 (aggregate, 100% first)
        // 705 students × 210,000 ; faculty total per Excel = 131,482,500
        // ─────────────────────────────────────────────────────────────────
        AcademicIncomeItem::create([
            'plan_id' => $plan->id,
            'section_code' => '1.2',
            'degree_program_id' => null,
            'student_count' => 705,
            'total_income' => 131482500,
        ]);

        // ─────────────────────────────────────────────────────────────────
        // Section 1.3 — ຄ່າໜ່ວຍກິດ ປີ 1 (60% first / 0% second)
        // ─────────────────────────────────────────────────────────────────
        // Bachelor year 1: rate = credit_unit × 35,000 (stored as 1 × full_rate for simplicity)
        $items13_bach = [
            ['dp' => 55, 'n' => 60, 'rate' => 1295000],
            ['dp' => 56, 'n' => 60, 'rate' => 1330000],
            ['dp' => 57, 'n' => 60, 'rate' => 1330000],
            ['dp' => 58, 'n' => 10, 'rate' => 1295000],
            ['dp' => 59, 'n' => 10, 'rate' => 1295000],
            ['dp' => 60, 'n' => 20, 'rate' => 1260000],
            ['dp' => 61, 'n' => 0, 'rate' => 1260000],
            ['dp' => 62, 'n' => 5, 'rate' => 1295000],
            ['dp' => 63, 'n' => 5, 'rate' => 1295000],
            ['dp' => 64, 'n' => 10, 'rate' => 1365000],
            ['dp' => 65, 'n' => 10, 'rate' => 1365000],
            ['dp' => 66, 'n' => 1, 'rate' => 1225000],
            ['dp' => 67, 'n' => 1, 'rate' => 1225000],
            ['dp' => 68, 'n' => 1, 'rate' => 1225000],
            ['dp' => 69, 'n' => 1, 'rate' => 1225000],
        ];

        foreach ($items13_bach as $r) {
            $total = round($r['n'] * $r['rate'] * (1 - 0.17), 2);
            AcademicIncomeItem::create([
                'plan_id' => $plan->id,
                'section_code' => '1.3',
                'degree_program_id' => $r['dp'],
                'student_count' => $r['n'],
                'total_income' => $total,
            ]);
        }

        // Master/PhD year 1: calculated from 60% of the total program credits.
        // The price per unit now lives in academic income settings.
        // gross = year1 credit share × price/unit × count  (same formula as sec 1.1 yr2+)
        $items13_master = [
            // ['dp', 'n', 'year1_cu', 'price_per_unit']
            ['dp' => 44, 'n' => 4, 'cu' => 66,   'price' => 240000],  // M-PHYS:  66×240k=15,840,000
            ['dp' => 45, 'n' => 4, 'cu' => 69,   'price' => 240000],  // M-MATH:  69×240k=16,560,000
            ['dp' => 46, 'n' => 0, 'cu' => 61.5, 'price' => 240000],  // M-BIO: 61.5×240k=14,760,000
            ['dp' => 47, 'n' => 0, 'cu' => 69,   'price' => 240000],  // M-CHEM:  69×240k=16,560,000
            ['dp' => 48, 'n' => 5, 'cu' => 69,   'price' => 240000],  // M-CS:    69×240k=16,560,000
            ['dp' => 49, 'n' => 4, 'cu' => 69,   'price' => 240000],  // MR-PHYS: 69×240k=16,560,000
            ['dp' => 50, 'n' => 5, 'cu' => 69,   'price' => 240000],  // MR-CHEM: 69×240k=16,560,000
            ['dp' => 51, 'n' => 4, 'cu' => 69,   'price' => 240000],  // MR-BIO:  69×240k=16,560,000
            ['dp' => 52, 'n' => 0, 'cu' => 57,   'price' => 600000],  // D-PHYS:  57×600k=34,200,000
            ['dp' => 53, 'n' => 0, 'cu' => 60,   'price' => 600000],  // D-BIO:   60×600k=36,000,000
        ];

        foreach ($items13_master as $r) {
            $total = round($r['n'] * $r['cu'] * $r['price'] * (1 - 0.10), 2);
            AcademicIncomeItem::create([
                'plan_id' => $plan->id,
                'section_code' => '1.3',
                'degree_program_id' => $r['dp'],
                'student_count' => $r['n'],
                'total_income' => $total,
            ]);
        }

        // ─────────────────────────────────────────────────────────────────
        // Section 1.4 — ຄ່າລົງທະບຽນ ປີ 1 (aggregate, 100% first)
        // 324 students × 210,000 ; faculty total per Excel = 51,516,000
        // ─────────────────────────────────────────────────────────────────
        AcademicIncomeItem::create([
            'plan_id' => $plan->id,
            'section_code' => '1.4',
            'degree_program_id' => null,
            'student_count' => 324,
            'total_income' => 51516000,
        ]);

        // ─────────────────────────────────────────────────────────────────
        // Section 4 — ຄ່າບູລະນະຫ້ອງທົດລອງຄອມພິວເຕີ
        // 1,029 students (324 yr1 + 705 yr2-4) × 50,000 = 51,450,000, no NUOL
        // ─────────────────────────────────────────────────────────────────
        AcademicIncomeItem::create([
            'plan_id' => $plan->id,
            'section_code' => '4',
            'degree_program_id' => null,
            'student_count' => 1029,
            'total_income' => 51450000,
        ]);

        // ─────────────────────────────────────────────────────────────────
        // Section 5 — ຄ່າບຳລຸງອຸປະກອນຫ້ອງທົດລອງ
        // 1,029 students × 20,000 = 20,580,000, no NUOL
        // ─────────────────────────────────────────────────────────────────
        AcademicIncomeItem::create([
            'plan_id' => $plan->id,
            'section_code' => '5',
            'degree_program_id' => null,
            'student_count' => 1029,
            'total_income' => 20580000,
        ]);
    }
}
