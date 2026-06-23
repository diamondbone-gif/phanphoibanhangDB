<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'order_histories';

    protected $fillable = [
        'customer_order_id',
        'action',
        'old_status_id',
        'new_status_id',
        'old_data',
        'new_data',
        'note',
        'created_by',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }
}
