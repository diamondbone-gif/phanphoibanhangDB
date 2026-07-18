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

    public function adjust(Warehouse $warehouse, Product $product, int $delta, ?ProductBatch $batch = null): array
    {
        return DB::transaction(function () use ($warehouse, $product, $delta, $batch) {
            $stock = $this->lockedStock($warehouse, $product, $batch);
            $before = $stock->on_hand_quantity;
            $after = $before + $delta;
            if ($after < $stock->reserved_quantity || $after < 0) {
                throw new RuntimeException('Điều chỉnh làm tồn thực tế thấp hơn tồn giữ chỗ hoặc nhỏ hơn 0.');
            }
            $stock->on_hand_quantity = $after;
            $stock->assertValidQuantities();
            $stock->save();
            $this->refreshLegacyTotals($product, $batch);

            return [$stock, $before, $after];
        });
    }

    public function transfer(Warehouse $source, Warehouse $destination, Product $product, int $quantity, ?ProductBatch $batch = null): array
    {
        if ($source->is($destination) || $quantity <= 0) {
            throw new RuntimeException('Kho nguồn, kho đích hoặc số lượng chuyển không hợp lệ.');
        }

        return DB::transaction(function () use ($source, $destination, $product, $quantity, $batch) {
            $sourceStock = $this->lockedStock($source, $product, $batch);
            $destinationStock = $this->lockedStock($destination, $product, $batch);
            $sourceBefore = $sourceStock->on_hand_quantity;
            $destinationBefore = $destinationStock->on_hand_quantity;
            if ($sourceStock->available_quantity < $quantity) {
                throw new RuntimeException('Tồn khả dụng tại kho nguồn không đủ để chuyển.');
            }
            $sourceStock->on_hand_quantity -= $quantity;
            $destinationStock->on_hand_quantity += $quantity;
            $sourceStock->assertValidQuantities();
            $destinationStock->assertValidQuantities();
            $sourceStock->save();
            $destinationStock->save();

            return [$sourceStock, $destinationStock, $sourceBefore, $destinationBefore];
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

    private function refreshLegacyTotals(Product $product, ?ProductBatch $batch): void
    {
        if ($batch !== null) {
            $batch->update([
                'current_quantity' => WarehouseStock::query()->where('product_batch_id', $batch->id)->sum('on_hand_quantity'),
            ]);
        }
        $product->update([
            'total_quantity' => WarehouseStock::query()->where('product_id', $product->id)->sum('on_hand_quantity'),
        ]);
    }
}
