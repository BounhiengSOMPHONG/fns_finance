<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('expense_plan_legacy_rows');
    }

    public function down(): void
    {
        Schema::create('expense_plan_legacy_rows', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('source_expense_plan_id')->unique();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('catalog_item_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->string('version', 50)->nullable();
            $table->string('plan_type', 100)->nullable();
            $table->string('item_name')->nullable();
            $table->string('plan_detail')->nullable();
            $table->text('detail')->nullable();
            $table->json('calculation_values')->nullable();
            $table->json('pattern_snapshot')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamps();
        });
    }
};
