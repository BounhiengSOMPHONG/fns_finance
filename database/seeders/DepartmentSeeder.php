<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['department_name' => 'ຝ່າຍບໍລິຫານ', 'department_type' => 'Management'],
            ['department_name' => 'ຝ່າຍການເງິນ', 'department_type' => 'Finance'],
            ['department_name' => 'ຝ່າຍບັນຊີ', 'department_type' => 'Accounting'],
            ['department_name' => 'ຝ່າຍຂາຍ', 'department_type' => 'Sales'],
            ['department_name' => 'ຝ່າຍຈັດຊື້', 'department_type' => 'Procurement'],
            ['department_name' => 'ຝ່າຍໄອທີ', 'department_type' => 'IT'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['department_name' => $department['department_name']],
                $department
            );
        }
    }
}
