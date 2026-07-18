<?php

declare(strict_types=1);

use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturn;
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

    $originalCommissionCents = Money::cents($commission->commission_amount);
    $newNetCents = intdiv(Money::cents($order->net_amount ?? $order->final_amount), 2);
    DB::table('customer_commissions')->where('id', $commission->id)->update([
        'paid_amount' => Money::decimal($originalCommissionCents),
        'status' => 'paid',
    ]);
    $return = CustomerOrderReturn::create([
        'return_code' => 'SMOKE-RETURN-'.$order->id,
        'customer_order_id' => $order->id,
        'refund_amount' => Money::decimal(Money::cents($order->final_amount) - $newNetCents),
        'status' => 'completed',
        'reason' => 'Smoke test thu hồi hoa hồng',
        'returned_at' => now(),
    ]);
    $order->update(['net_amount' => Money::decimal($newNetCents), 'return_status' => 'partial']);

    $service->recalculateForOrder($order->fresh(), null, $return);
    $service->recalculateForOrder($order->fresh(), null, $return);
    $adjustments = DB::table('customer_commission_adjustments')
        ->where('customer_order_return_id', $return->id)
        ->get();
    $updated = DB::table('customer_commissions')->where('id', $commission->id)->first();
    $expectedClawbackCents = $originalCommissionCents - Money::cents($updated->commission_amount);

    if ($adjustments->count() !== 1
        || Money::cents($updated->clawback_amount) !== $expectedClawbackCents
        || Money::cents($adjustments->first()->amount) !== $expectedClawbackCents
        || $updated->status !== 'clawback') {
        throw new RuntimeException('Thu hồi hoa hồng sau hoàn đơn không đúng hoặc bị ghi trùng.');
    }

    echo "PASSED: commission calculation and paid-commission clawback are correct and idempotent.\n";
} finally {
    DB::rollBack();
}
