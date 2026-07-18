<?php

namespace App\Services;

use App\Enums\OrderReturnCoverage;
use App\Enums\OrderReturnState;
use App\Enums\PaymentState;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\CustomerOrderReturn;
use App\Models\CustomerOrderReturnItem;
use App\Models\OrderHistory;
use App\Support\Money;
use App\Support\StatusHelper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnOrderService
{
    public function __construct(
        private StockService $stockService,
        private CommissionService $commissionService,
        private ReturnAmountCalculator $amountCalculator,
        private FinancialTransactionService $financialTransactionService,
    ) {}

    public function create(CustomerOrder $order, array $data, ?int $adminId = null): CustomerOrderReturn
    {
        return DB::transaction(function () use ($order, $data, $adminId) {
            $order = CustomerOrder::query()
                ->with(['items', 'returns.items', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ((int) $order->order_status_id !== StatusHelper::id('order_statuses', 'completed')) {
                throw new RuntimeException('Chỉ được hoàn trả đơn hàng đã hoàn thành.');
            }

            $requested = collect($data['items'] ?? [])
                ->mapWithKeys(fn (array $row) => [(int) $row['order_item_id'] => (int) $row['quantity']])
                ->filter(fn (int $quantity) => $quantity > 0);

            if ($requested->isEmpty()) {
                throw new RuntimeException('Vui lòng chọn ít nhất một sản phẩm cần hoàn.');
            }

            $soldAfterLineDiscountCents = max(1, $order->items->sum(
                fn (CustomerOrderItem $item) => Money::cents($item->final_total)
            ));
            $prepared = [];
            $refundTotalCents = 0;

            foreach ($requested as $itemId => $quantity) {
                /** @var CustomerOrderItem|null $item */
                $item = $order->items->firstWhere('id', $itemId);

                if (! $item) {
                    throw new RuntimeException("Dòng sản phẩm #{$itemId} không thuộc đơn hàng này.");
                }

                $alreadyReturned = (int) CustomerOrderReturnItem::query()
                    ->where('customer_order_item_id', $item->id)
                    ->whereHas('orderReturn', fn ($query) => $query->where('status', OrderReturnState::Completed->value))
                    ->sum('quantity');
                $remaining = max(0, (int) $item->quantity - $alreadyReturned);

                if ($quantity > $remaining) {
                    throw new RuntimeException("Sản phẩm {$item->product_name} chỉ còn {$remaining} sản phẩm có thể hoàn.");
                }

                $amounts = $this->amountCalculator->lineRefund(
                    $item->final_total,
                    (int) $item->quantity,
                    $quantity,
                    $order->final_amount,
                    Money::decimal($soldAfterLineDiscountCents)
                );
                $unitRefund = $amounts['unit_refund_amount'];
                $lineRefund = $amounts['refund_amount'];
                $refundTotalCents += Money::cents($lineRefund);
                $prepared[] = compact('item', 'quantity', 'unitRefund', 'lineRefund');
            }

            $remainingOrderCents = max(
                0,
                Money::cents($order->final_amount) - Money::cents($order->returned_amount)
            );
            $refundTotalCents = min($refundTotalCents, $remainingOrderCents);

            $preparedTotalCents = collect($prepared)->sum(
                fn (array $line) => Money::cents($line['lineRefund'])
            );
            if ($preparedTotalCents > $refundTotalCents && $prepared !== []) {
                $lastIndex = array_key_last($prepared);
                $lastLineCents = max(
                    0,
                    Money::cents($prepared[$lastIndex]['lineRefund'])
                        - ($preparedTotalCents - $refundTotalCents)
                );
                $prepared[$lastIndex]['lineRefund'] = Money::decimal($lastLineCents);
                $prepared[$lastIndex]['unitRefund'] = Money::decimal(
                    intdiv($lastLineCents, $prepared[$lastIndex]['quantity'])
                );
            }

            $cashRefundCents = $this->cashRefundCents($data, $refundTotalCents);
            if (($data['resolution_type'] ?? null) === 'mixed' && Money::cents($data['cash_refund_amount'] ?? 0) >= $refundTotalCents) {
                throw new RuntimeException('Hoàn kết hợp phải dành lại một phần giá trị để đổi sản phẩm.');
            }

            $return = CustomerOrderReturn::create([
                'return_code' => $this->makeCode(),
                'customer_order_id' => $order->id,
                'refund_amount' => Money::decimal($refundTotalCents),
                'refund_method' => $data['refund_method'] ?? null,
                'resolution_type' => $data['resolution_type'],
                'cash_refund_amount' => Money::decimal($cashRefundCents),
                'exchange_credit_amount' => Money::decimal($refundTotalCents - $cashRefundCents),
                'resolution_status' => $data['resolution_type'] === 'refund' ? 'completed' : 'pending_exchange',
                'exchange_note' => $data['exchange_note'] ?? null,
                'status' => OrderReturnState::Completed->value,
                'reason' => trim((string) $data['reason']),
                'note' => $data['note'] ?? null,
                'returned_at' => now(),
                'created_by' => $adminId,
            ]);

            foreach ($prepared as $line) {
                $returnItem = CustomerOrderReturnItem::create([
                    'customer_order_return_id' => $return->id,
                    'customer_order_item_id' => $line['item']->id,
                    'product_id' => $line['item']->product_id,
                    'product_batch_id' => $line['item']->product_batch_id,
                    'quantity' => $line['quantity'],
                    'unit_refund_amount' => $line['unitRefund'],
                    'refund_amount' => $line['lineRefund'],
                ]);

                $this->stockService->restoreReturnedItem(
                    $order,
                    $line['item'],
                    $returnItem->quantity,
                    $adminId
                );
            }

            if ($cashRefundCents > 0) {
                $this->financialTransactionService->recordCompletedRefund(
                    $order,
                    $return,
                    Money::decimal($cashRefundCents),
                    $data['refund_method'] ?? null,
                    $adminId,
                    $data['note'] ?? null,
                );
            }

            $finalAmountCents = Money::cents($order->final_amount);
            $returnedAmountCents = min(
                $finalAmountCents,
                Money::cents($order->returned_amount) + $refundTotalCents
            );
            $netAmountCents = max(0, $finalAmountCents - $returnedAmountCents);
            $returnStatus = $netAmountCents === 0
                ? OrderReturnCoverage::Full
                : OrderReturnCoverage::Partial;
            $paidAmountCents = min(Money::cents($order->paid_amount), $netAmountCents);

            $order->update([
                'returned_amount' => Money::decimal($returnedAmountCents),
                'net_amount' => Money::decimal($netAmountCents),
                'return_status' => $returnStatus->value,
                'paid_amount' => Money::decimal($paidAmountCents),
                'debt_amount' => Money::decimal(max(0, $netAmountCents - $paidAmountCents)),
                'payment_status_id' => StatusHelper::id(
                    'payment_statuses',
                    $paidAmountCents <= 0
                        ? PaymentState::Unpaid->value
                        : ($paidAmountCents >= $netAmountCents ? PaymentState::Paid->value : PaymentState::Partial->value)
                ),
                'updated_by' => $adminId,
            ]);

            if ($order->invoice) {
                $order->invoice->update([
                    'final_amount' => Money::decimal($netAmountCents),
                    'status' => $returnStatus === OrderReturnCoverage::Full
                        ? PaymentState::Refunded->value
                        : PaymentState::PartiallyRefunded->value,
                ]);
            }

            $this->commissionService->recalculateForOrder($order->fresh(), $adminId, $return);

            OrderHistory::create([
                'customer_order_id' => $order->id,
                'action' => 'returned',
                'old_status_id' => $order->order_status_id,
                'new_status_id' => $order->order_status_id,
                'old_data' => null,
                'new_data' => ['return_id' => $return->id, 'refund_amount' => Money::decimal($refundTotalCents)],
                'note' => $return->reason,
                'created_by' => $adminId,
            ]);

            return $return->fresh(['items.orderItem', 'order']);
        });
    }

    private function makeCode(): string
    {
        do {
            $code = 'HT'.now()->format('ymdHis').random_int(100, 999);
        } while (CustomerOrderReturn::query()->where('return_code', $code)->exists());

        return $code;
    }

    private function cashRefundCents(array $data, int $returnValueCents): int
    {
        return match ($data['resolution_type']) {
            'refund' => $returnValueCents,
            'exchange' => 0,
            'mixed' => min($returnValueCents, Money::cents($data['cash_refund_amount'] ?? 0)),
            default => throw new RuntimeException('Hình thức xử lý hoàn đơn không hợp lệ.'),
        };
    }
}
