<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_income_items')) return;
        Schema::create('academic_income_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('academic_income_plans')->cascadeOnDelete();
            $table->string('section_code', 5);
            $table->foreignId('degree_program_id')->nullable()->constrained('degree_programs')->cascadeOnDelete();
            $table->unsignedInteger('student_count')->default(0);
            $table->decimal('snap_credit_unit_price', 15, 2)->nullable();
            $table->unsignedSmallInteger('snap_course_credit_unit')->nullable();
            $table->decimal('snap_registration_fee_rate', 15, 2)->nullable();
            $table->decimal('snap_nuol_pct', 5, 4)->nullable();
            $table->decimal('total_income', 18, 2)->default(0);
            $table->decimal('first_payment_amount', 18, 2)->default(0);
            $table->decimal('second_payment_amount', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'section_code', 'degree_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_income_items');
    }
};
