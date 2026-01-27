<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_categories')->insert([
            [
                'category_code' => 'VPP',
                'category_name' => 'Văn phòng phẩm',
                'supplier_id' => 3, // Văn phòng phẩm Hồng Hà
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'category_code' => 'VTYT',
                'category_name' => 'Vật tư y tế',
                'supplier_id' => 1, // Công ty Thiết bị Y tế Minh Phát
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'category_code' => 'TBYT',
                'category_name' => 'Thiết bị y tế',
                'supplier_id' => 1, // Công ty Thiết bị Y tế Minh Phát
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'category_code' => 'HC',
                'category_name' => 'Hóa chất',
                'supplier_id' => 4, // Công ty Hóa chất Y Sinh
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'category_code' => 'KHAC',
                'category_name' => 'Khác',
                'supplier_id' => 5, // Nhà cung cấp tổng hợp Phúc Long
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
