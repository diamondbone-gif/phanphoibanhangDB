<?php

declare(strict_types=1);

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockDocumentService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sourceStock = WarehouseStock::query()
    ->with(['warehouse', 'product', 'batch'])
    ->whereRaw('on_hand_quantity - reserved_quantity > 0')
    ->first();
if ($sourceStock === null) {
    echo "SKIPPED: no available warehouse stock exists.\n";
    exit(0);
}

$beforeDocuments = DB::table('stock_documents')->count();
$beforeMovements = DB::table('product_stock_movements')->count();
DB::beginTransaction();
try {
    $destination = Warehouse::query()->create([
        'warehouse_code' => 'SMOKE-'.random_int(1000, 9999),
        'warehouse_name' => 'Kho smoke test',
        'is_default' => false,
        'is_active' => true,
    ]);
    $document = $app->make(StockDocumentService::class)->createAndPost([
        'document_type' => 'transfer',
        'source_warehouse_id' => $sourceStock->warehouse_id,
        'destination_warehouse_id' => $destination->id,
        'document_date' => now(),
        'reason' => 'Automated rollback-only stock document test',
    ], [[
        'product_id' => $sourceStock->product_id,
        'product_batch_id' => $sourceStock->product_batch_id,
        'quantity' => 1,
    ]], null);

    $movementCount = DB::table('product_stock_movements')->where('stock_document_id', $document->id)->count();
    if ($document->items->count() !== 1 || $movementCount !== 2) {
        throw new RuntimeException('Transfer document or two-sided movements were not created correctly.');
    }
} finally {
    DB::rollBack();
}

if (DB::table('stock_documents')->count() !== $beforeDocuments || DB::table('product_stock_movements')->count() !== $beforeMovements) {
    throw new RuntimeException('Stock document smoke test rollback did not restore original data.');
}

echo "PASSED: warehouse transfer posted two-sided movements and rolled back cleanly.\n";
