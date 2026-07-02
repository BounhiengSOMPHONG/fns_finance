<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registration_fee_items')) {
            return;
        }
        Schema::create('registration_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_setting_id')->constrained('registration_fee_settings')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('name', 255);
            $table->decimal('amount', 15, 2);
            $table->decimal('nuol_pct', 5, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_fee_items');
    }
};
