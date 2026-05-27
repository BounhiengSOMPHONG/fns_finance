<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('expense_plans')->cascadeOnDelete();
            $table->date('entry_date')->nullable();
            $table->string('ref_code', 30)->nullable();
            // chart_of_accounts.id is INT UNSIGNED (not the usual bigint) — match it
            // exactly so the FK is compatible (MySQL error 3780 otherwise).
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->string('main_cat')->nullable();
            $table->string('main_item')->nullable();
            $table->string('sub_item');
            $table->decimal('rate1', 18, 2)->default(0);
            $table->decimal('rate2', 18, 2)->default(0);
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('period', 10, 2)->default(1);
            $table->decimal('frequency', 10, 2)->default(1);
            $table->decimal('add_on', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->text('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['plan_id', 'main_cat', 'ref_code', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_entries');
    }
};
