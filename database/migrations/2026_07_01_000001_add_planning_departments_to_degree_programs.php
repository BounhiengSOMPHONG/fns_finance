<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEPARTMENTS = [
        'math_stats' => ['label' => 'ພາກວິຊາຄະນິດສາດ ແລະ ສະຖິຕິ', 'order' => 10],
        'physics' => ['label' => 'ພາກວິຊາຟິຊິກສາດ', 'order' => 20],
        'chemistry' => ['label' => 'ພາກວິຊາເຄມີສາດ', 'order' => 30],
        'biology' => ['label' => 'ພາກວິຊາຊີວະວິທະຍາ', 'order' => 40],
        'computer_science' => ['label' => 'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ', 'order' => 50],
        'other' => ['label' => 'ອື່ນໆ', 'order' => 90],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('degree_programs')) {
            return;
        }

        Schema::table('degree_programs', function (Blueprint $table): void {
            if (! Schema::hasColumn('degree_programs', 'academic_department')) {
                $table->string('academic_department')->nullable();
            }
            if (! Schema::hasColumn('degree_programs', 'department_sort_order')) {
                $table->unsignedSmallInteger('department_sort_order')->default(90);
            }
            if (! Schema::hasColumn('degree_programs', 'include_in_planning')) {
                $table->boolean('include_in_planning')->default(true);
            }
        });

        DB::table('degree_programs')
            ->whereNull('academic_department')
            ->orWhere('academic_department', '')
            ->orderBy('id')
            ->get(['id', 'code', 'is_active'])
            ->each(function ($program): void {
                [$department, $sortOrder] = $this->departmentForCode((string) $program->code);
                DB::table('degree_programs')
                    ->where('id', $program->id)
                    ->update([
                        'academic_department' => $department,
                        'department_sort_order' => $sortOrder,
                        'include_in_planning' => (bool) $program->is_active,
                        'updated_at' => now(),
                    ]);
            });

        $this->seedProgramUpdates();
        $this->copyCourseCredits();
    }

    public function down(): void
    {
        if (! Schema::hasTable('degree_programs')) {
            return;
        }

        Schema::table('degree_programs', function (Blueprint $table): void {
            if (Schema::hasColumn('degree_programs', 'include_in_planning')) {
                $table->dropColumn('include_in_planning');
            }
            if (Schema::hasColumn('degree_programs', 'department_sort_order')) {
                $table->dropColumn('department_sort_order');
            }
            if (Schema::hasColumn('degree_programs', 'academic_department')) {
                $table->dropColumn('academic_department');
            }
        });
    }

    private function seedProgramUpdates(): void
    {
        $now = now();
        $programs = [
            ['B-CSC-Y1', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ', 1],
            ['B-CSC-Y2', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ', 2],
            ['B-CSC-EVE-Y1', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ', 1],
            ['B-CSC-EVE-Y2', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ', 2],
        ];

        foreach ([1, 2, 3, 4] as $year) {
            $programs[] = ["B-CS-EVE-Y{$year}", 'ວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ', $year];
            $programs[] = ["B-PD-EVE-Y{$year}", 'ການພັດທະນາໂປຣແກຣມ ພາກຄໍ່າ', $year];
            $programs[] = ["B-WD-EVE-Y{$year}", 'ການພັດທະນາເວບໄຊ້ ພາກຄໍ່າ', $year];
            $programs[] = ["B-AI-Y{$year}", 'ເອໄອ ແລະ ນະວັດຕະກໍາ', $year];
        }

        foreach ($programs as [$code, $name, $year]) {
            [$department, $sortOrder] = $this->departmentForCode($code);
            DB::table('degree_programs')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'level' => 'bachelor',
                    'study_year' => $year,
                    'is_active' => true,
                    'include_in_planning' => true,
                    'academic_department' => $department,
                    'department_sort_order' => $sortOrder,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function copyCourseCredits(): void
    {
        if (! Schema::hasTable('course_credit_settings')) {
            return;
        }

        $copies = [
            'B-CSC-EVE' => 'B-CSC',
            'B-CS-EVE' => 'B-CS',
            'B-PD-EVE' => 'B-PD',
            'B-WD-EVE' => 'B-WD',
        ];

        foreach ($copies as $targetBase => $sourceBase) {
            foreach ([1, 2, 3, 4] as $year) {
                $sourceCode = "{$sourceBase}-Y{$year}";
                $targetCode = "{$targetBase}-Y{$year}";
                $sourceProgram = DB::table('degree_programs')->where('code', $sourceCode)->first(['id']);
                $targetProgram = DB::table('degree_programs')->where('code', $targetCode)->first(['id']);

                if (! $sourceProgram || ! $targetProgram) {
                    continue;
                }

                $sourceCredit = DB::table('course_credit_settings')
                    ->where('degree_program_id', $sourceProgram->id)
                    ->orderByDesc('start_year')
                    ->first();

                if (! $sourceCredit) {
                    continue;
                }

                DB::table('course_credit_settings')->updateOrInsert(
                    ['degree_program_id' => $targetProgram->id],
                    [
                        'course_credit_unit' => $sourceCredit->course_credit_unit,
                        'gov_doc_id' => $sourceCredit->gov_doc_id,
                        'start_year' => $sourceCredit->start_year,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function departmentForCode(string $code): array
    {
        $base = strtoupper($code);
        $base = preg_replace('/^MR-/', 'M-', $base);
        $base = preg_replace('/-EVE(?=-Y\d+$)/', '', $base);
        $base = preg_replace('/-Y\d+$/', '', $base);

        $department = match ($base) {
            'B-MAA', 'B-MAE', 'B-STAT', 'B-MATH', 'M-MATH' => 'math_stats',
            'B-PHYS', 'B-GPHY', 'B-MATS', 'B-NPHY', 'M-PHYS', 'D-PHYS' => 'physics',
            'B-CHEM', 'B-ECHE', 'M-CHEM' => 'chemistry',
            'B-BIO', 'B-BT', 'M-BIO', 'D-BIO' => 'biology',
            'B-CS', 'B-PD', 'B-WD', 'B-CSC', 'B-AI', 'M-CS' => 'computer_science',
            default => 'other',
        };

        return [$department, self::DEPARTMENTS[$department]['order']];
    }
};
