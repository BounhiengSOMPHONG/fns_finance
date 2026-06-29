<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ດຶງ ID ຂອງ Admin role ແລະ Management department
        $adminRole = Role::where('role_name', 'Admin')->first();
        $managementDept = Department::where('department_name', 'ຝ່າຍບໍລິຫານ')->first();

        if ($adminRole && $managementDept) {
            User::firstOrCreate(
                ['username' => 'admin'],
                [
                    'username' => 'admin',
                    'password' => Hash::make('admin123'),
                    'full_name' => 'ຜູ້ດູແລລະບົບ',
                    'role_id' => $adminRole->id,
                    'department_id' => $managementDept->id,
                    'is_active' => true,
                ]
            );

            // ສ້າງ user ທົດສອບເພີ່ມ
            User::firstOrCreate(
                ['username' => 'finance01'],
                [
                    'username' => 'finance01',
                    'password' => Hash::make('password123'),
                    'full_name' => 'ພະນັກງານການເງິນ 1',
                    'role_id' => Role::where('role_name', 'Finance')->first()?->id ?? $adminRole->id,
                    'department_id' => Department::where('department_name', 'ຝ່າຍການເງິນ')->first()?->id ?? $managementDept->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
