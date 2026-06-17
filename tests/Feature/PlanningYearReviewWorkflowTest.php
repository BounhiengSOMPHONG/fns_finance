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
        $this->assertDatabaseHas('planning_year_reviewers', [
            'user_id' => $this->reviewer->id,
            'notified_at' => null,
        ]);

        Notification::assertNothingSent();
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

        $this->assertDatabaseHas('planning_year_review_comment_agreements', [
            'planning_year_review_comment_id' => $comment->id,
            'user_id' => $this->otherUser->id,
        ]);

        $this->actingAs($this->otherUser)
            ->post(route('reviews.planning-years.comments.agreement', [1, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('planning_year_review_comment_agreements', [
            'planning_year_review_comment_id' => $comment->id,
            'user_id' => $this->otherUser->id,
        ]);
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

    private function createPendingReview(array $reviewerIds = []): int
    {
        $reviewerIds = $reviewerIds ?: [$this->reviewer->id];

        $roundId = DB::table('planning_year_review_rounds')->insertGetId([
            'planning_year_id' => 1,
            'requested_by' => $this->financeHead->id,
            'round_number' => 1,
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($reviewerIds as $reviewerId) {
            DB::table('planning_year_reviewers')->insert([
                'planning_year_review_round_id' => $roundId,
                'user_id' => $reviewerId,
                'notified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('planning_years')->where('id', 1)->update([
            'status' => PlanningYear::STATUS_PENDING_REVIEW,
            'current_review_round_id' => $roundId,
            'review_requested_at' => now(),
            'updated_at' => now(),
        ]);

        return $roundId;
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
            'planning_year_review_comment_agreements',
            'planning_year_review_comments',
            'planning_year_reviewers',
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

        Schema::create('planning_year_review_comments', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_review_round_id');
            $table->unsignedBigInteger('planning_year_id');
            $table->unsignedInteger('user_id');
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('planning_year_review_comment_agreements', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('planning_year_review_comment_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->unique(['planning_year_review_comment_id', 'user_id']);
        });
    }
}
