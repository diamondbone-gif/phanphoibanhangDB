<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'full_name',
        'phone',
        'email',
        'gender',
        'birth_date',

        'customer_type_id',
        'customer_role_id',
        'customer_status_id',
        'ctv_status_id',

        /*
        |--------------------------------------------------------------------------
        | NGƯỜI GIỚI THIỆU TRỰC TIẾP
        |--------------------------------------------------------------------------
        | Dùng khi bảng customers có cột referrer_id.
        | Khách này được giới thiệu bởi khách/CTV khác.
        |--------------------------------------------------------------------------
        */
        'referrer_id',

        'created_by',
        'updated_by',

        'commission_rate',
        'ctv_approved_by',
        'ctv_approved_at',

        'stopped_reason',
        'stopped_at',

        'note',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'commission_rate' => 'decimal:2',
        'ctv_approved_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Loại khách
    |--------------------------------------------------------------------------
    | Ví dụ:
    | - Tự tìm đến
    | - CTV giới thiệu
    |--------------------------------------------------------------------------
    */

    public function type(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Vai trò khách hàng
    |--------------------------------------------------------------------------
    | Ví dụ:
    | - Khách
    | - CTV
    |--------------------------------------------------------------------------
    */

    public function role(): BelongsTo
    {
        return $this->belongsTo(CustomerRole::class, 'customer_role_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Trạng thái khách hàng
    |--------------------------------------------------------------------------
    | Ví dụ:
    | - Đang hoạt động
    | - Ngừng mua
    |--------------------------------------------------------------------------
    */

    public function status(): BelongsTo
    {
        return $this->belongsTo(CustomerStatus::class, 'customer_status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Trạng thái CTV
    |--------------------------------------------------------------------------
    | Ví dụ:
    | - Đang hoạt động
    | - Tạm ngưng
    |--------------------------------------------------------------------------
    */

    public function ctvStatus(): BelongsTo
    {
        return $this->belongsTo(CtvStatus::class, 'ctv_status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Chi tiết khách hàng
    |--------------------------------------------------------------------------
    | Lưu địa chỉ, khách mua cho ai, sản phẩm quan tâm, ghi chú tư vấn...
    |--------------------------------------------------------------------------
    */

    public function detail(): HasOne
    {
        return $this->hasOne(CustomerDetail::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Người giới thiệu trực tiếp
    |--------------------------------------------------------------------------
    | Nếu bảng customers có cột referrer_id:
    | - Customer hiện tại là khách được giới thiệu
    | - referrer là người/CTV giới thiệu khách này
    |--------------------------------------------------------------------------
    */

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Danh sách khách được người này giới thiệu trực tiếp
    |--------------------------------------------------------------------------
    | Nếu bảng customers có cột referrer_id.
    |--------------------------------------------------------------------------
    */

    public function referredCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'referrer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Nhu cầu khách hàng
    |--------------------------------------------------------------------------
    */

    public function needMaps(): HasMany
    {
        return $this->hasMany(CustomerNeedMap::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Đơn hàng của khách
    |--------------------------------------------------------------------------
    | Dùng để đếm số đơn:
    | withCount('orders')
    |--------------------------------------------------------------------------
    */

    public function orders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'customer_id')
            ->latest('order_date')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Lịch sử chăm sóc
    |--------------------------------------------------------------------------
    */

    public function careLogs(): HasMany
    {
        return $this->hasMany(CustomerCareLog::class, 'customer_id')
            ->latest('care_date')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Lịch nhắc chăm sóc
    |--------------------------------------------------------------------------
    */

    public function careReminders(): HasMany
    {
        return $this->hasMany(CustomerCareReminder::class, 'customer_id')
            ->latest('reminder_date')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Người giới thiệu khách này theo bảng customer_referrals
    |--------------------------------------------------------------------------
    | Khách B do CTV A giới thiệu
    | => Customer B sẽ có receivedReferral
    |--------------------------------------------------------------------------
    */

    public function receivedReferral(): HasOne
    {
        return $this->hasOne(CustomerReferral::class, 'referred_customer_id')
            ->whereNull('ended_at')
            ->latestOfMany();
    }

    /*
    |--------------------------------------------------------------------------
    | Danh sách khách mà người này đã giới thiệu theo bảng customer_referrals
    |--------------------------------------------------------------------------
    | CTV A giới thiệu nhiều khách
    | => Customer A sẽ có givenReferrals
    |--------------------------------------------------------------------------
    */

    public function givenReferrals(): HasMany
    {
        return $this->hasMany(CustomerReferral::class, 'referrer_customer_id')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: số đơn thực tế
    |--------------------------------------------------------------------------
    */

    public function getOrderCountValue(): int
    {
        if (array_key_exists('orders_count', $this->attributes)) {
            return (int) $this->attributes['orders_count'];
        }

        if ($this->relationLoaded('orders')) {
            return $this->orders->count();
        }

        return $this->orders()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: nhãn tình trạng mua
    |--------------------------------------------------------------------------
    | 0 đơn      = Chưa mua
    | 1 đơn      = Đã mua
    | từ 2 đơn   = Mua lại
    |--------------------------------------------------------------------------
    */

    public function getBuyStatusLabel(): string
    {
        $orderCount = $this->getOrderCountValue();

        if ($orderCount === 0) {
            return 'Chưa mua';
        }

        if ($orderCount === 1) {
            return 'Đã mua';
        }

        return 'Mua lại';
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: kiểm tra CTV
    |--------------------------------------------------------------------------
    */

    public function isCtv(): bool
    {
        $roleName = mb_strtolower((string) ($this->role?->name ?? ''));
        $roleCode = mb_strtolower((string) ($this->role?->code ?? ''));

        return $roleName === 'ctv' || str_contains($roleCode, 'ctv');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: kiểm tra khách có trạng thái cảnh báo không
    |--------------------------------------------------------------------------
    */

    public function hasWarningStatus(): bool
    {
        $statusCode = mb_strtolower((string) ($this->status?->code ?? ''));

        if ($statusCode === '') {
            return false;
        }

        return !in_array($statusCode, [
            'active',
            'new',
            'moi',
            'dang_hoat_dong',
            'hoat_dong',
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: hiển thị địa chỉ đầy đủ
    |--------------------------------------------------------------------------
    | Dùng để lấy địa chỉ khách hàng từ bảng customer_details.
    |
    | Cách gọi:
    | $customer->display_address
    |
    | Nếu khách chưa có detail thì trả về ---
    |--------------------------------------------------------------------------
    */

    public function getDisplayAddressAttribute(): string
    {
        if (!$this->detail) {
            return '---';
        }

        return $this->detail->full_address;
    }
}