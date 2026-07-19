<?php

declare(strict_types=1);

use App\Enums\FinancialTransactionState;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturnItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\ReturnOrderService;
use App\Support\Money;
use App\Support\StatusHelper;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$order = CustomerOrder::query()
    ->with(['items.product', 'commission'])
    ->where('order_status_id', StatusHelper::id('order_statuses', 'completed'))
    ->latest('id')->get()
    ->first(function (CustomerOrder $order): bool {
        return $order->items->contains(function ($item): bool {
            $returned = CustomerOrderReturnItem::query()->where('customer_order_item_id', $item->id)->sum('quantity');

            return in_array($item->product?->product_type, ['single', 'physical', 'product', 'combo'], true)
                && (int) $item->quantity > (int) $returned;
        });
    });

if (! $order) {
    echo "SKIPPED: chưa có dòng hàng vật lý nào có thể hoàn.\n";
    exit(0);
}

$item = $order->items->first(function ($item): bool {
    $returned = CustomerOrderReturnItem::query()->where('customer_order_item_id', $item->id)->sum('quantity');

    return in_array($item->product?->product_type, ['single', 'physical', 'product', 'combo'], true)
        && (int) $item->quantity > (int) $returned;
});

DB::beginTransaction();
try {
    $productBefore = (int) Product::query()->findOrFail($item->product_id)->total_quantity;
    $batchBefore = $item->product_batch_id ? (int) ProductBatch::query()->findOrFail($item->product_batch_id)->current_quantity : null;
    $netBefore = Money::cents($order->net_amount);
    $movementCount = DB::table('product_stock_movements')->count();

    $return = app(ReturnOrderService::class)->create($order, [
        'resolution_type' => 'refund',
        'refund_method' => 'bank_transfer',
        'reason' => 'Smoke test kiểm tra hoàn kho, tiền và hoa hồng',
        'note' => 'Dữ liệu được rollback sau kiểm thử.',
        'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
    ]);

    $productAfter = (int) Product::query()->findOrFail($item->product_id)->total_quantity;
    $batchAfter = $item->product_batch_id ? (int) ProductBatch::query()->findOrFail($item->product_batch_id)->current_quantity : null;
    $orderAfter = $order->fresh();
    $transaction = $return->refundTransaction()->first();

    if ($productAfter !== $productBefore + 1
        || ($batchBefore !== null && $batchAfter !== $batchBefore + 1)
        || DB::table('product_stock_movements')->count() !== $movementCount + 1
        || Money::cents($orderAfter->net_amount) !== $netBefore - Money::cents($return->refund_amount)
        || ! $transaction
        || $transaction->status !== FinancialTransactionState::Requested
        || Money::cents($transaction->amount) !== Money::cents($return->cash_refund_amount)
        || trim($return->reason) === '') {
        throw new RuntimeException('Luồng hoàn kho, trừ tiền hoặc ghi nhận lý do không nhất quán.');
    }

    $commission = $orderAfter->commission;
    if ($commission) {
        $expected = Money::percentage(
            Money::cents($orderAfter->net_amount),
            Money::percentBasisPoints($commission->commission_rate_percent)
        );
        if (Money::cents($commission->commission_amount) !== $expected) {
            throw new RuntimeException('Hoa hồng chưa được giảm đúng theo giá trị đơn còn lại.');
        }
    }

    echo "PASSED: return quantity restored stock, reduced money and commission, and recorded a refund obligation.\n";
} finally {
    DB::rollBack();
}
