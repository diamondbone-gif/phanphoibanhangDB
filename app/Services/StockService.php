<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Support\Money;

class StockService
{
    /*
    |--------------------------------------------------------------------------
    | TRỪ KHO KHI TẠO / SỬA ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Hàm này nhận đơn hàng và danh sách sản phẩm đã được OrderCalculatorService
    | tính toán sẵn.
    |
    | Lưu ý quan trọng:
    | - Nên gọi hàm này bên trong DB::transaction() ở OrderService.
    | - lockForUpdate() chỉ khóa dòng dữ liệu đúng khi đang nằm trong transaction.
    |--------------------------------------------------------------------------
    */
    public function deductOrderItems(CustomerOrder $order, array $calculatedItems, ?int $adminId = null): void
    {
        foreach ($calculatedItems as $line) {
            $this->deductOneProduct($order, $line, $adminId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRỪ KHO CHO 1 SẢN PHẨM TRONG ĐƠN
    |--------------------------------------------------------------------------
    | Luồng xử lý:
    | 1. Lấy sản phẩm.
    | 2. Xác định sản phẩm là hàng vật lý hay dịch vụ.
    | 3. Nếu là dịch vụ thì chỉ tạo dòng đơn hàng, không trừ kho.
    | 4. Nếu là hàng vật lý không theo lô thì trừ total_quantity.
    | 5. Nếu là hàng vật lý theo lô thì trừ product_batches.current_quantity.
    |--------------------------------------------------------------------------
    */
    private function deductOneProduct(CustomerOrder $order, array $line, ?int $adminId = null): void
    {
        /** @var Product $product */
        $product = Product::query()
            ->lockForUpdate()
            ->findOrFail($line['product_id']);

        // Ép số lượng tối thiểu là 1 để tránh đơn hàng có số lượng 0 hoặc âm.
        $quantity = max(1, (int) ($line['quantity'] ?? 1));

        // Chuẩn hóa loại sản phẩm: single/combo/physical đều được xem là hàng vật lý.
        $productType = $this->productType($product);

        // Kiểm tra sản phẩm có quản lý theo lô không.
        $trackBatch = $this->trackBatch($product);

        // Kiểm tra sản phẩm có cho phép bán khi thiếu tồn không.
        $allowSellWithoutStock = $this->allowSellWithoutStock($product);

        /*
        |--------------------------------------------------------------------------
        | DỊCH VỤ / SẢN PHẨM KHÔNG QUẢN LÝ KHO
        |--------------------------------------------------------------------------
        | Ví dụ: dịch vụ tư vấn, phí vận chuyển, phí xử lý...
        | Không trừ kho, chỉ lưu vào customer_order_items.
        |--------------------------------------------------------------------------
        */
        if ($productType !== 'physical') {
            CustomerOrderItem::create(
                $this->makeOrderItemData($order, $line, null, $quantity, 0)
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HÀNG VẬT LÝ NHƯNG KHÔNG QUẢN LÝ THEO LÔ
        |--------------------------------------------------------------------------
        | Trừ trực tiếp vào products.total_quantity.
        |--------------------------------------------------------------------------
        */
        if (!$trackBatch) {
            $this->deductWithoutBatch(
                order: $order,
                product: $product,
                line: $line,
                quantity: $quantity,
                allowSellWithoutStock: $allowSellWithoutStock,
                adminId: $adminId
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HÀNG VẬT LÝ CÓ QUẢN LÝ THEO LÔ
        |--------------------------------------------------------------------------
        | Trừ vào product_batches.current_quantity.
        | Ưu tiên lô gần hết hạn trước.
        |--------------------------------------------------------------------------
        */
        $this->deductWithBatches(
            order: $order,
            product: $product,
            line: $line,
            quantity: $quantity,
            allowSellWithoutStock: $allowSellWithoutStock,
            adminId: $adminId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRỪ KHO KHÔNG THEO LÔ
    |--------------------------------------------------------------------------
    | Dùng cho sản phẩm có track_batch = 0.
    |--------------------------------------------------------------------------
    */
    private function deductWithoutBatch(
        CustomerOrder $order,
        Product $product,
        array $line,
        int $quantity,
        bool $allowSellWithoutStock,
        ?int $adminId = null
    ): void {
        $before = (int) ($product->total_quantity ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Không cho bán âm kho
        |--------------------------------------------------------------------------
        | Nếu sản phẩm không cho phép bán khi thiếu tồn và tồn hiện tại nhỏ hơn
        | số lượng cần bán thì dừng lại, báo lỗi.
        |--------------------------------------------------------------------------
        */
        if (!$allowSellWithoutStock && $before < $quantity) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn kho.");
        }

        /*
        |--------------------------------------------------------------------------
        | Tính tồn sau khi bán
        |--------------------------------------------------------------------------
        | Dùng max(0, ...) để tránh total_quantity bị âm nếu cột là unsigned integer.
        |--------------------------------------------------------------------------
        */
        $after = max(0, $before - $quantity);

        $product->update([
            'total_quantity' => $after,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Tạo dòng chi tiết đơn hàng
        |--------------------------------------------------------------------------
        */
        $item = CustomerOrderItem::create(
            $this->makeOrderItemData(
                $order,
                $line,
                null,
                $quantity,
                min($before, $quantity)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Ghi lịch sử kho
        |--------------------------------------------------------------------------
        */
        $this->createMovement(
            productId: $product->id,
            batchId: null,
            orderId: $order->id,
            orderItemId: $item->id,
            movementType: 'sale',
            quantity: -abs($quantity),
            beforeQuantity: $before,
            afterQuantity: $after,
            note: 'Xuất kho không theo lô khi lên đơn hàng',
            adminId: $adminId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRỪ KHO THEO LÔ
    |--------------------------------------------------------------------------
    | Dùng cho sản phẩm có track_batch = 1.
    |
    | Quy tắc:
    | - Lấy các lô còn hàng.
    | - Ưu tiên lô gần hết hạn trước.
    | - Mỗi lô bị trừ sẽ tạo 1 dòng customer_order_items riêng,
    |   có product_batch_id để sau này hủy đơn sẽ hoàn đúng về lô đó.
    |--------------------------------------------------------------------------
    */
    private function deductWithBatches(
        CustomerOrder $order,
        Product $product,
        array $line,
        int $quantity,
        bool $allowSellWithoutStock,
        ?int $adminId = null
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Tính tổng số lượng còn trong các lô có thể xuất
        |--------------------------------------------------------------------------
        */
        $availableBatchQuantity = (int) $this->availableBatchQuery($product)
            ->sum('current_quantity');

        /*
        |--------------------------------------------------------------------------
        | Không cho bán nếu tổng lô không đủ
        |--------------------------------------------------------------------------
        */
        if (!$allowSellWithoutStock && $availableBatchQuantity < $quantity) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn theo lô.");
        }

        $needQty = $quantity;

        /*
        |--------------------------------------------------------------------------
        | Lấy danh sách lô
        |--------------------------------------------------------------------------
        | orderByRaw('expiry_date IS NULL, expiry_date ASC'):
        | - Lô có hạn sử dụng được đưa lên trước.
        | - Lô gần hết hạn được xuất trước.
        | - Lô không có hạn sử dụng đứng sau.
        |--------------------------------------------------------------------------
        */
        $batches = $this->availableBatchQuery($product)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($needQty <= 0) {
                break;
            }

            $takeQty = min($needQty, (int) ($batch->current_quantity ?? 0));

            if ($takeQty <= 0) {
                continue;
            }

            $beforeBatchQty = (int) ($batch->current_quantity ?? 0);
            $afterBatchQty = max(0, $beforeBatchQty - $takeQty);

            /*
            |--------------------------------------------------------------------------
            | Cập nhật số lượng còn lại của lô
            |--------------------------------------------------------------------------
            */
            $batchUpdate = [
                'current_quantity' => $afterBatchQty,
            ];

            /*
            |--------------------------------------------------------------------------
            | Cập nhật trạng thái lô sau khi xuất.
            | Cột status được bảo đảm bởi migration của dự án.
            |--------------------------------------------------------------------------
            */
            $batchUpdate['status'] = $afterBatchQty <= 0
                ? $this->outOfStockStatus($batch->status)
                : $this->keepAvailableStatus($batch->status);

            /*
            |--------------------------------------------------------------------------
            | Giữ lô đang hoạt động.
            |--------------------------------------------------------------------------
            */
            $batchUpdate['is_active'] = true;

            $batch->update($batchUpdate);

            /*
            |--------------------------------------------------------------------------
            | Cập nhật tổng tồn của sản phẩm
            |--------------------------------------------------------------------------
            | Vì sản phẩm có thể bị cập nhật ở vòng lặp trước, refresh lại để lấy số mới.
            |--------------------------------------------------------------------------
            */
            $product->refresh();

            $beforeProductQty = (int) ($product->total_quantity ?? 0);
            $afterProductQty = max(0, $beforeProductQty - $takeQty);

            $product->update([
                'total_quantity' => $afterProductQty,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Tạo dòng chi tiết đơn hàng theo từng lô
            |--------------------------------------------------------------------------
            | Nếu một sản phẩm lấy từ 2 lô khác nhau, sẽ có 2 dòng item.
            | Nhờ vậy khi hủy đơn, hệ thống biết phải hoàn về đúng lô nào.
            |--------------------------------------------------------------------------
            */
            $item = CustomerOrderItem::create(
                $this->makeOrderItemData($order, $line, $batch->id, $takeQty, $takeQty)
            );

            /*
            |--------------------------------------------------------------------------
            | Ghi lịch sử xuất kho theo lô
            |--------------------------------------------------------------------------
            */
            $this->createMovement(
                productId: $product->id,
                batchId: $batch->id,
                orderId: $order->id,
                orderItemId: $item->id,
                movementType: 'sale',
                quantity: -abs($takeQty),
                beforeQuantity: $beforeBatchQty,
                afterQuantity: $afterBatchQty,
                note: 'Xuất kho theo lô khi lên đơn hàng',
                adminId: $adminId
            );

            $needQty -= $takeQty;
        }

        /*
        |--------------------------------------------------------------------------
        | Trường hợp cho phép bán khi thiếu tồn theo lô
        |--------------------------------------------------------------------------
        | Phần thiếu vẫn tạo dòng đơn hàng nhưng không có product_batch_id.
        | Lưu ý: phần này không trừ vào lô vì không còn lô để trừ.
        |--------------------------------------------------------------------------
        */
        if ($needQty > 0 && $allowSellWithoutStock) {
            CustomerOrderItem::create(
                $this->makeOrderItemData($order, $line, null, $needQty, 0)
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu vẫn còn thiếu mà không cho bán âm kho thì báo lỗi.
        |--------------------------------------------------------------------------
        */
        if ($needQty > 0 && !$allowSellWithoutStock) {
            throw new RuntimeException("Sản phẩm {$product->product_name} không đủ tồn theo lô.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HOÀN KHO KHI HỦY / SỬA ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Khi hủy đơn hoặc sửa đơn:
    | - Nếu item có product_batch_id thì hoàn đúng về lô.
    | - Nếu item không có product_batch_id thì hoàn về tổng tồn sản phẩm.
    |--------------------------------------------------------------------------
    */
    public function returnOrderStock(CustomerOrder $order, string $movementType = 'cancel_return', ?int $adminId = null): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $product = Product::query()
                ->lockForUpdate()
                ->find($item->product_id);

            if (!$product) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Dịch vụ thì không có kho để hoàn.
            |--------------------------------------------------------------------------
            */
            if ($this->productType($product) !== 'physical') {
                continue;
            }

            // New rows record exactly how much inventory was deducted. Legacy
            // rows fall back to the sold quantity.
            $quantity = max(0, (int) (
                $item->stock_quantity ?? $item->quantity ?? 0
            ));

            if ($quantity <= 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Hoàn kho đúng về lô nếu item có product_batch_id.
            |--------------------------------------------------------------------------
            */
            if ($item->product_batch_id) {
                $batch = ProductBatch::query()
                    ->lockForUpdate()
                    ->find($item->product_batch_id);

                if ($batch) {
                    $beforeBatchQty = (int) ($batch->current_quantity ?? 0);
                    $afterBatchQty = $beforeBatchQty + $quantity;

                    $batchUpdate = [
                        'current_quantity' => $afterBatchQty,
                    ];

                    $batchUpdate['status'] = $this->keepAvailableStatus($batch->status);
                    $batchUpdate['is_active'] = true;

                    $batch->update($batchUpdate);

                    /*
                    |--------------------------------------------------------------------------
                    | Cập nhật lại tổng tồn sản phẩm
                    |--------------------------------------------------------------------------
                    */
                    $product->refresh();

                    $beforeProductQty = (int) ($product->total_quantity ?? 0);
                    $afterProductQty = $beforeProductQty + $quantity;

                    $product->update([
                        'total_quantity' => $afterProductQty,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Ghi lịch sử hoàn kho về đúng lô
                    |--------------------------------------------------------------------------
                    */
                    $this->createMovement(
                        productId: $product->id,
                        batchId: $batch->id,
                        orderId: $order->id,
                        orderItemId: $item->id,
                        movementType: $movementType,
                        quantity: $quantity,
                        beforeQuantity: $beforeBatchQty,
                        afterQuantity: $afterBatchQty,
                        note: 'Hoàn kho về đúng lô từ đơn hàng',
                        adminId: $adminId
                    );

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Hoàn kho không theo lô
            |--------------------------------------------------------------------------
            | Trường hợp này xảy ra khi:
            | - Sản phẩm không quản lý theo lô.
            | - Đơn cũ chưa lưu product_batch_id.
            | - Phần bán vượt tồn được tạo item nhưng không có lô.
            |--------------------------------------------------------------------------
            */
            $beforeProductQty = (int) ($product->total_quantity ?? 0);
            $afterProductQty = $beforeProductQty + $quantity;

            $product->update([
                'total_quantity' => $afterProductQty,
            ]);

            $this->createMovement(
                productId: $product->id,
                batchId: null,
                orderId: $order->id,
                orderItemId: $item->id,
                movementType: $movementType,
                quantity: $quantity,
                beforeQuantity: $beforeProductQty,
                afterQuantity: $afterProductQty,
                note: 'Hoàn kho không theo lô từ đơn hàng',
                adminId: $adminId
            );
        }
    }

    public function restoreReturnedItem(
        CustomerOrder $order,
        CustomerOrderItem $item,
        int $quantity,
        ?int $adminId = null
    ): void {
        $quantity = max(0, $quantity);

        if ($quantity === 0) {
            return;
        }

        $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

        if ($this->productType($product) !== 'physical') {
            return;
        }

        $batch = null;
        $before = (int) ($product->total_quantity ?? 0);

        if ($item->product_batch_id) {
            $batch = ProductBatch::query()->lockForUpdate()->find($item->product_batch_id);
        }

        if ($batch) {
            $batchBefore = (int) $batch->current_quantity;
            $batchAfter = $batchBefore + $quantity;
            $batch->update([
                'current_quantity' => $batchAfter,
                'status' => $this->keepAvailableStatus($batch->status),
                'is_active' => true,
            ]);
            $movementBefore = $batchBefore;
            $movementAfter = $batchAfter;
        } else {
            $movementBefore = $before;
            $movementAfter = $before + $quantity;
        }

        $product->update(['total_quantity' => $before + $quantity]);

        $this->createMovement(
            productId: $product->id,
            batchId: $batch?->id,
            orderId: $order->id,
            orderItemId: $item->id,
            movementType: 'customer_return',
            quantity: $quantity,
            beforeQuantity: $movementBefore,
            afterQuantity: $movementAfter,
            note: 'Nhập kho từ phiếu hoàn trả đơn hàng',
            adminId: $adminId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TẠO DỮ LIỆU DÒNG ĐƠN HÀNG
    |--------------------------------------------------------------------------
    | Hàm này gom dữ liệu để insert vào customer_order_items.
    |--------------------------------------------------------------------------
    */
    private function makeOrderItemData(
        CustomerOrder $order,
        array $line,
        ?int $batchId,
        int $quantity,
        int $stockQuantity
    ): array
    {
        $unitPriceCents = Money::cents($line['unit_price'] ?? 0);
        $discountBasisPoints = Money::percentBasisPoints($line['discount_percent'] ?? 0);
        $originalTotalCents = $unitPriceCents * $quantity;
        $discountCents = Money::percentage($originalTotalCents, $discountBasisPoints);
        $finalTotalCents = max(0, $originalTotalCents - $discountCents);

        $data = [
            'customer_order_id' => $order->id,
            'product_id' => $line['product_id'],
            'product_batch_id' => $batchId,
            'product_code' => $line['product_code'] ?? '',
            'product_name' => $line['product_name'] ?? '',
            'quantity' => $quantity,
            'unit_price' => Money::decimal($unitPriceCents),
            'original_total' => Money::decimal($originalTotalCents),
            'discount_type' => $line['discount_type'] ?? 'none',
            'discount_percent' => Money::decimal($discountBasisPoints),
            'discount_amount' => Money::decimal($discountCents),
            'final_total' => Money::decimal($finalTotalCents),
            'note' => $line['note'] ?? null,
        ];

        $data['stock_quantity'] = max(0, min($quantity, $stockQuantity));

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | GHI LỊCH SỬ KHO
    |--------------------------------------------------------------------------
    | Schema được cố định theo migration đang chạy trên XAMPP MariaDB.
    |
    | Ghi đầy đủ liên kết sản phẩm, lô, đơn hàng và dòng đơn hàng.
    |--------------------------------------------------------------------------
    */
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
        /*
        |--------------------------------------------------------------------------
        | Dữ liệu bắt buộc, gần như bảng product_stock_movements nào cũng có.
        |--------------------------------------------------------------------------
        */
        $data = [
            'product_id' => $productId,
            'product_batch_id' => $batchId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => $afterQuantity,
            'reference_type' => CustomerOrder::class,
            'reference_id' => $orderId,
            'movement_date' => now(),
            'note' => $note,
            'created_by' => $adminId,
        ];

        /*
        |--------------------------------------------------------------------------
        | Tạo mã lịch sử kho duy nhất.
        |--------------------------------------------------------------------------
        */
        $data['movement_code'] = $this->makeCode('MV', 'product_stock_movements', 'movement_code');

        /*
        |--------------------------------------------------------------------------
        | Lưu liên kết đơn hàng.
        |--------------------------------------------------------------------------
        */
        $data['customer_order_id'] = $orderId;

        /*
        |--------------------------------------------------------------------------
        | Lưu liên kết dòng đơn hàng.
        |--------------------------------------------------------------------------
        */
        $data['customer_order_item_id'] = $orderItemId;

        /*
        |--------------------------------------------------------------------------
        | Schema được migration bảo đảm trước khi insert.
        |--------------------------------------------------------------------------
        */
        ProductStockMovement::create($data);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY LÔ CÒN HÀNG CÓ THỂ XUẤT
    |--------------------------------------------------------------------------
    | Chỉ lấy các lô:
    | - Thuộc đúng sản phẩm.
    | - current_quantity > 0.
    | - is_active = true hoặc null.
    | - status còn khả dụng.
    |--------------------------------------------------------------------------
    */
    private function availableBatchQuery(Product $product)
    {
        $query = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('current_quantity', '>', 0);

        /*
        |--------------------------------------------------------------------------
        | Chỉ lấy lô đang hoạt động.
        |--------------------------------------------------------------------------
        */
        $query->where('is_active', true);

        /*
        |--------------------------------------------------------------------------
        | Các trạng thái hợp lệ đang được dự án sử dụng.
        |--------------------------------------------------------------------------
        */
        $query->whereIn('status', [
            'active',
            'available',
            'near_expiry',
            'near_expired',
        ]);

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | QUY ĐỔI LOẠI SẢN PHẨM
    |--------------------------------------------------------------------------
    | Database của bạn đang lưu:
    | - single: sản phẩm bán lẻ, cần trừ kho.
    | - combo: combo sản phẩm, mặc định vẫn xử lý như hàng vật lý.
    | - physical/product: hàng vật lý.
    | - service/digital: không trừ kho.
    |--------------------------------------------------------------------------
    */
    private function productType(Product $product): string
    {
        $type = strtolower(trim((string) ($product->product_type ?? '')));

        if (in_array($type, ['single', 'physical', 'product', 'combo'], true)) {
            return 'physical';
        }

        if (in_array($type, ['service', 'digital'], true)) {
            return 'service';
        }

        return 'physical';
    }

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA SẢN PHẨM CÓ QUẢN LÝ THEO LÔ KHÔNG
    |--------------------------------------------------------------------------
    */
    private function trackBatch(Product $product): bool
    {
        return (bool) ($product->track_batch ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA CÓ CHO BÁN KHI THIẾU TỒN KHÔNG
    |--------------------------------------------------------------------------
    */
    private function allowSellWithoutStock(Product $product): bool
    {
        return (bool) ($product->allow_sell_without_stock ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | GIỮ TRẠNG THÁI LÔ CÒN KHẢ DỤNG
    |--------------------------------------------------------------------------
    */
    private function keepAvailableStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        if (in_array($status, ['near_expiry', 'near_expired'], true)) {
            return $status;
        }

        if ($status === 'active') {
            return 'active';
        }

        return 'available';
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI KHI LÔ HẾT HÀNG
    |--------------------------------------------------------------------------
    */
    private function outOfStockStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        if ($status === 'sold_out') {
            return 'sold_out';
        }

        return 'out_of_stock';
    }

    /*
    |--------------------------------------------------------------------------
    | TẠO MÃ TỰ ĐỘNG
    |--------------------------------------------------------------------------
    | Ví dụ: MV260623040039274
    |
    | Hàm này chỉ nên gọi khi chắc chắn cột cần kiểm tra đang tồn tại.
    |--------------------------------------------------------------------------
    */
    private function makeCode(string $prefix, string $table, string $column): string
    {
        /*
        |--------------------------------------------------------------------------
        | Nếu cột chưa tồn tại thì trả về chuỗi rỗng để tránh lỗi SQL.
        |--------------------------------------------------------------------------
        */
        do {
            $code = $prefix . now()->format('ymdHis') . random_int(100, 999);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | LỌC DATA CHỈ GIỮ CÁC CỘT ĐANG CÓ TRONG DATABASE
    |--------------------------------------------------------------------------
    | Mã movement_code là cột bắt buộc trong schema hiện tại.
    |--------------------------------------------------------------------------
    */
}
