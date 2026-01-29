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
            ['category_id' => 1, 'category_name' => 'Văn phòng phẩm', 'category_code' => 'VPP', 'supplier_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 2, 'category_name' => 'Vật tư tiêu hao', 'category_code' => 'VTTH', 'supplier_id' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 3, 'category_name' => 'Vật tư - Hóa chất vệ sinh', 'category_code' => 'HCVS', 'supplier_id' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 4, 'category_name' => 'Quảng cáo', 'category_code' => 'QC', 'supplier_id' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 5, 'category_name' => 'Biểu mẫu', 'category_code' => 'BM', 'supplier_id' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 6, 'category_name' => 'Nước uống đóng bình', 'category_code' => 'NUOC', 'supplier_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category_id' => 7, 'category_name' => 'Danh mục Khác', 'category_code' => 'KHAC', 'supplier_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
