<?php

namespace App\Services;

use App\Support\Money;
use InvalidArgumentException;

class ReturnAmountCalculator
{
    public function lineRefund(
        int|float|string $lineFinalAmount,
        int $soldQuantity,
        int $returnQuantity,
        int|float|string $orderFinalAmount,
        int|float|string $sumLineFinalAmounts
    ): array {
        if ($soldQuantity < 1 || $returnQuantity < 1 || $returnQuantity > $soldQuantity) {
            throw new InvalidArgumentException('Số lượng hoàn trả không hợp lệ.');
        }

        $lineCents = max(0, Money::cents($lineFinalAmount));
        $orderCents = max(0, Money::cents($orderFinalAmount));
        $sumLineCents = max(0, Money::cents($sumLineFinalAmounts));
        $discountedLineCents = $sumLineCents > 0
            ? intdiv(($lineCents * min($orderCents, $sumLineCents)) + intdiv($sumLineCents, 2), $sumLineCents)
            : 0;
        $unitRefundCents = intdiv($discountedLineCents + intdiv($soldQuantity, 2), $soldQuantity);

        return [
            'unit_refund_amount' => Money::decimal($unitRefundCents),
            'refund_amount' => Money::decimal($unitRefundCents * $returnQuantity),
        ];
    }
}
