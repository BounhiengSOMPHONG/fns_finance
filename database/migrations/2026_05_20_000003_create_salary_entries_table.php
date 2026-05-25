<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained('salary_plans')->cascadeOnDelete();
            $table->foreignId('budget_code_id')->constrained('salary_budget_codes')->cascadeOnDelete();
            $table->unsignedInteger('person_count')->default(0);
            $table->decimal('atm_amount', 15, 2)->default(0);
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('monthly_total', 15, 2)->default(0);
            $table->decimal('annual_amount', 15, 2)->default(0);
            $table->string('remark', 255)->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'budget_code_id']);
            $table->index('plan_id');
            $table->index('budget_code_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_entries');
    }
};
