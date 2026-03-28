<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentBudget;

class DepartmentBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        $currentYear = date('Y');

        foreach ($departments as $department) {
            // Tạo ngân sách cho năm hiện tại
            DepartmentBudget::create([
                'department_id' => $department->id,
                'year' => $currentYear,
                'total_budget' => 100000000, // 100 triệu VNĐ
                'used_budget' => 0,
                'remaining_budget' => 100000000,
                'notes' => 'Ngân sách năm ' . $currentYear,
            ]);

            // Tạo ngân sách cho năm sau (tùy chọn)
            DepartmentBudget::create([
                'department_id' => $department->id,
                'year' => $currentYear + 1,
                'total_budget' => 120000000, // 120 triệu VNĐ
                'used_budget' => 0,
                'remaining_budget' => 120000000,
                'notes' => 'Ngân sách năm ' . ($currentYear + 1),
            ]);
        }
    }
}
