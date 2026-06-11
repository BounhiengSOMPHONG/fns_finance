<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->string('payment_type', 20)->default('transfer')->after('person_count');
            $table->decimal('amount', 15, 2)->default(0)->after('payment_type');
        });

        DB::table('salary_entries')->update([
            'amount' => DB::raw('COALESCE(atm_amount, 0) + COALESCE(cash_amount, 0)'),
            'payment_type' => DB::raw("CASE WHEN COALESCE(cash_amount, 0) > 0 AND COALESCE(atm_amount, 0) = 0 THEN 'cash' ELSE 'transfer' END"),
        ]);

        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->dropColumn(['atm_amount', 'cash_amount']);
        });
    }

    public function down(): void
    {
        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->decimal('atm_amount', 15, 2)->default(0)->after('person_count');
            $table->decimal('cash_amount', 15, 2)->default(0)->after('atm_amount');
        });

        DB::table('salary_entries')->update([
            'atm_amount' => DB::raw("CASE WHEN payment_type = 'transfer' THEN amount ELSE 0 END"),
            'cash_amount' => DB::raw("CASE WHEN payment_type = 'cash' THEN amount ELSE 0 END"),
        ]);

        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->dropColumn(['payment_type', 'amount']);
        });
    }
};
