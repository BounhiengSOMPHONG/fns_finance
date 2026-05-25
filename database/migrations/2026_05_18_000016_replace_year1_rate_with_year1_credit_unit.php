<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_credit_settings', function (Blueprint $table) {
            $table->dropColumn('year1_rate');
            // decimal(8,2) to handle non-integer units (e.g. M-BIO = 61.5)
            $table->decimal('year1_credit_unit', 8, 2)->nullable()->after('course_credit_unit');
        });
    }

    public function down(): void
    {
        Schema::table('course_credit_settings', function (Blueprint $table) {
            $table->dropColumn('year1_credit_unit');
            $table->decimal('year1_rate', 15, 2)->nullable()->after('course_credit_unit');
        });
    }
};
