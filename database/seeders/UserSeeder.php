<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Note: Password hash provided by user is bcrypt for '123456'
        // '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli'

        DB::table('users')->insert([
            [
                'username' => 'TMMC-ADMIN',
                'email' => 'admin@hospital.vn',
                'full_name' => 'Quản trị hệ thống',
                'password' => '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli',
                'department_id' => null,
                'role_code' => 'ADMIN',
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'TMMC-NOI',
                'email' => 'noi@hospital.vn',
                'full_name' => 'Nguyễn Văn Nội',
                'password' => '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli',
                'department_id' => 2,
                'role_code' => 'DEPARTMENT',
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'TMMC-NGOAI',
                'email' => 'ngoai@hospital.vn',
                'full_name' => 'Trần Thị Ngoại',
                'password' => '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli',
                'department_id' => 3,
                'role_code' => 'DEPARTMENT',
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'TMMC-NHI',
                'email' => 'nhi@hospital.vn',
                'full_name' => 'Lê Văn Nhi',
                'password' => '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli',
                'department_id' => 4,
                'role_code' => 'DEPARTMENT',
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'TMMC-XETNGHIEM',
                'email' => 'xetngiem@hospital.vn',
                'full_name' => 'Phạm Thị Xét Nghiệm',
                'password' => '$2y$12$U2Y15a7fbhTyEpad6NP0u.pbbvScpODUsbzZXUQMnaAGUA.fuPyli',
                'department_id' => 5,
                'role_code' => 'DEPARTMENT',
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
