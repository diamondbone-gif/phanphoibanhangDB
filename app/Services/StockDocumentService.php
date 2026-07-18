<?php

namespace App\Services;

use App\Enums\StockDocumentState;
use App\Enums\StockDocumentType;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStockMovement;
use App\Models\StockDocument;
use App\Models\Warehouse;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockDocumentService
{
    public function __construct(private WarehouseInventoryService $inventory) {}

    public function createAndPost(array $data, array $lines, ?int $adminId): StockDocument
    {
        return DB::transaction(function () use ($data, $lines, $adminId) {
            $type = StockDocumentType::from($data['document_type']);
            $source = isset($data['source_warehouse_id'])
                ? Warehouse::query()->where('is_active', true)->findOrFail($data['source_warehouse_id'])
                : null;
            $destination = isset($data['destination_warehouse_id'])
                ? Warehouse::query()->where('is_active', true)->findOrFail($data['destination_warehouse_id'])
                : null;
            $this->validateWarehouses($type, $source, $destination);

            $document = StockDocument::query()->create([
                'document_code' => $this->makeCode(),
                'document_type' => $type,
                'status' => StockDocumentState::Posted,
                'source_warehouse_id' => $source?->id,
                'destination_warehouse_id' => $destination?->id,
                'document_date' => $data['document_date'] ?? now(),
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $adminId,
                'approved_by' => $adminId,
                'posted_by' => $adminId,
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $this->postLine($document, $type, $source, $destination, $line, $adminId);
            }

            return $document->fresh(['items', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }

    private function postLine(
        StockDocument $document,
        StockDocumentType $type,
        ?Warehouse $source,
        ?Warehouse $destination,
        array $line,
        ?int $adminId,
    ): void {
        $product = Product::query()->lockForUpdate()->findOrFail($line['product_id']);
        $batch = isset($line['product_batch_id'])
            ? ProductBatch::query()->where('product_id', $product->id)->lockForUpdate()->findOrFail($line['product_batch_id'])
            : null;
        if ($product->track_batch && $batch === null) {
            throw new RuntimeException("Sản phẩm {$product->product_name} bắt buộc phải chọn lô.");
        }

        $quantity = (int) $line['quantity'];
        $defaultUnitCost = $batch instanceof ProductBatch ? $batch->unit_cost : $product->average_cost;
        $unitCostCents = Money::cents($line['unit_cost'] ?? $defaultUnitCost);
        $document->items()->create([
            'product_id' => $product->id,
            'product_batch_id' => $batch?->id,
            'quantity' => $quantity,
            'unit_cost' => Money::decimal($unitCostCents),
            'total_cost' => Money::decimal($unitCostCents * $quantity),
            'note' => $line['note'] ?? null,
        ]);

        if ($type === StockDocumentType::Transfer) {
            if ($source === null || $destination === null) {
                throw new RuntimeException('Phiếu chuyển kho thiếu kho nguồn hoặc kho đích.');
            }
            [$sourceStock, $destinationStock, $sourceBefore, $destinationBefore] = $this->inventory->transfer(
                $source, $destination, $product, $quantity, $batch,
            );
            $this->movement($document, $source, $product, $batch, -$quantity, $sourceBefore, $sourceStock->on_hand_quantity, $unitCostCents, $adminId);
            $this->movement($document, $destination, $product, $batch, $quantity, $destinationBefore, $destinationStock->on_hand_quantity, $unitCostCents, $adminId);

            return;
        }

        $isIncrease = in_array($type, [StockDocumentType::Receipt, StockDocumentType::Return, StockDocumentType::AdjustmentIncrease], true);
        $warehouse = $isIncrease ? $destination : $source;
        if ($warehouse === null) {
            throw new RuntimeException('Chứng từ chưa xác định kho thực hiện.');
        }
        [$stock, $before, $after] = $this->inventory->adjust($warehouse, $product, $isIncrease ? $quantity : -$quantity, $batch);
        $this->movement($document, $warehouse, $product, $batch, $isIncrease ? $quantity : -$quantity, $before, $after, $unitCostCents, $adminId);

        if ($isIncrease && $unitCostCents > 0) {
            $oldValue = Money::cents($product->average_cost) * max(0, $product->total_quantity - $quantity);
            $product->update(['average_cost' => Money::decimal(intdiv($oldValue + ($unitCostCents * $quantity), max(1, $product->total_quantity)))]);
            $batch?->update(['unit_cost' => Money::decimal($unitCostCents)]);
        }
    }

    private function movement(
        StockDocument $document,
        Warehouse $warehouse,
        Product $product,
        ?ProductBatch $batch,
        int $quantity,
        int $before,
        int $after,
        int $unitCostCents,
        ?int $adminId,
    ): void {
        ProductStockMovement::query()->create([
            'movement_code' => $this->makeMovementCode(),
            'product_id' => $product->id,
            'product_batch_id' => $batch?->id,
            'warehouse_id' => $warehouse->id,
            'stock_document_id' => $document->id,
            'movement_type' => $document->document_type->value,
            'quantity' => $quantity,
            'before_quantity' => $before,
            'after_quantity' => $after,
            'unit_cost' => Money::decimal($unitCostCents),
            'total_cost' => Money::decimal(abs($quantity) * $unitCostCents),
            'reference_type' => StockDocument::class,
            'reference_id' => $document->id,
            'movement_date' => $document->document_date,
            'note' => $document->reason,
            'created_by' => $adminId,
        ]);
    }

    private function validateWarehouses(StockDocumentType $type, ?Warehouse $source, ?Warehouse $destination): void
    {
        if ($type === StockDocumentType::Transfer && ($source === null || $destination === null || $source->is($destination))) {
            throw new RuntimeException('Phiếu chuyển kho cần kho nguồn và kho đích khác nhau.');
        }
        if (in_array($type, [StockDocumentType::Issue, StockDocumentType::AdjustmentDecrease], true) && $source === null) {
            throw new RuntimeException('Chứng từ xuất/giảm kho bắt buộc chọn kho nguồn.');
        }
        if (in_array($type, [StockDocumentType::Receipt, StockDocumentType::Return, StockDocumentType::AdjustmentIncrease], true) && $destination === null) {
            throw new RuntimeException('Chứng từ nhập/tăng kho bắt buộc chọn kho nhận.');
        }
    }

    private function makeCode(): string
    {
        do {
            $code = 'PK'.now()->format('ymdHis').random_int(100, 999);
        } while (StockDocument::query()->where('document_code', $code)->exists());

        return $code;
    }

    private function makeMovementCode(): string
    {
        do {
            $code = 'MV'.now()->format('ymdHis').random_int(1000, 9999);
        } while (ProductStockMovement::query()->where('movement_code', $code)->exists());

        return $code;
    }
}
