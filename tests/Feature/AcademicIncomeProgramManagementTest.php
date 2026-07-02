<?php

namespace Tests\Feature;

use App\Models\AcademicIncomeItem;
use App\Models\AcademicIncomePlan;
use App\Models\DegreeProgram;
use App\Models\Role;
use App\Models\User;
use App\Services\AcademicIncomeReportBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicIncomeProgramManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->seedSettings();
        $this->seedUser();
    }

    public function test_assessment_uses_planning_inclusion_and_department_order(): void
    {
        $plan = AcademicIncomePlan::create(['fiscal_year' => 2027]);
        $this->seedProgram('B-CSC-Y1', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ', 1, 'computer_science', 50, true, 37);
        $this->seedProgram('B-CSC-EVE-Y1', 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ', 1, 'computer_science', 50, true, 37);
        $this->seedProgram('B-BIO-Y1', 'ຊີວະວິທະຍາ', 1, 'biology', 40, true, 37);
        $this->seedProgram('B-MATH-Y1', 'ຄະນິດທົ່ວໄປ', 1, 'math_stats', 10, true, 37);
        $this->seedProgram('B-HIDDEN-Y1', 'ບໍ່ຂຶ້ນແຜນ', 1, 'computer_science', 50, false, 37);

        $response = $this->actingAs(User::findOrFail(1))
            ->get(route('head_of_finance.academic-income.evaluate', $plan))
            ->assertOk()
            ->assertSee('ຄະນິດທົ່ວໄປ')
            ->assertSee('ຊີວະວິທະຍາ')
            ->assertSee('ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ')
            ->assertSee('ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກຄໍ່າ')
            ->assertDontSee('ບໍ່ຂຶ້ນແຜນ');

        $content = $response->getContent();
        $this->assertLessThan(strpos($content, 'ຊີວະວິທະຍາ'), strpos($content, 'ຄະນິດທົ່ວໄປ'));
        $this->assertLessThan(strpos($content, 'ຕໍ່ເນື່ອງວິທະຍາສາດຄອມພິວເຕີ ພາກປົກກະຕິ'), strpos($content, 'ຊີວະວິທະຍາ'));
    }

    public function test_report_placeholders_use_planning_inclusion(): void
    {
        $plan = AcademicIncomePlan::create(['fiscal_year' => 2027]);
        $visible = $this->seedProgram('B-VISIBLE-Y1', 'Visible planning program', 1, 'math_stats', 10, true, 37);
        $hidden = $this->seedProgram('B-HIDDEN-Y1', 'Hidden planning program', 1, 'computer_science', 50, false, 37);

        $report = app(AcademicIncomeReportBuilder::class)->buildForPlans($plan->newCollection([$plan]));
        $programIds = $report['items']
            ->whereIn('section_code', ['1.1', '1.3'])
            ->pluck('degree_program_id')
            ->filter()
            ->values()
            ->all();

        $this->assertContains($visible->id, $programIds);
        $this->assertNotContains($hidden->id, $programIds);
    }

    public function test_income_report_detail_rows_follow_department_order(): void
    {
        $plan = AcademicIncomePlan::create(['fiscal_year' => 2027, 'created_by' => 1]);

        $computer = $this->seedProgram('B-CS-Y2', 'ວິທະຍາສາດຄອມ', 2, 'computer_science', 50, true, 37);
        $math = $this->seedProgram('B-MATH-Y2', 'ຄະນິດທົ່ວໄປ', 2, 'math_stats', 10, true, 37);
        $physics = $this->seedProgram('B-PHYS-Y2', 'ຟີຊິກທົ່ວໄປ', 2, 'physics', 20, true, 36);
        $chemistry = $this->seedProgram('B-CHEM-Y2', 'ເຄມີທົ່ວໄປ', 2, 'chemistry', 30, true, 36);
        $biology = $this->seedProgram('B-BIO-Y2', 'ຊີວະວິທະຍາທົ່ວໄປ', 2, 'biology', 40, true, 36);

        foreach ([$computer, $math, $physics, $chemistry, $biology] as $program) {
            AcademicIncomeItem::create([
                'plan_id' => $plan->id,
                'section_code' => '1.1',
                'degree_program_id' => $program->id,
                'student_count' => 1,
                'snap_credit_unit_price' => 35000,
                'snap_course_credit_unit' => 36,
                'snap_nuol_pct' => 0.17,
                'total_income' => 1045800,
            ]);
        }

        $content = $this->actingAs(User::findOrFail(1))
            ->get(route('head_of_finance.academic-income.show', $plan))
            ->assertOk()
            ->getContent();

        $mathPosition = strpos($content, 'ປີ 2 ຄະນິດທົ່ວໄປ');
        $physicsPosition = strpos($content, 'ປີ 2 ຟີຊິກທົ່ວໄປ');
        $chemistryPosition = strpos($content, 'ປີ 2 ເຄມີທົ່ວໄປ');
        $biologyPosition = strpos($content, 'ປີ 2 ຊີວະວິທະຍາທົ່ວໄປ');
        $computerPosition = strpos($content, 'ປີ 2 ວິທະຍາສາດຄອມ');

        $this->assertLessThan($physicsPosition, $mathPosition);
        $this->assertLessThan($chemistryPosition, $physicsPosition);
        $this->assertLessThan($biologyPosition, $chemistryPosition);
        $this->assertLessThan($computerPosition, $biologyPosition);
    }

    public function test_settings_form_stores_department_and_planning_inclusion(): void
    {
        $response = $this->actingAs(User::findOrFail(1))
            ->post(route('head_of_finance.settings.degree-programs.store'), [
                'code' => 'B-NEW',
                'name' => 'New Program',
                'level' => 'bachelor',
                'study_years' => [1],
                'include_in_planning' => '1',
                'academic_department' => 'biology',
            ]);

        $response->assertRedirect(route('head_of_finance.settings.degree-programs.index'));

        $this->assertDatabaseHas('degree_programs', [
            'code' => 'B-NEW-Y1',
            'name' => 'New Program',
            'include_in_planning' => true,
            'academic_department' => 'biology',
            'department_sort_order' => 40,
        ]);

        $this->actingAs(User::findOrFail(1))
            ->get(route('head_of_finance.settings.degree-programs.index'))
            ->assertOk()
            ->assertDontSee('ເປີດໃຊ້ງານ');
    }

    private function seedSettings(): void
    {
        DB::table('credit_unit_price_settings')->insert([
            ['level' => 'bachelor', 'credit_unit_price' => 35000, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'master', 'credit_unit_price' => 240000, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'phd', 'credit_unit_price' => 600000, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('nuol_pct_settings')->insert([
            ['level' => 'bachelor', 'percentage' => 0.17, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'master', 'percentage' => 0.10, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'phd', 'percentage' => 0.10, 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('income_rate_settings')->insert([
            ['key' => 'item3_rate', 'label' => 'Item 3', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item4_rate', 'label' => 'Item 4', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item5_rate', 'label' => 'Item 5', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'item6_rate', 'label' => 'Item 6', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('registration_fee_settings')->insert([
            ['id' => 1, 'section_type' => 'year1', 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'section_type' => 'year2_4', 'start_year' => 2026, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('registration_fee_items')->insert([
            ['fee_setting_id' => 1, 'sort_order' => 1, 'name' => 'Fee', 'amount' => 0, 'nuol_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['fee_setting_id' => 2, 'sort_order' => 1, 'name' => 'Fee', 'amount' => 0, 'nuol_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedUser(): void
    {
        Role::create(['id' => 1, 'role_name' => 'head_of_finance']);
        User::create([
            'id' => 1,
            'username' => 'finance',
            'password' => 'password',
            'full_name' => 'Finance Head',
            'role_id' => 1,
            'is_active' => true,
        ]);
    }

    private function seedProgram(
        string $code,
        string $name,
        int $studyYear,
        string $department,
        int $sortOrder,
        bool $includeInPlanning,
        int $credits
    ): DegreeProgram {
        $program = DegreeProgram::create([
            'code' => $code,
            'name' => $name,
            'level' => 'bachelor',
            'study_year' => $studyYear,
            'include_in_planning' => $includeInPlanning,
            'academic_department' => $department,
            'department_sort_order' => $sortOrder,
        ]);

        DB::table('course_credit_settings')->insert([
            'degree_program_id' => $program->id,
            'course_credit_unit' => $credits,
            'start_year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $program;
    }

    private function createTables(): void
    {
        foreach ([
            'registration_fee_items',
            'registration_fee_settings',
            'income_rate_settings',
            'nuol_pct_settings',
            'credit_unit_price_settings',
            'course_credit_split_settings',
            'course_credit_settings',
            'academic_income_items',
            'academic_income_plans',
            'planning_year_review_rounds',
            'planning_years',
            'degree_programs',
            'users',
            'roles',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('roles', function ($table): void {
            $table->increments('id');
            $table->string('role_name', 50);
        });

        Schema::create('users', function ($table): void {
            $table->increments('id');
            $table->string('username', 50);
            $table->string('password');
            $table->string('full_name', 100);
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
        });

        Schema::create('degree_programs', function ($table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('level');
            $table->unsignedSmallInteger('study_year')->nullable();
            $table->boolean('include_in_planning')->default(true);
            $table->string('academic_department')->nullable();
            $table->unsignedSmallInteger('department_sort_order')->default(90);
            $table->timestamps();
        });

        Schema::create('academic_income_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('planning_years', function ($table): void {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->string('name')->nullable();
            $table->string('status', 30)->default('DRAFT');
            $table->timestamps();
        });

        Schema::create('planning_year_review_rounds', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('requested_by')->nullable();
            $table->unsignedInteger('closed_by')->nullable();
            $table->unsignedInteger('round_number')->default(1);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_income_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->string('section_code', 20);
            $table->unsignedBigInteger('degree_program_id')->nullable();
            $table->unsignedInteger('student_count')->default(0);
            $table->unsignedBigInteger('credit_unit_price_setting_id')->nullable();
            $table->unsignedBigInteger('income_rate_setting_id')->nullable();
            $table->unsignedBigInteger('registration_fee_setting_id')->nullable();
            $table->unsignedBigInteger('nuol_pct_setting_id')->nullable();
            $table->decimal('snap_credit_unit_price', 18, 2)->nullable();
            $table->decimal('snap_course_credit_unit', 8, 2)->nullable();
            $table->decimal('snap_registration_fee_rate', 18, 2)->nullable();
            $table->decimal('snap_nuol_pct', 8, 4)->nullable();
            $table->decimal('total_income', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('course_credit_settings', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('degree_program_id');
            $table->unsignedSmallInteger('course_credit_unit');
            $table->string('gov_doc_id')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });

        Schema::create('course_credit_split_settings', function ($table): void {
            $table->id();
            $table->string('level');
            $table->decimal('year1_percentage', 5, 4)->default(0.6000);
            $table->decimal('year2_percentage', 5, 4)->default(0.4000);
            $table->unsignedSmallInteger('start_year')->default(2026);
            $table->timestamps();
        });

        Schema::create('credit_unit_price_settings', function ($table): void {
            $table->id();
            $table->string('level');
            $table->decimal('credit_unit_price', 18, 2);
            $table->string('gov_doc_id')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });

        Schema::create('nuol_pct_settings', function ($table): void {
            $table->id();
            $table->string('level');
            $table->decimal('percentage', 8, 4);
            $table->string('gov_doc_id')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });

        Schema::create('income_rate_settings', function ($table): void {
            $table->id();
            $table->string('key');
            $table->string('label');
            $table->decimal('rate', 18, 2);
            $table->timestamps();
        });

        Schema::create('registration_fee_settings', function ($table): void {
            $table->id();
            $table->string('section_type');
            $table->string('gov_doc_id')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->timestamps();
        });

        Schema::create('registration_fee_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('fee_setting_id');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('name');
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('nuol_pct', 8, 4)->default(0);
            $table->timestamps();
        });
    }
}
