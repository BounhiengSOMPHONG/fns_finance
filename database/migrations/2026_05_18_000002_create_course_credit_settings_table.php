<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_credit_settings')) return;
        Schema::create('course_credit_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('degree_program_id')->constrained('degree_programs')->cascadeOnDelete();
            $table->unsignedSmallInteger('course_credit_unit');
            $table->string('gov_doc_id', 255)->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_credit_settings');
    }
};
