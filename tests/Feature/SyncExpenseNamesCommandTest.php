<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncExpenseNamesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createExpenseSyncTables();
    }

    public function test_dry_run_reports_changes_without_writing(): void
    {
        DB::table('expense_sections')->insert([
            'id' => 1,
            'planning_year_id' => 13,
            'code' => '2.1',
            'name' => 'ກຸ່ມລາຍຈ່າຍ 2.1',
            'updated_at' => now(),
        ]);

        $this->artisan('expense:sync-names --dry-run')
            ->expectsOutputToContain('DRY RUN')
            ->expectsOutputToContain('Sections: 1')
            ->assertExitCode(0);

        $this->assertSame('ກຸ່ມລາຍຈ່າຍ 2.1', DB::table('expense_sections')->value('name'));
    }

    public function test_syncs_section_and_subsection_names_by_code(): void
    {
        DB::table('expense_sections')->insert([
            'id' => 1,
            'planning_year_id' => 13,
            'code' => '2.1',
            'name' => 'ກຸ່ມລາຍຈ່າຍ 2.1',
            'updated_at' => now(),
        ]);

        DB::table('expense_subsections')->insert([
            'id' => 10,
            'section_id' => 1,
            'code' => '2.1.1',
            'name' => 'ລາຍການ 2.1.1',
            'display_order' => 1,
            'updated_at' => now(),
        ]);

        $this->artisan('expense:sync-names')->assertExitCode(0);

        $this->assertSame(
            'ແຜນງົບປະມານລາຍຈ່າຍບໍລິຫານປົກກະຕິຂອງ ຄວທ',
            DB::table('expense_sections')->where('id', 1)->value('name')
        );
        $this->assertSame(
            'ບໍລິຫານສັງລວມ',
            DB::table('expense_subsections')->where('id', 10)->value('name')
        );
    }

    public function test_syncs_subsection_display_order_by_numeric_code(): void
    {
        DB::table('expense_sections')->insert([
            'id' => 1,
            'planning_year_id' => 13,
            'code' => '2.1',
            'name' => '2.1',
            'updated_at' => now(),
        ]);

        DB::table('expense_subsections')->insert([
            ['id' => 10, 'section_id' => 1, 'code' => '2.1.1', 'name' => '2.1.1', 'display_order' => 1, 'updated_at' => now()],
            ['id' => 11, 'section_id' => 1, 'code' => '2.1.10', 'name' => '2.1.10', 'display_order' => 2, 'updated_at' => now()],
            ['id' => 12, 'section_id' => 1, 'code' => '2.1.2', 'name' => '2.1.2', 'display_order' => 3, 'updated_at' => now()],
        ]);

        $this->artisan('expense:sync-names --dry-run')
            ->expectsOutputToContain('Subsection display orders: 2')
            ->assertExitCode(0);

        $this->assertSame(2, (int) DB::table('expense_subsections')->where('code', '2.1.10')->value('display_order'));

        $this->artisan('expense:sync-names')->assertExitCode(0);

        $this->assertSame(1, (int) DB::table('expense_subsections')->where('code', '2.1.1')->value('display_order'));
        $this->assertSame(2, (int) DB::table('expense_subsections')->where('code', '2.1.2')->value('display_order'));
        $this->assertSame(3, (int) DB::table('expense_subsections')->where('code', '2.1.10')->value('display_order'));
    }

    public function test_syncs_known_default_row_typo_and_matching_plan_values_only(): void
    {
        DB::table('expense_sections')->insert([
            'id' => 1,
            'planning_year_id' => 13,
            'code' => '2.2',
            'name' => '2.2',
            'updated_at' => now(),
        ]);

        DB::table('expense_subsections')->insert([
            'id' => 20,
            'section_id' => 1,
            'code' => '2.2.4',
            'name' => '2.2.4',
            'display_order' => 1,
            'updated_at' => now(),
        ]);

        DB::table('expense_catalog_items')->insert([
            'id' => 30,
            'subsection_id' => 20,
            'item_name' => 'ອູປະກອນທົດລອງຟິຊິກສາດ',
            'sort_order' => 1,
            'chart_of_account_id' => 123,
            'pattern_id' => null,
            'default_values' => json_encode(['yearly_total' => 999]),
            'is_active' => true,
            'updated_at' => now(),
        ]);

        DB::table('expense_plan_rows')->insert([
            [
                'id' => 40,
                'planning_year_id' => 13,
                'section_id' => 1,
                'subsection_id' => 20,
                'catalog_item_id' => 30,
                'chart_of_account_id' => 123,
                'pattern_id' => null,
                'item_name' => 'ອູປະກອນທົດລອງຟິຊິກສາດ',
                'plan_detail' => 'ອູປະກອນທົດລອງຟິຊິກສາດ',
                'detail' => 'keep note',
                'calculation_values' => json_encode(['yearly_total' => 999]),
                'pattern_snapshot' => json_encode(['formula_schema' => ['operation' => 'multiply', 'fields' => []]]),
                'updated_at' => now(),
            ],
            [
                'id' => 41,
                'planning_year_id' => 13,
                'section_id' => 1,
                'subsection_id' => 20,
                'catalog_item_id' => null,
                'chart_of_account_id' => null,
                'pattern_id' => null,
                'item_name' => 'custom user text',
                'plan_detail' => 'custom user text',
                'detail' => 'keep custom note',
                'calculation_values' => json_encode(['yearly_total' => 222]),
                'pattern_snapshot' => json_encode(['formula_schema' => ['operation' => 'multiply', 'fields' => []]]),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('expense:sync-names')->assertExitCode(0);

        $this->assertSame('ອຸປະກອນທົດລອງຟິຊິກສາດ', DB::table('expense_catalog_items')->where('id', 30)->value('item_name'));
        $this->assertSame(123, DB::table('expense_catalog_items')->where('id', 30)->value('chart_of_account_id'));
        $this->assertSame(['yearly_total' => 999], json_decode(DB::table('expense_catalog_items')->where('id', 30)->value('default_values'), true));
        $this->assertSame('ອຸປະກອນທົດລອງຟິຊິກສາດ', DB::table('expense_plan_rows')->where('id', 40)->value('plan_detail'));
        $this->assertSame('ອຸປະກອນທົດລອງຟິຊິກສາດ', DB::table('expense_plan_rows')->where('id', 40)->value('item_name'));
        $this->assertSame('custom user text', DB::table('expense_plan_rows')->where('id', 41)->value('plan_detail'));
        $this->assertSame('custom user text', DB::table('expense_plan_rows')->where('id', 41)->value('item_name'));
        $this->assertSame('keep note', DB::table('expense_plan_rows')->where('id', 40)->value('detail'));
    }

    private function createExpenseSyncTables(): void
    {
        Schema::dropIfExists('expense_plan_rows');
        Schema::dropIfExists('expense_catalog_items');
        Schema::dropIfExists('expense_subsections');
        Schema::dropIfExists('expense_sections');

        Schema::create('expense_sections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->string('code', 30);
            $table->string('name');
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('expense_subsections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('code', 30);
            $table->string('name');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('expense_catalog_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('subsection_id');
            $table->string('item_name');
            $table->unsignedInteger('sort_order')->default(1);
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->json('default_values')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('expense_plan_rows', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->unsignedBigInteger('catalog_item_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('plan_detail');
            $table->text('detail')->nullable();
            $table->json('calculation_values')->nullable();
            $table->json('pattern_snapshot')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
}
