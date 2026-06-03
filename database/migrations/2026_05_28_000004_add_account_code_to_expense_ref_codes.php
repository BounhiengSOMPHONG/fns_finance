<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_ref_codes', function (Blueprint $table) {
            $table->string('account_code', 30)->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('expense_ref_codes', function (Blueprint $table) {
            $table->dropColumn('account_code');
        });
    }
};
