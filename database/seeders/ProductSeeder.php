<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy category IDs
        $categories = DB::table('categories')->get()->keyBy('name');

        $products = [
            // Văn phòng phẩm - Nhà sách Thành Vân
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Bìa còng 10p', 'unit' => 'Cái', 'price' => 15000, 'display_order' => 1],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Bìa giấy 10p', 'unit' => 'Cái', 'price' => 8000, 'display_order' => 2],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Sổ caro A4', 'unit' => 'Quyển', 'price' => 34000, 'display_order' => 3],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Bút bi xanh', 'unit' => 'Cây', 'price' => 3000, 'display_order' => 4],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Bút bi đỏ', 'unit' => 'Cây', 'price' => 3000, 'display_order' => 5],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Tập 100 tờ', 'unit' => 'Quyển', 'price' => 12000, 'display_order' => 6],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Giấy A4', 'unit' => 'Ream', 'price' => 57000, 'display_order' => 7],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Kẹp giấy', 'unit' => 'Hộp', 'price' => 5000, 'display_order' => 8],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Ghim bấm', 'unit' => 'Hộp', 'price' => 8000, 'display_order' => 9],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Băng keo trong', 'unit' => 'Cuộn', 'price' => 12000, 'display_order' => 10],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Thước kẻ 30cm', 'unit' => 'Cái', 'price' => 6000, 'display_order' => 11],
            ['category' => 'Văn phòng phẩm - Nhà sách Thành Vân', 'name' => 'Kéo văn phòng', 'unit' => 'Cái', 'price' => 25000, 'display_order' => 12],

            // Quảng cáo Rạng
            ['category' => 'Quảng cáo Rạng', 'name' => 'Form siêu âm', 'unit' => 'Tờ', 'price' => 500, 'display_order' => 1],
            ['category' => 'Quảng cáo Rạng', 'name' => 'Sổ theo dõi bệnh án', 'unit' => 'Cuốn', 'price' => 45000, 'display_order' => 2],
            ['category' => 'Quảng cáo Rạng', 'name' => 'Phiếu khám bệnh', 'unit' => 'Tờ', 'price' => 300, 'display_order' => 3],
            ['category' => 'Quảng cáo Rạng', 'name' => 'Sổ theo dõi xét nghiệm', 'unit' => 'Cuốn', 'price' => 38000, 'display_order' => 4],
            ['category' => 'Quảng cáo Rạng', 'name' => 'Giấy in nhiệt', 'unit' => 'Cuộn', 'price' => 15000, 'display_order' => 5],

            // Văn phòng phẩm khác
            ['category' => 'Văn phòng phẩm khác', 'name' => 'Bìa đựng hồ sơ', 'unit' => 'Cái', 'price' => 12000, 'display_order' => 1],
            ['category' => 'Văn phòng phẩm khác', 'name' => 'Hộp đựng bút', 'unit' => 'Cái', 'price' => 35000, 'display_order' => 2],
            ['category' => 'Văn phòng phẩm khác', 'name' => 'Bảng tên để bàn', 'unit' => 'Cái', 'price' => 28000, 'display_order' => 3],

            // Thiết bị y tế
            ['category' => 'Thiết bị y tế', 'name' => 'Găng tay y tế', 'unit' => 'Hộp', 'price' => 85000, 'display_order' => 1],
            ['category' => 'Thiết bị y tế', 'name' => 'Khẩu trang y tế', 'unit' => 'Hộp', 'price' => 45000, 'display_order' => 2],
            ['category' => 'Thiết bị y tế', 'name' => 'Bông y tế', 'unit' => 'Gói', 'price' => 12000, 'display_order' => 3],
        ];

        foreach ($products as $product) {
            $categoryId = $categories[$product['category']]->id ?? null;
            
            if ($categoryId) {
                DB::table('products')->insert([
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'unit' => $product['unit'],
                    'price' => $product['price'] ?? rand(1000, 100000),
                    'display_order' => $product['display_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
