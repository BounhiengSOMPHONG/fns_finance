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
    }

    public function test_admin_cannot_delete_user_with_advance_requests(): void
    {
        $admin = $this->createUser(1, 'admin');
        $requester = $this->createUser(2, 'staff');

        DB::table('advance_requests')->insert([
            'id' => 1,
            'requester_id' => $requester->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $requester))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'คำขอเบิกล่วงหน้า'));

        $this->assertDatabaseHas('users', [
            'id' => $requester->id,
        ]);
    }

    public function test_admin_can_delete_user_without_protected_references(): void
    {
        $admin = $this->createUser(1, 'admin');
        $user = $this->createUser(2, 'staff');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'ลบผู้ใช้งานสำเร็จ');

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
