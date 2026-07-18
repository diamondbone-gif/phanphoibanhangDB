<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WarehouseInventoryService
{
    public function defaultWarehouse(): Warehouse
    {
        return Warehouse::query()->where('is_default', true)->where('is_active', true)->firstOrFail();
    }

    public function reserve(Warehouse $warehouse, Product $product, int $quantity, ?ProductBatch $batch = null): WarehouseStock
    {
        return DB::transaction(function () use ($warehouse, $product, $quantity, $batch) {
            $stock = $this->lockedStock($warehouse, $product, $batch);
            if ($quantity <= 0 || $stock->available_quantity < $quantity) {
                throw new RuntimeException('Tồn khả dụng tại kho không đủ để giữ chỗ.');
            }
            $stock->reserved_quantity += $quantity;
            $stock->assertValidQuantities();
            $stock->save();

            return $stock;
        });
    }

    public function release(Warehouse $warehouse, Product $product, int $quantity, ?ProductBatch $batch = null): WarehouseStock
    {
        return DB::transaction(function () use ($warehouse, $product, $quantity, $batch) {
            $stock = $this->lockedStock($warehouse, $product, $batch);
            if ($quantity <= 0 || $quantity > $stock->reserved_quantity) {
                throw new RuntimeException('Số lượng giải phóng lớn hơn tồn đang giữ chỗ.');
            }
            $stock->reserved_quantity -= $quantity;
            $stock->save();

            return $stock;
        });
    }

    public function syncActualStock(Product $product, int $quantity, ?ProductBatch $batch = null, ?Warehouse $warehouse = null): WarehouseStock
    {
        $warehouse ??= $this->defaultWarehouse();

        return DB::transaction(function () use ($warehouse, $product, $quantity, $batch) {
            $stock = $this->lockedStock($warehouse, $product, $batch);
            if ($quantity < $stock->reserved_quantity) {
                throw new RuntimeException('Tồn thực tế không được thấp hơn tồn đang giữ chỗ.');
            }
            $stock->on_hand_quantity = $quantity;
            $stock->save();

            return $stock;
        });
    }

    private function lockedStock(Warehouse $warehouse, Product $product, ?ProductBatch $batch): WarehouseStock
    {
        $batchKey = $batch === null ? 0 : $batch->id;
        $stock = WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->where('batch_key', $batchKey)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        return WarehouseStock::query()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch?->id,
            'batch_key' => $batchKey,
            'on_hand_quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }
}
