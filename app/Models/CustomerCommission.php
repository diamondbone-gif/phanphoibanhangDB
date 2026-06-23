<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class CustomerCommission extends Model
// {
//     protected $table = 'customer_commissions';

//     protected $fillable = [
//         'referrer_customer_id',
//         'referred_customer_id',
//         'referral_id',
//         'customer_order_id',
//         'order_code',
//         'order_amount',
//         'commission_rate',
//         'commission_amount',
//         'commission_status_id',
//         'approved_by',
//         'approved_at',
//         'paid_at',
//         'cancelled_reason',
//     ];

//     protected $casts = [
//         'order_amount' => 'decimal:2',
//         'commission_rate' => 'decimal:2',
//         'commission_amount' => 'decimal:2',
//         'approved_at' => 'datetime',
//         'paid_at' => 'datetime',
//     ];

//     public function referrer(): BelongsTo
//     {
//         return $this->belongsTo(Customer::class, 'referrer_customer_id');
//     }

//     public function referred(): BelongsTo
//     {
//         return $this->belongsTo(Customer::class, 'referred_customer_id');
//     }

//     public function referral(): BelongsTo
//     {
//         return $this->belongsTo(CustomerReferral::class, 'referral_id');
//     }

//     public function order(): BelongsTo
//     {
//         return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
//     }

//     public function approver(): BelongsTo
//     {
//         return $this->belongsTo(OperationManager::class, 'approved_by');
//     }
// }



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCommission extends Model
{
    protected $table = 'customer_commissions';

    protected $fillable = [
        'referrer_customer_id',
        'referred_customer_id',
        'referral_id',
        'customer_order_id',
        'order_code',
        'order_amount',
        'commission_base_amount',
        'commission_rate',
        'commission_amount',
        'commission_status_id',
        'approved_by',
        'approved_at',
        'paid_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(CustomerReferral::class, 'referral_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerCommissionItem::class, 'customer_commission_id');
    }
}
