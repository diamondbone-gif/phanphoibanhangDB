<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'payment_code',
        'customer_order_id',
        'payment_status_id',
        'amount',
        'payment_method',
        'payment_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
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
