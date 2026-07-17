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

        $this->assertNull(DB::table('expense_catalog_items')->where('id', 1)->value('chart_of_account_id'));
        $this->assertNull(DB::table('expense_plan_rows')->where('id', 1)->value('chart_of_account_id'));
    }

    public function test_sync_fills_missing_links_and_preserves_user_customized_links(): void
    {
        $this->seedRows();

        $this->artisan('expense:sync-account-links')->assertExitCode(0);

        $this->assertSame(1, (int) DB::table('expense_catalog_items')->where('id', 1)->value('chart_of_account_id'));
        $this->assertSame(2, (int) DB::table('expense_catalog_items')->where('id', 2)->value('chart_of_account_id'));
        $this->assertSame(1, (int) DB::table('expense_plan_rows')->where('id', 1)->value('chart_of_account_id'));
    }

    private function seedRows(): void
    {
        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'account_code' => '61400500', 'account_name' => 'Teaching overtime', 'parent_id' => null],
            ['id' => 2, 'account_code' => '62100100', 'account_name' => 'Fuel', 'parent_id' => null],
        ]);

        DB::table('expense_sections')->insert(['id' => 1, 'planning_year_id' => 1, 'code' => '2.5', 'name' => 'Teaching']);
        DB::table('expense_subsections')->insert(['id' => 1, 'section_id' => 1, 'code' => '2.5.1', 'name' => 'Teaching']);
        DB::table('expense_catalog_items')->insert([
            [
                'id' => 1,
                'subsection_id' => 1,
                'item_name' => 'Special teaching',
                'chart_of_account_id' => null,
                'pattern_id' => null,
                'sort_order' => 1,
                'default_values' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'subsection_id' => 1,
                'item_name' => 'User customized row',
                'chart_of_account_id' => 2,
                'pattern_id' => null,
                'sort_order' => 2,
                'default_values' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('expense_plan_rows')->insert([
            ['id' => 1, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'catalog_item_id' => 1, 'chart_of_account_id' => null, 'item_name' => 'Special teaching', 'plan_detail' => 'Special teaching'],
            ['id' => 2, 'planning_year_id' => 1, 'section_id' => 1, 'subsection_id' => 1, 'catalog_item_id' => 2, 'chart_of_account_id' => 2, 'item_name' => 'User customized row', 'plan_detail' => 'User customized row'],
        ]);
    }

    private function createTables(): void
    {
        foreach ([
            'expense_plan_rows',
            'expense_catalog_items',
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

        Schema::create('expense_catalog_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('subsection_id');
            $table->string('item_name');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->json('default_values')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_plan_rows', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('catalog_item_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('plan_detail');
            $table->timestamps();
        });
    }
}
