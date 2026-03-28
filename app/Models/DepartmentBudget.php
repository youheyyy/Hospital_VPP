<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'year',
        'total_budget',
        'used_budget',
        'remaining_budget',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_budget' => 'decimal:2',
        'used_budget' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
    ];

    /**
     * Get the department that owns the budget.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Kiểm tra xem còn đủ ngân sách không
     */
    public function hasEnoughBudget($amount)
    {
        return $this->remaining_budget >= $amount;
    }

    /**
     * Sử dụng ngân sách
     */
    public function useBudget($amount)
    {
        $this->used_budget += $amount;
        $this->remaining_budget = $this->total_budget - $this->used_budget;
        $this->save();
    }

    /**
     * Hoàn trả ngân sách (khi hủy đơn hàng)
     */
    public function refundBudget($amount)
    {
        $this->used_budget -= $amount;
        $this->remaining_budget = $this->total_budget - $this->used_budget;
        $this->save();
    }

    /**
     * Cập nhật lại ngân sách đã sử dụng từ các đơn hàng
     */
    public function recalculateUsedBudget()
    {
        $year = $this->year;
        
        // Tính tổng giá trị đơn hàng trong năm
        $totalUsed = MonthlyOrder::where('department_id', $this->department_id)
            ->whereYear('created_at', $year)
            ->get()
            ->sum(function ($order) {
                return $order->quantity * ($order->product->price ?? 0);
            });

        $this->used_budget = $totalUsed;
        $this->remaining_budget = $this->total_budget - $this->used_budget;
        $this->save();
    }
}
