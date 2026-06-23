<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class CustomerReferral extends Model
// {
//     protected $table = 'customer_referrals';

//     protected $fillable = [
//         'referrer_customer_id',
//         'referred_customer_id',
//         'referrer_phone',
//         'commission_rate',
//         'referral_status_id',
//         'started_at',
//         'ended_at',
//         'note',
//     ];

//     protected $casts = [
//         'commission_rate' => 'decimal:2',
//         'started_at' => 'datetime',
//         'ended_at' => 'datetime',
//     ];

//     public function referrer(): BelongsTo
//     {
//         return $this->belongsTo(Customer::class, 'referrer_customer_id');
//     }

//     public function referred(): BelongsTo
//     {
//         return $this->belongsTo(Customer::class, 'referred_customer_id');
//     }

//     public function referralStatus(): BelongsTo
//     {
//         return $this->belongsTo(ReferralStatus::class, 'referral_status_id');
//     }
// }




namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReferral extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    | Bảng lưu quan hệ khách hàng được CTV / khách hàng khác giới thiệu.
    |--------------------------------------------------------------------------
    */

    protected $table = 'customer_referrals';


    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm / sửa bằng create() hoặc update().
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'referrer_customer_id',
        'referred_customer_id',

        'referrer_phone',
        'commission_rate',

        'referral_status_id',

        'started_at',
        'ended_at',

        'effective_from',
        'effective_to',

        'note',
        'created_by',
        'updated_by',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'commission_rate' => 'decimal:2',

        'started_at' => 'datetime',
        'ended_at' => 'datetime',

        'effective_from' => 'date',
        'effective_to' => 'date',
    ];


    /*
    |--------------------------------------------------------------------------
    | NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    | Đây là khách hàng / CTV giới thiệu người khác.
    |--------------------------------------------------------------------------
    */

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }


    /*
    |--------------------------------------------------------------------------
    | NGƯỜI ĐƯỢC GIỚI THIỆU
    |--------------------------------------------------------------------------
    | Đây là khách hàng được người khác giới thiệu.
    |--------------------------------------------------------------------------
    */

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }


    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI GIỚI THIỆU
    |--------------------------------------------------------------------------
    | Ví dụ: đang hiệu lực, đã kết thúc, tạm ngưng...
    |--------------------------------------------------------------------------
    */

    public function referralStatus(): BelongsTo
    {
        return $this->belongsTo(ReferralStatus::class, 'referral_status_id');
    }


    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO
    |--------------------------------------------------------------------------
    | Admin / nhân sự vận hành tạo liên kết giới thiệu này.
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }


    /*
    |--------------------------------------------------------------------------
    | NGƯỜI CẬP NHẬT
    |--------------------------------------------------------------------------
    */

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'updated_by');
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPE: ĐANG HIỆU LỰC
    |--------------------------------------------------------------------------
    | Lấy các liên kết giới thiệu còn hiệu lực.
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $q) {
                $q->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->toDateString());
            });
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPE: THEO NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    */

    public function scopeByReferrer(Builder $query, int $referrerCustomerId): Builder
    {
        return $query->where('referrer_customer_id', $referrerCustomerId);
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPE: THEO NGƯỜI ĐƯỢC GIỚI THIỆU
    |--------------------------------------------------------------------------
    */

    public function scopeByReferred(Builder $query, int $referredCustomerId): Builder
    {
        return $query->where('referred_customer_id', $referredCustomerId);
    }
}
