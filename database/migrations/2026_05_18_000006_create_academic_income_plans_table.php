<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_income_plans')) return;
        Schema::create('academic_income_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year')->unique();
            $table->enum('status', ['DRAFT', 'APPROVED'])->default('DRAFT');
            $table->decimal('nuol_pct_1_1', 5, 4)->default(0.1700);
            $table->decimal('nuol_pct_1_2', 5, 4)->default(0.1700);
            $table->decimal('nuol_pct_1_3', 5, 4)->default(0.1700);
            $table->decimal('nuol_pct_1_4', 5, 4)->default(0.1700);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_income_plans');
    }
};
