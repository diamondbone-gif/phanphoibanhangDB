<?php

declare(strict_types=1);

use App\Models\CustomerOrder;
use App\Services\CommissionService;
use App\Support\Money;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$orderId = DB::table('customer_orders as orders')
    ->join('order_statuses as order_status', 'order_status.id', '=', 'orders.order_status_id')
    ->join('customer_referrals as referral', 'referral.referred_customer_id', '=', 'orders.customer_id')
    ->where('order_status.code', 'completed')
    ->whereNotNull('referral.referrer_customer_id')
    ->whereNull('referral.ended_at')
    ->orderByDesc('orders.id')
    ->value('orders.id');

if (! $orderId) {
    fwrite(STDERR, "SKIPPED: chưa có đơn hoàn thành với quan hệ giới thiệu đang hoạt động.\n");
    exit(0);
}

DB::beginTransaction();
try {
    $order = CustomerOrder::query()->findOrFail($orderId);
    $service = app(CommissionService::class);
    $first = $service->createForOrder($order);
    $second = $service->createForOrder($order->fresh());

    $rows = DB::table('customer_commissions')
        ->where('customer_order_id', $orderId)
        ->whereNull('deleted_at')
        ->get();
    if ($rows->count() !== 1 || ! $first || ! $second || $first->id !== $second->id) {
        throw new RuntimeException('Tính idempotent thất bại: một đơn phải có đúng một dòng hoa hồng.');
    }

    $commission = $rows->first();
    $expected = Money::percentage(
        Money::cents($order->net_amount ?? $order->final_amount),
        Money::percentBasisPoints($commission->commission_rate_percent)
    );
    if (Money::cents($commission->commission_amount) !== $expected) {
        throw new RuntimeException('Số tiền hoa hồng không khớp net_amount × tỷ lệ đã chốt.');
    }

    echo "PASSED: commission is idempotent and amount matches the completed order net amount.\n";
} finally {
    DB::rollBack();
}
