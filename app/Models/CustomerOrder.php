<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasOne;

// class CustomerOrder extends Model
// {
//     use SoftDeletes;

//     protected $table = 'customer_orders';

//     /*
//     |--------------------------------------------------------------------------
//     | FILLABLE
//     |--------------------------------------------------------------------------
//     | Các cột được phép thêm/sửa bằng create() hoặc update()
//     |--------------------------------------------------------------------------
//     */

//     protected $fillable = [
//         'order_code',
//         'customer_id',

//         'order_status_id',
//         'payment_status_id',

//         'subtotal_amount',
//         'product_discount_amount',
//         'combo_discount_amount',
//         'order_discount_percent',
//         'order_discount_amount',
//         'final_amount',
//         'paid_amount',
//         'debt_amount',

//         'stock_reverted',
//         'commission_created',

//         'order_date',
//         'confirmed_by',
//         'completed_at',

//         'cancelled_by',
//         'cancelled_at',
//         'cancel_reason',

//         'created_by',
//         'updated_by',
//     ];

//     /*
//     |--------------------------------------------------------------------------
//     | CASTS
//     |--------------------------------------------------------------------------
//     | Ép kiểu dữ liệu khi Laravel lấy từ database
//     |--------------------------------------------------------------------------
//     */

//     protected $casts = [
//         'subtotal_amount' => 'decimal:2',
//         'product_discount_amount' => 'decimal:2',
//         'combo_discount_amount' => 'decimal:2',
//         'order_discount_percent' => 'decimal:2',
//         'order_discount_amount' => 'decimal:2',
//         'final_amount' => 'decimal:2',
//         'paid_amount' => 'decimal:2',
//         'debt_amount' => 'decimal:2',

//         'stock_reverted' => 'boolean',
//         'commission_created' => 'boolean',

//         'order_date' => 'datetime',
//         'completed_at' => 'datetime',
//         'cancelled_at' => 'datetime',
//         'deleted_at' => 'datetime',
//     ];

//     /*
//     |--------------------------------------------------------------------------
//     | ROUTE MODEL BINDING
//     |--------------------------------------------------------------------------
//     | Khi dùng route /orders/{order:order_code}, Laravel sẽ tìm theo order_code
//     | thay vì ID thật.
//     |--------------------------------------------------------------------------
//     */

//     public function getRouteKeyName(): string
//     {
//         return 'order_code';
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | KHÁCH HÀNG
//     |--------------------------------------------------------------------------
//     */

//     public function customer(): BelongsTo
//     {
//         return $this->belongsTo(Customer::class, 'customer_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | TRẠNG THÁI ĐƠN HÀNG
//     |--------------------------------------------------------------------------
//     */

//     public function orderStatus(): BelongsTo
//     {
//         return $this->belongsTo(OrderStatus::class, 'order_status_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | TRẠNG THÁI THANH TOÁN
//     |--------------------------------------------------------------------------
//     */

//     public function paymentStatus(): BelongsTo
//     {
//         return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | CHI TIẾT ĐƠN HÀNG
//     |--------------------------------------------------------------------------
//     */

//     public function items(): HasMany
//     {
//         return $this->hasMany(CustomerOrderItem::class, 'customer_order_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | HÓA ĐƠN
//     |--------------------------------------------------------------------------
//     */

//     public function invoice(): HasOne
//     {
//         return $this->hasOne(CustomerInvoice::class, 'customer_order_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | THANH TOÁN
//     |--------------------------------------------------------------------------
//     | Một đơn hàng có thể có nhiều lần thanh toán.
//     |--------------------------------------------------------------------------
//     */

//     public function payments(): HasMany
//     {
//         return $this->hasMany(Payment::class, 'customer_order_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | LỊCH SỬ ĐƠN HÀNG
//     |--------------------------------------------------------------------------
//     | Giữ latest('id') để lịch sử mới nhất nằm trên cùng.
//     |--------------------------------------------------------------------------
//     */

//     public function histories(): HasMany
//     {
//         return $this->hasMany(OrderHistory::class, 'customer_order_id')
//             ->latest('id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | HOA HỒNG
//     |--------------------------------------------------------------------------
//     | Một đơn hàng thường sinh một hoa hồng chính cho CTV.
//     |--------------------------------------------------------------------------
//     */

//     public function commission(): HasOne
//     {
//         return $this->hasOne(CustomerCommission::class, 'customer_order_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | DANH SÁCH HOA HỒNG
//     |--------------------------------------------------------------------------
//     | Giữ thêm hàm này nếu code cũ có gọi $order->commissions.
//     |--------------------------------------------------------------------------
//     */

//     public function commissions(): HasMany
//     {
//         return $this->hasMany(CustomerCommission::class, 'customer_order_id');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | NGƯỜI TẠO / NGƯỜI SỬA / NGƯỜI XÁC NHẬN / NGƯỜI HỦY
//     |--------------------------------------------------------------------------
//     | Nhóm tên cũ: creator, updater, confirmer, canceller.
//     | Giữ lại để không lỗi code cũ.
//     |--------------------------------------------------------------------------
//     */

//     public function creator(): BelongsTo
//     {
//         return $this->belongsTo(OperationManager::class, 'created_by');
//     }

//     public function updater(): BelongsTo
//     {
//         return $this->belongsTo(OperationManager::class, 'updated_by');
//     }

//     public function confirmer(): BelongsTo
//     {
//         return $this->belongsTo(OperationManager::class, 'confirmed_by');
//     }

//     public function canceller(): BelongsTo
//     {
//         return $this->belongsTo(OperationManager::class, 'cancelled_by');
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | ALIAS TÊN MỚI
//     |--------------------------------------------------------------------------
//     | Có thể dùng trong code mới:
//     | $order->createdBy
//     | $order->updatedBy
//     | $order->confirmedBy
//     | $order->cancelledBy
//     |--------------------------------------------------------------------------
//     */

//     public function createdBy(): BelongsTo
//     {
//         return $this->creator();
//     }

//     public function updatedBy(): BelongsTo
//     {
//         return $this->updater();
//     }

//     public function confirmedBy(): BelongsTo
//     {
//         return $this->confirmer();
//     }

//     public function cancelledBy(): BelongsTo
//     {
//         return $this->canceller();
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | HÀM KIỂM TRA TRẠNG THÁI NHANH
//     |--------------------------------------------------------------------------
//     */

//     public function isStockReverted(): bool
//     {
//         return (bool) $this->stock_reverted;
//     }

//     public function isCommissionCreated(): bool
//     {
//         return (bool) $this->commission_created;
//     }
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerOrder extends Model
{
    use SoftDeletes;

    protected $table = 'customer_orders';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm/sửa bằng create() hoặc update()
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'order_code',
        'customer_id',

        'order_status_id',
        'payment_status_id',

        'subtotal_amount',
        'product_discount_amount',
        'combo_discount_amount',
        'order_discount_percent',
        'order_discount_amount',
        'final_amount',
        'paid_amount',
        'debt_amount',

        'stock_reverted',
        'commission_created',

        'order_date',
        'confirmed_by',
        'completed_at',

        'cancelled_by',
        'cancelled_at',
        'cancel_reason',

        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    | Ép kiểu dữ liệu khi Laravel lấy từ database
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'product_discount_amount' => 'decimal:2',
        'combo_discount_amount' => 'decimal:2',
        'order_discount_percent' => 'decimal:2',
        'order_discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',

        'stock_reverted' => 'boolean',
        'commission_created' => 'boolean',

        'order_date' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | ROUTE MODEL BINDING
    |--------------------------------------------------------------------------
    | Khi dùng route /orders/{order:order_code}, Laravel sẽ tìm theo order_code
    | thay vì ID thật.
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    /*
    |--------------------------------------------------------------------------
    | KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI THANH TOÁN
    |--------------------------------------------------------------------------
    */

    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | CHI TIẾT ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HÓA ĐƠN
    |--------------------------------------------------------------------------
    */

    public function invoice(): HasOne
    {
        return $this->hasOne(CustomerInvoice::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | THANH TOÁN
    |--------------------------------------------------------------------------
    | Một đơn hàng có thể có nhiều lần thanh toán.
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Giữ latest('id') để lịch sử mới nhất nằm trên cùng.
    |--------------------------------------------------------------------------
    */

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'customer_order_id')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | HOA HỒNG CHÍNH
    |--------------------------------------------------------------------------
    | Một đơn hàng thường sinh một hoa hồng chính cho CTV.
    |--------------------------------------------------------------------------
    */

    public function commission(): HasOne
    {
        return $this->hasOne(CustomerCommission::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH HOA HỒNG
    |--------------------------------------------------------------------------
    | Giữ thêm hàm này nếu code cũ có gọi $order->commissions.
    |--------------------------------------------------------------------------
    */

    public function commissions(): HasMany
    {
        return $this->hasMany(CustomerCommission::class, 'customer_order_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO / NGƯỜI SỬA / NGƯỜI XÁC NHẬN / NGƯỜI HỦY
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'updated_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'confirmed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'cancelled_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ALIAS TÊN MỚI
    |--------------------------------------------------------------------------
    | Có thể dùng trong code mới:
    | $order->createdBy
    | $order->updatedBy
    | $order->confirmedBy
    | $order->cancelledBy
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->updater();
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->confirmer();
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->canceller();
    }

    /*
    |--------------------------------------------------------------------------
    | HÀM KIỂM TRA TRẠNG THÁI NHANH
    |--------------------------------------------------------------------------
    */

    public function isStockReverted(): bool
    {
        return (bool) $this->stock_reverted;
    }

    public function isCommissionCreated(): bool
    {
        return (bool) $this->commission_created;
    }
}
