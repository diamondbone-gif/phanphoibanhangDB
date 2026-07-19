<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderReturnItem extends Model
{
    protected $fillable = [
        'customer_order_return_id', 'customer_order_item_id', 'product_id',
        'product_batch_id', 'quantity', 'unit_refund_amount', 'refund_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_refund_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderReturn::class, 'customer_order_return_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderItem::class, 'customer_order_item_id');
    }
}
