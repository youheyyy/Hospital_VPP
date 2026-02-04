<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'CDHA', 'name' => 'Chẩn đoán hình ảnh', 'is_active' => true],
            ['code' => 'XN', 'name' => 'Xét nghiệm', 'is_active' => true],
            ['code' => 'PK', 'name' => 'Phòng khám', 'is_active' => true],
            ['code' => 'NGOAI', 'name' => 'Khoa Ngoại', 'is_active' => true],
            ['code' => 'NOI', 'name' => 'Khoa Nội', 'is_active' => true],
            ['code' => 'SANPK', 'name' => 'Sản phụ khoa', 'is_active' => true],
            ['code' => 'NHI', 'name' => 'Khoa Nhi', 'is_active' => true],
            ['code' => 'DUOC', 'name' => 'Khoa Dược', 'is_active' => true],
            ['code' => 'HCTH', 'name' => 'Hành chính tổng hợp', 'is_active' => true],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'code' => $department['code'],
                'name' => $department['name'],
                'is_active' => $department['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
