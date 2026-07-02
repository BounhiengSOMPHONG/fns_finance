<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('academic_income_plans', 'status')) {
            return;
        }
        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('academic_income_plans', 'status')) {
            return;
        }
        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->enum('status', ['DRAFT', 'APPROVED'])->default('DRAFT')->after('fiscal_year');
        });
    }
};
