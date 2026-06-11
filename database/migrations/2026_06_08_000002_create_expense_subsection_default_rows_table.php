<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->id();
            $table->string('subsection_code', 30)->index();
            $table->string('item_name');
            $table->string('reference', 80)->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->json('default_values')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subsection_code', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_subsection_default_rows');
    }
};
