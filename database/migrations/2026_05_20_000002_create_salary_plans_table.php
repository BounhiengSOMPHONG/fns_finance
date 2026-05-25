<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('fiscal_year', 10);
            $table->unsignedTinyInteger('month');
            $table->string('status', 20)->default('DRAFT');
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['fiscal_year', 'month']);
            $table->index('fiscal_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_plans');
    }
};
