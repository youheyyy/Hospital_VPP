<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Văn phòng phẩm - Nhà sách Thành Vân',
                'parent_id' => null,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Quảng cáo Rạng',
                'parent_id' => null,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Văn phòng phẩm khác',
                'parent_id' => null,
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Thiết bị y tế',
                'parent_id' => null,
                'display_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'parent_id' => $category['parent_id'],
                'display_order' => $category['display_order'],
                'is_active' => $category['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
