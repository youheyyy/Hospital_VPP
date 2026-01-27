<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'request_item_id'; // NOTE: Check migration if it was request_item_id or request_item_id

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'quantity_requested',
        'quantity_approved',
        'decision_status',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id', 'purchase_request_id');
    }
}
