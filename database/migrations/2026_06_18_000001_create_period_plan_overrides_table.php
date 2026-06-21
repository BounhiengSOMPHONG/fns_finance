<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_plan_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planning_year_id')->constrained('planning_years')->cascadeOnDelete();
            $table->string('account_code', 30);
            $table->decimal('period_1_amount', 18, 2)->default(0);
            $table->decimal('period_2_amount', 18, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['planning_year_id', 'account_code'], 'period_plan_overrides_year_account_unique');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_plan_overrides');
    }
};
