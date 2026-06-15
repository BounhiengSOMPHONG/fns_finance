<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        Schema::table('academic_income_items', function (Blueprint $table): void {
            foreach ([
                'snap_credit_unit_price',
                'snap_course_credit_unit',
                'snap_registration_fee_rate',
                'snap_nuol_pct',
            ] as $column) {
                if (Schema::hasColumn('academic_income_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        Schema::table('academic_income_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_income_items', 'snap_credit_unit_price')) {
                $table->decimal('snap_credit_unit_price', 15, 2)->nullable()->after('student_count');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_course_credit_unit')) {
                $table->unsignedSmallInteger('snap_course_credit_unit')->nullable()->after('snap_credit_unit_price');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_registration_fee_rate')) {
                $table->decimal('snap_registration_fee_rate', 15, 2)->nullable()->after('snap_course_credit_unit');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_nuol_pct')) {
                $table->decimal('snap_nuol_pct', 5, 4)->nullable()->after('snap_registration_fee_rate');
            }
        });
    }
};
