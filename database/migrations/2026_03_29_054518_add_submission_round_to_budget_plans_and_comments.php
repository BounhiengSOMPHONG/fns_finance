<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('budget_plans', 'submission_round')) {
            Schema::table('budget_plans', function (Blueprint $table) {
                $table->integer('submission_round')->default(0);
            });
        }

        if (!Schema::hasColumn('budget_plan_comments', 'submission_round')) {
            Schema::table('budget_plan_comments', function (Blueprint $table) {
                $table->integer('submission_round')->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn('submission_round');
        });

        Schema::table('budget_plan_comments', function (Blueprint $table) {
            $table->dropColumn('submission_round');
        });
    }
};
