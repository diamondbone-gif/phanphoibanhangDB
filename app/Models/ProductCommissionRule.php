<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCommissionRule extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MODEL: PRODUCT COMMISSION RULE
    |--------------------------------------------------------------------------
    | Model này đại diện cho bảng product_commission_rules.
    |
    | Bảng này dùng để lưu quy tắc hoa hồng theo sản phẩm hoặc nhóm sản phẩm.
    |
    | Ví dụ:
    | - Sản phẩm ID 5 được hoa hồng 5%.
    | - Sản phẩm ID 6 được hoa hồng 5%.
    |
    | Khi bấm "Hoàn thành đơn", CommissionService sẽ đọc bảng này để tính
    | hoa hồng cho CTV / người giới thiệu.
    |--------------------------------------------------------------------------
    */

    protected $table = 'product_commission_rules';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm/sửa bằng create() hoặc update().
    |
    | Lưu ý:
    | - product_id: áp dụng hoa hồng theo từng sản phẩm cụ thể.
    | - product_category_id: áp dụng hoa hồng theo nhóm sản phẩm nếu có.
    | - commission_type: kiểu hoa hồng, ví dụ: percent hoặc fixed.
    | - commission_percent: phần trăm hoa hồng.
    | - commission_amount: số tiền hoa hồng cố định.
    | - start_date / end_date: cột mà CommissionService hiện tại đang dùng.
    | - effective_from / effective_to: cột cũ, giữ lại để tránh lỗi phần khác.
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'product_id',
        'product_category_id',

        'commission_type',
        'commission_percent',
        'commission_amount',

        'is_active',

        'start_date',
        'end_date',

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
    | Ép kiểu dữ liệu để Laravel xử lý đúng hơn.
    |
    | Ví dụ:
    | - is_active sẽ tự hiểu là true/false.
    | - start_date, end_date sẽ tự hiểu là kiểu ngày.
    | - commission_percent, commission_amount sẽ giữ đúng dạng số thập phân.
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'product_id' => 'integer',
        'product_category_id' => 'integer',

        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',

        'is_active' => 'boolean',

        'start_date' => 'date',
        'end_date' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',

        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | GIÁ TRỊ MẶC ĐỊNH
    |--------------------------------------------------------------------------
    | Nếu tạo rule mới nhưng chưa truyền đủ dữ liệu, Laravel sẽ dùng mặc định.
    |--------------------------------------------------------------------------
    */
    protected $attributes = [
        'commission_type' => 'percent',
        'commission_percent' => 0,
        'commission_amount' => 0,
        'is_active' => true,
    ];

    /*
    |--------------------------------------------------------------------------
    | QUAN HỆ VỚI SẢN PHẨM
    |--------------------------------------------------------------------------
    | Một quy tắc hoa hồng có thể thuộc về một sản phẩm.
    |--------------------------------------------------------------------------
    */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: CHỈ LẤY QUY TẮC ĐANG BẬT
    |--------------------------------------------------------------------------
    | Dùng khi cần lọc các quy tắc hoa hồng đang hoạt động.
    |
    | Ví dụ:
    | ProductCommissionRule::active()->get();
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: LỌC THEO NGÀY HIỆU LỰC
    |--------------------------------------------------------------------------
    | Dùng để lấy rule còn hiệu lực tại một ngày cụ thể.
    |
    | Nếu start_date = NULL: hiểu là có hiệu lực từ trước đến nay.
    | Nếu end_date = NULL: hiểu là chưa hết hiệu lực.
    |
    | Ví dụ:
    | ProductCommissionRule::effectiveAt(today())->get();
    |--------------------------------------------------------------------------
    */
    public function scopeEffectiveAt($query, $date)
    {
        return $query
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA RULE CÓ TÍNH THEO PHẦN TRĂM KHÔNG
    |--------------------------------------------------------------------------
    */
    public function isPercentType(): bool
    {
        return $this->commission_type === 'percent';
    }

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA RULE CÓ TÍNH THEO SỐ TIỀN CỐ ĐỊNH KHÔNG
    |--------------------------------------------------------------------------
    */
    public function isFixedType(): bool
    {
        return $this->commission_type === 'fixed';
    }
}
