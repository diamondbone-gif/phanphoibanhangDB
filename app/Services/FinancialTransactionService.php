<?php

namespace App\Services;

use App\Enums\FinancialTransactionState;
use App\Enums\FinancialTransactionType;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderReturn;
use App\Models\FinancialTransaction;
use App\Support\Money;

class FinancialTransactionService
{
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
