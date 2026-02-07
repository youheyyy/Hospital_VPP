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
}
