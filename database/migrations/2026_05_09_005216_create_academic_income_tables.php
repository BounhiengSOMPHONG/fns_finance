<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            ['key' => 'price_per_credit',          'value' => '35000',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'teaching_rate_bachelor',     'value' => '0.40',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'teaching_rate_masters_phd',  'value' => '0.60',   'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('academic_income_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year')->unique();
            $table->enum('status', ['DRAFT', 'APPROVED'])->default('DRAFT');
            $table->integer('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('academic_income_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->foreign('plan_id')->references('id')->on('academic_income_plans')->onDelete('cascade');
            $table->char('section_code', 3);
            $table->integer('sort_order')->default(0);
            $table->string('item_name');
            $table->unsignedInteger('num_credits')->nullable();
            $table->decimal('rate_per_person', 15, 2)->nullable();
            $table->unsignedInteger('num_persons')->default(0);
            $table->decimal('nuol_percentage', 5, 4)->default(0.1700);
            $table->string('student_year', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_income_items');
        Schema::dropIfExists('academic_income_plans');
        Schema::dropIfExists('app_settings');
    }
};
