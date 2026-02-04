<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy departments và products
        $departments = DB::table('departments')->get();
        $products = DB::table('products')->get();

        // Tạo dữ liệu mẫu cho tháng 01/2026 và 02/2026
        $months = ['01/2026', '02/2026'];

        foreach ($months as $month) {
            foreach ($departments as $department) {
                // Mỗi khoa đặt hàng ngẫu nhiên 5-10 sản phẩm
                $randomProducts = $products->random(rand(5, 10));
                
                foreach ($randomProducts as $product) {
                    DB::table('monthly_orders')->insert([
                        'department_id' => $department->id,
                        'product_id' => $product->id,
                        'month' => $month,
                        'quantity' => rand(1, 20), // Số lượng ngẫu nhiên từ 1-20
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Thêm một số dữ liệu cụ thể cho tháng 09/2025 (như trong ví dụ)
        $cdhaId = DB::table('departments')->where('code', 'CDHA')->value('id');
        $xnId = DB::table('departments')->where('code', 'XN')->value('id');
        $pkId = DB::table('departments')->where('code', 'PK')->value('id');

        $biaCongId = DB::table('products')->where('name', 'Bìa còng 10p')->value('id');
        $soCaroId = DB::table('products')->where('name', 'Sổ caro A4')->value('id');
        $formSieuAmId = DB::table('products')->where('name', 'Form siêu âm')->value('id');

        if ($cdhaId && $biaCongId) {
            DB::table('monthly_orders')->insert([
                'department_id' => $cdhaId,
                'product_id' => $biaCongId,
                'month' => '09/2025',
                'quantity' => 2,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($cdhaId && $soCaroId) {
            DB::table('monthly_orders')->insert([
                'department_id' => $cdhaId,
                'product_id' => $soCaroId,
                'month' => '09/2025',
                'quantity' => 5,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($cdhaId && $formSieuAmId) {
            DB::table('monthly_orders')->insert([
                'department_id' => $cdhaId,
                'product_id' => $formSieuAmId,
                'month' => '09/2025',
                'quantity' => 10,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($xnId && $biaCongId) {
            DB::table('monthly_orders')->insert([
                'department_id' => $xnId,
                'product_id' => $biaCongId,
                'month' => '09/2025',
                'quantity' => 5,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($pkId && $biaCongId) {
            DB::table('monthly_orders')->insert([
                'department_id' => $pkId,
                'product_id' => $biaCongId,
                'month' => '09/2025',
                'quantity' => 3,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
