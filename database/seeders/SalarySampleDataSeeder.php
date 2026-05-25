<?php

namespace Database\Seeders;

use App\Models\SalaryBudgetCode;
use App\Models\SalaryEntry;
use App\Models\SalaryPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the sample payroll data from the official PDF document:
 * "ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ ຕາມສາລະບານງົບປະມານ"
 * ເດືອນ 01/2026 (ງວດທີ 1 ສົກປີ 2026)
 * ມະຫາວິທະຍາໄລແຫ່ງຊາດ — ຄະນະວິທະຍາສາດທຳມະຊາດ
 */
class SalarySampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Load all leaf codes keyed by their full dot-path ─────────
        $codeMap = $this->buildCodeMap();

        // ── 2. Create (or reuse) the January 2026 plan ──────────────────
        $plan = SalaryPlan::firstOrCreate(
            ['fiscal_year' => '2026', 'month' => 1],
            ['status' => 'DRAFT', 'notes' => 'ຂໍ້ມູນຕົວຢ່າງ ເດືອນ 01/2026', 'created_by' => 1]
        );

        // ── 3. Rows: [code_path, persons, atm, cash, annual_override] ───
        //   annual_override is only set for annual_mode='direct' entries
        $rows = [
            // ── 60.10 ເງິນເດືອນພື້ນຖານ ──────────────────────────────
            ['60.10.01', 98,  249_716_250,   0,          null],
            ['60.10.02', 53,    1_015_850,   0,          null],
            ['60.10.03',  0,            0,   0,          null],
            ['60.10.05',  3,   12_220_515,   0,          null],
            ['60.10.06',  0,            0,   0,          null],

            // ── 60.20 ເງິນອຸດໜູນປົກກະຕິ ──────────────────────────────
            ['60.20.01', 79,  51_812_355,    0,          null],
            ['60.20.03', 96,  23_768_430,    0,          null],
            ['60.20.04', 104,  9_385_000,    0,          null],
            ['60.20.05', 37,   1_102_304,    0,          null],
            ['60.20.07', 12,           0,    7_500_000,  null],  // cash only
            ['60.20.08',  0,           0,    0,          null],

            // ── 61.20 ເງິນອຸດໜູນຄອບຄົວ ───────────────────────────────
            ['61.20.1',  78,   2_904_720,    0,          null],
            ['61.20.2',  14,     411_600,    0,          null],

            // ── 61.30 ກ່ອນຮັບບຳນານ ────────────────────────────────────
            ['61.30.02',  6,           0,    0,          null],

            // ── 61.40 ວຽກເພີ່ມ ────────────────────────────────────────
            ['61.40.01', 167,          0,    6_680_000,  null],         // cash × 12
            ['61.40.03',   6,          0,    0,          500_000_000],  // direct annual
            ['61.40.04',  15,          0,    0,          300_000_000],  // direct annual
            ['61.40.05',   0,          0,    0,          658_200_000],  // direct annual
            ['61.40.06',  23,          0,    4_820_000,  null],         // cash × 12

            // ── 61.50.01 ນັກຮຽນພາຍໃນ ─────────────────────────────────
            ['61.50.01.03', 280,       0,   56_000_000,  null],        // cash × 12
            ['61.50.01.04',   0,       0,            0,  null],

            // ── 61.50.03 ຝຶກງານ (one-time × 1) ───────────────────────
            ['61.50.03.01', 280,       0,  100_800_000,  null],        // cash × 1
            ['61.50.03.02', 280,       0,   11_200_000,  null],        // cash × 1

            // ── 61.80 ສັງຄົມ ──────────────────────────────────────────
            ['61.80.02',  115,         0,    5_750_000,  null],        // cash × 12
        ];

        // ── 4. Upsert each entry ─────────────────────────────────────
        foreach ($rows as [$path, $persons, $atm, $cash, $annualOverride]) {
            $codeId = $codeMap[$path] ?? null;
            if (! $codeId) {
                $this->command->warn("Budget code not found: {$path}");
                continue;
            }

            $budgetCode  = SalaryBudgetCode::find($codeId);
            $monthlyTotal = (float) $atm + (float) $cash;

            $annual = match ($budgetCode->annual_mode) {
                'x12'    => $monthlyTotal * 12,
                'x1'     => $monthlyTotal,
                'direct' => (float) ($annualOverride ?? 0),
                default  => $monthlyTotal * 12,
            };

            SalaryEntry::updateOrCreate(
                ['plan_id' => $plan->id, 'budget_code_id' => $codeId],
                [
                    'person_count'  => $persons,
                    'atm_amount'    => $atm,
                    'cash_amount'   => $cash,
                    'monthly_total' => $monthlyTotal,
                    'annual_amount' => $annual,
                    'remark'        => null,
                ]
            );
        }

        $grandMonthly = $plan->entries()->sum('monthly_total');
        $grandAnnual  = $plan->entries()->sum('annual_amount');

        $this->command->info(sprintf(
            'Seeded plan #%d (ເດືອນ 01/2026): monthly=%s ກີບ, annual=%s ກີບ',
            $plan->id,
            number_format($grandMonthly, 0),
            number_format($grandAnnual, 0)
        ));
    }

    /**
     * Returns ['60.10.01' => id, '60.20.03' => id, ...] for every leaf node.
     */
    private function buildCodeMap(): array
    {
        $map = [];

        SalaryBudgetCode::with('parent.parent.parent')
            ->where('is_leaf', true)
            ->get()
            ->each(function (SalaryBudgetCode $node) use (&$map): void {
                $path = collect([
                    $node->parent?->parent?->parent?->code,
                    $node->parent?->parent?->code,
                    $node->parent?->code,
                    $node->code,
                ])->filter()->join('.');

                $map[$path] = $node->id;
            });

        return $map;
    }
}
