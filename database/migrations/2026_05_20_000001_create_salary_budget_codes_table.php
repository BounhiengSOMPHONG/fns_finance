<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_budget_codes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('salary_budget_codes')->nullOnDelete();
            $table->string('code', 10);
            $table->string('name');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_leaf')->default(false);
            // 'x12' = monthly × 12, 'x1' = monthly × 1 (one-time), 'direct' = annual entered directly
            $table->string('annual_mode', 10)->default('x12');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_budget_codes');
    }
};
