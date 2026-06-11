<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows') || Schema::hasColumn('expense_subsection_default_rows', 'chart_of_account_id')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->unsignedInteger('chart_of_account_id')->nullable()->after('reference');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows') || ! Schema::hasColumn('expense_subsection_default_rows', 'chart_of_account_id')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->dropForeign(['chart_of_account_id']);
            $table->dropColumn('chart_of_account_id');
        });
    }
};
