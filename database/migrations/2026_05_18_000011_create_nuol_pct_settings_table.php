<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nuol_pct_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['bachelor', 'master_phd']);
            $table->decimal('percentage', 5, 4);
            $table->string('gov_doc_id', 255)->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });

        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->dropColumn(['nuol_pct_bachelor', 'nuol_pct_master_phd']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuol_pct_settings');

        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->decimal('nuol_pct_bachelor',   5, 4)->default(0.1700)->after('status');
            $table->decimal('nuol_pct_master_phd', 5, 4)->default(0.1000)->after('nuol_pct_bachelor');
        });
    }
};
