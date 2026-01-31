<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'purchase_order_item_id';

    protected $fillable = [
        'purchase_order_id',
        'aggregation_item_id',
        'product_id',
        'quantity_ordered',
        'unit_price',
        'total_price',
    ];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'purchase_order_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'purchase_order_id');
    }

    public function aggregationItem()
    {
        return $this->belongsTo(AggregationItem::class, 'aggregation_item_id', 'aggregation_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
