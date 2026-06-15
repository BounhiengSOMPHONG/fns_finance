<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salary_entries') && Schema::hasColumn('salary_entries', 'remark')) {
            Schema::table('salary_entries', function (Blueprint $table): void {
                $table->dropColumn('remark');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('salary_entries') && ! Schema::hasColumn('salary_entries', 'remark')) {
            Schema::table('salary_entries', function (Blueprint $table): void {
                $table->string('remark', 255)->nullable()->after('annual_amount');
            });
        }
    }
};
