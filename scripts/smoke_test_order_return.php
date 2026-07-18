<?php

declare(strict_types=1);

use App\Models\CustomerOrder;
use App\Services\ReturnOrderService;
use App\Support\StatusHelper;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$order = CustomerOrder::query()
    ->where('order_status_id', StatusHelper::id('order_statuses', 'completed'))
    ->whereHas('items')
    ->with(['items', 'returns.items'])
    ->first();

if (! $order) {
    echo "SKIPPED: no completed order with items exists.\n";
    exit(0);
}

$item = $order->items->first(function ($item) use ($order) {
    $returned = $order->returns->flatMap->items
        ->where('customer_order_item_id', $item->id)
        ->sum('quantity');

    return $returned < $item->quantity;
});

if (! $item) {
    echo "SKIPPED: completed orders have no returnable items.\n";
    exit(0);
}

$beforeReturnCount = DB::table('customer_order_returns')->count();
$beforeFinancialCount = DB::table('financial_transactions')->count();
$beforeOrder = DB::table('customer_orders')->where('id', $order->id)->first();

DB::beginTransaction();

try {
    $return = $app->make(ReturnOrderService::class)->create($order, [
        'reason' => 'Automated rollback-only smoke test',
        'refund_method' => 'other',
        'items' => [[
            'order_item_id' => $item->id,
            'quantity' => 1,
        ]],
    ]);

    if (! $return->exists || $return->items->sum('quantity') !== 1) {
        throw new RuntimeException('Return document was not created correctly.');
    }

    $refundTransaction = DB::table('financial_transactions')
        ->where('customer_order_return_id', $return->id)
        ->where('type', 'refund')
        ->where('status', 'completed')
        ->first();
    if (! $refundTransaction || $refundTransaction->amount !== $return->refund_amount) {
        throw new RuntimeException('Refund financial transaction was not created correctly.');
    }
} finally {
    DB::rollBack();
}

$afterReturnCount = DB::table('customer_order_returns')->count();
$afterFinancialCount = DB::table('financial_transactions')->count();
$afterOrder = DB::table('customer_orders')->where('id', $order->id)->first();

if (
    $beforeReturnCount !== $afterReturnCount
    || $beforeFinancialCount !== $afterFinancialCount
    || $beforeOrder->returned_amount !== $afterOrder->returned_amount
    || $beforeOrder->net_amount !== $afterOrder->net_amount
) {
    throw new RuntimeException('Smoke test rollback did not restore the original database state.');
}

echo "PASSED: return workflow executed and rolled back without changing persistent data.\n";
