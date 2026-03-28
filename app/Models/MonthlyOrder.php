<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'product_id',
        'month',
        'quantity',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the department that owns the order.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the product that owns the order.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Tính tổng giá trị đơn hàng
     */
    public function getTotalAmount()
    {
        return $this->quantity * ($this->product->price ?? 0);
    }

    /**
     * Lấy năm từ tháng đơn hàng
     */
    public function getYearAttribute()
    {
        // Format month: MM/YYYY hoặc 09/2025
        $parts = explode('/', $this->month);
        return isset($parts[1]) ? (int)$parts[1] : date('Y');
    }
}
