<?php

namespace Tests\Feature;

use App\Models\PlanningYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceHeadRouteRegressionTest extends TestCase
{
    private User $financeHead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->seedData();
        $this->withoutVite();
    }

    public function test_manage_plan_index_uses_expense_rows_for_totals(): void
    {
        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.index'))
            ->assertOk()
            ->assertSee('100 ກີບ')
            ->assertSee('50 ກີບ')
            ->assertSee('25 ກີບ');
    }

    public function test_salary_show_redirects_to_manage_page(): void
    {
        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.salary.show', 1))
            ->assertRedirect(route('head_of_finance.salary.manage', 1));
    }

    private function seedData(): void
    {
        $role = Role::create(['id' => 1, 'role_name' => 'head_of_finance']);

        $this->financeHead = User::create([
            'id' => 1,
            'username' => 'finance',
            'password' => 'password',
            'full_name' => 'Finance Head',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        PlanningYear::create([
            'id' => 1,
            'year' => 2027,
            'name' => 'Planning 2027',
            'is_active' => true,
            'status' => PlanningYear::STATUS_DRAFT,
        ]);

        DB::table('academic_income_plans')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'fiscal_year' => 2027,
            'created_by' => 1,
        ]);
        DB::table('academic_income_items')->insert([
            'id' => 1,
            'plan_id' => 1,
            'total_income' => 100,
        ]);
        DB::table('salary_plans')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'fiscal_year' => 2027,
            'month' => 1,
            'created_by' => 1,
        ]);
        DB::table('salary_entries')->insert([
            'id' => 1,
            'plan_id' => 1,
            'annual_amount' => 50,
        ]);
        DB::table('expense_plan_rows')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'section_id' => 1,
            'subsection_id' => null,
            'pattern_id' => null,
            'item_name' => 'Expense row',
            'plan_detail' => 'Expense row',
            'calculation_values' => json_encode(['yearly_total' => 25]),
            'pattern_snapshot' => null,
        ]);
    }

    private function createTables(): void
    {
        foreach ([
            'expense_plan_rows',
            'expense_patterns',
            'salary_entries',
            'salary_plans',
            'academic_income_items',
            'academic_income_plans',
            'planning_year_review_rounds',
            'planning_years',
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

        Schema::create('planning_years', function ($table): void {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 30)->default(PlanningYear::STATUS_DRAFT);
            $table->unsignedBigInteger('current_review_round_id')->nullable();
            $table->timestamp('review_requested_at')->nullable();
            $table->timestamp('review_closed_at')->nullable();
            $table->timestamp('period_1_2_saved_at')->nullable();
            $table->timestamp('period_3_4_saved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('planning_year_review_rounds', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('requested_by')->nullable();
            $table->unsignedInteger('closed_by')->nullable();
            $table->unsignedInteger('round_number')->default(1);
            $table->text('note')->nullable();
            $table->json('reviewer_user_ids')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_income_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_income_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->decimal('total_income', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('salary_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_entries', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->decimal('annual_amount', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('expense_patterns', function ($table): void {
            $table->id();
            $table->string('key')->nullable();
            $table->string('name')->nullable();
            $table->json('fields_schema')->nullable();
            $table->json('formula_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_plan_rows', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('catalog_item_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('plan_detail');
            $table->text('detail')->nullable();
            $table->json('calculation_values')->nullable();
            $table->json('pattern_snapshot')->nullable();
            $table->timestamps();
        });
    }
}
