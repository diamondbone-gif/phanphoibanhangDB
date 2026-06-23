<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderItem extends Model
{
    protected $fillable = [
        'customer_order_id',
        'product_id',
        'product_batch_id',
        'product_combo_id',
        'product_event_id',
        'product_code',
        'product_name',
        'quantity',
        'unit_price',
        'original_total',
        'discount_type',
        'discount_percent',
        'discount_amount',
        'final_total',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }
}
