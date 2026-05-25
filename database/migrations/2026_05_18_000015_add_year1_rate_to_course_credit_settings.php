<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_credit_settings', function (Blueprint $table) {
            $table->decimal('year1_rate', 15, 2)->nullable()->after('course_credit_unit');
        });
    }

    public function down(): void
    {
        Schema::table('course_credit_settings', function (Blueprint $table) {
            $table->dropColumn('year1_rate');
        });
    }
};
