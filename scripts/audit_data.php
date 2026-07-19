<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$duplicateChecks = [
    ['customers', ['phone']],
    ['customers', ['email']],
    ['customer_orders', ['order_code']],
    ['customer_invoices', ['invoice_code']],
    ['payments', ['payment_code']],
    ['customer_commissions', ['commission_code']],
    ['customer_commissions', ['customer_order_id']],
    ['products', ['product_code']],
    ['product_batches', ['product_id', 'batch_number']],
];

$orphanChecks = [
    ['customer_details', 'customer_id', 'customers'],
    ['customer_orders', 'customer_id', 'customers'],
    ['customer_order_items', 'customer_order_id', 'customer_orders'],
    ['customer_order_items', 'product_id', 'products'],
    ['customer_invoices', 'customer_order_id', 'customer_orders'],
    ['payments', 'customer_order_id', 'customer_orders'],
    ['customer_commissions', 'customer_order_id', 'customer_orders'],
    ['customer_commissions', 'ctv_customer_id', 'customers'],
    ['customer_care_logs', 'customer_id', 'customers'],
    ['customer_care_reminders', 'customer_id', 'customers'],
    ['customer_order_returns', 'customer_order_id', 'customer_orders'],
    ['customer_order_return_items', 'customer_order_return_id', 'customer_order_returns'],
    ['customer_order_return_items', 'customer_order_item_id', 'customer_order_items'],
];

$report = [
    'database' => DB::connection()->getDatabaseName(),
    'duplicates' => [],
    'orphans' => [],
    'integrity' => [],
    'indexes' => [],
];

foreach ($duplicateChecks as [$table, $columns]) {
    if (!Schema::hasTable($table)) {
        $report['duplicates'][] = compact('table', 'columns') + ['status' => 'missing_table'];
        continue;
    }

    $missingColumns = array_values(array_filter(
        $columns,
        fn (string $column): bool => !Schema::hasColumn($table, $column)
    ));

    if ($missingColumns !== []) {
        $report['duplicates'][] = compact('table', 'columns', 'missingColumns') + ['status' => 'missing_columns'];
        continue;
    }

    $query = DB::table($table)
        ->select($columns)
        ->selectRaw('COUNT(*) AS duplicate_count');

    foreach ($columns as $column) {
        $query->whereNotNull($column);
    }

    $groups = $query
        ->groupBy($columns)
        ->havingRaw('COUNT(*) > 1')
        ->limit(100)
        ->get();

    $report['duplicates'][] = compact('table', 'columns', 'groups') + [
        'status' => 'checked',
        'group_count' => $groups->count(),
    ];

    $report['indexes'][$table] = array_values(array_map(
        static fn (array $index): array => [
            'name' => $index['name'],
            'columns' => $index['columns'],
            'unique' => $index['unique'],
        ],
        Schema::getIndexes($table)
    ));
}

foreach ($orphanChecks as [$table, $foreignKey, $parentTable]) {
    if (
        !Schema::hasTable($table)
        || !Schema::hasTable($parentTable)
        || !Schema::hasColumn($table, $foreignKey)
    ) {
        $report['orphans'][] = compact('table', 'foreignKey', 'parentTable') + ['status' => 'not_applicable'];
        continue;
    }

    $count = DB::table($table . ' as child')
        ->leftJoin($parentTable . ' as parent', 'parent.id', '=', 'child.' . $foreignKey)
        ->whereNotNull('child.' . $foreignKey)
        ->whereNull('parent.id')
        ->count();

    $report['orphans'][] = compact('table', 'foreignKey', 'parentTable', 'count') + ['status' => 'checked'];
}

if (Schema::hasTable('product_batches') && Schema::hasColumn('product_batches', 'current_quantity')) {
    $report['integrity']['negative_batch_stock'] = DB::table('product_batches')
        ->where('current_quantity', '<', 0)
        ->count();
}

if (Schema::hasTable('warehouse_stocks')) {
    $report['integrity']['invalid_warehouse_stock'] = DB::table('warehouse_stocks')
        ->whereColumn('reserved_quantity', '>', 'on_hand_quantity')
        ->orWhere('on_hand_quantity', '<', 0)
        ->orWhere('reserved_quantity', '<', 0)
        ->count();

    $report['integrity']['default_warehouse_stock_mismatches'] = DB::table('warehouse_stocks as ws')
        ->join('warehouses as w', 'w.id', '=', 'ws.warehouse_id')
        ->leftJoin('product_batches as pb', 'pb.id', '=', 'ws.product_batch_id')
        ->leftJoin('products as p', 'p.id', '=', 'ws.product_id')
        ->where('w.is_default', true)
        ->where(function ($query) {
            $query->where(function ($batchQuery) {
                $batchQuery->whereNotNull('ws.product_batch_id')
                    ->whereColumn('ws.on_hand_quantity', '<>', 'pb.current_quantity');
            })->orWhere(function ($productQuery) {
                $productQuery->whereNull('ws.product_batch_id')
                    ->whereColumn('ws.on_hand_quantity', '<>', 'p.total_quantity');
            });
        })
        ->select('ws.id', 'ws.product_id', 'ws.product_batch_id', 'ws.on_hand_quantity')
        ->get()
        ->all();
}

if (
    Schema::hasTable('products')
    && Schema::hasTable('product_batches')
    && Schema::hasColumn('products', 'total_quantity')
) {
    $report['integrity']['product_batch_total_mismatches'] = DB::table('products as product')
        ->join('product_batches as batch', 'batch.product_id', '=', 'product.id')
        ->select('product.id', 'product.product_code', 'product.product_name', 'product.track_batch', 'product.total_quantity')
        ->selectRaw('SUM(batch.current_quantity) AS batch_quantity')
        ->where('product.track_batch', true)
        ->groupBy('product.id', 'product.product_code', 'product.product_name', 'product.track_batch', 'product.total_quantity')
        ->havingRaw('ABS(product.total_quantity - SUM(batch.current_quantity)) > 0')
        ->get();
}

if (Schema::hasTable('customer_referrals')) {
    $referrals = DB::table('customer_referrals')
        ->select('referred_customer_id')
        ->selectRaw('COUNT(*) AS active_count')
        ->whereNotNull('referred_customer_id');

    if (Schema::hasColumn('customer_referrals', 'effective_to')) {
        $referrals->where(function ($query) {
            $query->whereNull('effective_to')->orWhere('effective_to', '>=', now());
        });
    }

    $report['integrity']['customers_with_multiple_active_referrals'] = $referrals
        ->groupBy('referred_customer_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
}

if (Schema::hasTable('customers')) {
    $similarCustomers = DB::table('customers as customer')
        ->leftJoin('customer_details as detail', 'detail.customer_id', '=', 'customer.id')
        ->selectRaw('LOWER(TRIM(customer.full_name)) AS normalized_name')
        ->selectRaw("LOWER(TRIM(CONCAT_WS('|', detail.address, detail.ward, detail.district, detail.province))) AS normalized_address")
        ->selectRaw('COUNT(*) AS customer_count')
        ->whereNotNull('customer.full_name')
        ->groupByRaw('LOWER(TRIM(customer.full_name)), LOWER(TRIM(CONCAT_WS(\'|\', detail.address, detail.ward, detail.district, detail.province)))')
        ->havingRaw('COUNT(*) > 1')
        ->limit(100)
        ->get();
    $report['integrity']['similar_name_address_groups'] = $similarCustomers;
}

if (
    Schema::hasTable('customer_orders')
    && Schema::hasColumn('customer_orders', 'paid_amount')
    && Schema::hasColumn('customer_orders', 'final_amount')
) {
    $report['integrity']['orders_overpaid'] = DB::table('customer_orders')
        ->whereColumn('paid_amount', '>', 'final_amount')
        ->count();
}

if (
    Schema::hasTable('customer_commissions')
    && Schema::hasColumn('customer_commissions', 'paid_amount')
    && Schema::hasColumn('customer_commissions', 'commission_amount')
) {
    $report['integrity']['commissions_overpaid'] = DB::table('customer_commissions')
        ->whereColumn('paid_amount', '>', 'commission_amount')
        ->where(function ($query) {
            $query->whereNull('clawback_amount')->orWhere('clawback_amount', '<=', 0);
        })
        ->count();

    $report['integrity']['commission_clawbacks'] = DB::table('customer_commissions')
        ->where('clawback_amount', '>', 0)
        ->count();
}

if (
    Schema::hasTable('customer_orders')
    && Schema::hasColumn('customer_orders', 'returned_amount')
    && Schema::hasColumn('customer_orders', 'net_amount')
) {
    $report['integrity']['order_net_amount_mismatches'] = DB::table('customer_orders')
        ->whereRaw('ABS(net_amount - GREATEST(0, final_amount - returned_amount)) > 0.01')
        ->count();
}

$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$output = $argv[1] ?? null;

if ($output) {
    file_put_contents($output, $json . PHP_EOL);
    echo "Data audit created: {$output}\n";
} else {
    echo $json . PHP_EOL;
}
