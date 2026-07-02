<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registration_fee_settings')) {
            return;
        }
        Schema::create('registration_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('section_type', ['year2_4', 'year1']);
            $table->string('gov_doc_id', 255)->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_fee_settings');
    }
};
