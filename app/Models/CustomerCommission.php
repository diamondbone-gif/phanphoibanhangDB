<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCommission extends Model
{
    use SoftDeletes;

    protected $table = 'customer_commissions';

    protected $fillable = [
        /*
        |--------------------------------------------------------------------------
        | MÃ HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'commission_code',

        /*
        |--------------------------------------------------------------------------
        | THÔNG TIN CTV / KHÁCH ĐƯỢC GIỚI THIỆU
        |--------------------------------------------------------------------------
        | Giữ cả referrer_customer_id và ctv_customer_id để tương thích
        | với code cũ và code mới.
        |--------------------------------------------------------------------------
        */
        'referrer_customer_id',
        'ctv_customer_id',
        'referred_customer_id',
        'referral_id',

        /*
        |--------------------------------------------------------------------------
        | THÔNG TIN ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */
        'customer_order_id',
        'order_code',

        /*
        |--------------------------------------------------------------------------
        | SỐ TIỀN ĐƠN HÀNG
        |--------------------------------------------------------------------------
        | order_final_amount là số tiền cuối cùng sau giảm giá.
        | Hoa hồng nên tính theo cột này.
        |--------------------------------------------------------------------------
        */
        'order_amount',
        'order_final_amount',

        /*
        |--------------------------------------------------------------------------
        | THÔNG TIN HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'commission_rate',
        'commission_rate_percent',
        'commission_amount',

        /*
        |--------------------------------------------------------------------------
        | TRẠNG THÁI HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'commission_status_id',
        'status',

        /*
        |--------------------------------------------------------------------------
        | DUYỆT HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'approved_by',
        'approved_at',

        /*
        |--------------------------------------------------------------------------
        | THANH TOÁN HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'paid_amount',
        'clawback_amount',
        'paid_by',
        'paid_at',

        /*
        |--------------------------------------------------------------------------
        | NGÀY PHÁT SINH HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'commission_date',

        /*
        |--------------------------------------------------------------------------
        | HỦY HOA HỒNG
        |--------------------------------------------------------------------------
        */
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'cancelled_reason',

        /*
        |--------------------------------------------------------------------------
        | GHI CHÚ / NGƯỜI TẠO
        |--------------------------------------------------------------------------
        */
        'note',
        'created_by',
    ];

    protected $casts = [
        'referrer_customer_id' => 'integer',
        'ctv_customer_id' => 'integer',
        'referred_customer_id' => 'integer',
        'referral_id' => 'integer',
        'customer_order_id' => 'integer',

        'order_amount' => 'decimal:2',
        'order_final_amount' => 'decimal:2',

        'commission_rate' => 'decimal:2',
        'commission_rate_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',

        'paid_amount' => 'decimal:2',
        'clawback_amount' => 'decimal:2',

        'commission_status_id' => 'integer',
        'approved_by' => 'integer',
        'paid_by' => 'integer',
        'cancelled_by' => 'integer',
        'created_by' => 'integer',

        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'commission_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | ĐƠN HÀNG PHÁT SINH HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | CTV ĐƯỢC NHẬN HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function ctvCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'ctv_customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI GIỚI THIỆU / CTV THEO CODE CŨ
    |--------------------------------------------------------------------------
    */
    public function referrerCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | KHÁCH HÀNG ĐƯỢC GIỚI THIỆU
    |--------------------------------------------------------------------------
    */
    public function referredCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ GIỚI THIỆU
    |--------------------------------------------------------------------------
    */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(CustomerReferral::class, 'referral_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI DUYỆT HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI THANH TOÁN HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'paid_by');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI HỦY HOA HỒNG
    |--------------------------------------------------------------------------
    */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'cancelled_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: CHƯA THANH TOÁN
    |--------------------------------------------------------------------------
    */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: ĐÃ THANH TOÁN
    |--------------------------------------------------------------------------
    */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: ĐÃ HỦY
    |--------------------------------------------------------------------------
    */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: THEO CTV
    |--------------------------------------------------------------------------
    */
    public function scopeOfCtv(Builder $query, int $ctvCustomerId): Builder
    {
        return $query->where('ctv_customer_id', $ctvCustomerId);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: THEO KHÁCH ĐƯỢC GIỚI THIỆU
    |--------------------------------------------------------------------------
    */
    public function scopeOfReferredCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('referred_customer_id', $customerId);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: THEO ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function scopeOfOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('customer_order_id', $orderId);
    }

    /*
    |--------------------------------------------------------------------------
    | HIỂN THỊ TRẠNG THÁI TIẾNG VIỆT
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
            default => 'Chưa thanh toán',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | TÊN CTV
    |--------------------------------------------------------------------------
    */
    public function getCtvNameAttribute(): string
    {
        $customer = $this->ctvCustomer ?: $this->referrerCustomer;

        return $this->getCustomerDisplayName(
            $customer,
            'CTV #' . ($this->ctv_customer_id ?? $this->referrer_customer_id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TÊN KHÁCH ĐƯỢC GIỚI THIỆU
    |--------------------------------------------------------------------------
    */
    public function getReferredNameAttribute(): string
    {
        return $this->getCustomerDisplayName(
            $this->referredCustomer,
            'Khách #' . $this->referred_customer_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SỐ TIỀN HOA HỒNG CÒN LẠI
    |--------------------------------------------------------------------------
    */
    public function getRemainingAmountAttribute(): float
    {
        return max(
            0,
            (float) $this->commission_amount - (float) $this->paid_amount
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ĐÃ THANH TOÁN CHƯA
    |--------------------------------------------------------------------------
    */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /*
    |--------------------------------------------------------------------------
    | CHƯA THANH TOÁN CHƯA
    |--------------------------------------------------------------------------
    */
    public function getIsUnpaidAttribute(): bool
    {
        return $this->status === 'unpaid';
    }

    /*
    |--------------------------------------------------------------------------
    | ĐÃ HỦY CHƯA
    |--------------------------------------------------------------------------
    */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: LẤY TÊN KHÁCH HÀNG AN TOÀN
    |--------------------------------------------------------------------------
    */
    private function getCustomerDisplayName(?Customer $customer, string $fallback): string
    {
        if (!$customer) {
            return $fallback;
        }

        return $customer->full_name
            ?? $customer->customer_name
            ?? $customer->name
            ?? $customer->phone
            ?? $fallback;
    }
}
