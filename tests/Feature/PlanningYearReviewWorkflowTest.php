<?php

namespace Tests\Feature;

use App\Models\PlanningYear;
use App\Models\PlanningYearReviewComment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlanningYearReviewWorkflowTest extends TestCase
{
    protected User $financeHead;

    protected User $reviewer;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->seedUsers();
    }

    public function test_finance_head_can_request_review_without_sending_notifications(): void
    {
        Notification::fake();

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.request-review', 1), [
                'reviewer_ids' => [$this->reviewer->id],
                'note' => 'Please review totals',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('planning_years', [
            'id' => 1,
            'status' => PlanningYear::STATUS_PENDING_REVIEW,
        ]);
        $this->assertDatabaseHas('planning_year_review_rounds', [
            'planning_year_id' => 1,
            'requested_by' => $this->financeHead->id,
            'round_number' => 1,
            'note' => 'Please review totals',
        ]);
        $this->assertSame(
            [$this->reviewer->id],
            json_decode((string) DB::table('planning_year_review_rounds')->where('id', 1)->value('reviewer_user_ids'), true)
        );

        Notification::assertNothingSent();
    }

    public function test_pending_review_plan_is_not_editable_until_review_is_closed(): void
    {
        $planningYear = PlanningYear::findOrFail(1);
        $this->assertTrue($planningYear->canBeEdited());

        $this->createPendingReview();
        $this->assertFalse($planningYear->fresh()->canBeEdited());

        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_MODIFYING,
            'review_closed_at' => now(),
        ]);

        $this->assertTrue($planningYear->fresh()->canBeEdited());
    }

    public function test_finance_head_can_save_plan_and_lock_editing(): void
    {
        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.save', 1))
            ->assertRedirect();

        $planningYear = PlanningYear::findOrFail(1);

        $this->assertSame(PlanningYear::STATUS_SAVED, $planningYear->status);
        $this->assertFalse($planningYear->canBeEdited());
    }

    public function test_only_selected_current_reviewer_can_comment_while_pending(): void
    {
        $this->createPendingReview();

        $this->actingAs($this->otherUser)
            ->post(route('reviews.planning-years.comments.store', 1), [
                'comment' => 'Not selected',
            ])
            ->assertForbidden();

        $this->actingAs($this->reviewer)
            ->post(route('reviews.planning-years.comments.store', 1), [
                'comment' => 'Budget looks reasonable.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('planning_year_review_comments', [
            'planning_year_id' => 1,
            'user_id' => $this->reviewer->id,
            'comment' => 'Budget looks reasonable.',
        ]);
    }

    public function test_reviewer_can_toggle_agreement_once_and_cannot_agree_with_own_comment(): void
    {
        $roundId = $this->createPendingReview([$this->reviewer->id, $this->otherUser->id]);
        $comment = PlanningYearReviewComment::create([
            'planning_year_review_round_id' => $roundId,
            'planning_year_id' => 1,
            'user_id' => $this->reviewer->id,
            'comment' => 'Please confirm salary rows.',
        ]);

        $this->actingAs($this->reviewer)
            ->post(route('reviews.planning-years.comments.agreement', [1, $comment]))
            ->assertForbidden();

        $this->actingAs($this->otherUser)
            ->post(route('reviews.planning-years.comments.agreement', [1, $comment]))
            ->assertRedirect();

        $this->assertSame([$this->otherUser->id], $comment->fresh()->agreementIds());

        $this->actingAs($this->otherUser)
            ->post(route('reviews.planning-years.comments.agreement', [1, $comment]))
            ->assertRedirect();

        $this->assertSame([], $comment->fresh()->agreementIds());
    }

    public function test_closing_review_moves_plan_to_modifying_and_blocks_new_comments(): void
    {
        $this->createPendingReview();

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.close-review', 1))
            ->assertRedirect();

        $this->assertDatabaseHas('planning_years', [
            'id' => 1,
            'status' => PlanningYear::STATUS_MODIFYING,
        ]);
        $this->assertNotNull(DB::table('planning_year_review_rounds')->where('id', 1)->value('closed_at'));

        $this->actingAs($this->reviewer)
            ->post(route('reviews.planning-years.comments.store', 1), [
                'comment' => 'Too late',
            ])
            ->assertForbidden();
    }

    public function test_modifying_plan_can_be_sent_to_review_again_as_new_round(): void
    {
        Notification::fake();
        $this->createPendingReview();

        PlanningYear::query()->whereKey(1)->update([
            'status' => PlanningYear::STATUS_MODIFYING,
            'review_closed_at' => now(),
        ]);
        DB::table('planning_year_review_rounds')->where('id', 1)->update([
            'closed_by' => $this->financeHead->id,
            'closed_at' => now(),
        ]);

        $this->actingAs($this->financeHead)
            ->post(route('head_of_finance.manage-plan.request-review', 1), [
                'reviewer_ids' => [$this->otherUser->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('planning_year_review_rounds', [
            'planning_year_id' => 1,
            'round_number' => 2,
        ]);
        $this->assertDatabaseHas('planning_years', [
            'id' => 1,
            'status' => PlanningYear::STATUS_PENDING_REVIEW,
        ]);
    }

    public function test_review_inbox_shows_latest_round_once_per_plan(): void
    {
        $this->withoutVite();

        $firstRoundId = $this->createPendingReview();
        DB::table('planning_year_review_rounds')->where('id', $firstRoundId)->update([
            'closed_by' => $this->financeHead->id,
            'closed_at' => now(),
        ]);

        $secondRoundId = DB::table('planning_year_review_rounds')->insertGetId([
            'planning_year_id' => 1,
            'requested_by' => $this->financeHead->id,
            'round_number' => 2,
            'reviewer_user_ids' => json_encode([$this->reviewer->id]),
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('planning_years')->where('id', 1)->update([
            'status' => PlanningYear::STATUS_PENDING_REVIEW,
            'current_review_round_id' => $secondRoundId,
            'review_requested_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->reviewer)
            ->get(route('reviews.planning-years.index'))
            ->assertOk()
            ->assertSee('Round 2')
            ->assertDontSee('Round 1');

        $assignments = $response->viewData('assignments');
        $this->assertSame(1, $assignments->count());
        $this->assertSame(2, (int) $assignments->first()->reviewRound->round_number);
    }

    public function test_review_inbox_labels_modifying_plan_as_being_edited(): void
    {
        $this->withoutVite();
        $roundId = $this->createPendingReview();

        DB::table('planning_year_review_rounds')->where('id', $roundId)->update([
            'closed_by' => $this->financeHead->id,
            'closed_at' => now(),
        ]);
        DB::table('planning_years')->where('id', 1)->update([
            'status' => PlanningYear::STATUS_MODIFYING,
            'review_closed_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->reviewer)
            ->get(route('reviews.planning-years.index'))
            ->assertOk()
            ->assertSee('ກຳລັງແກ້ໄຂ')
            ->assertDontSee('ປິດຮອບແລ້ວ');
    }

    public function test_finance_head_can_delete_pending_review_plan(): void
    {
        $roundId = $this->createPendingReview();
        $commentId = DB::table('planning_year_review_comments')->insertGetId([
            'planning_year_review_round_id' => $roundId,
            'planning_year_id' => 1,
            'user_id' => $this->reviewer->id,
            'comment' => 'Please adjust totals.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('planning_year_review_comments')
            ->where('id', $commentId)
            ->update(['agreement_user_ids' => json_encode([$this->otherUser->id])]);
        $this->seedPlanningYearChildren();

        $this->actingAs($this->financeHead)
            ->delete(route('head_of_finance.manage-plan.destroy', 1))
            ->assertRedirect(route('head_of_finance.manage-plan.index'));

        $this->assertDatabaseMissing('planning_years', ['id' => 1]);
        $this->assertDatabaseMissing('planning_year_review_rounds', ['id' => $roundId]);
        $this->assertDatabaseMissing('planning_year_review_comments', ['id' => $commentId]);
        $this->assertDatabaseMissing('period_plan_overrides', ['planning_year_id' => 1]);
        $this->assertDatabaseMissing('expense_plans', ['planning_year_id' => 1]);
        $this->assertDatabaseHas('expense_sections', ['id' => 1, 'planning_year_id' => null]);
        $this->assertDatabaseHas('expense_subsections', ['id' => 1, 'section_id' => 1]);
        $this->assertDatabaseHas('expense_catalog_items', ['id' => 1, 'subsection_id' => 1, 'chart_of_account_id' => 123]);
    }

    private function createPendingReview(array $reviewerIds = []): int
    {
        $reviewerIds = $reviewerIds ?: [$this->reviewer->id];

        $roundId = DB::table('planning_year_review_rounds')->insertGetId([
            'planning_year_id' => 1,
            'requested_by' => $this->financeHead->id,
            'round_number' => 1,
            'reviewer_user_ids' => json_encode(array_values($reviewerIds)),
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('planning_years')->where('id', 1)->update([
            'status' => PlanningYear::STATUS_PENDING_REVIEW,
            'current_review_round_id' => $roundId,
            'review_requested_at' => now(),
            'updated_at' => now(),
        ]);

        return $roundId;
    }

    private function seedPlanningYearChildren(): void
    {
        DB::table('academic_income_plans')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'fiscal_year' => 2027,
            'created_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('academic_income_items')->insert([
            'id' => 1,
            'plan_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('salary_plans')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'fiscal_year' => 2027,
            'month' => 1,
            'created_by' => $this->financeHead->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('salary_entries')->insert([
            'id' => 1,
            'plan_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expense_sections')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expense_subsections')->insert([
            'id' => 1,
            'section_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expense_catalog_items')->insert([
            'id' => 1,
            'subsection_id' => 1,
            'item_name' => 'Linked default row',
            'chart_of_account_id' => 123,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expense_plans')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'section_id' => 1,
            'subsection_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expense_plan_values')->insert([
            'id' => 1,
            'expense_plan_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('period_plan_overrides')->insert([
            'id' => 1,
            'planning_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedUsers(): void
    {
        $financeRole = Role::create(['id' => 1, 'role_name' => 'head_of_finance']);
        $reviewerRole = Role::create(['id' => 2, 'role_name' => 'accountant']);

        $this->financeHead = User::create([
            'id' => 1,
            'username' => 'finance',
            'password' => 'password',
            'full_name' => 'Finance Head',
            'role_id' => $financeRole->id,
            'is_active' => true,
        ]);
        $this->reviewer = User::create([
            'id' => 2,
            'username' => 'reviewer',
            'password' => 'password',
            'full_name' => 'Reviewer One',
            'role_id' => $reviewerRole->id,
            'is_active' => true,
        ]);
        $this->otherUser = User::create([
            'id' => 3,
            'username' => 'other',
            'password' => 'password',
            'full_name' => 'Other Reviewer',
            'role_id' => $reviewerRole->id,
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

    private function createTables(): void
    {
        foreach ([
            'period_plan_overrides',
            'expense_plan_values',
            'expense_plans',
            'expense_catalog_items',
            'expense_subsections',
            'expense_sections',
            'salary_entries',
            'salary_plans',
            'academic_income_items',
            'academic_income_plans',
            'planning_year_review_comments',
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
            $table->timestamps();
        });

        Schema::create('planning_year_review_rounds', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('closed_by')->nullable();
            $table->unsignedInteger('round_number');
            $table->text('note')->nullable();
            $table->json('reviewer_user_ids')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('planning_year_review_comments', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_review_round_id');
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('user_id');
            $table->text('comment');
            $table->json('agreement_user_ids')->nullable();
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
            $table->timestamps();
        });

        Schema::create('salary_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('created_by');
            $table->timestamps();
        });

        Schema::create('salary_entries', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->timestamps();
        });

        Schema::create('expense_sections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_subsections', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_catalog_items', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('subsection_id');
            $table->string('item_name')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_plans', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_plan_values', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('expense_plan_id');
            $table->timestamps();
        });

        Schema::create('period_plan_overrides', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_id');
            $table->timestamps();
        });
    }
}
