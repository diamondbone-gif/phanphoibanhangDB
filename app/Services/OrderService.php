<?php

namespace App\Services;

use App\Models\CustomerInvoice;
use App\Models\CustomerOrder;
use App\Models\OrderHistory;
use App\Models\Payment;
use App\Support\StatusHelper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        private OrderCalculatorService $calculator,
        private StockService $stockService,
        private CommissionService $commissionService,
    ) {}

    public function create(array $data, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($data, $adminId) {
            $calc = $this->calculator->calculate($data);

            $order = CustomerOrder::create([
                'order_code' => $this->makeCode('DH', 'customer_orders', 'order_code'),
                'customer_id' => $data['customer_id'],
                'order_status_id' => StatusHelper::id('order_statuses', 'pending'),
                'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),
                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => $calc['combo_discount_amount'],
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
            ]);

            $this->stockService->deductOrderItems($order, $calc['items'], $adminId);

            $invoice = CustomerInvoice::create([
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
            ]);

            if ($calc['paid_amount'] > 0) {
                Payment::create([
                    'customer_order_id' => $order->id,
                    'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),
                    'payment_code' => $this->makeCode('PAY', 'payments', 'payment_code'),
                    'amount' => $calc['paid_amount'],
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_date' => now(),
                    'note' => 'Thanh toán khi tạo đơn',
                    'created_by' => $adminId,
                ]);
            }

            $this->history($order, 'created', null, $order->fresh()->toArray(), 'Tạo đơn hàng', $adminId);

            return $order->fresh(['items', 'invoice', 'customer']);
        });
    }

    public function update(CustomerOrder $order, array $data, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $data, $adminId) {
            $order = CustomerOrder::query()
                ->with(['items', 'invoice', 'commission'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'cancelled')) {
                throw new RuntimeException('Đơn hàng đã hủy, không thể sửa.');
            }

            $oldData = $order->load('items')->toArray();

            if (!$order->stock_reverted) {
                $this->stockService->returnOrderStock($order, 'edit_return', $adminId);
            }

            $order->items()->delete();

            if ($order->commission_created) {
                $this->commissionService->reverseForCancelledOrder($order, 'Đảo hoa hồng để sửa đơn hàng', $adminId);
            }

            $calc = $this->calculator->calculate($data);

            $order->update([
                'customer_id' => $data['customer_id'],
                'payment_status_id' => $this->paymentStatusId($calc['paid_amount'], $calc['final_amount']),
                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => $calc['combo_discount_amount'],
                'order_discount_percent' => $calc['order_discount_percent'],
                'order_discount_amount' => $calc['order_discount_amount'],
                'final_amount' => $calc['final_amount'],
                'paid_amount' => $calc['paid_amount'],
                'debt_amount' => $calc['debt_amount'],
                'stock_reverted' => false,
                'updated_by' => $adminId,
            ]);

            $this->stockService->deductOrderItems($order, $calc['items'], $adminId);

            if ($order->invoice) {
                $order->invoice->update([
                    'customer_id' => $order->customer_id,
                    'total_amount' => $calc['subtotal_amount'],
                    'final_amount' => $calc['final_amount'],
                    'note' => $data['note'] ?? null,
                ]);
            }

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'completed')) {
                $this->commissionService->createForCompletedOrder($order->fresh('items.product'), $adminId);
            }

            $this->history($order, 'updated', $oldData, $order->fresh('items')->toArray(), 'Sửa đơn hàng', $adminId);

            return $order->fresh(['items', 'invoice', 'customer']);
        });
    }

    public function complete(CustomerOrder $order, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $adminId) {
            $order = CustomerOrder::query()->lockForUpdate()->findOrFail($order->id);

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'cancelled')) {
                throw new RuntimeException('Đơn hàng đã hủy, không thể hoàn thành.');
            }

            $oldData = $order->toArray();

            $order->update([
                'order_status_id' => StatusHelper::id('order_statuses', 'completed'),
                'completed_at' => now(),
                'confirmed_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            $this->commissionService->createForCompletedOrder($order->fresh(['items.product']), $adminId);

            $this->history($order, 'completed', $oldData, $order->fresh()->toArray(), 'Hoàn thành đơn hàng', $adminId);

            return $order->fresh(['items', 'invoice', 'commission']);
        });
    }

    public function cancel(CustomerOrder $order, string $reason, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $reason, $adminId) {
            $order = CustomerOrder::query()
                ->with(['items', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'cancelled')) {
                return $order;
            }

            $oldData = $order->toArray();

            if (!$order->stock_reverted) {
                $this->stockService->returnOrderStock($order, 'cancel_return', $adminId);
            }

            $order->update([
                'order_status_id' => StatusHelper::id('order_statuses', 'cancelled'),
                'cancelled_by' => $adminId,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'stock_reverted' => true,
                'updated_by' => $adminId,
            ]);

            if ($order->invoice) {
                $order->invoice->update([
                    'status' => 'void',
                    'note' => trim(($order->invoice->note ?? '') . "\nHủy hóa đơn: " . $reason),
                ]);
            }

            $this->commissionService->reverseForCancelledOrder($order, $reason, $adminId);

            $this->history($order, 'cancelled', $oldData, $order->fresh()->toArray(), $reason, $adminId);

            return $order->fresh(['items', 'invoice', 'commission']);
        });
    }

    public function delete(CustomerOrder $order, string $reason, ?int $adminId = null): void
    {
        DB::transaction(function () use ($order, $reason, $adminId) {
            $order = $this->cancel($order, $reason, $adminId);

            $this->history($order, 'deleted', $order->toArray(), null, 'Xóa mềm đơn hàng/hóa đơn', $adminId);

            $order->delete();
        });
    }

    private function paymentStatusId(float $paidAmount, float $finalAmount): int
    {
        if ($paidAmount <= 0) {
            return StatusHelper::id('payment_statuses', 'unpaid');
        }

        if ($paidAmount >= $finalAmount) {
            return StatusHelper::id('payment_statuses', 'paid');
        }

        return StatusHelper::id('payment_statuses', 'partial');
    }

    private function history(
        CustomerOrder $order,
        string $action,
        ?array $oldData,
        ?array $newData,
        ?string $note,
        ?int $adminId
    ): void {
        OrderHistory::create([
            'customer_order_id' => $order->id,
            'action' => $action,
            'old_status_id' => $oldData['order_status_id'] ?? null,
            'new_status_id' => $newData['order_status_id'] ?? null,
            'old_data' => $oldData,
            'new_data' => $newData,
            'note' => $note,
            'created_by' => $adminId,
        ]);
    }

    private function makeCode(string $prefix, string $table, string $column): string
    {
        do {
            $code = $prefix . now()->format('ymdHis') . random_int(100, 999);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
