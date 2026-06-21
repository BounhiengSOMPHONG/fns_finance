<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_years', function (Blueprint $table): void {
            $table->timestamp('period_1_2_saved_at')->nullable()->after('review_closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('planning_years', function (Blueprint $table): void {
            $table->dropColumn('period_1_2_saved_at');
        });
    }
};
