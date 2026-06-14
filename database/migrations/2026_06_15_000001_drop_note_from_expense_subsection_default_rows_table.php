<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows') || ! Schema::hasColumn('expense_subsection_default_rows', 'note')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->dropColumn('note');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows') || Schema::hasColumn('expense_subsection_default_rows', 'note')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->text('note')->nullable()->after('reference');
        });
    }
};
