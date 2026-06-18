<?php

namespace Tests\Feature;

use App\Models\PlanningYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PeriodPlanOverrideTest extends TestCase
{
    private User $financeHead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->seedUserAndPlan();
        $this->seedAccountsAndExpense();
    }

    public function test_finance_head_can_save_period_override(): void
    {
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100201']), [
                'period_1_amount' => 20,
                'period_2_amount' => 30,
            ])
            ->assertOk()
            ->assertJsonPath('row.first_half_amount', 50)
            ->assertJsonPath('row.second_half_amount', 50);

        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 20,
            'period_2_amount' => 30,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
        ]);
    }

    public function test_period_one_two_page_renders_editable_academic_rows(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $response = $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-1-2', 1))
            ->assertOk()
            ->assertSee('62100201')
            ->assertSee('data-period-input="period_1_amount"', false)
            ->assertDontSee('61000000');

        $this->assertSame(2, preg_match_all('/<input[^>]+data-period-input=/', $response->getContent()));
        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 25,
            'period_2_amount' => 25,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
        ]);
    }

    public function test_period_one_two_page_does_not_replace_saved_period_amounts(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 10,
            'period_2_amount' => 35,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-1-2', 1))
            ->assertOk();

        $this->assertDatabaseCount('period_plan_overrides', 1);
        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 10,
            'period_2_amount' => 35,
        ]);
    }

    public function test_period_override_rejects_negative_amounts(): void
    {
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100201']), [
                'period_1_amount' => -1,
                'period_2_amount' => 10,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['period_1_amount']);

        $this->assertDatabaseCount('period_plan_overrides', 0);
    }

    public function test_period_override_rejects_sum_above_yearly_amount(): void
    {
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100201']), [
                'period_1_amount' => 70,
                'period_2_amount' => 40,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['period_1_amount', 'period_2_amount']);

        $this->assertDatabaseCount('period_plan_overrides', 0);
    }

    public function test_period_override_rejects_account_codes_below_academic_section(): void
    {
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '61000000']), [
                'period_1_amount' => 10,
                'period_2_amount' => 10,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('period_plan_overrides', 0);
    }

    public function test_period_override_rejects_group_account_codes(): void
    {
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100000']), [
                'period_1_amount' => 20,
                'period_2_amount' => 20,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('period_plan_overrides', 0);
    }

    public function test_period_override_requires_saved_planning_year(): void
    {
        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100201']), [
                'period_1_amount' => 20,
                'period_2_amount' => 20,
            ])
            ->assertStatus(423);

        $this->assertDatabaseCount('period_plan_overrides', 0);
    }

    public function test_period_three_four_requires_saved_period_one_two(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-3-4', 1))
            ->assertRedirect(route('head_of_finance.manage-plan.index'));
    }

    public function test_finance_head_can_save_period_one_two_and_open_period_three_four(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update(['status' => PlanningYear::STATUS_SAVED]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-1-2.save', 1))
            ->assertRedirect();

        $this->assertNotNull(PlanningYear::findOrFail(1)->period_1_2_saved_at);

        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-3-4', 1))
            ->assertOk()
            ->assertSee('ງວດ 3-4');
    }

    public function test_period_three_four_page_renders_editable_rows_after_period_one_two_is_saved(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);

        $response = $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-3-4', 1))
            ->assertOk()
            ->assertSee('ແຜນຂໍຫຼຸດ')
            ->assertSee('ແຜນຂໍເພີ່ມ')
            ->assertSee('ແຜນງວດ 3')
            ->assertSee('colspan="2" class="period-adjust-head">ແຜນດັດແກ້ສະເລ່ຍ', false)
            ->assertSee('<th class="period-adjust-head">ເພີ່ມ</th>', false)
            ->assertSee('<th class="period-adjust-head">ຫຼຸດ</th>', false)
            ->assertSee('ແຜນປະຕິບັດ<br>ໝົດປີ 2027', false)
            ->assertSee('data-period-input="average_increase_amount"', false)
            ->assertSee('data-period-input="average_decrease_amount"', false)
            ->assertSee('data-period-input="requested_decrease_amount"', false)
            ->assertSee('data-period-input="period_4_amount"', false);

        $this->assertSame(6, preg_match_all('/<input[^>]+data-period-input=/', $response->getContent()));
    }

    public function test_finance_head_can_save_period_three_four_override(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 20,
            'period_2_amount' => 30,
            'average_increase_amount' => 0,
            'average_decrease_amount' => 0,
            'period_3_amount' => 25,
            'period_4_amount' => 25,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 5,
                'average_decrease_amount' => 15,
                'requested_decrease_amount' => 10,
                'requested_increase_amount' => 20,
                'period_3_amount' => 25,
                'period_4_amount' => 25,
            ])
            ->assertOk()
            ->assertJsonPath('row.adjusted_second_half_amount', 50)
            ->assertJsonPath('row.period_3_4_total_amount', 50);

        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'average_increase_amount' => 5,
            'average_decrease_amount' => 15,
            'requested_decrease_amount' => 10,
            'requested_increase_amount' => 20,
            'period_3_amount' => 25,
            'period_4_amount' => 25,
        ]);
    }

    public function test_period_three_four_rejects_negative_amounts(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 0,
                'average_decrease_amount' => 0,
                'requested_decrease_amount' => -1,
                'requested_increase_amount' => 0,
                'period_3_amount' => 25,
                'period_4_amount' => 25,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['requested_decrease_amount']);
    }

    public function test_period_three_four_rejects_requested_decrease_above_second_half(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 0,
                'average_decrease_amount' => 0,
                'requested_decrease_amount' => 60,
                'requested_increase_amount' => 0,
                'period_3_amount' => 0,
                'period_4_amount' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['requested_decrease_amount']);
    }

    public function test_period_three_four_autosaves_unbalanced_row_but_final_save_rejects_it(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 0,
                'average_decrease_amount' => 0,
                'requested_decrease_amount' => 10,
                'requested_increase_amount' => 20,
                'period_3_amount' => 20,
                'period_4_amount' => 20,
            ])
            ->assertOk();

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1))
            ->assertSessionHas('error');

        $this->assertNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);
    }

    public function test_finance_head_can_save_period_three_four_and_lock_editing(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 25,
            'period_2_amount' => 25,
            'period_3_amount' => 25,
            'period_4_amount' => 25,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1))
            ->assertRedirect();

        $this->assertNotNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 0,
                'average_decrease_amount' => 0,
                'requested_decrease_amount' => 0,
                'requested_increase_amount' => 0,
                'period_3_amount' => 25,
                'period_4_amount' => 25,
            ])
            ->assertStatus(423);
    }

    public function test_period_three_four_final_save_accepts_balanced_average_transfer_between_rows(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('chart_of_accounts')->insert([
            'id' => 5,
            'account_code' => '62100202',
            'account_name' => 'Printing',
            'parent_id' => 3,
        ]);
        DB::table('expense_plans')->insert([
            'id' => 3,
            'planning_year_id' => 1,
            'section_id' => 1,
            'subsection_id' => 1,
            'plan_detail' => 'Second academic row',
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 3, 'field_key' => 'reference', 'value_text' => '62100202'],
            ['expense_plan_id' => 3, 'field_key' => 'yearly_total', 'value_number' => 100],
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1), [
                'period_rows' => json_encode([
                    [
                        'account_code' => '62100201',
                        'average_increase_amount' => 0,
                        'average_decrease_amount' => 10,
                        'requested_decrease_amount' => 0,
                        'requested_increase_amount' => 0,
                        'period_3_amount' => 20,
                        'period_4_amount' => 20,
                    ],
                    [
                        'account_code' => '62100202',
                        'average_increase_amount' => 10,
                        'average_decrease_amount' => 0,
                        'requested_decrease_amount' => 0,
                        'requested_increase_amount' => 0,
                        'period_3_amount' => 30,
                        'period_4_amount' => 30,
                    ],
                ]),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);
        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'average_decrease_amount' => 10,
            'period_3_amount' => 20,
            'period_4_amount' => 20,
        ]);
        $this->assertDatabaseHas('period_plan_overrides', [
            'planning_year_id' => 1,
            'chart_of_account_id' => 5,
            'average_increase_amount' => 10,
            'period_3_amount' => 30,
            'period_4_amount' => 30,
        ]);
    }

    public function test_saved_period_three_four_page_is_read_only_and_rejects_more_edits(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
            'period_3_4_saved_at' => now(),
        ]);

        $response = $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-3-4', 1))
            ->assertOk()
            ->assertSee('ງວດ 3-4 ຖືກບັນທຶກແລ້ວ')
            ->assertDontSee('data-period-input="period_3_amount"', false);

        $this->assertSame(0, preg_match_all('/<input[^>]+data-period-input=/', $response->getContent()));

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-3-4.override', [1, '62100201']), [
                'average_increase_amount' => 0,
                'average_decrease_amount' => 0,
                'requested_decrease_amount' => 0,
                'requested_increase_amount' => 0,
                'period_3_amount' => 25,
                'period_4_amount' => 25,
            ])
            ->assertStatus(423);
    }

    public function test_period_three_four_final_save_requires_balanced_average_adjustment(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 25,
            'period_2_amount' => 25,
            'average_increase_amount' => 0,
            'average_decrease_amount' => 10,
            'period_3_amount' => 20,
            'period_4_amount' => 20,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1))
            ->assertSessionHas('error');

        $this->assertNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);
    }

    public function test_period_three_four_final_save_accepts_requested_decrease_as_real_budget_reduction(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 25,
            'period_2_amount' => 25,
            'requested_decrease_amount' => 10,
            'requested_increase_amount' => 0,
            'period_3_amount' => 20,
            'period_4_amount' => 20,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);
    }

    public function test_period_three_four_final_save_accepts_requested_increase_as_real_budget_increase(): void
    {
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'planning_year_id' => 1,
            'chart_of_account_id' => 4,
            'period_1_amount' => 25,
            'period_2_amount' => 25,
            'requested_decrease_amount' => 0,
            'requested_increase_amount' => 20,
            'period_3_amount' => 35,
            'period_4_amount' => 35,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.period-3-4.save', 1))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull(PlanningYear::findOrFail(1)->period_3_4_saved_at);
    }

    public function test_saved_period_one_two_page_is_read_only_and_rejects_more_edits(): void
    {
        $this->withoutVite();
        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_SAVED,
            'period_1_2_saved_at' => now(),
        ]);

        $response = $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.manage-plan.period-1-2', 1))
            ->assertOk()
            ->assertSee('ງວດ 1-2 ຖືກບັນທຶກແລ້ວ')
            ->assertDontSee('data-period-input="period_1_amount"', false)
            ->assertDontSee('ບັນທຶກອີກຄັ້ງ');

        $this->assertSame(0, preg_match_all('/<input[^>]+data-period-input=/', $response->getContent()));

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.manage-plan.period-1-2.override', [1, '62100201']), [
                'period_1_amount' => 20,
                'period_2_amount' => 20,
            ])
            ->assertStatus(423);
    }

    private function seedUserAndPlan(): void
    {
        $financeRole = Role::create(['id' => 1, 'role_name' => 'head_of_finance']);

        $this->financeHead = User::create([
            'id' => 1,
            'username' => 'finance',
            'password' => 'password',
            'full_name' => 'Finance Head',
            'role_id' => $financeRole->id,
            'is_active' => true,
        ]);

        PlanningYear::create([
            'id' => 1,
            'year' => 2027,
            'name' => 'Planning 2027',
            'is_active' => true,
            'status' => PlanningYear::STATUS_DRAFT,
        ]);
    }

    private function seedAccountsAndExpense(): void
    {
        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'account_code' => '61000000', 'account_name' => 'Non academic', 'parent_id' => null],
            ['id' => 2, 'account_code' => '62000000', 'account_name' => 'Academic', 'parent_id' => null],
            ['id' => 3, 'account_code' => '62100000', 'account_name' => 'Purchases', 'parent_id' => 2],
            ['id' => 4, 'account_code' => '62100201', 'account_name' => 'Paper', 'parent_id' => 3],
        ]);
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
    }

    private function createTables(): void
    {
        foreach ([
            'expense_plan_values',
            'period_plan_overrides',
            'expense_plans',
            'expense_pattern_fields',
            'expense_patterns',
            'expense_subsection_default_rows',
            'expense_subsections',
            'expense_sections',
            'salary_entries',
            'salary_plans',
            'planning_year_reviewers',
            'planning_year_review_rounds',
            'planning_years',
            'chart_of_accounts',
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

        Schema::create('chart_of_accounts', function ($table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('account_code');
            $table->string('account_name');
            $table->unsignedInteger('parent_id')->nullable();
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
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('closed_by')->nullable();
            $table->unsignedInteger('round_number');
            $table->text('note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('planning_year_reviewers', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_review_round_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->unique(['planning_year_review_round_id', 'user_id']);
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

        Schema::create('expense_patterns', function ($table): void {
            $table->id();
            $table->string('key');
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('expense_pattern_fields', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('pattern_id');
            $table->string('field_key');
            $table->string('default_label')->nullable();
            $table->string('data_type')->default('number');
            $table->integer('display_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_calculated')->default(false);
            $table->string('default_value')->nullable();
        });

        Schema::create('expense_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->string('plan_detail');
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
