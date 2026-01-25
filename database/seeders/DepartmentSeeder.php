<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_code' => 'KHOA_HANH_CHINH_TONG_HOP',
                'department_name' => 'Khoa Hành Chính Tổng Hợp',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'KHOA_NOI',
                'department_name' => 'Khoa Nội',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'KHOA_NGOAI',
                'department_name' => 'Khoa Ngoại',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'KOHA_NHI',
                'department_name' => 'Khoa Nhi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'KOHA_XET_NGHIEM',
                'department_name' => 'Khoa Xét Nghiệm',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
