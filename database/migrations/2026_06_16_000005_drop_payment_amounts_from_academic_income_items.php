<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_income_items', function (Blueprint $table): void {
            if (Schema::hasColumn('academic_income_items', 'first_payment_amount')) {
                $table->dropColumn('first_payment_amount');
            }

            if (Schema::hasColumn('academic_income_items', 'second_payment_amount')) {
                $table->dropColumn('second_payment_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_income_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_income_items', 'first_payment_amount')) {
                $table->decimal('first_payment_amount', 18, 2)->default(0)->after('total_income');
            }

            if (! Schema::hasColumn('academic_income_items', 'second_payment_amount')) {
                $table->decimal('second_payment_amount', 18, 2)->default(0)->after('first_payment_amount');
            }
        });
    }
};
