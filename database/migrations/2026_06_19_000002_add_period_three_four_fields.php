<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->decimal('requested_decrease_amount', 18, 2)->default(0)->after('period_2_amount');
            $table->decimal('requested_increase_amount', 18, 2)->default(0)->after('requested_decrease_amount');
            $table->decimal('period_3_amount', 18, 2)->default(0)->after('requested_increase_amount');
            $table->decimal('period_4_amount', 18, 2)->default(0)->after('period_3_amount');
        });

        Schema::table('planning_years', function (Blueprint $table): void {
            $table->timestamp('period_3_4_saved_at')->nullable()->after('period_1_2_saved_at');
        });
    }

    public function down(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->dropColumn([
                'requested_decrease_amount',
                'requested_increase_amount',
                'period_3_amount',
                'period_4_amount',
            ]);
        });

        Schema::table('planning_years', function (Blueprint $table): void {
            $table->dropColumn('period_3_4_saved_at');
        });
    }
};
