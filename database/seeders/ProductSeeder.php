<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'product_code' => 'VPP0001',
                'product_name' => 'Giấy A4 Double A',
                'category_id' => 1,
                'unit' => 'Ream',
                'unit_price' => 75000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_code' => 'VTYT0001',
                'product_name' => 'Găng tay y tế',
                'category_id' => 2,
                'unit' => 'Hộp',
                'unit_price' => 120000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_code' => 'TBYT00001',
                'product_name' => 'Nhiệt kế điện tử',
                'category_id' => 3,
                'unit' => 'Cái',
                'unit_price' => 250000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_code' => 'HC0001',
                'product_name' => 'Cồn y tế 70 độ',
                'category_id' => 4,
                'unit' => 'Chai',
                'unit_price' => 45000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_code' => 'VPP0002',
                'product_name' => 'Bút bi xanh',
                'category_id' => 1,
                'unit' => 'Cây',
                'unit_price' => 5000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
