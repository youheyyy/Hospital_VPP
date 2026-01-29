<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_id' => 1,
                'department_code' => 'KHOA_CHUAN_DOAN_HINH_ANH',
                'department_name' => 'Khoa Chuẩn Đoán Hình Ảnh',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 2,
                'department_code' => 'KHOA_XET_NGHIEM',
                'department_name' => 'Khoa Xét Nghiệm',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 3,
                'department_code' => 'KHOA_CAP_CUU',
                'department_name' => 'Khoa Cấp Cứu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 4,
                'department_code' => 'PHONG_MO',
                'department_name' => 'Phòng Mổ',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 5,
                'department_code' => 'KHOA_DUOC',
                'department_name' => 'Khoa Dược',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 6,
                'department_code' => 'PHONG_KHAM',
                'department_name' => 'Phòng Khám',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 7,
                'department_code' => 'KHOA_HOI_SUC',
                'department_name' => 'Khoa Hồi Sức',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 8,
                'department_code' => 'KHOA_SAN',
                'department_name' => 'Khoa Sản',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 9,
                'department_code' => 'PHONG_KINH_DOANH_CSKH',
                'department_name' => 'Phòng Kinh Doanh - Chăm Sóc Khách Hàng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 10,
                'department_code' => 'KHOA_NGOAI',
                'department_name' => 'Khoa Ngoại',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 11,
                'department_code' => 'KHOA_NHI',
                'department_name' => 'Khoa Nhi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 12,
                'department_code' => 'PHONG_HANH_CHINH_NHAN_SU',
                'department_name' => 'Phòng Hành Chính - Nhân Sự',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 13,
                'department_code' => 'PHONG_HO_TRO_DICH_VU',
                'department_name' => 'Phòng Hỗ Trợ Dịch Vụ',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 14,
                'department_code' => 'PHONG_KE_TOAN',
                'department_name' => 'Phòng Kế Toán',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 15,
                'department_code' => 'KHOA_NOI',
                'department_name' => 'Khoa Nội',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 16,
                'department_code' => 'PHONG_Y_VU',
                'department_name' => 'Phòng Y Vụ',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 17,
                'department_code' => 'KHO_VAN_PHONG_PHAM',
                'department_name' => 'Kho Văn Phòng Phẩm (Photo Biểu Mẫu)',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 18,
                'department_code' => 'KHOA_DA_LIEU',
                'department_name' => 'Khoa Da Liễu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 19,
                'department_code' => 'KHOA_KIEM_SOAT_NHIEM_KHUAN',
                'department_name' => 'Khoa Kiểm Soát Nhiễm Khuẩn',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 20,
                'department_code' => 'KHOA_DSA',
                'department_name' => 'Khoa DSA',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 21,
                'department_code' => 'PHONG_THAM_DO_CHUC_NANG',
                'department_name' => 'Phòng Thăm Dò Chức Năng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
