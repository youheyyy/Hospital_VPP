<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SuperAdmin
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'SuperAdmin',
            'department_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'department_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Department Users
        $departments = DB::table('departments')->get();
        
        foreach ($departments as $department) {
            DB::table('users')->insert([
                'name' => 'User ' . $department->name,
                'email' => strtolower($department->code) . '@hospital.com',
                'password' => Hash::make('password'),
                'role' => 'Department',
                'department_id' => $department->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
