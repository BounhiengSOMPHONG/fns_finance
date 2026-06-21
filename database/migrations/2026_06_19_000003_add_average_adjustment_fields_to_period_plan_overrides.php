<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->decimal('average_increase_amount', 18, 2)->default(0)->after('period_2_amount');
            $table->decimal('average_decrease_amount', 18, 2)->default(0)->after('average_increase_amount');
        });
    }

    public function down(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->dropColumn([
                'average_increase_amount',
                'average_decrease_amount',
            ]);
        });
    }
};
