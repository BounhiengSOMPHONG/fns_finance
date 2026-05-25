<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->string('formula_type', 10)->default('AB')->after('sort_order');
            $table->string('col_a_label', 60)->nullable()->after('formula_type');
            $table->string('col_b_label', 60)->nullable()->after('col_a_label');
            $table->string('col_c_label', 60)->nullable()->after('col_b_label');
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->decimal('qty_c', 15, 2)->nullable()->default(null)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn(['formula_type', 'col_a_label', 'col_b_label', 'col_c_label']);
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn('qty_c');
        });
    }
};
