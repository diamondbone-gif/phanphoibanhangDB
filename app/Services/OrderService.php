<?php

namespace App\Services;

use App\Models\CustomerInvoice;
use App\Models\CustomerOrder;
use App\Models\OrderHistory;
use App\Models\Payment;
use App\Support\Money;
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
                'payment_status_id' => $this->paymentStatusId(
                    $calc['paid_amount'],
                    $calc['final_amount']
                ),

                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => $calc['combo_discount_amount'],
                'order_discount_percent' => $calc['order_discount_percent'],
                'order_discount_amount' => $calc['order_discount_amount'],
                'final_amount' => $calc['final_amount'],
                'returned_amount' => 0,
                'net_amount' => $calc['final_amount'],
                'return_status' => 'none',
                'paid_amount' => $calc['paid_amount'],
                'debt_amount' => $calc['debt_amount'],

                'stock_reverted' => false,
                'commission_created' => false,
                'commission_base_amount' => 0,

                'order_date' => now(),

                'created_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            $this->stockService->deductOrderItems($order, $calc['items'], $adminId);

            CustomerInvoice::create([
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

            if (Money::cents($calc['paid_amount']) > 0) {
                Payment::create([
                    'customer_order_id' => $order->id,
                    'payment_status_id' => $this->paymentStatusId(
                        $calc['paid_amount'],
                        $calc['final_amount']
                    ),
                    'payment_code' => $this->makeCode('PAY', 'payments', 'payment_code'),
                    'amount' => $calc['paid_amount'],
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_date' => now(),
                    'note' => 'Thanh toán khi tạo đơn',
                    'created_by' => $adminId,
                ]);
            }

            $this->history(
                order: $order,
                action: 'created',
                oldData: null,
                newData: $order->fresh()->toArray(),
                note: 'Tạo đơn hàng',
                adminId: $adminId
            );

            return $order->fresh(['items', 'invoice', 'customer', 'commission']);
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

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'completed')) {
                throw new RuntimeException('Đơn hàng đã hoàn thành không thể sửa; hãy dùng chức năng hoàn trả.');
            }

            if (($order->return_status ?? 'none') !== 'none') {
                throw new RuntimeException('Đơn hàng đã phát sinh hoàn trả, không thể sửa nội dung gốc.');
            }

            $oldData = $order->load('items')->toArray();

            /*
            |--------------------------------------------------------------------------
            | HOÀN KHO CŨ TRƯỚC KHI SỬA ĐƠN
            |--------------------------------------------------------------------------
            */
            if (! $order->stock_reverted) {
                $this->stockService->returnOrderStock($order, 'edit_return', $adminId);
            }

            /*
            |--------------------------------------------------------------------------
            | XÓA ITEM CŨ
            |--------------------------------------------------------------------------
            */
            $order->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | HỦY HOA HỒNG CŨ NẾU ĐƠN ĐÃ TỪNG TẠO HOA HỒNG
            |--------------------------------------------------------------------------
            | Khi sửa đơn đã hoàn thành, cần hủy hoa hồng cũ để tạo lại theo final_amount mới.
            |--------------------------------------------------------------------------
            */
            if ($order->commission_created) {
                $this->commissionService->cancelForOrder(
                    $order,
                    'Hủy hoa hồng cũ để sửa đơn hàng'
                );
            }

            $calc = $this->calculator->calculate($data);

            $order->update([
                'customer_id' => $data['customer_id'],

                'payment_status_id' => $this->paymentStatusId(
                    $calc['paid_amount'],
                    $calc['final_amount']
                ),

                'subtotal_amount' => $calc['subtotal_amount'],
                'product_discount_amount' => $calc['product_discount_amount'],
                'combo_discount_amount' => $calc['combo_discount_amount'],
                'order_discount_percent' => $calc['order_discount_percent'],
                'order_discount_amount' => $calc['order_discount_amount'],
                'final_amount' => $calc['final_amount'],
                'returned_amount' => 0,
                'net_amount' => $calc['final_amount'],
                'return_status' => 'none',
                'paid_amount' => $calc['paid_amount'],
                'debt_amount' => $calc['debt_amount'],

                'stock_reverted' => false,
                'commission_created' => false,
                'commission_base_amount' => 0,

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

            /*
            |--------------------------------------------------------------------------
            | NẾU ĐƠN ĐANG HOÀN THÀNH THÌ TẠO LẠI HOA HỒNG THEO FINAL_AMOUNT MỚI
            |--------------------------------------------------------------------------
            */
            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'completed')) {
                $this->commissionService->createForOrder(
                    $order->fresh(['items.product', 'customer']),
                    $adminId
                );
            }

            $this->history(
                order: $order,
                action: 'updated',
                oldData: $oldData,
                newData: $order->fresh('items')->toArray(),
                note: 'Sửa đơn hàng',
                adminId: $adminId
            );

            return $order->fresh(['items', 'invoice', 'customer', 'commission']);
        });
    }

    public function complete(CustomerOrder $order, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $adminId) {
            $order = CustomerOrder::query()
                ->with(['items.product', 'customer'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'cancelled')) {
                throw new RuntimeException('Đơn hàng đã hủy, không thể hoàn thành.');
            }

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'completed')) {
                $this->commissionService->createForOrder($order, $adminId);

                return $order->fresh(['items', 'invoice', 'customer', 'commission']);
            }

            if (($order->return_status ?? 'none') !== 'none') {
                throw new RuntimeException('Đơn hàng đã phát sinh hoàn trả, không thể hoàn thành lại.');
            }

            $oldData = $order->toArray();

            /*
            |--------------------------------------------------------------------------
            | CẬP NHẬT ĐƠN HÀNG THÀNH HOÀN THÀNH
            |--------------------------------------------------------------------------
            */
            $order->update([
                'order_status_id' => StatusHelper::id('order_statuses', 'completed'),
                'completed_at' => now(),
                'confirmed_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | BƯỚC QUAN TRỌNG: TẠO HOA HỒNG CTV KHI ĐƠN HOÀN THÀNH
            |--------------------------------------------------------------------------
            | CommissionService sẽ tự lấy:
            | - Khách hàng của đơn
            | - CTV giới thiệu khách đó
            | - % hoa hồng đã cài cho CTV
            | - final_amount của đơn hàng
            |
            | Công thức:
            | hoa hồng = final_amount × % hoa hồng CTV
            |--------------------------------------------------------------------------
            */
            $this->commissionService->createForOrder(
                $order->fresh(['items.product', 'customer']),
                $adminId
            );

            $this->history(
                order: $order,
                action: 'completed',
                oldData: $oldData,
                newData: $order->fresh()->toArray(),
                note: 'Hoàn thành đơn hàng và tạo hoa hồng CTV nếu có',
                adminId: $adminId
            );

            return $order->fresh(['items', 'invoice', 'customer', 'commission']);
        });
    }

    public function cancel(CustomerOrder $order, string $reason, ?int $adminId = null): CustomerOrder
    {
        return DB::transaction(function () use ($order, $reason, $adminId) {
            $order = CustomerOrder::query()
                ->with(['items', 'invoice', 'commission'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'cancelled')) {
                return $order;
            }

            if ((int) $order->order_status_id === StatusHelper::id('order_statuses', 'completed')) {
                throw new RuntimeException('Đơn hàng đã hoàn thành không thể hủy; hãy hoàn trả hàng hóa.');
            }

            if (($order->return_status ?? 'none') !== 'none') {
                throw new RuntimeException('Đơn hàng đã phát sinh hoàn trả, không thể hủy theo luồng hủy đơn.');
            }

            $oldData = $order->toArray();

            /*
            |--------------------------------------------------------------------------
            | HOÀN KHO KHI HỦY ĐƠN
            |--------------------------------------------------------------------------
            */
            if (! $order->stock_reverted) {
                $this->stockService->returnOrderStock($order, 'cancel_return', $adminId);
            }

            /*
            |--------------------------------------------------------------------------
            | CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG THÀNH ĐÃ HỦY
            |--------------------------------------------------------------------------
            */
            $order->update([
                'order_status_id' => StatusHelper::id('order_statuses', 'cancelled'),

                'cancelled_by' => $adminId,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,

                'stock_reverted' => true,

                'updated_by' => $adminId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | HỦY HÓA ĐƠN NẾU CÓ
            |--------------------------------------------------------------------------
            */
            if ($order->invoice) {
                $order->invoice->update([
                    'status' => 'void',
                    'voided_by' => $adminId,
                    'voided_at' => now(),
                    'note' => trim(($order->invoice->note ?? '')."\nHủy hóa đơn: ".$reason),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | HỦY HOA HỒNG CTV KHI HỦY ĐƠN
            |--------------------------------------------------------------------------
            */
            $this->commissionService->cancelForOrder(
                $order,
                $reason ?: 'Hủy đơn hàng'
            );

            $this->history(
                order: $order,
                action: 'cancelled',
                oldData: $oldData,
                newData: $order->fresh()->toArray(),
                note: $reason,
                adminId: $adminId
            );

            return $order->fresh(['items', 'invoice', 'customer', 'commission']);
        });
    }

    public function delete(CustomerOrder $order, string $reason, ?int $adminId = null): void
    {
        DB::transaction(function () use ($order, $reason, $adminId) {
            $order = $this->cancel($order, $reason, $adminId);

            $this->history(
                order: $order,
                action: 'deleted',
                oldData: $order->toArray(),
                newData: null,
                note: 'Xóa mềm đơn hàng/hóa đơn',
                adminId: $adminId
            );

            $order->delete();
        });
    }

    private function paymentStatusId(int|float|string $paidAmount, int|float|string $finalAmount): int
    {
        $paidCents = Money::cents($paidAmount);
        $finalCents = Money::cents($finalAmount);

        if ($paidCents <= 0) {
            return StatusHelper::id('payment_statuses', 'unpaid');
        }

        if ($paidCents >= $finalCents) {
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
            $code = $prefix.now()->format('ymdHis').random_int(100, 999);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
