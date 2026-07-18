<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'product_batches';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    | Các cột được phép thêm / sửa bằng create() hoặc update().
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'product_id',

        'supplier_id',
        'warehouse_id',
        'warehouse_location_id',

        'batch_code',
        'batch_number',

        'manufacture_date',
        'import_date',
        'expiry_date',

        'supplier_name',

        'initial_quantity',
        'current_quantity',

        'import_price',
        'unit_cost',

        'status',
        'is_active',
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
        'manufacture_date' => 'date',
        'import_date' => 'date',
        'expiry_date' => 'date',

        'initial_quantity' => 'integer',
        'current_quantity' => 'integer',

        'import_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',

        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | APPENDS
    |--------------------------------------------------------------------------
    | Các thuộc tính tự sinh thêm khi lấy dữ liệu ra.
    |--------------------------------------------------------------------------
    */

    protected $appends = [
        'sold_quantity',
        'status_text',
        'status_badge_class',
        'display_batch_code',
    ];

    /*
    |--------------------------------------------------------------------------
    | SẢN PHẨM CỦA LÔ HÀNG
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ NHẬP / XUẤT KHO CỦA LÔ HÀNG
    |--------------------------------------------------------------------------
    */

    public function movements(): HasMany
    {
        return $this->hasMany(ProductStockMovement::class, 'product_batch_id');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI TẠO LÔ HÀNG
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | NGƯỜI CẬP NHẬT LÔ HÀNG
    |--------------------------------------------------------------------------
    */

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(OperationManager::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SỐ LƯỢNG ĐÃ BÁN / ĐÃ XUẤT
    |--------------------------------------------------------------------------
    | Công thức:
    | initial_quantity - current_quantity
    |--------------------------------------------------------------------------
    */

    public function getSoldQuantityAttribute(): int
    {
        $initialQuantity = (int) ($this->initial_quantity ?? 0);
        $currentQuantity = (int) ($this->current_quantity ?? 0);

        $soldQuantity = $initialQuantity - $currentQuantity;

        return max($soldQuantity, 0);
    }

    /*
    |--------------------------------------------------------------------------
    | MÃ LÔ HIỂN THỊ
    |--------------------------------------------------------------------------
    | Hỗ trợ cả code cũ batch_number và code mới batch_code.
    |--------------------------------------------------------------------------
    */

    public function getDisplayBatchCodeAttribute(): ?string
    {
        return $this->batch_code ?: $this->batch_number;
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI LÔ HÀNG DẠNG CHỮ
    |--------------------------------------------------------------------------
    */

    public function getStatusTextAttribute(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Nếu có cột is_active và is_active = false thì xem như đã ẩn.
        | Nếu database chưa có is_active thì không ép là đã ẩn.
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('is_active', $this->attributes) && $this->is_active === false) {
            return 'Đã ẩn';
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu status đã lưu sẵn trong database thì ưu tiên diễn giải status.
        |--------------------------------------------------------------------------
        */

        if (! empty($this->status)) {
            return match ($this->status) {
                'active' => 'An toàn',
                'inactive' => 'Đã ẩn',
                'out_of_stock' => 'Hết hàng',
                'expired' => 'Hết hạn',
                'near_expiry' => 'Cận date',
                default => $this->status,
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu không có status thì tự tính theo số lượng và hạn dùng.
        |--------------------------------------------------------------------------
        */

        if ((int) ($this->current_quantity ?? 0) <= 0) {
            return 'Hết hàng';
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return 'Hết hạn';
        }

        if ($this->expiry_date && $this->expiry_date->lte(Carbon::now()->addMonths(6))) {
            return 'Cận date';
        }

        return 'An toàn';
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS BADGE HIỂN THỊ TRẠNG THÁI
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_text) {
            'Đã ẩn' => 'bg-secondary',
            'Hết hàng', 'Hết hạn' => 'bg-danger',
            'Cận date' => 'bg-warning text-dark',
            default => 'bg-success',
        };
    }
}
