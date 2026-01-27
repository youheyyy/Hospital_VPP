<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_code',
        'product_name',
        'category_id',
        'unit',
        'unit_price',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Accessor for backward compatibility / logic relying on direct relationship
    public function getSupplierAttribute()
    {
        return $this->category ? $this->category->supplier : null;
    }

    public function getSupplierIdAttribute()
    {
        return $this->category ? $this->category->supplier_id : null;
    }
}
