<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    public function deductOrderItems(CustomerOrder $order, array $calculatedItems, ?int $adminId = null): void
    {
        foreach ($calculatedItems as $line) {
            $this->deductOneProduct($order, $line, $adminId);
        }
    }

    private function deductOneProduct(CustomerOrder $order, array $line, ?int $adminId = null): void
    {
        /** @var Product $product */
        $product = Product::query()->lockForUpdate()->findOrFail($line['product_id']);

        if ($product->product_type !== 'physical') {
            CustomerOrderItem::create($this->makeOrderItemData($order, $line, null, $line['quantity']));
            return;
        }

        if (!$product->allow_sell_without_stock && $product->total_quantity < $line['quantity']) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn kho.");
        }

        if (!$product->track_batch) {
            $before = (int) $product->total_quantity;
            $after = max(0, $before - $line['quantity']);

            $product->update(['total_quantity' => $after]);

            $item = CustomerOrderItem::create($this->makeOrderItemData($order, $line, null, $line['quantity']));

            $this->createMovement(
                productId: $product->id,
                batchId: null,
                orderId: $order->id,
                orderItemId: $item->id,
                movementType: 'sale',
                quantity: -$line['quantity'],
                beforeQuantity: $before,
                afterQuantity: $after,
                note: 'Xuất kho khi lên đơn hàng',
                adminId: $adminId
            );

            return;
        }

        $needQty = (int) $line['quantity'];

        $batches = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('current_quantity', '>', 0)
            ->whereIn('status', ['available'])
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($needQty <= 0) {
                break;
            }

            $takeQty = min($needQty, (int) $batch->current_quantity);

            $beforeBatchQty = (int) $batch->current_quantity;
            $afterBatchQty = $beforeBatchQty - $takeQty;

            $batch->update([
                'current_quantity' => $afterBatchQty,
                'status' => $afterBatchQty <= 0 ? 'out_of_stock' : $batch->status,
            ]);

            $product->decrement('total_quantity', $takeQty);

            $item = CustomerOrderItem::create(
                $this->makeOrderItemData($order, $line, $batch->id, $takeQty)
            );

            $this->createMovement(
                productId: $product->id,
                batchId: $batch->id,
                orderId: $order->id,
                orderItemId: $item->id,
                movementType: 'sale',
                quantity: -$takeQty,
                beforeQuantity: $beforeBatchQty,
                afterQuantity: $afterBatchQty,
                note: 'Xuất kho theo lô khi lên đơn hàng',
                adminId: $adminId
            );

            $needQty -= $takeQty;
        }

        if ($needQty > 0 && !$product->allow_sell_without_stock) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn theo lô.");
        }

        if ($needQty > 0 && $product->allow_sell_without_stock) {
            CustomerOrderItem::create($this->makeOrderItemData($order, $line, null, $needQty));
        }
    }

    public function returnOrderStock(CustomerOrder $order, string $movementType = 'cancel_return', ?int $adminId = null): void
    {
        foreach ($order->items as $item) {
            $product = Product::query()->lockForUpdate()->find($item->product_id);

            if (!$product || $product->product_type !== 'physical') {
                continue;
            }

            if ($item->product_batch_id) {
                $batch = ProductBatch::query()->lockForUpdate()->find($item->product_batch_id);

                if ($batch) {
                    $before = (int) $batch->current_quantity;
                    $after = $before + (int) $item->quantity;

                    $batch->update([
                        'current_quantity' => $after,
                        'status' => 'available',
                    ]);

                    $product->increment('total_quantity', (int) $item->quantity);

                    $this->createMovement(
                        productId: $product->id,
                        batchId: $batch->id,
                        orderId: $order->id,
                        orderItemId: $item->id,
                        movementType: $movementType,
                        quantity: (int) $item->quantity,
                        beforeQuantity: $before,
                        afterQuantity: $after,
                        note: 'Hoàn kho từ đơn hàng',
                        adminId: $adminId
                    );
                }
            } else {
                $before = (int) $product->total_quantity;
                $after = $before + (int) $item->quantity;

                $product->update(['total_quantity' => $after]);

                $this->createMovement(
                    productId: $product->id,
                    batchId: null,
                    orderId: $order->id,
                    orderItemId: $item->id,
                    movementType: $movementType,
                    quantity: (int) $item->quantity,
                    beforeQuantity: $before,
                    afterQuantity: $after,
                    note: 'Hoàn kho không theo lô',
                    adminId: $adminId
                );
            }
        }
    }

    private function makeOrderItemData(CustomerOrder $order, array $line, ?int $batchId, int $quantity): array
    {
        $originalTotal = $line['unit_price'] * $quantity;
        $discountAmount = round($originalTotal * $line['discount_percent'] / 100);
        $finalTotal = max(0, $originalTotal - $discountAmount);

        return [
            'customer_order_id' => $order->id,
            'product_id' => $line['product_id'],
            'product_batch_id' => $batchId,
            'product_code' => $line['product_code'],
            'product_name' => $line['product_name'],
            'quantity' => $quantity,
            'unit_price' => $line['unit_price'],
            'original_total' => $originalTotal,
            'discount_type' => $line['discount_type'],
            'discount_percent' => $line['discount_percent'],
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal,
        ];
    }

    private function createMovement(
        int $productId,
        ?int $batchId,
        int $orderId,
        ?int $orderItemId,
        string $movementType,
        int $quantity,
        int $beforeQuantity,
        int $afterQuantity,
        ?string $note = null,
        ?int $adminId = null
    ): void {
        ProductStockMovement::create([
            'product_id' => $productId,
            'product_batch_id' => $batchId,
            'customer_order_id' => $orderId,
            'customer_order_item_id' => $orderItemId,
            'movement_code' => $this->makeCode('MV', 'product_stock_movements', 'movement_code'),
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => $afterQuantity,
            'reference_type' => CustomerOrder::class,
            'reference_id' => $orderId,
            'movement_date' => now(),
            'note' => $note,
            'created_by' => $adminId,
        ]);
    }

    private function makeCode(string $prefix, string $table, string $column): string
    {
        do {
            $code = $prefix . now()->format('ymdHis') . random_int(100, 999);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
