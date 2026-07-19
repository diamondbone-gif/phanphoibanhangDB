<?php

namespace Tests\Unit;

use App\Models\WarehouseStock;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class WarehouseStockTest extends TestCase
{
    #[Test]
    public function it_calculates_available_stock_from_on_hand_and_reserved_quantities(): void
    {
        $stock = (new WarehouseStock)->forceFill([
            'on_hand_quantity' => 12,
            'reserved_quantity' => 5,
        ]);

        self::assertSame(7, $stock->available_quantity);
    }

    #[Test]
    public function it_rejects_reserved_quantity_greater_than_on_hand_quantity(): void
    {
        $stock = (new WarehouseStock)->forceFill([
            'on_hand_quantity' => 3,
            'reserved_quantity' => 4,
        ]);

        $this->expectException(RuntimeException::class);

        $stock->assertValidQuantities();
    }
}
