<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('expense_calculation_rules');
        Schema::dropIfExists('planning_year_field_settings');
    }

    public function down(): void
    {
        if (! Schema::hasTable('planning_year_field_settings')) {
            Schema::create('planning_year_field_settings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('planning_year_id')->constrained('planning_years')->cascadeOnDelete();
                $table->foreignId('pattern_field_id')->constrained('expense_pattern_fields')->cascadeOnDelete();
                $table->string('label');
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('default_value')->nullable();
                $table->timestamps();

                $table->unique(['planning_year_id', 'pattern_field_id'], 'planning_year_field_settings_year_field_unique');
            });
        }

        if (! Schema::hasTable('expense_calculation_rules')) {
            Schema::create('expense_calculation_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('planning_year_id')->constrained('planning_years')->cascadeOnDelete();
                $table->foreignId('pattern_id')->constrained('expense_patterns')->cascadeOnDelete();
                $table->foreignId('section_id')->nullable()->constrained('expense_sections')->cascadeOnDelete();
                $table->foreignId('subsection_id')->nullable()->constrained('expense_subsections')->cascadeOnDelete();
                $table->string('target_field_key', 50);
                $table->string('formula');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }
};
