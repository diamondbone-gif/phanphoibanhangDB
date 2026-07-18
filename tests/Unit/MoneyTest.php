<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function it_converts_and_rounds_decimal_money_without_float_arithmetic(): void
    {
        self::assertSame(12346, Money::cents('123.456'));
        self::assertSame('123.46', Money::decimal(12346));
    }

    #[Test]
    public function it_calculates_percentages_in_integer_cents(): void
    {
        self::assertSame(3_657_500, Money::percentage(Money::cents('731500'), 500));
        self::assertSame('36575.00', Money::decimal(3_657_500));
    }
}
