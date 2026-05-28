<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_entries', function (Blueprint $table) {
            $table->string('main_cat_code', 30)->nullable()->after('ref_code');
            $table->string('main_item_code', 30)->nullable()->after('main_cat_code');
        });
    }

    public function down(): void
    {
        Schema::table('expense_entries', function (Blueprint $table) {
            $table->dropColumn(['main_cat_code', 'main_item_code']);
        });
    }
};
