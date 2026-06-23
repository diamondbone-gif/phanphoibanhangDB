<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'product_code',
        'product_name',

        'product_category_id',
        'product_unit_id',
        'brand_id',

        'product_type',
        'unit_name',

        'price',
        'cost_price',

        'main_image',
        'short_description',
        'description',

        'total_quantity',
        'track_batch',
        'track_expiry',
        'min_quantity_alert',

        'is_discountable',
        'is_commissionable',
        'default_commission_rate',
        'allow_sell_without_stock',

        'sort_order',
        'is_active',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'default_commission_rate' => 'decimal:2',

        'total_quantity' => 'integer',
        'min_quantity_alert' => 'integer',
        'sort_order' => 'integer',

        'track_batch' => 'boolean',
        'track_expiry' => 'boolean',
        'is_discountable' => 'boolean',
        'is_commissionable' => 'boolean',
        'allow_sell_without_stock' => 'boolean',
        'is_active' => 'boolean',

        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'image_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | DANH MỤC SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ĐƠN VỊ TÍNH
    |--------------------------------------------------------------------------
    */

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | THƯƠNG HIỆU
    |--------------------------------------------------------------------------
    */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH LÔ HÀNG
    |--------------------------------------------------------------------------
    */

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | LÔ HÀNG MỚI NHẤT
    |--------------------------------------------------------------------------
    */

    public function latestBatch(): HasOne
    {
        return $this->hasOne(ProductBatch::class, 'product_id')->latestOfMany('id');
    }

    /*
    |--------------------------------------------------------------------------
    | HÌNH ẢNH PHỤ CỦA SẢN PHẨM
    |--------------------------------------------------------------------------
    | Một sản phẩm có thể có nhiều hình ảnh.
    |--------------------------------------------------------------------------
    */

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HÌNH ẢNH CHÍNH CỦA SẢN PHẨM
    |--------------------------------------------------------------------------
    | Lấy hình ảnh được đánh dấu is_main = true.
    | Nếu có nhiều hình chính thì ưu tiên theo sort_order.
    |--------------------------------------------------------------------------
    */

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id')
            ->where('is_main', true)
            ->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ GIÁ
    |--------------------------------------------------------------------------
    */

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ NHẬP / XUẤT KHO
    |--------------------------------------------------------------------------
    */

    public function stockMovements(): HasMany
    {
        return $this->hasMany(ProductStockMovement::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SẢN PHẨM TRONG ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */

    public function orderItems(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO
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
    | LINK ẢNH CHÍNH
    |--------------------------------------------------------------------------
    | Ưu tiên lấy main_image trong bảng products.
    | Nếu main_image rỗng thì trả về null.
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->main_image)) {
            return null;
        }

        if (str_starts_with($this->main_image, 'http://') || str_starts_with($this->main_image, 'https://')) {
            return $this->main_image;
        }

        return asset('storage/' . ltrim($this->main_image, '/'));
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: SẢN PHẨM ĐANG HOẠT ĐỘNG
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: SẢN PHẨM CÓ THỂ BÁN
    |--------------------------------------------------------------------------
    */

    public function scopeSellable(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: SẮP HẾT HÀNG
    |--------------------------------------------------------------------------
    */

    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->whereNotNull('min_quantity_alert')
            ->whereColumn('total_quantity', '<=', 'min_quantity_alert')
            ->where('total_quantity', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: HẾT HÀNG
    |--------------------------------------------------------------------------
    */

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('total_quantity', '<=', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: SẢN PHẨM ĐƯỢC TÍNH HOA HỒNG
    |--------------------------------------------------------------------------
    */

    public function scopeCommissionable(Builder $query): Builder
    {
        return $query->where('is_commissionable', true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: SẢN PHẨM ĐƯỢC GIẢM GIÁ
    |--------------------------------------------------------------------------
    */

    public function scopeDiscountable(Builder $query): Builder
    {
        return $query->where('is_discountable', true);
    }
}
