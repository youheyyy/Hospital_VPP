# Giải Thích Chức Năng Tính Lại Ngân Sách

## 1. Vấn đề đã sửa: Chức năng sửa ngân sách không hoạt động

### Nguyên nhân
Khi nhấn nút "Sửa" (biểu tượng bút chì), form modal hiện lên nhưng dropdown chọn khoa phòng bị `disabled`. Khi submit form, các input bị disabled sẽ không gửi giá trị đi, dẫn đến validation fail vì thiếu `department_id`.

### Giải pháp
- Không disable dropdown nữa, thay vào đó chỉ thay đổi style để người dùng biết không nên thay đổi
- Thêm event listener để ngăn thay đổi giá trị khi đang ở chế độ sửa
- Giá trị `department_id` vẫn được gửi đi khi submit form

### Code đã sửa
File: `resources/views/superadmin/budgets/index.blade.php`

```javascript
function editBudget(deptId, deptName, totalBudget, notes) {
    // Không dùng disabled = true nữa
    document.getElementById('departmentSelect').disabled = false;
    // Thay đổi style để hiển thị readonly
    document.getElementById('departmentSelect').style.backgroundColor = '#f3f4f6';
    document.getElementById('departmentSelect').style.cursor = 'not-allowed';
    // Ngăn thay đổi giá trị
    document.getElementById('departmentSelect').addEventListener('mousedown', function(e) {
        if (this.style.backgroundColor === 'rgb(243, 244, 246)') {
            e.preventDefault();
        }
    });
}
```

---

## 2. Cách hoạt động của chức năng "Tính Lại Ngân Sách"

### Mục đích
Chức năng này dùng để đồng bộ lại số liệu ngân sách đã sử dụng với thực tế các đơn hàng trong hệ thống.

### Khi nào cần dùng?
- Khi có sự chênh lệch giữa ngân sách đã sử dụng và tổng giá trị đơn hàng thực tế
- Sau khi import dữ liệu đơn hàng từ Excel
- Sau khi xóa hoặc sửa đơn hàng thủ công trong database
- Khi nghi ngờ dữ liệu không chính xác

### Cách tính toán

#### Công thức
```
Ngân sách đã sử dụng = Tổng (Số lượng × Đơn giá) của tất cả đơn hàng trong năm
Ngân sách còn lại = Tổng ngân sách - Ngân sách đã sử dụng
```

#### Chi tiết logic (trong `app/Models/DepartmentBudget.php`)
```php
public function recalculateUsedBudget()
{
    $year = $this->year;
    
    // Lấy tất cả đơn hàng của khoa trong năm
    $totalUsed = MonthlyOrder::where('department_id', $this->department_id)
        ->whereYear('created_at', $year)
        ->get()
        ->sum(function ($order) {
            // Tính: Số lượng × Đơn giá sản phẩm
            return $order->quantity * ($order->product->price ?? 0);
        });

    // Cập nhật lại
    $this->used_budget = $totalUsed;
    $this->remaining_budget = $this->total_budget - $this->used_budget;
    $this->save();
}
```

### Ví dụ cụ thể

Giả sử Khoa Dược có:
- Tổng ngân sách năm 2026: **900.300.000 VNĐ**
- Các đơn hàng trong năm 2026:
  - Đơn 1: 100 hộp × 50.000 VNĐ = 5.000.000 VNĐ
  - Đơn 2: 200 lọ × 30.000 VNĐ = 6.000.000 VNĐ
  - Đơn 3: 50 hộp × 100.000 VNĐ = 5.000.000 VNĐ

Khi nhấn nút "Tính lại ngân sách":
1. Hệ thống tính tổng: 5.000.000 + 6.000.000 + 5.000.000 = **16.000.000 VNĐ**
2. Cập nhật `used_budget` = 16.000.000 VNĐ
3. Cập nhật `remaining_budget` = 900.300.000 - 16.000.000 = **884.300.000 VNĐ**
4. Tỷ lệ sử dụng = (16.000.000 / 900.300.000) × 100 = **1.78%**

### Lưu ý quan trọng
- Chức năng này chỉ tính dựa trên `created_at` của đơn hàng (năm tạo đơn)
- Nếu sản phẩm không có giá (`product->price` null), sẽ tính là 0
- Chức năng này ghi đè hoàn toàn số liệu `used_budget` cũ

### Cách sử dụng
1. Vào trang "Quản Lý Ngân Sách" (menu SuperAdmin)
2. Tìm khoa phòng cần tính lại
3. Nhấn nút biểu tượng "refresh" (mũi tên tròn màu xanh lá)
4. Xác nhận trong hộp thoại
5. Trang sẽ tự động reload với số liệu mới

---

## 3. Kiểm tra kết quả

Sau khi tính lại, bạn có thể kiểm tra:
- Cột "Đã Sử Dụng" phải khớp với tổng giá trị đơn hàng thực tế
- Cột "Còn Lại" = Tổng Ngân Sách - Đã Sử Dụng
- Tỷ lệ % phải chính xác
- Màu thanh progress bar thay đổi theo tỷ lệ:
  - Xanh lá: < 70%
  - Cam: 70% - 90%
  - Đỏ: > 90%
