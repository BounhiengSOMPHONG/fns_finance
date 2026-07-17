<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\CourseCreditSetting;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanRow;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\NuolPctSetting;
use App\Models\PlanningYear;
use App\Models\RegistrationFeeItem;
use App\Models\RegistrationFeeSetting;
use App\Models\Role;
use App\Models\SalaryPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceHeadSaveActionSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $financeHead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $role = Role::create(['role_name' => 'head_of_finance']);
        $this->financeHead = User::create([
            'username' => 'finance-smoke',
            'password' => 'password',
            'full_name' => 'Finance Smoke',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_finance_head_ajax_save_buttons_update_expense_and_salary_rows(): void
    {
        [$year, $account, $pattern, $section, $subsection, $catalogItem] = $this->seedExpenseStructure();

        $expensePlan = ExpensePlan::create([
            'planning_year_id' => $year->id,
            'fiscal_year' => $year->year,
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
        ]);
        $expenseRow = ExpensePlanRow::create([
            'expense_plan_id' => $expensePlan->id,
            'planning_year_id' => $year->id,
            'section_id' => $section->id,
            'subsection_id' => $subsection->id,
            'catalog_item_id' => $catalogItem->id,
            'chart_of_account_id' => $account->id,
            'pattern_id' => $pattern->id,
            'version' => (string) $year->year,
            'plan_type' => $pattern->key,
            'item_name' => 'Existing row',
            'plan_detail' => 'Existing row',
            'calculation_values' => ['yearly_total' => 10],
            'pattern_snapshot' => $pattern->snapshot(),
            'created_by' => $this->financeHead->id,
            'updated_by' => $this->financeHead->id,
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.expense-plan-rows.update', $expenseRow), [
                'plan_detail' => 'Existing row',
                'detail' => 'Updated by save smoke',
                'values' => ['yearly_total' => 123],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Updated by save smoke', $expenseRow->refresh()->detail);
        $this->assertSame(123, (int) $expenseRow->calculation_values['yearly_total']);

        $this->actingAs($this->financeHead)
            ->postJson(route('head_of_finance.expense-plan-rows.store'), [
                'planning_year_id' => $year->id,
                'section_id' => $section->id,
                'subsection_id' => $subsection->id,
                'pattern_id' => $pattern->id,
                'plan_detail' => 'New custom row',
                'detail' => 'Created by save smoke',
                'values' => ['yearly_total' => 456],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $salaryPlan = SalaryPlan::create([
            'planning_year_id' => $year->id,
            'fiscal_year' => $year->year,
            'month' => 1,
            'created_by' => $this->financeHead->id,
        ]);

        $this->actingAs($this->financeHead)
            ->postJson(route('head_of_finance.salary-entries.store'), [
                'plan_id' => $salaryPlan->id,
                'chart_of_account_id' => $account->id,
                'person_count' => 2,
                'payment_type' => 'cash',
                'amount' => 120,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('entry.annual_amount', 1440);
    }

    public function test_finance_head_settings_save_buttons_accept_valid_payloads(): void
    {
        [$year, $account, $pattern, $section, $subsection, $catalogItem] = $this->seedExpenseStructure();
        $program = DegreeProgram::create([
            'code' => 'B-SMOKE-Y1',
            'name' => 'Smoke Program',
            'level' => 'bachelor',
            'study_year' => 1,
            'academic_department' => 'math_stats',
            'department_sort_order' => 10,
            'include_in_planning' => true,
        ]);
        $courseCredit = CourseCreditSetting::create([
            'degree_program_id' => $program->id,
            'course_credit_unit' => 30,
            'gov_doc_id' => null,
            'start_year' => 2026,
        ]);
        $creditPrice = CreditUnitPriceSetting::create([
            'level' => 'bachelor',
            'credit_unit_price' => 35000,
            'gov_doc_id' => null,
            'start_year' => 2026,
        ]);
        $nuolPct = NuolPctSetting::create([
            'level' => 'bachelor',
            'percentage' => 0.17,
            'gov_doc_id' => null,
            'start_year' => 2026,
        ]);
        $registrationFee = RegistrationFeeSetting::create([
            'section_type' => 'year1',
            'gov_doc_id' => null,
            'start_year' => 2026,
        ]);
        RegistrationFeeItem::create([
            'fee_setting_id' => $registrationFee->id,
            'sort_order' => 0,
            'name' => 'Old Fee',
            'amount' => 100,
            'nuol_pct' => 0.10,
        ]);

        $this->actingAs($this->financeHead)
            ->patchJson(route('head_of_finance.settings.expense-default-rows.account.update', $catalogItem), [
                'chart_of_account_id' => $account->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.expense-structure.sections.update', $section), [
                'code' => $section->code,
                'name' => 'Updated Section',
                'description' => 'Updated',
                'display_order' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.expense-structure.subsections.update', $subsection), [
                'parent_id' => null,
                'code' => $subsection->code,
                'name' => 'Updated Subsection',
                'description' => 'Updated',
                'default_pattern_id' => $pattern->id,
                'display_order' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.expense-patterns.fields.update', [$pattern, 'yearly_total']), [
                'default_label' => 'Yearly Total',
                'data_type' => 'number',
                'display_order' => 1,
                'is_required' => '1',
                'is_calculated' => '0',
                'is_active' => '1',
                'include_in_formula' => '0',
                'default_value' => '0',
            ])
            ->assertRedirect();

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.course-credits.update', $courseCredit), [
                'degree_program_id' => $program->id,
                'course_credit_unit' => 36,
                'gov_doc_id' => 'DOC-CC',
                'start_year' => 2027,
            ])
            ->assertRedirect(route('head_of_finance.settings.course-credits.index'));

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.credit-unit-price.update', $creditPrice), [
                'level' => 'bachelor',
                'credit_unit_price' => 40000,
                'gov_doc_id' => 'DOC-CUP',
                'start_year' => 2027,
            ])
            ->assertRedirect(route('head_of_finance.settings.course-credits.index'));

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.nuol-pct.update', $nuolPct), [
                'level' => 'bachelor',
                'percentage' => 18,
                'gov_doc_id' => 'DOC-NUOL',
                'start_year' => 2027,
            ])
            ->assertRedirect(route('head_of_finance.settings.course-credits.index'));

        $this->actingAs($this->financeHead)
            ->patch(route('head_of_finance.settings.registration-fee.update', $registrationFee), [
                'section_type' => 'year1',
                'gov_doc_id' => 'DOC-FEE',
                'start_year' => 2027,
                'items' => [
                    ['name' => 'New Fee', 'amount' => 200, 'nuol_pct' => 15],
                ],
            ])
            ->assertRedirect(route('head_of_finance.settings.registration-fee.index'));

        $this->assertSame('Updated Section', $section->refresh()->name);
        $this->assertSame('Updated Subsection', $subsection->refresh()->name);
        $this->assertSame(36, (int) $courseCredit->refresh()->course_credit_unit);
        $this->assertSame('40000.00', $creditPrice->refresh()->credit_unit_price);
        $this->assertSame('0.1800', $nuolPct->refresh()->percentage);
        $this->assertDatabaseHas('registration_fee_items', [
            'fee_setting_id' => $registrationFee->id,
            'name' => 'New Fee',
            'amount' => 200,
        ]);
        $this->assertSame($year->id, $section->planning_year_id);
    }

    public function test_account_link_page_uses_selected_planning_year_rows(): void
    {
        [$targetYear, $account, $pattern] = $this->seedExpenseStructure();
        $targetYear->update(['is_active' => false]);

        $activeYear = PlanningYear::create([
            'year' => 2028,
            'name' => 'Planning 2028',
            'is_active' => true,
            'status' => PlanningYear::STATUS_DRAFT,
        ]);
        $activeSection = ExpenseSection::create([
            'planning_year_id' => $activeYear->id,
            'code' => '7.21',
            'name' => 'Active Section',
            'description' => null,
            'display_order' => 1,
            'is_active' => true,
        ]);
        $activeSubsection = ExpenseSubsection::create([
            'section_id' => $activeSection->id,
            'code' => '7.21.1',
            'name' => 'Active Subsection',
            'description' => null,
            'default_pattern_id' => $pattern->id,
            'display_order' => 1,
            'is_active' => true,
        ]);
        ExpenseCatalogItem::create([
            'subsection_id' => $activeSubsection->id,
            'item_name' => 'Active Year Only Item',
            'chart_of_account_id' => $account->id,
            'pattern_id' => $pattern->id,
            'default_values' => [],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.settings.expense-default-rows.accounts.index', [
                'planning_year_id' => $targetYear->id,
            ]))
            ->assertOk()
            ->assertSee('Smoke Catalog Item')
            ->assertDontSee('Active Year Only Item');
    }

    public function test_expense_setup_account_link_card_lists_each_planning_year(): void
    {
        [$firstYear, $account, $pattern] = $this->seedExpenseStructure();
        $secondYear = PlanningYear::create([
            'year' => 2028,
            'name' => 'Planning 2028',
            'is_active' => true,
            'status' => PlanningYear::STATUS_DRAFT,
        ]);
        $secondSection = ExpenseSection::create([
            'planning_year_id' => $secondYear->id,
            'code' => '7.21',
            'name' => 'Second Section',
            'description' => null,
            'display_order' => 1,
            'is_active' => true,
        ]);
        $secondSubsection = ExpenseSubsection::create([
            'section_id' => $secondSection->id,
            'code' => '7.21.1',
            'name' => 'Second Subsection',
            'description' => null,
            'default_pattern_id' => $pattern->id,
            'display_order' => 1,
            'is_active' => true,
        ]);
        ExpenseCatalogItem::create([
            'subsection_id' => $secondSubsection->id,
            'item_name' => 'Second Year Item',
            'chart_of_account_id' => $account->id,
            'pattern_id' => $pattern->id,
            'default_values' => [],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->financeHead)
            ->get(route('head_of_finance.settings.expense-setup.index'))
            ->assertOk()
            ->assertSee('2027')
            ->assertSee('2028')
            ->assertSee(route('head_of_finance.settings.expense-default-rows.accounts.index', [
                'planning_year_id' => $firstYear->id,
            ]), false)
            ->assertSee(route('head_of_finance.settings.expense-default-rows.accounts.index', [
                'planning_year_id' => $secondYear->id,
            ]), false);
    }

    private function seedExpenseStructure(): array
    {
        $year = PlanningYear::create([
            'year' => 2027,
            'name' => 'Planning 2027',
            'is_active' => true,
            'status' => PlanningYear::STATUS_DRAFT,
        ]);
        $account = ChartOfAccount::create([
            'account_code' => '62109999',
            'account_name' => 'Smoke Account',
        ]);
        $pattern = ExpensePattern::create([
            'key' => 'smoke_total',
            'name' => 'Smoke Total',
            'description' => null,
            'fields_schema' => [[
                'field_key' => 'yearly_total',
                'default_label' => 'Yearly Total',
                'data_type' => 'number',
                'display_order' => 1,
                'is_required' => true,
                'is_calculated' => false,
                'is_active' => true,
                'default_value' => '0',
            ]],
            'formula_schema' => ['operation' => 'multiply', 'fields' => []],
            'is_active' => true,
        ]);
        $section = ExpenseSection::create([
            'planning_year_id' => $year->id,
            'code' => '6.21',
            'name' => 'Smoke Section',
            'description' => null,
            'display_order' => 1,
            'is_active' => true,
        ]);
        $subsection = ExpenseSubsection::create([
            'section_id' => $section->id,
            'code' => '6.21.1',
            'name' => 'Smoke Subsection',
            'description' => null,
            'default_pattern_id' => $pattern->id,
            'display_order' => 1,
            'is_active' => true,
        ]);
        $catalogItem = ExpenseCatalogItem::create([
            'subsection_id' => $subsection->id,
            'item_name' => 'Smoke Catalog Item',
            'chart_of_account_id' => $account->id,
            'pattern_id' => $pattern->id,
            'default_values' => [],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [$year, $account, $pattern, $section, $subsection, $catalogItem];
    }
}
