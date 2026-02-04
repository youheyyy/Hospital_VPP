# HƯỚNG DẪN CHẠY DATABASE

## Thông tin Database
- **Database Name**: hospital_vpp_2
- **Username**: root
- **Password**: (để trống)
- **Host**: 127.0.0.1
- **Port**: 3306

## Các lệnh chạy Database

### 1. Tạo Database mới (nếu chưa có)
```bash
# Mở MySQL Command Line hoặc phpMyAdmin và chạy:
CREATE DATABASE IF NOT EXISTS hospital_vpp_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Chạy Migrations (Tạo các bảng)
```bash
php artisan migrate:fresh
```

### 3. Chạy Seeders (Thêm dữ liệu mẫu)
```bash
php artisan db:seed
```

### 4. Hoặc chạy cả hai cùng lúc (Khuyến nghị)
```bash
php artisan migrate:fresh --seed
```

## Dữ liệu mẫu sau khi seed

### Users (Tài khoản đăng nhập)
| Email | Password | Role | Department |
|-------|----------|------|------------|
| superadmin@hospital.com | password | SuperAdmin | - |
| admin@hospital.com | password | Admin | - |
| cdha@hospital.com | password | Department | Chẩn đoán hình ảnh |
| xn@hospital.com | password | Department | Xét nghiệm |
| pk@hospital.com | password | Department | Phòng khám |
| ngoai@hospital.com | password | Department | Khoa Ngoại |
| noi@hospital.com | password | Department | Khoa Nội |
| sanpk@hospital.com | password | Department | Sản phụ khoa |
| nhi@hospital.com | password | Department | Khoa Nhi |
| duoc@hospital.com | password | Department | Khoa Dược |
| hcth@hospital.com | password | Department | Hành chính tổng hợp |

### Departments (Khoa phòng)
- CDHA - Chẩn đoán hình ảnh
- XN - Xét nghiệm
- PK - Phòng khám
- NGOAI - Khoa Ngoại
- NOI - Khoa Nội
- SANPK - Sản phụ khoa
- NHI - Khoa Nhi
- DUOC - Khoa Dược
- HCTH - Hành chính tổng hợp

### Categories (Danh mục sản phẩm)
1. Văn phòng phẩm - Nhà sách Thành Vân
2. Quảng cáo Rạng
3. Văn phòng phẩm khác
4. Thiết bị y tế

### Products (Sản phẩm)
Có 24 sản phẩm mẫu được phân loại theo danh mục, bao gồm:
- Văn phòng phẩm: Bìa còng, Sổ caro, Bút bi, Giấy A4, v.v.
- Quảng cáo Rạng: Form siêu âm, Phiếu khám bệnh, v.v.
- Thiết bị y tế: Găng tay, Khẩu trang, Bông y tế

### Monthly Orders (Yêu cầu hàng tháng)
Có dữ liệu mẫu cho các tháng:
- 09/2025 (dữ liệu cụ thể theo ví dụ)
- 01/2026 (dữ liệu ngẫu nhiên)
- 02/2026 (dữ liệu ngẫu nhiên)

## Lệnh hữu ích khác

### Reset database hoàn toàn
```bash
php artisan migrate:fresh --seed
```

### Chỉ chạy lại seeders (không xóa dữ liệu cũ)
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Chạy từng seeder riêng lẻ
```bash
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=MonthlyOrderSeeder
```

### Kiểm tra kết nối database
```bash
php artisan migrate:status
```

### Rollback migration (quay lại bước trước)
```bash
php artisan migrate:rollback
```

## Lưu ý
- Đảm bảo MySQL/MariaDB đã được khởi động
- Đảm bảo file `.env` đã được cấu hình đúng thông tin database
- Nếu gặp lỗi, kiểm tra lại thông tin kết nối trong file `.env`
- Mật khẩu mặc định cho tất cả tài khoản là: **password**
