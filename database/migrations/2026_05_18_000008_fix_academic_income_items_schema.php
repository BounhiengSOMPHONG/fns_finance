<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_income_items', function (Blueprint $table) {
            // Make degree_program_id nullable (needed for sections 1.2 and 1.4)
            if (!Schema::hasColumn('academic_income_items', 'snap_nuol_pct')) {
                $table->decimal('snap_nuol_pct', 5, 4)->nullable()->after('snap_registration_fee_rate');
            }
        });

        // Alter degree_program_id to allow NULL (drop FK first, alter, re-add FK)
        $colInfo = DB::select("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'academic_income_items'
              AND COLUMN_NAME = 'degree_program_id'");

        if (!empty($colInfo) && $colInfo[0]->IS_NULLABLE === 'NO') {
            DB::statement('ALTER TABLE academic_income_items DROP FOREIGN KEY academic_income_items_degree_program_id_foreign');
            DB::statement('ALTER TABLE academic_income_items MODIFY degree_program_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE academic_income_items ADD CONSTRAINT academic_income_items_degree_program_id_foreign FOREIGN KEY (degree_program_id) REFERENCES degree_programs(id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        Schema::table('academic_income_items', function (Blueprint $table) {
            if (Schema::hasColumn('academic_income_items', 'snap_nuol_pct')) {
                $table->dropColumn('snap_nuol_pct');
            }
        });
    }
};
