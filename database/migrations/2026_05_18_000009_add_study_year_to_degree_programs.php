<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('degree_programs', 'study_year')) {
            return;
        }

        Schema::table('degree_programs', function (Blueprint $table) {
            $table->unsignedSmallInteger('study_year')->nullable()->after('level');
        });
    }

    public function down(): void
    {
        Schema::table('degree_programs', function (Blueprint $table) {
            $table->dropColumn('study_year');
        });
    }
};
