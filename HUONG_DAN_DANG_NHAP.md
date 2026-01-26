# Hướng Dẫn Đăng Nhập Hệ Thống

## 🔐 Thông Tin Đăng Nhập

Hệ thống đã được cấu hình với các tài khoản mẫu sau:

### 1. Tài Khoản Admin (Quản Trị Viên)
- **Username**: `TMMC-ADMIN`
- **Password**: `123456`
- **Email**: admin@hospital.vn
- **Quyền**: ADMIN
- **Dashboard**: `/admin/dashboard`

### 2. Tài Khoản Department (Khoa)
#### Khoa Nội
- **Username**: `TMMC-NOI`
- **Password**: `123456`
- **Email**: noi@hospital.vn
- **Quyền**: DEPARTMENT
- **Dashboard**: `/department/dashboard`

#### Khoa Ngoại
- **Username**: `TMMC-NGOAI`
- **Password**: `123456`
- **Email**: ngoai@hospital.vn
- **Quyền**: DEPARTMENT
- **Dashboard**: `/department/dashboard`

#### Khoa Nhi
- **Username**: `TMMC-NHI`
- **Password**: `123456`
- **Email**: nhi@hospital.vn
- **Quyền**: DEPARTMENT
- **Dashboard**: `/department/dashboard`

#### Khoa Xét Nghiệm
- **Username**: `TMMC-XETNGHIEM`
- **Password**: `123456`
- **Email**: xetngiem@hospital.vn
- **Quyền**: DEPARTMENT
- **Dashboard**: `/department/dashboard`

## 🚀 Cách Sử Dụng

### Bước 1: Truy cập trang đăng nhập
Mở trình duyệt và truy cập: `http://127.0.0.1:8000`

Hệ thống sẽ tự động chuyển hướng đến trang đăng nhập.

### Bước 2: Nhập thông tin đăng nhập
- Nhập **Username** (ví dụ: `TMMC-ADMIN`)
- Nhập **Password** (ví dụ: `123456`)
- (Tùy chọn) Chọn "Ghi nhớ đăng nhập" để duy trì phiên đăng nhập

### Bước 3: Nhấn "ĐĂNG NHẬP"
Hệ thống sẽ tự động chuyển hướng đến dashboard tương ứng với quyền của bạn:
- **ADMIN** → Admin Dashboard
- **DEPARTMENT** → Department Dashboard
- **BUYER** → Buyer Dashboard

### Bước 4: Đăng xuất
Hover chuột vào avatar ở góc trên bên phải, sau đó click "Đăng xuất"

## 🔧 Tính Năng Bảo Mật

✅ **Mã hóa mật khẩu**: Sử dụng bcrypt để mã hóa mật khẩu
✅ **CSRF Protection**: Bảo vệ chống tấn công CSRF
✅ **Session Management**: Quản lý phiên đăng nhập an toàn
✅ **Active User Check**: Chỉ cho phép user active đăng nhập
✅ **Role-based Access**: Phân quyền theo vai trò
✅ **Middleware Protection**: Bảo vệ các route bằng middleware auth

## 📋 Cấu Trúc Phân Quyền

### ADMIN (Quản Trị Viên)
- Quản lý toàn bộ hệ thống
- Quản lý người dùng
- Quản lý sản phẩm, nhà cung cấp
- Xem báo cáo tổng hợp

### DEPARTMENT (Khoa/Phòng)
- Tạo phiếu yêu cầu văn phòng phẩm
- Xem lịch sử yêu cầu
- Quản lý vật tư của khoa

### BUYER (Nhân Viên Mua Hàng)
- Xem và xử lý yêu cầu từ các khoa
- Tạo đơn hàng
- Quản lý nhà cung cấp

## 🛠️ Troubleshooting

### Lỗi: "Tên đăng nhập hoặc mật khẩu không chính xác"
- Kiểm tra lại username và password
- Đảm bảo không có khoảng trắng thừa
- Kiểm tra caps lock

### Lỗi: Không thể đăng nhập
- Kiểm tra database đã được migrate chưa
- Chạy lệnh: `php artisan migrate:fresh --seed`
- Kiểm tra file .env đã cấu hình database đúng chưa

### Lỗi: Redirect loop
- Xóa cache: `php artisan cache:clear`
- Xóa session: `php artisan session:flush`

## 📝 Ghi Chú

- Mật khẩu mặc định cho tất cả tài khoản: `123456`
- Nên đổi mật khẩu sau lần đăng nhập đầu tiên
- Database: `hospital_VPP`
- Session lifetime: 120 phút

## 🔄 Reset Database (Nếu Cần)

Nếu cần reset lại database và tạo lại users mẫu:

```bash
php artisan migrate:fresh --seed
```

Lệnh này sẽ:
1. Xóa toàn bộ tables
2. Tạo lại tables từ migrations
3. Chạy seeders để tạo dữ liệu mẫu
