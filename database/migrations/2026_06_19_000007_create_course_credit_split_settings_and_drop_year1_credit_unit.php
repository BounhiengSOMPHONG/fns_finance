<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_credit_split_settings')) {
            Schema::create('course_credit_split_settings', function (Blueprint $table): void {
                $table->id();
                $table->enum('level', ['master', 'phd']);
                $table->decimal('year1_percentage', 5, 4)->default(0.6000);
                $table->decimal('year2_percentage', 5, 4)->default(0.4000);
                $table->string('gov_doc_id')->nullable();
                $table->unsignedSmallInteger('start_year')->default(2026);
                $table->timestamps();

                $table->unique(['level', 'start_year']);
            });
        }

        foreach (['master', 'phd'] as $level) {
            DB::table('course_credit_split_settings')->updateOrInsert(
                ['level' => $level, 'start_year' => 2026],
                [
                    'year1_percentage' => 0.6000,
                    'year2_percentage' => 0.4000,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('course_credit_settings') && Schema::hasColumn('course_credit_settings', 'year1_credit_unit')) {
            DB::table('course_credit_settings')
                ->whereNotNull('year1_credit_unit')
                ->where('year1_credit_unit', '>', 0)
                ->update([
                    'course_credit_unit' => DB::raw('course_credit_unit + year1_credit_unit'),
                    'updated_at' => now(),
                ]);

            Schema::table('course_credit_settings', function (Blueprint $table): void {
                $table->dropColumn('year1_credit_unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_credit_settings') && ! Schema::hasColumn('course_credit_settings', 'year1_credit_unit')) {
            Schema::table('course_credit_settings', function (Blueprint $table): void {
                $table->decimal('year1_credit_unit', 8, 2)->nullable()->after('course_credit_unit');
            });

            DB::table('course_credit_settings')
                ->join('degree_programs', 'course_credit_settings.degree_program_id', '=', 'degree_programs.id')
                ->whereIn('degree_programs.level', ['master', 'phd'])
                ->update([
                    'year1_credit_unit' => DB::raw('ROUND(course_credit_unit * 0.60, 2)'),
                    'course_credit_settings.course_credit_unit' => DB::raw('ROUND(course_credit_unit * 0.40, 0)'),
                    'course_credit_settings.updated_at' => now(),
                ]);
        }

        Schema::dropIfExists('course_credit_split_settings');
    }
};
