<?php

namespace App\Services;

use App\Enums\FinancialTransactionState;
use App\Enums\FinancialTransactionType;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturn;
use App\Models\FinancialTransaction;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialTransactionService
{
    public function approve(FinancialTransaction $transaction, ?int $adminId): FinancialTransaction
    {
        return DB::transaction(function () use ($transaction, $adminId) {
            $transaction = FinancialTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== FinancialTransactionState::Requested) {
                throw new RuntimeException('Chỉ giao dịch đang yêu cầu mới được duyệt.');
            }
            $transaction->update([
                'status' => FinancialTransactionState::Approved,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            return $transaction;
        });
    }

    public function complete(FinancialTransaction $transaction, ?int $adminId, ?string $bankReference = null): FinancialTransaction
    {
        return DB::transaction(function () use ($transaction, $adminId, $bankReference) {
            $transaction = FinancialTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== FinancialTransactionState::Approved) {
                throw new RuntimeException('Giao dịch phải được duyệt trước khi thực hiện.');
            }
            $transaction->update([
                'status' => FinancialTransactionState::Completed,
                'executed_by' => $adminId,
                'executed_at' => now(),
                'bank_reference' => $bankReference,
                'failure_reason' => null,
            ]);

            return $transaction;
        });
    }

    public function fail(FinancialTransaction $transaction, ?int $adminId, string $reason): FinancialTransaction
    {
        return DB::transaction(function () use ($transaction, $adminId, $reason) {
            $transaction = FinancialTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status === FinancialTransactionState::Completed) {
                throw new RuntimeException('Không thể đánh dấu thất bại cho giao dịch đã hoàn tất.');
            }
            $transaction->update([
                'status' => FinancialTransactionState::Failed,
                'executed_by' => $adminId,
                'executed_at' => now(),
                'failure_reason' => trim($reason),
            ]);

            return $transaction;
        });
    }

    public function recordCompletedRefund(
        CustomerOrder $order,
        CustomerOrderReturn $return,
        string $amount,
        ?string $method,
        ?int $adminId,
        ?string $note = null,
    ): FinancialTransaction {
        return FinancialTransaction::query()->create([
            'transaction_code' => $this->makeCode('RF'),
            'type' => FinancialTransactionType::Refund,
            'status' => FinancialTransactionState::Completed,
            'customer_order_id' => $order->id,
            'customer_order_return_id' => $return->id,
            'amount' => Money::decimal(Money::cents($amount)),
            'payment_method' => $method,
            'requested_by' => $adminId,
            'approved_by' => $adminId,
            'executed_by' => $adminId,
            'requested_at' => now(),
            'approved_at' => now(),
            'executed_at' => now(),
            'note' => $note,
        ]);
    }

    private function makeCode(string $prefix): string
    {
        do {
            $code = $prefix.now()->format('ymdHis').random_int(1000, 9999);
        } while (FinancialTransaction::query()->where('transaction_code', $code)->exists());

        return $code;
    }
}
