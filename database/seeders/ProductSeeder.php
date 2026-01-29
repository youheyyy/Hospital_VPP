<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // prefixMap dựa trên category_id trong ProductCategorySeeder
        $prefixMap = [
            1 => 'VPP',
            2 => 'VTTH',
            3 => 'HCVS',
            4 => 'QC',
            5 => 'BM',
            6 => 'NUOC',
            7 => 'KHAC',
        ];

        $counters = [];

        $rawProducts = [
            // ===== VĂN PHÒNG PHẨM (Category 1) =====
            ['name' => 'Băng keo giấy 2p4', 'unit' => 'Cuộn', 'category_id' => 1],
            ['name' => 'Băng keo trong 5p', 'unit' => 'Cuộn', 'category_id' => 1],
            ['name' => 'Băng keo simili 5p', 'unit' => 'Cuộn', 'category_id' => 1],
            ['name' => 'Bìa còng 7p', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Bìa còng 10p', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Bìa sơ mi lá lỗ cung tròn', 'unit' => 'Xấp', 'category_id' => 1],
            ['name' => 'Kim bấm', 'unit' => 'Hộp', 'category_id' => 1],
            ['name' => 'Gôm tẩy', 'unit' => 'Cục', 'category_id' => 1],
            ['name' => 'Ghim kẹp', 'unit' => 'Hộp', 'category_id' => 1],
            ['name' => 'Hồ dán', 'unit' => 'Chai', 'category_id' => 1],
            ['name' => 'Kéo văn phòng', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Kẹp bướm 1', 'unit' => 'Hộp', 'category_id' => 1],
            ['name' => 'Kẹp bướm 2', 'unit' => 'Hộp', 'category_id' => 1],
            ['name' => 'Pin CR', 'unit' => 'Viên', 'category_id' => 1],
            ['name' => 'Sổ caro', 'unit' => 'Quyển', 'category_id' => 1],
            ['name' => 'Sơ mi kẹp', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Pin AA', 'unit' => 'Cặp', 'category_id' => 1],
            ['name' => 'Pin AAA', 'unit' => 'Cặp', 'category_id' => 1],
            ['name' => 'Pin Maxell Alkaline', 'unit' => 'Cặp', 'category_id' => 1],
            ['name' => 'Tập', 'unit' => 'Quyển', 'category_id' => 1],
            ['name' => 'Chai nước rửa tay Lifebuoy', 'unit' => 'Chai', 'category_id' => 1],
            ['name' => 'Viết dạ quang', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Viết lông bảng', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Viết lông kim (PM04)', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Viết đen Thiên Long', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Viết đỏ Thiên Long', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Viết xanh Thiên Long', 'unit' => 'Cây', 'category_id' => 1],
            ['name' => 'Giấy in A4', 'unit' => 'Gram', 'category_id' => 1],
            ['name' => 'Giấy Cos C bóng 1 mặt', 'unit' => 'Xấp', 'category_id' => 1],
            ['name' => 'Lưỡi dao rọc giấy', 'unit' => 'Hộp', 'category_id' => 1],
            ['name' => 'Kệ hồ sơ 3 tầng', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Giấy in A4 bóng 2 mặt hướng dương', 'unit' => 'Gram', 'category_id' => 1],
            ['name' => 'Giấy note 4', 'unit' => 'Xấp', 'category_id' => 1],
            ['name' => 'Giấy note 5', 'unit' => 'Xấp', 'category_id' => 1],
            ['name' => 'Mộc', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Giấy decal nhiệt 50x30', 'unit' => 'Cuộn', 'category_id' => 1],
            ['name' => 'Giấy cứng nhám couch 230', 'unit' => 'Xấp', 'category_id' => 1],
            ['name' => 'Giấy A5 xanh dương', 'unit' => 'Gram', 'category_id' => 1],
            ['name' => 'Dao cạo', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Bìa sơ mi 40 lá', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Giấy in nhiệt (80)', 'unit' => 'Cuộn', 'category_id' => 1],
            ['name' => 'Dây thun vòng', 'unit' => 'Bịch', 'category_id' => 1],
            ['name' => 'Bút đế cắm Thiên Long', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Bao thư TTCL', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Đèn pin khám bệnh', 'unit' => 'Cái', 'category_id' => 1],
            ['name' => 'Giấy decan A4', 'unit' => 'Gram', 'category_id' => 1],

            // ===== QUẢNG CÁO (Category 4) =====
            ['name' => 'Bìa hồ sơ bệnh án ngoại', 'unit' => 'Cuốn', 'category_id' => 4],
            ['name' => 'Bìa hồ sơ bệnh án nhi', 'unit' => 'Cuốn', 'category_id' => 4],
            ['name' => 'Bìa hồ sơ bệnh án sản', 'unit' => 'Cuốn', 'category_id' => 4],
            ['name' => 'Giấy khám sức khỏe trên 18 tuổi', 'unit' => 'Tờ', 'category_id' => 4],
            ['name' => 'Form siêu âm', 'unit' => 'Tờ', 'category_id' => 4],
            ['name' => 'Sổ theo dõi tiêm ngừa', 'unit' => 'Cuốn', 'category_id' => 4],

            // ===== VẬT TƯ TIÊU HAO (Category 2) =====
            ['name' => 'Bọc trắng 15T', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bọc trắng 20T', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bọc trắng 24T', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bọc trắng 30T', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bọc Mro', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Ly nhựa trung 3815ml', 'unit' => 'Cây', 'category_id' => 2],
            ['name' => 'Ly rau câu', 'unit' => 'Cây', 'category_id' => 2],
            ['name' => 'Ly nhựa lớn 500ml', 'unit' => 'Cây', 'category_id' => 2],
            ['name' => 'Túi zipper trung', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bọc yaourt (6x12)', 'unit' => 'Kg', 'category_id' => 2],
            ['name' => 'Bàn chải chà dụng cụ (loại dài)', 'unit' => 'Cái', 'category_id' => 2],
            ['name' => 'Chai lifeboy (Nước rửa tay)', 'unit' => 'Chai', 'category_id' => 2],
            ['name' => 'Cước nhôm', 'unit' => 'Miếng', 'category_id' => 2],
            ['name' => 'Găng tay cao su (hợp thành)', 'unit' => 'Cặp', 'category_id' => 2],
            ['name' => 'Giấy note 3x4', 'unit' => 'Xấp', 'category_id' => 2],
            ['name' => 'Khăn sữa', 'unit' => 'Cái', 'category_id' => 2],
            ['name' => 'Hộp khăn giấy rút', 'unit' => 'Hộp', 'category_id' => 2],
            ['name' => 'Bọc 1kg có quai', 'unit' => 'Kg', 'category_id' => 2],

            // ===== VẬT TƯ - HÓA CHẤT VỆ SINH (Category 3) =====
            ['name' => 'Thùng hủy kim lớn', 'unit' => 'Thùng', 'category_id' => 3],

            // ===== NƯỚC UỐNG (Category 6) =====
            ['name' => 'Nước uống đóng bình 20L', 'unit' => 'Bình', 'category_id' => 6],
            ['name' => 'Thùng nước suối Aquafina 500ml', 'unit' => 'Thùng', 'category_id' => 6],

            // ===== DANH MỤC KHÁC (Category 7) =====
            ['name' => 'Vật tư kỹ thuật khác', 'unit' => 'Cái', 'category_id' => 7],
        ];

        $insertData = [];

        foreach ($rawProducts as $item) {
            $catId = $item['category_id'];

            if (!isset($counters[$catId])) {
                $counters[$catId] = 0;
            }
            $counters[$catId]++;

            $prefix = $prefixMap[$catId] ?? 'PROD';
            $code = $prefix . str_pad($counters[$catId], 4, '0', STR_PAD_LEFT);

            $insertData[] = [
                'product_code' => $code,
                'product_name' => $item['name'],
                'category_id' => $catId,
                'unit' => $item['unit'],
                'unit_price' => rand(5000, 500000), // Random giá để dữ liệu trông thật hơn
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('products')->delete(); // Xóa cũ nếu có
        DB::table('products')->insert($insertData);
    }
}
