<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\ExpenseEntry;
use App\Models\ExpensePlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpensePlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ExpenseEntry::truncate();
        ExpensePlan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $adminId = DB::table('users')->value('id');

        $plan = ExpensePlan::create([
            'fiscal_year' => 2026,
            'status'      => 'DRAFT',
            'notes'       => 'ຕົວຢ່າງຂໍ້ມູນ flat entries',
            'created_by'  => $adminId,
        ]);

        // Sample flat entries: [ref_code, account_code, sub_item, rate1, rate2, qty, period, frequency, add_on]
        $samples = [
            ['2.1.1', '60100101', 'ເງິນເດືອນພະນັກງານສົມບູນ',       5000000, 0, 12, 1, 1, 0],
            ['2.1.1', '60100102', 'ເງິນເດືອນພະນັກງານຝຶກງານ',       2500000, 0, 12, 1, 1, 0],
            ['2.1.2', '60100200', 'ເລື່ອນຊັ້ນ ແລະ ເລື່ອນຂັ້ນ',     1200000, 0,  4, 1, 1, 0],
            ['2.4.1', '60200100', 'ອຸດໜູນຕຳແໜ່ງງານ',                800000, 0, 12, 1, 1, 0],
            ['2.1.4', '60100300', 'ພະນັກງານເຂົ້າການໃໝ່',           3000000, 0,  1, 1, 1, 500000],
        ];

        foreach ($samples as $s) {
            [$ref, $code, $sub, $r1, $r2, $qty, $period, $freq, $addon] = $s;
            $coa = ChartOfAccount::where('account_code', $code)->first();

            ExpenseEntry::create([
                'plan_id'             => $plan->id,
                'ref_code'            => $ref,
                'chart_of_account_id' => $coa?->id,
                'main_cat'            => $coa?->mainCat(),
                'main_item'           => $coa?->mainItem(),
                'sub_item'            => $sub,
                'rate1'               => $r1,
                'rate2'               => $r2,
                'qty'                 => $qty,
                'period'              => $period,
                'frequency'           => $freq,
                'add_on'              => $addon,
            ]);
        }
    }
}
