<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('income_rate_settings');

        Schema::create('income_rate_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();   // e.g. item3_rate, item4_rate
            $table->string('label', 255);           // display name
            $table->decimal('rate', 15, 2)->default(0);
            $table->timestamps();
        });

        // Seed the 4 default keys so they always exist
        DB::table('income_rate_settings')->insert([
            ['key' => 'item3_rate', 'label' => 'ອັດຕາ Item 3 (ຕໍ່ ນ/ສ)', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item4_rate', 'label' => 'ອັດຕາ Item 4 (ຕໍ່ ນ/ສ)', 'rate' => 50000, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item5_rate', 'label' => 'ອັດຕາ Item 5 (ຕໍ່ ນ/ສ)', 'rate' => 20000, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item6_rate', 'label' => 'ອັດຕາ Item 6 (ຕໍ່ ນ/ສ)', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('income_rate_settings');
    }
};
