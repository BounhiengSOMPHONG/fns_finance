<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('role_name', 50)->unique();
            });
        }

        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('department_name', 100)->unique();
                $table->string('department_type', 50)->nullable();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('username', 50)->unique();
                $table->string('password');
                $table->string('full_name', 100);
                $table->unsignedInteger('role_id')->nullable();
                $table->unsignedInteger('department_id')->nullable();
                $table->boolean('is_active')->default(true);

                $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('account_code', 20)->unique();
                $table->string('account_name');
                $table->unsignedInteger('parent_id')->nullable();

                $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('users');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('roles');
    }
};
