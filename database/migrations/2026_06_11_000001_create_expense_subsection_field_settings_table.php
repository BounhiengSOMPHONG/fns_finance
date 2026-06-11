<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_subsection_field_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subsection_id')->constrained('expense_subsections')->cascadeOnDelete();
            $table->foreignId('pattern_field_id')->constrained('expense_pattern_fields')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->unsignedInteger('display_order')->nullable();
            $table->boolean('is_required')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('default_value')->nullable();
            $table->timestamps();

            $table->unique(['subsection_id', 'pattern_field_id'], 'subsection_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_subsection_field_settings');
    }
};
