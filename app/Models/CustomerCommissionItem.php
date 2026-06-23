<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCommissionItem extends Model
{
    protected $fillable = [
        'customer_commission_id',
        'customer_order_item_id',
        'product_id',
        'product_name',
        'eligible_amount',
        'commission_rate',
        'commission_amount',
    ];
}
