<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_years', function (Blueprint $table): void {
            if (! Schema::hasColumn('planning_years', 'status')) {
                $table->string('status', 30)->default('DRAFT')->after('is_active')->index();
            }

            if (! Schema::hasColumn('planning_years', 'current_review_round_id')) {
                $table->unsignedBigInteger('current_review_round_id')->nullable()->after('status')->index();
            }

            if (! Schema::hasColumn('planning_years', 'review_requested_at')) {
                $table->timestamp('review_requested_at')->nullable()->after('current_review_round_id');
            }

            if (! Schema::hasColumn('planning_years', 'review_closed_at')) {
                $table->timestamp('review_closed_at')->nullable()->after('review_requested_at');
            }
        });

        if (! Schema::hasTable('planning_year_review_rounds')) {
            Schema::create('planning_year_review_rounds', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('planning_year_id')->constrained('planning_years')->cascadeOnDelete();
                $table->integer('requested_by');
                $table->integer('closed_by')->nullable();
                $table->unsignedInteger('round_number');
                $table->text('note')->nullable();
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
                $table->unique(['planning_year_id', 'round_number']);
            });
        }

        if (Schema::hasTable('planning_year_review_rounds')) {
            Schema::table('planning_years', function (Blueprint $table): void {
                $table->foreign('current_review_round_id')
                    ->references('id')
                    ->on('planning_year_review_rounds')
                    ->nullOnDelete();
            });
        }

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

        if (! Schema::hasTable('planning_year_review_comments')) {
            Schema::create('planning_year_review_comments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('planning_year_review_round_id');
                $table->unsignedBigInteger('planning_year_id');
                $table->integer('user_id');
                $table->text('comment');
                $table->timestamps();

                $table->foreign('planning_year_review_round_id', 'py_review_comments_round_fk')
                    ->references('id')
                    ->on('planning_year_review_rounds')
                    ->cascadeOnDelete();
                $table->foreign('planning_year_id', 'py_review_comments_year_fk')
                    ->references('id')
                    ->on('planning_years')
                    ->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['planning_year_review_round_id', 'created_at'], 'planning_year_review_comments_round_created_index');
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

    }

    public function down(): void
    {
        Schema::dropIfExists('planning_year_review_comment_agreements');
        Schema::dropIfExists('planning_year_review_comments');
        Schema::dropIfExists('planning_year_reviewers');

        Schema::table('planning_years', function (Blueprint $table): void {
            if (Schema::hasColumn('planning_years', 'current_review_round_id')) {
                $table->dropForeign(['current_review_round_id']);
            }
        });

        Schema::dropIfExists('planning_year_review_rounds');

        Schema::table('planning_years', function (Blueprint $table): void {
            foreach (['review_closed_at', 'review_requested_at', 'current_review_round_id', 'status'] as $column) {
                if (Schema::hasColumn('planning_years', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
