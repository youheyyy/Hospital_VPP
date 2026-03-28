<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the monthly orders for the department.
     */
    public function monthlyOrders()
    {
        return $this->hasMany(MonthlyOrder::class);
    }

    /**
     * Get the budgets for the department.
     */
    public function budgets()
    {
        return $this->hasMany(DepartmentBudget::class);
    }

    /**
     * Get the budget for a specific year.
     */
    public function getBudgetForYear($year)
    {
        return $this->budgets()->where('year', $year)->first();
    }
}
