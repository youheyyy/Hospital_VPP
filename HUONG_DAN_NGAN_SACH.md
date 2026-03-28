# Hướng Dẫn Sử Dụng Tính Năng Quản Lý Ngân Sách Khoa Phòng

## Tổng Quan

Hệ thống quản lý ngân sách cho phép ràng buộc chi tiêu của các khoa phòng trong một năm. Mỗi khoa sẽ được cấp một ngân sách cố định cho mỗi năm và hệ thống sẽ tự động kiểm tra và trừ ngân sách khi tạo đơn hàng.

## Tính Năng

### SuperAdmin
- Thiết lập ngân sách cho từng khoa phòng theo năm
- Xem tổng quan ngân sách của tất cả các khoa
- Tính lại ngân sách đã sử dụng từ các đơn hàng
- Xóa ngân sách

### Khoa Phòng (Department)
- Xem thanh tiến độ ngân sách trực tiếp trên trang yêu cầu VPP
- Xem thanh tiến độ ngân sách trên trang lịch sử
- Cảnh báo tự động khi ngân sách sắp hết (>70%, >90%)
- Không thể tạo đơn hàng khi vượt ngân sách

## Cài Đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Tạo Dữ Liệu Mẫu (Tùy chọn)

```bash
php artisan db:seed --class=DepartmentBudgetSeeder
```

## Cấu Trúc Database

### Bảng `department_budgets`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | bigint | ID tự tăng |
| department_id | bigint | ID khoa phòng |
| year | year | Năm ngân sách (VD: 2026) |
| total_budget | decimal(15,2) | Tổng ngân sách được cấp |
| used_budget | decimal(15,2) | Ngân sách đã sử dụng |
| remaining_budget | decimal(15,2) | Ngân sách còn lại |
| notes | text | Ghi chú |

## Sử Dụng

### 1. SuperAdmin - Thiết Lập Ngân Sách

Truy cập: `/superadmin/budgets`

- Click "Thêm Ngân Sách"
- Chọn khoa phòng
- Nhập năm và tổng ngân sách
- Lưu

### 2. Khoa Phòng - Xem Ngân Sách

Thanh tiến độ ngân sách hiển thị tự động trên:
- Trang yêu cầu VPP (`/department`)
- Trang lịch sử (`/department/history`)

Thông tin hiển thị:
- Tổng ngân sách
- Đã sử dụng
- Còn lại
- Tỷ lệ % sử dụng
- Cảnh báo khi >70% hoặc >90%

### 3. Tự Động Kiểm Tra Ngân Sách

Khi khoa phòng tạo đơn hàng:
1. Hệ thống tự động tính tổng giá trị đơn hàng
2. Kiểm tra ngân sách còn lại
3. Nếu đủ → Trừ ngân sách và tạo đơn
4. Nếu không đủ → Báo lỗi và từ chối

Khi xóa đơn hàng:
- Tự động hoàn trả ngân sách

## Sử Dụng Trong Code

### 1. Tạo Ngân Sách Cho Khoa

```php
use App\Models\DepartmentBudget;

$budget = DepartmentBudget::create([
    'department_id' => 1,
    'year' => 2026,
    'total_budget' => 100000000, // 100 triệu VNĐ
    'used_budget' => 0,
    'remaining_budget' => 100000000,
    'notes' => 'Ngân sách năm 2026',
]);
```

### 2. Lấy Ngân Sách Của Khoa

```php
use App\Models\Department;

$department = Department::find(1);
$budget = $department->getBudgetForYear(2026);

if ($budget) {
    echo "Còn lại: " . number_format($budget->remaining_budget, 0, ',', '.') . " VNĐ";
}
```

### 3. Tính Lại Ngân Sách

```php
$budget = DepartmentBudget::find(1);
$budget->recalculateUsedBudget();
```

## Các Phương Thức Hữu Ích

### Model DepartmentBudget

- `hasEnoughBudget($amount)` - Kiểm tra còn đủ ngân sách không
- `useBudget($amount)` - Sử dụng ngân sách
- `refundBudget($amount)` - Hoàn trả ngân sách
- `recalculateUsedBudget()` - Tính lại ngân sách đã sử dụng

### Model Department

- `getBudgetForYear($year)` - Lấy ngân sách của năm cụ thể
- `budgets()` - Lấy tất cả ngân sách

### Model MonthlyOrder

- `getTotalAmount()` - Tính tổng giá trị đơn hàng
- `year` - Lấy năm từ tháng đơn hàng

### BudgetHelper

- `checkAndDeductBudget()` - Kiểm tra và trừ ngân sách
- `refundBudget()` - Hoàn trả ngân sách
- `updateBudgetForOrderChange()` - Cập nhật ngân sách khi sửa đơn

## Màu Sắc Cảnh Báo

- **Xanh lá** (0-70%): Ngân sách còn nhiều
- **Cam** (70-90%): Cảnh báo đã sử dụng hơn 70%
- **Đỏ** (>90%): Cảnh báo ngân sách sắp hết

## Lưu Ý

1. Mỗi khoa chỉ có một ngân sách cho một năm (unique constraint)
2. Hệ thống tự động tính năm từ trường `month` của đơn hàng (format: MM/YYYY)
3. Ngân sách được tính bằng: `quantity * price`
4. Khi xóa hoặc sửa đơn hàng, ngân sách được tự động cập nhật
5. SuperAdmin có thể dùng `recalculateUsedBudget()` để đồng bộ lại dữ liệu nếu cần

## Routes

### SuperAdmin
- `GET /superadmin/budgets` - Danh sách ngân sách
- `POST /superadmin/budgets` - Tạo/cập nhật ngân sách
- `DELETE /superadmin/budgets/{budget}` - Xóa ngân sách
- `POST /superadmin/budgets/{budget}/recalculate` - Tính lại ngân sách

### Department
- Thanh tiến độ hiển thị tự động trên trang yêu cầu VPP và lịch sử
- Không cần route riêng

