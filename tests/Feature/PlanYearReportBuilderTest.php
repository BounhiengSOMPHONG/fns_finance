<?php

namespace Tests\Feature;

use App\Models\PlanningYear;
use App\Services\PeriodPlanReportBuilder;
use App\Services\PlanYearReportBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlanYearReportBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    public function test_expense_uses_default_row_account_before_reference_and_rolls_up_without_double_counting(): void
    {
        $this->seedAccounts();
        DB::table('planning_years')->insert(['id' => 1, 'year' => 2027, 'name' => 'Planning 2027']);
        DB::table('salary_plans')->insert(['id' => 1, 'planning_year_id' => 1, 'fiscal_year' => 2027, 'month' => 1]);
        DB::table('salary_entries')->insert([
            'id' => 1,
            'plan_id' => 1,
            'chart_of_account_id' => 3,
            'person_count' => 1,
            'payment_type' => 'transfer',
            'amount' => 100,
            'monthly_total' => 100,
            'annual_amount' => 1200,
        ]);

        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.1', 'name' => 'Expense']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.1.1', 'name' => 'Office']);
        DB::table('expense_subsection_default_rows')->insert([
            'id' => 1,
            'subsection_code' => '2.1.1',
            'item_name' => 'Paper',
            'chart_of_account_id' => 7,
            'sort_order' => 1,
        ]);
        DB::table('expense_plans')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Paper'],
            ['id' => 2, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Fallback row'],
            ['id' => 3, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Unlinked row'],
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 1, 'field_key' => 'item_name', 'value_text' => 'Paper'],
            ['expense_plan_id' => 1, 'field_key' => 'reference', 'value_text' => '60100100'],
            ['expense_plan_id' => 1, 'field_key' => 'yearly_total', 'value_number' => 100],
            ['expense_plan_id' => 2, 'field_key' => 'reference', 'value_text' => '62100201'],
            ['expense_plan_id' => 2, 'field_key' => 'yearly_total', 'value_number' => 50],
            ['expense_plan_id' => 3, 'field_key' => 'yearly_total', 'value_number' => 25],
        ]);

        $report = app(PlanYearReportBuilder::class)->buildForPlanningYear(PlanningYear::findOrFail(1));
        $rows = collect($report['rows'])->keyBy('code');

        $this->assertSame(1200.0, $rows->get('60000000')['state_amount']);
        $this->assertSame(1200.0, $rows->get('60100100')['state_amount']);
        $this->assertTrue($rows->get('60100000')['is_group']);
        $this->assertFalse($rows->get('60100100')['is_group']);
        $this->assertSame(150.0, $rows->get('62000000')['faculty_amount']);
        $this->assertSame(0.0, $rows->get('60100100')['faculty_amount']);
        $this->assertSame(150.0, $rows->get('62100201')['faculty_amount']);
        $this->assertSame(1350.0, $report['totals']['total_amount']);
        $this->assertCount(1, $report['warnings']['reference_fallbacks']);
        $this->assertCount(1, $report['warnings']['unlinked_expenses']);
    }

    public function test_period_report_defaults_to_quarter_amounts_and_excludes_non_academic_accounts(): void
    {
        $this->seedAccounts();
        DB::table('chart_of_accounts')->insert([
            ['id' => 8, 'account_code' => '61000000', 'account_name' => 'Non academic', 'parent_id' => null],
        ]);
        DB::table('planning_years')->insert(['id' => 1, 'year' => 2027, 'name' => 'Planning 2027']);
        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.1', 'name' => 'Expense']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.1.1', 'name' => 'Office']);
        DB::table('expense_plans')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Academic row'],
            ['id' => 2, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Blocked row'],
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 1, 'field_key' => 'reference', 'value_text' => '62100201'],
            ['expense_plan_id' => 1, 'field_key' => 'yearly_total', 'value_number' => 100],
            ['expense_plan_id' => 2, 'field_key' => 'reference', 'value_text' => '61000000'],
            ['expense_plan_id' => 2, 'field_key' => 'yearly_total', 'value_number' => 80],
        ]);

        $report = app(PeriodPlanReportBuilder::class)->buildForPlanningYear(PlanningYear::findOrFail(1));
        $rows = collect($report['rows'])->keyBy('account_code');

        $this->assertFalse($rows->has('61000000'));
        $this->assertTrue($rows->has('62000000'));
        $this->assertTrue($rows->has('62100201'));
        $this->assertSame(25.0, $rows->get('62100201')['period_1_amount']);
        $this->assertSame(25.0, $rows->get('62100201')['period_2_amount']);
        $this->assertSame(50.0, $rows->get('62100201')['first_half_amount']);
        $this->assertSame(50.0, $rows->get('62100201')['second_half_amount']);
        $this->assertSame(0.0, $rows->get('62100201')['average_increase_amount']);
        $this->assertSame(0.0, $rows->get('62100201')['average_decrease_amount']);
        $this->assertSame(25.0, $rows->get('62100201')['period_3_amount']);
        $this->assertSame(25.0, $rows->get('62100201')['period_4_amount']);
        $this->assertSame(50.0, $rows->get('62100201')['adjusted_second_half_amount']);
        $this->assertSame(200.0, $rows->get('62100201')['reduction_percent']);
        $this->assertSame(50.0, $rows->get('62000000')['adjusted_second_half_amount']);
        $this->assertSame(100.0, $report['totals']['yearly_amount']);
        $this->assertSame(25.0, $report['totals']['period_1_amount']);
        $this->assertSame(25.0, $report['totals']['period_2_amount']);
        $this->assertSame(25.0, $report['totals']['period_3_amount']);
        $this->assertSame(25.0, $report['totals']['period_4_amount']);
    }

    public function test_period_report_uses_saved_override_for_period_amounts(): void
    {
        $this->seedAccounts();
        DB::table('planning_years')->insert(['id' => 1, 'year' => 2027, 'name' => 'Planning 2027']);
        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.1', 'name' => 'Expense']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.1.1', 'name' => 'Office']);
        DB::table('expense_plans')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Academic row'],
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 1, 'field_key' => 'reference', 'value_text' => '62100201'],
            ['expense_plan_id' => 1, 'field_key' => 'yearly_total', 'value_number' => 100],
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 7,
            'period_1_amount' => 10,
            'period_2_amount' => 35,
            'average_increase_amount' => 3,
            'average_decrease_amount' => 8,
            'requested_decrease_amount' => 5,
            'requested_increase_amount' => 15,
            'period_3_amount' => 28,
            'period_4_amount' => 32,
        ]);

        $report = app(PeriodPlanReportBuilder::class)->buildForPlanningYear(PlanningYear::findOrFail(1));
        $row = collect($report['rows'])->firstWhere('account_code', '62100201');

        $this->assertSame(10.0, $row['period_1_amount']);
        $this->assertSame(35.0, $row['period_2_amount']);
        $this->assertSame(45.0, $row['first_half_amount']);
        $this->assertSame(55.0, $row['second_half_amount']);
        $this->assertSame(3.0, $row['average_increase_amount']);
        $this->assertSame(8.0, $row['average_decrease_amount']);
        $this->assertSame(5.0, $row['requested_decrease_amount']);
        $this->assertSame(15.0, $row['requested_increase_amount']);
        $this->assertSame(60.0, $row['adjusted_second_half_amount']);
        $this->assertSame(28.0, $row['period_3_amount']);
        $this->assertSame(32.0, $row['period_4_amount']);
        $this->assertTrue($row['has_override']);
    }

    public function test_period_three_four_defaults_to_quarter_amounts_when_only_period_one_two_was_saved(): void
    {
        $this->seedAccounts();
        DB::table('planning_years')->insert(['id' => 1, 'year' => 2027, 'name' => 'Planning 2027']);
        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.1', 'name' => 'Expense']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.1.1', 'name' => 'Office']);
        DB::table('expense_plans')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Academic row'],
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 1, 'field_key' => 'reference', 'value_text' => '62100201'],
            ['expense_plan_id' => 1, 'field_key' => 'yearly_total', 'value_number' => 100],
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 7,
            'period_1_amount' => 10,
            'period_2_amount' => 35,
        ]);

        $report = app(PeriodPlanReportBuilder::class)->buildForPlanningYear(PlanningYear::findOrFail(1));
        $row = collect($report['rows'])->firstWhere('account_code', '62100201');

        $this->assertSame(55.0, $row['second_half_amount']);
        $this->assertSame(55.0, $row['adjusted_second_half_amount']);
        $this->assertSame(25.0, $row['period_3_amount']);
        $this->assertSame(25.0, $row['period_4_amount']);
        $this->assertSame(50.0, $row['period_3_4_total_amount']);
        $this->assertSame(200.0, $row['reduction_percent']);
    }

    private function seedAccounts(): void
    {
        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'account_code' => '60000000', 'account_name' => 'Salary', 'parent_id' => null],
            ['id' => 2, 'account_code' => '60100000', 'account_name' => 'Base salary', 'parent_id' => 1],
            ['id' => 3, 'account_code' => '60100100', 'account_name' => 'Active staff', 'parent_id' => 2],
            ['id' => 4, 'account_code' => '62000000', 'account_name' => 'Administration', 'parent_id' => null],
            ['id' => 5, 'account_code' => '62100000', 'account_name' => 'Purchases', 'parent_id' => 4],
            ['id' => 6, 'account_code' => '62100200', 'account_name' => 'Office supplies', 'parent_id' => 5],
            ['id' => 7, 'account_code' => '62100201', 'account_name' => 'Paper', 'parent_id' => 6],
        ]);
    }

    private function createTables(): void
    {
        foreach ([
            'expense_plan_values',
            'period_plan_overrides',
            'expense_plans',
            'expense_subsection_default_rows',
            'expense_subsections',
            'expense_sections',
            'salary_entries',
            'salary_plans',
            'planning_years',
            'chart_of_accounts',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('chart_of_accounts', function ($table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('account_code');
            $table->string('account_name');
            $table->unsignedInteger('parent_id')->nullable();
        });

        Schema::create('planning_years', function ($table): void {
            $table->id();
            $table->integer('year');
            $table->string('name')->nullable();
        });

        Schema::create('period_plan_overrides', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->decimal('period_1_amount', 18, 2)->default(0);
            $table->decimal('period_2_amount', 18, 2)->default(0);
            $table->decimal('average_increase_amount', 18, 2)->default(0);
            $table->decimal('average_decrease_amount', 18, 2)->default(0);
            $table->decimal('requested_decrease_amount', 18, 2)->default(0);
            $table->decimal('requested_increase_amount', 18, 2)->default(0);
            $table->decimal('period_3_amount', 18, 2)->default(0);
            $table->decimal('period_4_amount', 18, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['planning_year_id', 'chart_of_account_id']);
        });

        Schema::create('salary_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->integer('fiscal_year');
            $table->integer('month')->default(1);
        });

        Schema::create('salary_entries', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->integer('person_count')->default(0);
            $table->string('payment_type')->default('transfer');
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('monthly_total', 18, 2)->default(0);
            $table->decimal('annual_amount', 18, 2)->default(0);
        });

        Schema::create('expense_sections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->string('code', 30);
            $table->string('name');
        });

        Schema::create('expense_subsections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 30);
            $table->string('name');
        });

        Schema::create('expense_subsection_default_rows', function ($table): void {
            $table->id();
            $table->string('subsection_code', 30);
            $table->string('item_name');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->json('default_values')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->string('plan_detail');
        });

        Schema::create('expense_plan_values', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('expense_plan_id');
            $table->string('field_key', 50);
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 18, 2)->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('value_boolean')->nullable();
        });
    }
}
