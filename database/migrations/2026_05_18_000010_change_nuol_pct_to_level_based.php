<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->decimal('nuol_pct_bachelor',   5, 4)->default(0.1700)->after('status');
            $table->decimal('nuol_pct_master_phd', 5, 4)->default(0.1000)->after('nuol_pct_bachelor');
        });

        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->dropColumn(['nuol_pct_1_1', 'nuol_pct_1_2', 'nuol_pct_1_3', 'nuol_pct_1_4']);
        });
    }

    public function down(): void
    {
        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->decimal('nuol_pct_1_1', 5, 4)->default(0.1700)->after('status');
            $table->decimal('nuol_pct_1_2', 5, 4)->default(0.1700)->after('nuol_pct_1_1');
            $table->decimal('nuol_pct_1_3', 5, 4)->default(0.1700)->after('nuol_pct_1_2');
            $table->decimal('nuol_pct_1_4', 5, 4)->default(0.1700)->after('nuol_pct_1_3');
        });

        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->dropColumn(['nuol_pct_bachelor', 'nuol_pct_master_phd']);
        });
    }
};
