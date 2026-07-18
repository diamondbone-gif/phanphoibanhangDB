<?php

namespace App\Models;

use App\Enums\FinancialTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerOrderReturn extends Model
{
    protected $fillable = [
        'return_code', 'customer_order_id', 'refund_amount', 'refund_method',
        'status', 'reason', 'note', 'returned_at', 'created_by',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'returned_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'return_code';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderReturnItem::class, 'customer_order_return_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }

    public function refundTransaction(): HasOne
    {
        return $this->hasOne(FinancialTransaction::class, 'customer_order_return_id')
            ->where('type', FinancialTransactionType::Refund->value);
    }
}
