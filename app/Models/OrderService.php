<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class OrderService
{
    /*
    |--------------------------------------------------------------------------
    | TẠO ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | JS chỉ tạm tính ở giao diện.
    | Khi bấm Tạo đơn hàng, Laravel sẽ tính lại, lưu đơn, trừ kho,
    | tạo hóa đơn và ghi lịch sử.
    |--------------------------------------------------------------------------
    */
    public function create(array $data, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($data, $adminId) {
            $calc = $this->calculateOrder($data);

            $orderCode = $this->makeCode('DH', 'customer_orders', 'order_code');

            $orderData = [
                'order_code' => $orderCode,
                'customer_id' => $data['customer_id'],

                'order_status_id' => $this->statusId('order_statuses', 'pending'),
                'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),

                // Cột cũ nếu database đang có
                'total_amount' => $calc['final_amount'],
                'discount_amount' => $calc['product_discount_amount'] + $calc['order_discount_amount'],
                'shipping_fee' => 0,
                'commission_base_amount' => $calc['final_amount'],

                // Cột mới cho logic bán hàng
                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => 0,
                'order_discount_percent' => $calc['order_discount_percent'],
                'order_discount_amount' => $calc['order_discount_amount'],
                'final_amount' => $calc['final_amount'],
                'paid_amount' => $calc['paid_amount'],
                'debt_amount' => $calc['debt_amount'],

                'stock_reverted' => false,
                'commission_created' => false,

                'order_date' => now(),
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ];

            $orderId = $this->insertRow('customer_orders', $orderData);

            /** @var CustomerOrder $order */
            $order = CustomerOrder::query()->findOrFail($orderId);

            $this->deductStockAndCreateItems($order, $calc['items'], $adminId);
            $this->createInvoice($order, $calc, $data, $adminId);
            $this->createPaymentIfNeeded($order, $calc, $data, $adminId);

            $this->writeHistory(
                order: $order,
                action: 'created',
                oldData: null,
                newData: $order->fresh()->toArray(),
                note: 'Tạo đơn hàng',
                adminId: $adminId
            );

            return $order->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function update(CustomerOrder $order, array $data, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $data, $adminId) {
            /** @var CustomerOrder $order */
            $order = CustomerOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldData = $order->fresh()->toArray();

            $cancelledId = $this->statusId('order_statuses', 'cancelled');

            if ((int) $order->order_status_id === (int) $cancelledId) {
                throw new RuntimeException('Đơn hàng đã hủy, không thể sửa.');
            }

            $this->returnStock($order, 'edit_return', $adminId);

            if (Schema::hasTable('customer_order_items')) {
                DB::table('customer_order_items')
                    ->where('customer_order_id', $order->id)
                    ->delete();
            }

            $this->reverseCommission($order, 'Đảo hoa hồng do sửa đơn hàng', $adminId);

            $calc = $this->calculateOrder($data);

            $updateData = [
                'customer_id' => $data['customer_id'],

                'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),

                'total_amount' => $calc['final_amount'],
                'discount_amount' => $calc['product_discount_amount'] + $calc['order_discount_amount'],
                'shipping_fee' => 0,
                'commission_base_amount' => $calc['final_amount'],

                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => 0,
                'order_discount_percent' => $calc['order_discount_percent'],
                'order_discount_amount' => $calc['order_discount_amount'],
                'final_amount' => $calc['final_amount'],
                'paid_amount' => $calc['paid_amount'],
                'debt_amount' => $calc['debt_amount'],

                'stock_reverted' => false,
                'commission_created' => false,

                'updated_by' => $adminId,
            ];

            $this->updateRow('customer_orders', $order->id, $updateData);

            $order = CustomerOrder::query()->findOrFail($order->id);

            $this->deductStockAndCreateItems($order, $calc['items'], $adminId);
            $this->updateInvoice($order, $calc, $data);

            $completedId = $this->statusId('order_statuses', 'completed');

            if ((int) $order->order_status_id === (int) $completedId) {
                $this->createCommissionForCompletedOrder($order, $adminId);
            }

            $this->writeHistory(
                order: $order,
                action: 'updated',
                oldData: $oldData,
                newData: $order->fresh()->toArray(),
                note: 'Cập nhật đơn hàng',
                adminId: $adminId
            );

            return $order->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HOÀN THÀNH ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Chỉ khi hoàn thành mới tính hoa hồng.
    |--------------------------------------------------------------------------
    */
    public function complete(CustomerOrder $order, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $adminId) {
            /** @var CustomerOrder $order */
            $order = CustomerOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldData = $order->fresh()->toArray();

            $cancelledId = $this->statusId('order_statuses', 'cancelled');

            if ((int) $order->order_status_id === (int) $cancelledId) {
                throw new RuntimeException('Đơn hàng đã hủy, không thể hoàn thành.');
            }

            $updateData = [
                'order_status_id' => $this->statusId('order_statuses', 'completed'),
                'completed_at' => now(),
                'confirmed_by' => $adminId,
                'updated_by' => $adminId,
            ];

            $this->updateRow('customer_orders', $order->id, $updateData);

            $order = CustomerOrder::query()->findOrFail($order->id);

            $this->createCommissionForCompletedOrder($order, $adminId);

            $this->writeHistory(
                order: $order,
                action: 'completed',
                oldData: $oldData,
                newData: $order->fresh()->toArray(),
                note: 'Hoàn thành đơn hàng',
                adminId: $adminId
            );

            return $order->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HỦY ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Khi hủy:
    | - hoàn kho
    | - hủy hóa đơn
    | - trừ/hủy hoa hồng
    | - ghi lịch sử
    |--------------------------------------------------------------------------
    */
    public function cancel(CustomerOrder $order, string $reason, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $reason, $adminId) {
            /** @var CustomerOrder $order */
            $order = CustomerOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldData = $order->fresh()->toArray();

            $cancelledId = $this->statusId('order_statuses', 'cancelled');

            if ((int) $order->order_status_id === (int) $cancelledId) {
                return $order;
            }

            if (!$order->stock_reverted) {
                $this->returnStock($order, 'cancel_return', $adminId);
            }

            $updateData = [
                'order_status_id' => $cancelledId,
                'cancelled_by' => $adminId,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'stock_reverted' => true,
                'updated_by' => $adminId,
            ];

            $this->updateRow('customer_orders', $order->id, $updateData);

            $this->voidInvoice($order, $reason);
            $this->reverseCommission($order, $reason, $adminId);

            $order = CustomerOrder::query()->findOrFail($order->id);

            $this->writeHistory(
                order: $order,
                action: 'cancelled',
                oldData: $oldData,
                newData: $order->fresh()->toArray(),
                note: $reason,
                adminId: $adminId
            );

            return $order->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | XÓA MỀM ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    public function delete(CustomerOrder $order, string $reason, ?int $adminId = null): void
    {
        DB::transaction(function () use ($order, $reason, $adminId) {
            $order = $this->cancel($order, $reason, $adminId);

            $this->writeHistory(
                order: $order,
                action: 'deleted',
                oldData: $order->fresh()->toArray(),
                newData: null,
                note: 'Xóa mềm đơn hàng: ' . $reason,
                adminId: $adminId
            );

            $order->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | TÍNH TOÁN ĐƠN HÀNG PHÍA SERVER
    |--------------------------------------------------------------------------
    */
    private function calculateOrder(array $data): array
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new RuntimeException('Đơn hàng phải có ít nhất 1 sản phẩm.');
        }

        $items = [];
        $subtotal = 0;
        $productDiscountAmount = 0;

        foreach ($data['items'] as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $quantity = max(1, (int) ($row['quantity'] ?? 1));
            $discountPercent = (float) ($row['discount_percent'] ?? 0);
            $discountPercent = max(0, min(100, $discountPercent));

            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            $price = (float) ($product->price ?? 0);

            $isDiscountable = true;

            if (Schema::hasColumn('products', 'is_discountable')) {
                $isDiscountable = (bool) $product->is_discountable;
            }

            if (!$isDiscountable) {
                $discountPercent = 0;
            }

            $originalTotal = $price * $quantity;
            $discountAmount = round($originalTotal * $discountPercent / 100);
            $finalTotal = max(0, $originalTotal - $discountAmount);

            $subtotal += $originalTotal;
            $productDiscountAmount += $discountAmount;

            $items[] = [
                'product' => $product,
                'product_id' => $product->id,
                'product_code' => $product->product_code ?? '',
                'product_name' => $product->product_name ?? '',
                'quantity' => $quantity,
                'unit_price' => $price,
                'original_total' => $originalTotal,
                'discount_type' => $discountPercent > 0 ? 'product' : 'none',
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
            ];
        }

        $orderDiscountPercent = (float) ($data['order_discount_percent'] ?? 0);
        $orderDiscountPercent = max(0, min(100, $orderDiscountPercent));

        $afterProductDiscount = max(0, $subtotal - $productDiscountAmount);
        $orderDiscountAmount = round($afterProductDiscount * $orderDiscountPercent / 100);
        $finalAmount = max(0, $afterProductDiscount - $orderDiscountAmount);

        $paidAmount = (float) ($data['paid_amount'] ?? 0);
        $paidAmount = max(0, min($finalAmount, $paidAmount));

        $debtAmount = max(0, $finalAmount - $paidAmount);

        return [
            'items' => $items,
            'subtotal_amount' => $subtotal,
            'product_discount_amount' => $productDiscountAmount,
            'order_discount_percent' => $orderDiscountPercent,
            'order_discount_amount' => $orderDiscountAmount,
            'final_amount' => $finalAmount,
            'paid_amount' => $paidAmount,
            'debt_amount' => $debtAmount,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TRỪ KHO VÀ TẠO CHI TIẾT ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    private function deductStockAndCreateItems(CustomerOrder $order, array $items, ?int $adminId = null): void
    {
        foreach ($items as $item) {
            $product = Product::query()
                ->whereKey($item['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $productType = Schema::hasColumn('products', 'product_type')
                ? ($product->product_type ?? 'physical')
                : 'physical';

            $allowSellWithoutStock = Schema::hasColumn('products', 'allow_sell_without_stock')
                ? (bool) $product->allow_sell_without_stock
                : false;

            $trackBatch = Schema::hasColumn('products', 'track_batch')
                ? (bool) $product->track_batch
                : false;

            if ($productType !== 'physical') {
                $this->createOrderItem($order, $item, null, $item['quantity']);
                continue;
            }

            if (Schema::hasColumn('products', 'total_quantity')) {
                $currentTotal = (int) ($product->total_quantity ?? 0);

                if (!$allowSellWithoutStock && $currentTotal < $item['quantity']) {
                    throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn kho.");
                }
            }

            if (
                $trackBatch &&
                Schema::hasTable('product_batches') &&
                Schema::hasColumn('product_batches', 'current_quantity')
            ) {
                $this->deductByBatches($order, $product, $item, $allowSellWithoutStock, $adminId);
                continue;
            }

            $beforeQuantity = Schema::hasColumn('products', 'total_quantity')
                ? (int) ($product->total_quantity ?? 0)
                : 0;

            if (Schema::hasColumn('products', 'total_quantity')) {
                $afterQuantity = max(0, $beforeQuantity - (int) $item['quantity']);

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['total_quantity' => $afterQuantity]);
            } else {
                $afterQuantity = 0;
            }

            $orderItemId = $this->createOrderItem($order, $item, null, $item['quantity']);

            $this->createStockMovement([
                'product_id' => $product->id,
                'product_batch_id' => null,
                'customer_order_id' => $order->id,
                'customer_order_item_id' => $orderItemId,
                'movement_type' => 'sale',
                'quantity' => -abs((int) $item['quantity']),
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $afterQuantity,
                'note' => 'Xuất kho khi tạo đơn hàng',
                'created_by' => $adminId,
            ]);
        }
    }

    private function deductByBatches(
        CustomerOrder $order,
        Product $product,
        array $item,
        bool $allowSellWithoutStock,
        ?int $adminId = null
    ): void {
        $needQty = (int) $item['quantity'];

        $batchQuery = DB::table('product_batches')
            ->where('product_id', $product->id)
            ->where('current_quantity', '>', 0)
            ->orderBy('id');

        if (Schema::hasColumn('product_batches', 'expiry_date')) {
            $batchQuery->orderByRaw('expiry_date IS NULL, expiry_date ASC');
        }

        $batches = $batchQuery->lockForUpdate()->get();

        foreach ($batches as $batch) {
            if ($needQty <= 0) {
                break;
            }

            $takeQty = min($needQty, (int) $batch->current_quantity);

            $beforeBatchQty = (int) $batch->current_quantity;
            $afterBatchQty = $beforeBatchQty - $takeQty;

            $batchUpdate = ['current_quantity' => $afterBatchQty];

            if (Schema::hasColumn('product_batches', 'status')) {
                $batchUpdate['status'] = $afterBatchQty <= 0 ? 'out_of_stock' : ($batch->status ?? 'available');
            }

            DB::table('product_batches')
                ->where('id', $batch->id)
                ->update($batchUpdate);

            if (Schema::hasColumn('products', 'total_quantity')) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->decrement('total_quantity', $takeQty);
            }

            $line = $item;
            $line['quantity'] = $takeQty;

            $orderItemId = $this->createOrderItem($order, $line, $batch->id, $takeQty);

            $this->createStockMovement([
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'customer_order_id' => $order->id,
                'customer_order_item_id' => $orderItemId,
                'movement_type' => 'sale',
                'quantity' => -abs($takeQty),
                'before_quantity' => $beforeBatchQty,
                'after_quantity' => $afterBatchQty,
                'note' => 'Xuất kho theo lô khi tạo đơn hàng',
                'created_by' => $adminId,
            ]);

            $needQty -= $takeQty;
        }

        if ($needQty > 0 && !$allowSellWithoutStock) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn theo lô.");
        }

        if ($needQty > 0 && $allowSellWithoutStock) {
            $line = $item;
            $line['quantity'] = $needQty;

            $this->createOrderItem($order, $line, null, $needQty);
        }
    }

    private function createOrderItem(CustomerOrder $order, array $item, ?int $batchId, int $quantity): int
    {
        if (!Schema::hasTable('customer_order_items')) {
            throw new RuntimeException('Chưa có bảng customer_order_items.');
        }

        $originalTotal = $item['unit_price'] * $quantity;
        $discountAmount = round($originalTotal * $item['discount_percent'] / 100);
        $finalTotal = max(0, $originalTotal - $discountAmount);

        $data = [
            'customer_order_id' => $order->id,
            'order_id' => $order->id,

            'product_id' => $item['product_id'],
            'product_batch_id' => $batchId,

            'product_code' => $item['product_code'],
            'product_name' => $item['product_name'],

            'quantity' => $quantity,
            'qty' => $quantity,

            'unit_price' => $item['unit_price'],
            'price' => $item['unit_price'],

            'original_total' => $originalTotal,
            'discount_type' => $item['discount_type'],
            'discount_percent' => $item['discount_percent'],
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal,
            'amount' => $finalTotal,
        ];

        return $this->insertRow('customer_order_items', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | HOÀN KHO
    |--------------------------------------------------------------------------
    */
    private function returnStock(CustomerOrder $order, string $movementType, ?int $adminId = null): void
    {
        if (!Schema::hasTable('customer_order_items')) {
            return;
        }

        $items = DB::table('customer_order_items')
            ->where('customer_order_id', $order->id)
            ->get();

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $quantity = (int) ($item->quantity ?? $item->qty ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            if (Schema::hasColumn('products', 'total_quantity')) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->increment('total_quantity', $quantity);
            }

            $batchId = $item->product_batch_id ?? null;

            if (
                $batchId &&
                Schema::hasTable('product_batches') &&
                Schema::hasColumn('product_batches', 'current_quantity')
            ) {
                $batch = DB::table('product_batches')
                    ->where('id', $batchId)
                    ->lockForUpdate()
                    ->first();

                if ($batch) {
                    $before = (int) $batch->current_quantity;
                    $after = $before + $quantity;

                    $batchUpdate = ['current_quantity' => $after];

                    if (Schema::hasColumn('product_batches', 'status')) {
                        $batchUpdate['status'] = 'available';
                    }

                    DB::table('product_batches')
                        ->where('id', $batchId)
                        ->update($batchUpdate);

                    $this->createStockMovement([
                        'product_id' => $item->product_id,
                        'product_batch_id' => $batchId,
                        'customer_order_id' => $order->id,
                        'customer_order_item_id' => $item->id,
                        'movement_type' => $movementType,
                        'quantity' => $quantity,
                        'before_quantity' => $before,
                        'after_quantity' => $after,
                        'note' => 'Hoàn kho từ đơn hàng',
                        'created_by' => $adminId,
                    ]);
                }
            } else {
                $this->createStockMovement([
                    'product_id' => $item->product_id,
                    'product_batch_id' => null,
                    'customer_order_id' => $order->id,
                    'customer_order_item_id' => $item->id,
                    'movement_type' => $movementType,
                    'quantity' => $quantity,
                    'before_quantity' => 0,
                    'after_quantity' => 0,
                    'note' => 'Hoàn kho từ đơn hàng',
                    'created_by' => $adminId,
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HÓA ĐƠN
    |--------------------------------------------------------------------------
    */
    private function createInvoice(CustomerOrder $order, array $calc, array $data, ?int $adminId = null): void
    {
        if (!Schema::hasTable('customer_invoices')) {
            throw new RuntimeException('Chưa có bảng customer_invoices.');
        }

        $invoiceData = [
            'invoice_code' => $this->makeCode('HD', 'customer_invoices', 'invoice_code'),
            'customer_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => $calc['subtotal_amount'],
            'tax_amount' => 0,
            'final_amount' => $calc['final_amount'],
            'status' => 'issued',
            'note' => $data['note'] ?? null,
            'created_by' => $adminId,
        ];

        $this->insertRow('customer_invoices', $invoiceData);
    }

    private function updateInvoice(CustomerOrder $order, array $calc, array $data): void
    {
        if (!Schema::hasTable('customer_invoices')) {
            return;
        }

        $invoice = DB::table('customer_invoices')
            ->where('customer_order_id', $order->id)
            ->first();

        if (!$invoice) {
            return;
        }

        $updateData = [
            'customer_id' => $order->customer_id,
            'total_amount' => $calc['subtotal_amount'],
            'final_amount' => $calc['final_amount'],
            'note' => $data['note'] ?? null,
        ];

        $this->updateRow('customer_invoices', $invoice->id, $updateData);
    }

    private function voidInvoice(CustomerOrder $order, string $reason): void
    {
        if (!Schema::hasTable('customer_invoices')) {
            return;
        }

        $invoice = DB::table('customer_invoices')
            ->where('customer_order_id', $order->id)
            ->first();

        if (!$invoice) {
            return;
        }

        $oldNote = $invoice->note ?? '';

        $updateData = [
            'status' => 'void',
            'note' => trim($oldNote . "\nHủy hóa đơn: " . $reason),
        ];

        $this->updateRow('customer_invoices', $invoice->id, $updateData);
    }

    /*
    |--------------------------------------------------------------------------
    | THANH TOÁN
    |--------------------------------------------------------------------------
    */
    private function createPaymentIfNeeded(CustomerOrder $order, array $calc, array $data, ?int $adminId = null): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        if ($calc['paid_amount'] <= 0) {
            return;
        }

        $paymentData = [
            'customer_order_id' => $order->id,
            'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),
            'payment_code' => $this->makeCode('PAY', 'payments', 'payment_code'),
            'amount' => $calc['paid_amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_date' => now(),
            'note' => 'Thanh toán khi tạo đơn hàng',
            'created_by' => $adminId,
        ];

        $this->insertRow('payments', $paymentData);
    }

    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ ĐƠN HÀNG
    |--------------------------------------------------------------------------
    */
    private function writeHistory(
        CustomerOrder $order,
        string $action,
        ?array $oldData,
        ?array $newData,
        ?string $note,
        ?int $adminId
    ): void {
        if (!Schema::hasTable('order_histories')) {
            return;
        }

        $historyData = [
            'customer_order_id' => $order->id,
            'action' => $action,
            'old_status_id' => $oldData['order_status_id'] ?? null,
            'new_status_id' => $newData['order_status_id'] ?? null,
            'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note' => $note,
            'created_by' => $adminId,
        ];

        $this->insertRow('order_histories', $historyData);
    }

    /*
    |--------------------------------------------------------------------------
    | HOA HỒNG
    |--------------------------------------------------------------------------
    */
    private function createCommissionForCompletedOrder(CustomerOrder $order, ?int $adminId = null): void
    {
        if (!Schema::hasTable('customer_commissions')) {
            return;
        }

        if (!Schema::hasTable('customer_referrals')) {
            return;
        }

        if ($order->commission_created) {
            return;
        }

        $referral = DB::table('customer_referrals')
            ->where('referred_customer_id', $order->customer_id)
            ->orderByDesc('id')
            ->first();

        if (!$referral) {
            return;
        }

        if (!Schema::hasTable('customer_order_items')) {
            return;
        }

        $items = DB::table('customer_order_items')
            ->where('customer_order_id', $order->id)
            ->get();

        $totalBase = 0;
        $totalCommission = 0;
        $commissionLines = [];

        foreach ($items as $item) {
            $product = DB::table('products')
                ->where('id', $item->product_id)
                ->first();

            if (!$product) {
                continue;
            }

            $isCommissionable = true;

            if (Schema::hasColumn('products', 'is_commissionable')) {
                $isCommissionable = (bool) ($product->is_commissionable ?? true);
            }

            if (!$isCommissionable) {
                continue;
            }

            $rate = 0;

            if (Schema::hasColumn('products', 'default_commission_rate')) {
                $rate = (float) ($product->default_commission_rate ?? 0);
            }

            if ($rate <= 0) {
                continue;
            }

            $eligibleAmount = (float) ($item->final_total ?? $item->amount ?? 0);
            $commissionAmount = round($eligibleAmount * $rate / 100);

            if ($commissionAmount <= 0) {
                continue;
            }

            $totalBase += $eligibleAmount;
            $totalCommission += $commissionAmount;

            $commissionLines[] = [
                'customer_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name ?? '',
                'eligible_amount' => $eligibleAmount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
            ];
        }

        if ($totalCommission <= 0) {
            return;
        }

        $commissionData = [
            'referrer_customer_id' => $referral->referrer_customer_id ?? null,
            'referred_customer_id' => $order->customer_id,
            'referral_id' => $referral->id,
            'customer_order_id' => $order->id,
            'order_code' => $order->order_code,
            'order_amount' => $order->final_amount ?? $order->total_amount ?? 0,
            'commission_base_amount' => $totalBase,
            'commission_rate' => $totalBase > 0 ? round($totalCommission / $totalBase * 100, 2) : 0,
            'commission_amount' => $totalCommission,
            'commission_status_id' => $this->statusId('commission_statuses', 'pending'),
        ];

        $commissionId = $this->insertRow('customer_commissions', $commissionData);

        if (Schema::hasTable('customer_commission_items')) {
            foreach ($commissionLines as $line) {
                $line['customer_commission_id'] = $commissionId;
                $this->insertRow('customer_commission_items', $line);
            }
        }

        $this->updateRow('customer_orders', $order->id, [
            'commission_created' => true,
        ]);
    }

    private function reverseCommission(CustomerOrder $order, string $reason, ?int $adminId = null): void
    {
        if (!Schema::hasTable('customer_commissions')) {
            return;
        }

        $commission = DB::table('customer_commissions')
            ->where('customer_order_id', $order->id)
            ->first();

        if (!$commission) {
            return;
        }

        if (Schema::hasTable('customer_commission_adjustments')) {
            $adjustmentData = [
                'customer_commission_id' => $commission->id,
                'adjustment_code' => $this->makeCode('ADJ', 'customer_commission_adjustments', 'adjustment_code'),
                'adjustment_type' => 'reverse',
                'amount' => -abs((float) ($commission->commission_amount ?? 0)),
                'reason' => $reason,
                'created_by' => $adminId,
            ];

            $this->insertRow('customer_commission_adjustments', $adjustmentData);
        }

        $this->updateRow('customer_commissions', $commission->id, [
            'commission_status_id' => $this->statusId('commission_statuses', 'cancelled'),
            'cancelled_reason' => $reason,
        ]);

        $this->updateRow('customer_orders', $order->id, [
            'commission_created' => false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK MOVEMENT
    |--------------------------------------------------------------------------
    */
    private function createStockMovement(array $data): void
    {
        if (!Schema::hasTable('product_stock_movements')) {
            return;
        }

        $data['movement_code'] = $this->makeCode('MV', 'product_stock_movements', 'movement_code');
        $data['movement_date'] = now();
        $data['reference_type'] = CustomerOrder::class;
        $data['reference_id'] = $data['customer_order_id'] ?? null;

        $this->insertRow('product_stock_movements', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPER
    |--------------------------------------------------------------------------
    */
    private function paymentStatusId(float $paidAmount, float $finalAmount): int
    {
        if ($paidAmount <= 0) {
            return $this->statusId('payment_statuses', 'unpaid');
        }

        if ($paidAmount >= $finalAmount) {
            return $this->statusId('payment_statuses', 'paid');
        }

        return $this->statusId('payment_statuses', 'partial');
    }

    private function statusId(string $table, string $code): int
    {
        if (!Schema::hasTable($table)) {
            return 1;
        }

        if (Schema::hasColumn($table, 'code')) {
            $id = DB::table($table)
                ->where('code', $code)
                ->value('id');

            if ($id) {
                return (int) $id;
            }
        }

        $firstId = DB::table($table)->value('id');

        return $firstId ? (int) $firstId : 1;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE HELPER
    |--------------------------------------------------------------------------
    */
    private function insertRow(string $table, array $data): int
    {
        if (!Schema::hasTable($table)) {
            throw new RuntimeException("Chưa có bảng {$table}.");
        }

        $data = $this->filterColumns($table, $data);

        if (Schema::hasColumn($table, 'created_at') && !array_key_exists('created_at', $data)) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($table, 'updated_at') && !array_key_exists('updated_at', $data)) {
            $data['updated_at'] = now();
        }

        return (int) DB::table($table)->insertGetId($data);
    }

    private function updateRow(string $table, int $id, array $data): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $data = $this->filterColumns($table, $data);

        if (empty($data)) {
            return;
        }

        if (Schema::hasColumn($table, 'updated_at') && !array_key_exists('updated_at', $data)) {
            $data['updated_at'] = now();
        }

        DB::table($table)
            ->where('id', $id)
            ->update($data);
    }

    private function filterColumns(string $table, array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function makeCode(string $prefix, string $table, string $column): string
    {
        do {
            $code = $prefix . now()->format('ymdHis') . random_int(100, 999);

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                return $code;
            }

            $exists = DB::table($table)
                ->where($column, $code)
                ->exists();
        } while ($exists);

        return $code;
    }
}
