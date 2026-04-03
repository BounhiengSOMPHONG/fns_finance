<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('budget_plan_comments', 'marked_at')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE budget_plan_comments ADD COLUMN marked_at DATETIME NULL DEFAULT NULL');
        }
        if (!Schema::hasColumn('budget_plan_comments', 'marked_by')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE budget_plan_comments ADD COLUMN marked_by INT NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('budget_plan_comments', function (Blueprint $table) {
            $table->dropColumn(['marked_at', 'marked_by']);
        });
    }
};
