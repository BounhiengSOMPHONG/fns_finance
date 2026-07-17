<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_plans') || ! Schema::hasColumn('expense_plans', 'status')) {
            return;
        }

        Schema::table('expense_plans', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_plans') || Schema::hasColumn('expense_plans', 'status')) {
            return;
        }

        Schema::table('expense_plans', function (Blueprint $table): void {
            $table->string('status', 20)->default('DRAFT')->after('fiscal_year');
        });
    }
};
