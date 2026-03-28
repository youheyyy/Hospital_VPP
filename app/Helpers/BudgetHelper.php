<?php

namespace App\Helpers;

use App\Models\DepartmentBudget;
use App\Models\MonthlyOrder;

class BudgetHelper
{
    /**
     * Kiểm tra và trừ ngân sách khi tạo đơn hàng mới
     * 
     * @param int $departmentId
     * @param int $productId
     * @param float $quantity
     * @param float $price
     * @param string $month (format: MM/YYYY)
     * @return array ['success' => bool, 'message' => string, 'budget' => DepartmentBudget|null]
     */
    public static function checkAndDeductBudget($departmentId, $productId, $quantity, $price, $month)
    {
        // Lấy năm từ tháng
        $parts = explode('/', $month);
        $year = isset($parts[1]) ? (int)$parts[1] : date('Y');

        // Tính tổng giá trị đơn hàng
        $orderAmount = $quantity * $price;

        // Lấy ngân sách của khoa trong năm
        $budget = DepartmentBudget::where('department_id', $departmentId)
            ->where('year', $year)
            ->first();

        // Nếu chưa có ngân sách cho năm này
        if (!$budget) {
            return [
                'success' => false,
                'message' => "Khoa phòng chưa được cấp ngân sách cho năm {$year}. Vui lòng liên hệ quản trị viên.",
                'budget' => null,
            ];
        }

        // Kiểm tra ngân sách còn lại
        if (!$budget->hasEnoughBudget($orderAmount)) {
            return [
                'success' => false,
                'message' => sprintf(
                    "Không đủ ngân sách! Cần: %s VNĐ, Còn lại: %s VNĐ",
                    number_format($orderAmount, 0, ',', '.'),
                    number_format($budget->remaining_budget, 0, ',', '.')
                ),
                'budget' => $budget,
            ];
        }

        // Trừ ngân sách
        $budget->useBudget($orderAmount);

        return [
            'success' => true,
            'message' => sprintf(
                "Đã sử dụng %s VNĐ. Ngân sách còn lại: %s VNĐ",
                number_format($orderAmount, 0, ',', '.'),
                number_format($budget->remaining_budget, 0, ',', '.')
            ),
            'budget' => $budget,
        ];
    }

    /**
     * Hoàn trả ngân sách khi xóa hoặc hủy đơn hàng
     * 
     * @param MonthlyOrder $order
     * @return bool
     */
    public static function refundBudget(MonthlyOrder $order)
    {
        // Lấy năm từ tháng
        $parts = explode('/', $order->month);
        $year = isset($parts[1]) ? (int)$parts[1] : date('Y');

        // Tính tổng giá trị đơn hàng
        $orderAmount = $order->getTotalAmount();

        // Lấy ngân sách của khoa trong năm
        $budget = DepartmentBudget::where('department_id', $order->department_id)
            ->where('year', $year)
            ->first();

        if ($budget) {
            $budget->refundBudget($orderAmount);
            return true;
        }

        return false;
    }

    /**
     * Cập nhật ngân sách khi sửa đơn hàng
     * 
     * @param MonthlyOrder $order
     * @param float $oldQuantity
     * @param float $newQuantity
     * @return array
     */
    public static function updateBudgetForOrderChange(MonthlyOrder $order, $oldQuantity, $newQuantity)
    {
        $price = $order->product->price ?? 0;
        $oldAmount = $oldQuantity * $price;
        $newAmount = $newQuantity * $price;
        $difference = $newAmount - $oldAmount;

        // Lấy năm từ tháng
        $parts = explode('/', $order->month);
        $year = isset($parts[1]) ? (int)$parts[1] : date('Y');

        // Lấy ngân sách
        $budget = DepartmentBudget::where('department_id', $order->department_id)
            ->where('year', $year)
            ->first();

        if (!$budget) {
            return [
                'success' => false,
                'message' => "Không tìm thấy ngân sách cho năm {$year}",
            ];
        }

        // Nếu tăng số lượng, kiểm tra ngân sách
        if ($difference > 0) {
            if (!$budget->hasEnoughBudget($difference)) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        "Không đủ ngân sách để tăng số lượng! Cần thêm: %s VNĐ, Còn lại: %s VNĐ",
                        number_format($difference, 0, ',', '.'),
                        number_format($budget->remaining_budget, 0, ',', '.')
                    ),
                ];
            }
            $budget->useBudget($difference);
        } else {
            // Giảm số lượng, hoàn trả ngân sách
            $budget->refundBudget(abs($difference));
        }

        return [
            'success' => true,
            'message' => sprintf(
                "Đã cập nhật ngân sách. Còn lại: %s VNĐ",
                number_format($budget->remaining_budget, 0, ',', '.')
            ),
        ];
    }
}
