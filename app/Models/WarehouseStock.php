<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class WarehouseStock extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'on_hand_quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->on_hand_quantity - $this->reserved_quantity);
    }

    public function assertValidQuantities(): void
    {
        if ($this->on_hand_quantity < 0 || $this->reserved_quantity < 0 || $this->reserved_quantity > $this->on_hand_quantity) {
            throw new RuntimeException('Tồn kho, tồn giữ chỗ hoặc tồn khả dụng không hợp lệ.');
        }
    }
}
