<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Split the combined "master_phd" NUOL-% level into separate "master" and "phd"
     * rows so each can be set independently — matching credit_unit_price_settings,
     * whose level enum is ('bachelor','master','phd').
     */
    public function up(): void
    {
        // widen the enum so master/phd become valid values during conversion
        DB::statement("ALTER TABLE nuol_pct_settings MODIFY level ENUM('bachelor','master','phd','master_phd') NOT NULL");

        foreach (DB::table('nuol_pct_settings')->where('level', 'master_phd')->get() as $row) {
            // re-label the existing combined row as "master"
            DB::table('nuol_pct_settings')->where('id', $row->id)
                ->update(['level' => 'master', 'updated_at' => now()]);

            // create a matching "phd" row (same value) unless one already exists
            $exists = DB::table('nuol_pct_settings')
                ->where('level', 'phd')->where('start_year', $row->start_year)->exists();

            if (! $exists) {
                DB::table('nuol_pct_settings')->insert([
                    'level'      => 'phd',
                    'percentage' => $row->percentage,
                    'gov_doc_id' => $row->gov_doc_id,
                    'start_year' => $row->start_year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // finalize enum to match credit_unit_price_settings
        DB::statement("ALTER TABLE nuol_pct_settings MODIFY level ENUM('bachelor','master','phd') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE nuol_pct_settings MODIFY level ENUM('bachelor','master','phd','master_phd') NOT NULL");

        // merge back: "master" → "master_phd", drop "phd"
        DB::table('nuol_pct_settings')->where('level', 'master')
            ->update(['level' => 'master_phd', 'updated_at' => now()]);
        DB::table('nuol_pct_settings')->where('level', 'phd')->delete();

        DB::statement("ALTER TABLE nuol_pct_settings MODIFY level ENUM('bachelor','master_phd') NOT NULL");
    }
};
