<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AggregationBatch extends Model
{
    use HasFactory;

    protected $primaryKey = 'aggregation_batch_id';

    protected $fillable = [
        'batch_code',
        'batch_month',
        'batch_year',
        'status',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(AggregationItem::class, 'aggregation_batch_id');
    }
}
