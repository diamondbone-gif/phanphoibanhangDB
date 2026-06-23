<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDetail extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    | Model này dùng bảng customer_details.
    |--------------------------------------------------------------------------
    */
    protected $table = 'customer_details';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm/sửa bằng create() hoặc update().
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'customer_id',
        'province',
        'district',
        'ward',
        'address',
        'source_channel_id',
        'medical_note',
        'buy_for_option_id',
        'interested_product_id',
        'consultation_note',
    ];

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: CHI TIẾT KHÁCH HÀNG THUỘC VỀ KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: NGUỒN KHÁCH HÀNG
    |--------------------------------------------------------------------------
    | Ví dụ: Facebook, Zalo, Người quen giới thiệu...
    |--------------------------------------------------------------------------
    */
    public function sourceChannel(): BelongsTo
    {
        return $this->belongsTo(CustomerSourceChannel::class, 'source_channel_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: MUA CHO AI
    |--------------------------------------------------------------------------
    | Ví dụ: mua cho bản thân, mua cho người thân...
    |--------------------------------------------------------------------------
    */
    public function buyForOption(): BelongsTo
    {
        return $this->belongsTo(BuyForOption::class, 'buy_for_option_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ: SẢN PHẨM QUAN TÂM
    |--------------------------------------------------------------------------
    | Khách hàng đang quan tâm sản phẩm nào.
    |--------------------------------------------------------------------------
    */
    public function interestedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'interested_product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | FULL ADDRESS
    |--------------------------------------------------------------------------
    | Ghép địa chỉ đầy đủ từ:
    | Số nhà / đường + phường/xã + quận/huyện + tỉnh/thành.
    |
    | Cách gọi:
    | $customer->detail->full_address
    |--------------------------------------------------------------------------
    */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->ward,
            $this->district,
            $this->province,
        ]);

        return !empty($parts) ? implode(', ', $parts) : '---';
    }
}
