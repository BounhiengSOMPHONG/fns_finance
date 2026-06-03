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

            // chart_of_accounts.id is INT UNSIGNED — must match exactly. See db-migration-state-landmine.
            $table->unsignedInteger('chart_of_account_id');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->restrictOnDelete();

            $table->unsignedInteger('person_count')->default(0);
            $table->decimal('atm_amount', 15, 2)->default(0);
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('monthly_total', 15, 2)->default(0);
            $table->decimal('annual_amount', 15, 2)->default(0);
            $table->string('remark', 255)->nullable();
            $table->timestamps();

            $table->index('plan_id');
            $table->index('chart_of_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_entries');
    }
};
