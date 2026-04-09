<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('budget_plan_reviewers')) {
            Schema::create('budget_plan_reviewers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('budget_plan_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('assigned_by');
                $table->timestamps();

                $table->foreign('budget_plan_id')->references('id')->on('budget_plans')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['budget_plan_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_plan_reviewers');
    }
};
