<?php

namespace Tests\Unit;

use App\Services\ReturnAmountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReturnAmountCalculatorTest extends TestCase
{
    #[Test]
    public function it_calculates_a_partial_return_after_order_discount(): void
    {
        $result = (new ReturnAmountCalculator)->lineRefund(
            lineFinalAmount: 200_000,
            soldQuantity: 2,
            returnQuantity: 1,
            orderFinalAmount: 270_000,
            sumLineFinalAmounts: 300_000,
        );

        self::assertSame('90000.00', $result['unit_refund_amount']);
        self::assertSame('90000.00', $result['refund_amount']);
    }

    #[Test]
    public function it_never_increases_refund_when_order_total_exceeds_lines(): void
    {
        $result = (new ReturnAmountCalculator)->lineRefund(100_000, 1, 1, 120_000, 100_000);

        self::assertSame('100000.00', $result['refund_amount']);
    }

    #[Test]
    public function it_rejects_returning_more_than_was_sold(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ReturnAmountCalculator)->lineRefund(100_000, 1, 2, 100_000, 100_000);
    }
}
