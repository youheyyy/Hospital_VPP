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
            ['supplier_id' => 1, 'supplier_name' => 'Nhà sách Thanh Vân', 'supplier_code' => 'SUP001', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 2, 'supplier_name' => 'Nhà sách Quốc Nam', 'supplier_code' => 'SUP002', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 3, 'supplier_name' => 'Cửa hàng TTBYT Minh Trí', 'supplier_code' => 'SUP003', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 4, 'supplier_name' => 'Quảng Cáo Rạng', 'supplier_code' => 'SUP004', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 5, 'supplier_name' => 'Cửa hàng Nguyệt Trang', 'supplier_code' => 'SUP005', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 6, 'supplier_name' => 'Cửa hàng Gia Kiệt', 'supplier_code' => 'SUP006', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 7, 'supplier_name' => 'Nhà cung cấp Khác', 'supplier_code' => 'SUP007', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 8, 'supplier_name' => 'Hộ kinh doanh Thi Đỗ', 'supplier_code' => 'SUP008', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['supplier_id' => 9, 'supplier_name' => 'Bách Hóa Xanh ĐT 49', 'supplier_code' => 'SUP009', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
