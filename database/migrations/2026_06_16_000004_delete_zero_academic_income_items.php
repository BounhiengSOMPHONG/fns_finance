<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        DB::table('academic_income_items')
            ->where('student_count', '<=', 0)
            ->delete();
    }

    public function down(): void
    {
        // Zero-count rows are intentionally not restored.
    }
};
