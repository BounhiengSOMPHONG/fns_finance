<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('degree_programs') || ! Schema::hasColumn('degree_programs', 'is_active')) {
            return;
        }

        Schema::table('degree_programs', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('degree_programs') || Schema::hasColumn('degree_programs', 'is_active')) {
            return;
        }

        Schema::table('degree_programs', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('study_year');
        });
    }
};
