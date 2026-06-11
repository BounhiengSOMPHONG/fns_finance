<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_sections', function (Blueprint $table): void {
            $table->decimal('summary_period_count', 10, 2)->default(12)->after('display_order');
        });

        Schema::table('expense_subsections', function (Blueprint $table): void {
            $table->decimal('summary_period_count', 10, 2)->default(12)->after('default_pattern_id');
        });
    }

    public function down(): void
    {
        Schema::table('expense_subsections', function (Blueprint $table): void {
            $table->dropColumn('summary_period_count');
        });

        Schema::table('expense_sections', function (Blueprint $table): void {
            $table->dropColumn('summary_period_count');
        });
    }
};
