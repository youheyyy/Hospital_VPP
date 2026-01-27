<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AggregationItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'aggregation_item_id';

    protected $fillable = [
        'aggregation_batch_id',
        'product_id',
        'supplier_id',
        'total_requested',
        'total_approved',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function batch()
    {
        return $this->belongsTo(AggregationBatch::class, 'aggregation_batch_id');
    }
}
