<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncExpenseAccountLinksCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    public function test_dry_run_does_not_write_account_links(): void
    {
        $this->seedRows();

        $this->artisan('expense:sync-account-links --dry-run')
            ->expectsOutputToContain('DRY RUN')
            ->expectsOutputToContain('Account link changes: 1')
            ->assertExitCode(0);

        $this->assertNull(DB::table('expense_subsection_default_rows')->where('id', 1)->value('chart_of_account_id'));
        $this->assertNull(DB::table('expense_plan_values')->where('field_key', 'reference')->value('value_text'));
    }

    public function test_sync_fills_missing_links_and_preserves_user_customized_links(): void
    {
        $this->seedRows();

        $this->artisan('expense:sync-account-links')->assertExitCode(0);

        $this->assertSame(1, (int) DB::table('expense_subsection_default_rows')->where('id', 1)->value('chart_of_account_id'));
        $this->assertSame(2, (int) DB::table('expense_subsection_default_rows')->where('id', 2)->value('chart_of_account_id'));
        $this->assertSame('61400500', DB::table('expense_plan_values')->where('expense_plan_id', 1)->where('field_key', 'reference')->value('value_text'));
    }

    private function seedRows(): void
    {
        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'account_code' => '61400500', 'account_name' => 'Teaching overtime', 'parent_id' => null],
            ['id' => 2, 'account_code' => '62100100', 'account_name' => 'Fuel', 'parent_id' => null],
        ]);

        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.5', 'name' => 'Teaching']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.5.1', 'name' => 'Teaching']);
        DB::table('expense_subsection_default_rows')->insert([
            [
                'id' => 1,
                'subsection_code' => '2.5.1',
                'item_name' => 'Special teaching',
                'chart_of_account_id' => null,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'subsection_code' => '2.5.1',
                'item_name' => 'User customized row',
                'chart_of_account_id' => 2,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('expense_plans')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'Special teaching'],
            ['id' => 2, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'plan_detail' => 'User customized row'],
        ]);
        DB::table('expense_plan_values')->insert([
            ['expense_plan_id' => 1, 'field_key' => 'reference', 'value_text' => null],
            ['expense_plan_id' => 2, 'field_key' => 'reference', 'value_text' => '62100100'],
        ]);
    }

    private function createTables(): void
    {
        foreach ([
            'expense_plan_values',
            'expense_plans',
            'expense_subsection_default_rows',
            'expense_subsections',
            'expense_sections',
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

        Schema::create('expense_sections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->string('code', 30);
            $table->string('name');
        });

        Schema::create('expense_subsections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('section_id');
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
            $table->timestamps();
        });
    }
}
