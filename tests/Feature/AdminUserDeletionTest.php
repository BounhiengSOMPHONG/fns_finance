<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminUserDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('advance_requests');
        Schema::dropIfExists('request_workflow_logs');
        Schema::dropIfExists('treasury_reconciliation_items');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        Schema::create('roles', function ($table): void {
            $table->integer('id')->primary();
            $table->string('role_name');
        });

        Schema::create('users', function ($table): void {
            $table->integer('id')->primary();
            $table->string('username');
            $table->string('password');
            $table->string('full_name');
            $table->integer('role_id');
            $table->integer('department_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('advance_requests', function ($table): void {
            $table->integer('id')->primary();
            $table->integer('requester_id');
        });

        Schema::create('request_workflow_logs', function ($table): void {
            $table->integer('id')->primary();
            $table->integer('request_id');
            $table->integer('user_id');
            $table->string('action');
        });

        Schema::create('treasury_reconciliation_items', function ($table): void {
            $table->integer('id')->primary();
            $table->integer('transaction_id');
            $table->date('reconciliation_date');
            $table->integer('user_id');
        });
    }

    public function test_admin_deletes_user_with_related_history(): void
    {
        $admin = $this->createUser(1, 'admin');
        $requester = $this->createUser(2, 'staff');

        DB::table('advance_requests')->insert([
            'id' => 1,
            'requester_id' => $requester->id,
        ]);
        DB::table('request_workflow_logs')->insert([
            'id' => 1,
            'request_id' => 1,
            'user_id' => $requester->id,
            'action' => 'created',
        ]);
        DB::table('treasury_reconciliation_items')->insert([
            'id' => 1,
            'transaction_id' => 1,
            'reconciliation_date' => '2026-06-30',
            'user_id' => $requester->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $requester))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'ລຶບຜູ້ໃຊ້ ແລະ ຂໍ້ມູນປະຫວັດສຳເລັດ');

        $this->assertDatabaseMissing('users', [
            'id' => $requester->id,
        ]);
        $this->assertDatabaseMissing('advance_requests', [
            'requester_id' => $requester->id,
        ]);
        $this->assertDatabaseMissing('request_workflow_logs', [
            'user_id' => $requester->id,
        ]);
        $this->assertDatabaseMissing('treasury_reconciliation_items', [
            'user_id' => $requester->id,
        ]);
    }

    public function test_admin_can_delete_user_without_protected_references(): void
    {
        $admin = $this->createUser(1, 'admin');
        $user = $this->createUser(2, 'staff');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'ລຶບຜູ້ໃຊ້ ແລະ ຂໍ້ມູນປະຫວັດສຳເລັດ');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    private function createUser(int $id, string $roleName): User
    {
        DB::table('roles')->insert([
            'id' => $id,
            'role_name' => $roleName,
        ]);

        DB::table('users')->insert([
            'id' => $id,
            'username' => $roleName,
            'password' => Hash::make('password'),
            'full_name' => ucfirst($roleName),
            'role_id' => $id,
            'is_active' => true,
        ]);

        return User::query()->findOrFail($id);
    }
}
