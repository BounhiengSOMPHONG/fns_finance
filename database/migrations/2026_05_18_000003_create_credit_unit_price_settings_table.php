<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_unit_price_settings')) return;
        Schema::create('credit_unit_price_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['bachelor', 'master', 'phd']);
            $table->decimal('credit_unit_price', 15, 2);
            $table->string('gov_doc_id', 255)->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_unit_price_settings');
    }
};
