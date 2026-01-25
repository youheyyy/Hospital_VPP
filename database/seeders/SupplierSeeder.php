<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'supplier_code' => 'SUP0001',
                'supplier_name' => 'Công ty Thiết bị Y tế Minh Phát',
                'contact_person' => 'Nguyễn Minh',
                'phone_number' => '0909123456',
                'email' => 'minhphat@supplier.vn',
                'address' => 'TP.HCM',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'supplier_code' => 'SUP0002',
                'supplier_name' => 'Công ty Dược An Khang',
                'contact_person' => 'Trần An',
                'phone_number' => '0911222333',
                'email' => 'ankhang@supplier.vn',
                'address' => 'Hà Nội',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'supplier_code' => 'SUP0003',
                'supplier_name' => 'Văn phòng phẩm Hồng Hà',
                'contact_person' => 'Lê Hồng',
                'phone_number' => '0988777666',
                'email' => 'hongha@supplier.vn',
                'address' => 'Đà Nẵng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'supplier_code' => 'SUP0004',
                'supplier_name' => 'Công ty Hóa chất Y Sinh',
                'contact_person' => 'Phạm Sinh',
                'phone_number' => '0933444555',
                'email' => 'ysinh@supplier.vn',
                'address' => 'TP.HCM',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'supplier_code' => 'SUP0005',
                'supplier_name' => 'Nhà cung cấp tổng hợp Phúc Long',
                'contact_person' => 'Võ Long',
                'phone_number' => '0977666555',
                'email' => 'phuclong@supplier.vn',
                'address' => 'Cần Thơ',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
