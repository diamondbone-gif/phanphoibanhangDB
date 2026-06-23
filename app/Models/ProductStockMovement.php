<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_batch_id',
        'customer_order_id',
        'customer_order_item_id',
        'movement_code',
        'movement_type',
        'quantity',
        'before_quantity',
        'after_quantity',
        'reference_type',
        'reference_id',
        'movement_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];
}
