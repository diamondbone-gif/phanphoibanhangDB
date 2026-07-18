<?php

namespace App\Models;

use App\Enums\FinancialTransactionState;
use App\Enums\FinancialTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => FinancialTransactionType::class,
        'status' => FinancialTransactionState::class,
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderReturn::class, 'customer_order_return_id');
    }
}
