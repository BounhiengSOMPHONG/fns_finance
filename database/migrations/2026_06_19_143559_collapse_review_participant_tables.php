<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('planning_year_review_rounds')
            && ! Schema::hasColumn('planning_year_review_rounds', 'reviewer_user_ids')) {
            Schema::table('planning_year_review_rounds', function (Blueprint $table): void {
                $table->json('reviewer_user_ids')->nullable()->after('note');
            });
        }

        if (Schema::hasTable('planning_year_review_comments')
            && ! Schema::hasColumn('planning_year_review_comments', 'agreement_user_ids')) {
            Schema::table('planning_year_review_comments', function (Blueprint $table): void {
                $table->json('agreement_user_ids')->nullable()->after('comment');
            });
        }

        if (Schema::hasTable('planning_year_review_rounds')
            && Schema::hasTable('planning_year_reviewers')
            && Schema::hasColumn('planning_year_review_rounds', 'reviewer_user_ids')) {
            $reviewerIdsByRound = DB::table('planning_year_reviewers')
                ->select('planning_year_review_round_id', 'user_id')
                ->orderBy('id')
                ->get()
                ->groupBy('planning_year_review_round_id')
                ->map(fn ($reviewers) => $reviewers
                    ->pluck('user_id')
                    ->map(fn ($id): int => (int) $id)
                    ->unique()
                    ->values()
                    ->all());

            DB::table('planning_year_review_rounds')
                ->select('id')
                ->orderBy('id')
                ->get()
                ->each(function ($round) use ($reviewerIdsByRound): void {
                    DB::table('planning_year_review_rounds')
                        ->where('id', $round->id)
                        ->update([
                            'reviewer_user_ids' => json_encode($reviewerIdsByRound->get($round->id, [])),
                        ]);
                });
        }

        if (Schema::hasTable('planning_year_review_comments')
            && Schema::hasTable('planning_year_review_comment_agreements')
            && Schema::hasColumn('planning_year_review_comments', 'agreement_user_ids')) {
            $agreementIdsByComment = DB::table('planning_year_review_comment_agreements')
                ->select('planning_year_review_comment_id', 'user_id')
                ->orderBy('id')
                ->get()
                ->groupBy('planning_year_review_comment_id')
                ->map(fn ($agreements) => $agreements
                    ->pluck('user_id')
                    ->map(fn ($id): int => (int) $id)
                    ->unique()
                    ->values()
                    ->all());

            DB::table('planning_year_review_comments')
                ->select('id')
                ->orderBy('id')
                ->get()
                ->each(function ($comment) use ($agreementIdsByComment): void {
                    DB::table('planning_year_review_comments')
                        ->where('id', $comment->id)
                        ->update([
                            'agreement_user_ids' => json_encode($agreementIdsByComment->get($comment->id, [])),
                        ]);
                });
        }

        Schema::dropIfExists('planning_year_review_comment_agreements');
        Schema::dropIfExists('planning_year_reviewers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('planning_year_reviewers')) {
            Schema::create('planning_year_reviewers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('planning_year_review_round_id')->constrained('planning_year_review_rounds')->cascadeOnDelete();
                $table->integer('user_id');
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['planning_year_review_round_id', 'user_id'], 'planning_year_reviewers_round_user_unique');
                $table->index('user_id');
            });
        }

        if (Schema::hasTable('planning_year_review_rounds')
            && Schema::hasTable('planning_year_reviewers')
            && Schema::hasColumn('planning_year_review_rounds', 'reviewer_user_ids')) {
            DB::table('planning_year_review_rounds')
                ->select('id', 'reviewer_user_ids', 'created_at', 'updated_at')
                ->orderBy('id')
                ->get()
                ->each(function ($round): void {
                    foreach (json_decode((string) $round->reviewer_user_ids, true) ?: [] as $userId) {
                        DB::table('planning_year_reviewers')->updateOrInsert(
                            [
                                'planning_year_review_round_id' => $round->id,
                                'user_id' => (int) $userId,
                            ],
                            [
                                'notified_at' => null,
                                'created_at' => $round->created_at,
                                'updated_at' => $round->updated_at,
                            ]
                        );
                    }
                });
        }

        if (! Schema::hasTable('planning_year_review_comment_agreements')) {
            Schema::create('planning_year_review_comment_agreements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('planning_year_review_comment_id');
                $table->integer('user_id');
                $table->timestamps();

                $table->foreign('planning_year_review_comment_id', 'py_review_agreements_comment_fk')
                    ->references('id')
                    ->on('planning_year_review_comments')
                    ->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['planning_year_review_comment_id', 'user_id'], 'planning_year_review_agreements_comment_user_unique');
            });
        }

        if (Schema::hasTable('planning_year_review_comments')
            && Schema::hasTable('planning_year_review_comment_agreements')
            && Schema::hasColumn('planning_year_review_comments', 'agreement_user_ids')) {
            DB::table('planning_year_review_comments')
                ->select('id', 'agreement_user_ids', 'created_at', 'updated_at')
                ->orderBy('id')
                ->get()
                ->each(function ($comment): void {
                    foreach (json_decode((string) $comment->agreement_user_ids, true) ?: [] as $userId) {
                        DB::table('planning_year_review_comment_agreements')->updateOrInsert(
                            [
                                'planning_year_review_comment_id' => $comment->id,
                                'user_id' => (int) $userId,
                            ],
                            [
                                'created_at' => $comment->created_at,
                                'updated_at' => $comment->updated_at,
                            ]
                        );
                    }
                });
        }

        if (Schema::hasTable('planning_year_review_comments')
            && Schema::hasColumn('planning_year_review_comments', 'agreement_user_ids')) {
            Schema::table('planning_year_review_comments', function (Blueprint $table): void {
                $table->dropColumn('agreement_user_ids');
            });
        }

        if (Schema::hasTable('planning_year_review_rounds')
            && Schema::hasColumn('planning_year_review_rounds', 'reviewer_user_ids')) {
            Schema::table('planning_year_review_rounds', function (Blueprint $table): void {
                $table->dropColumn('reviewer_user_ids');
            });
        }
    }
};
