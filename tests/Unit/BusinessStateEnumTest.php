<?php

namespace Tests\Unit;

use App\Enums\BatchState;
use App\Enums\CommissionState;
use App\Enums\OrderReturnCoverage;
use App\Enums\OrderReturnState;
use App\Enums\PaymentState;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessStateEnumTest extends TestCase
{
    #[Test]
    public function business_state_values_match_persisted_database_values(): void
    {
        $this->assertSame('completed', OrderReturnState::Completed->value);
        $this->assertSame('partial', OrderReturnCoverage::Partial->value);
        $this->assertSame('clawback', CommissionState::Clawback->value);
        $this->assertSame('partially_refunded', PaymentState::PartiallyRefunded->value);
        $this->assertSame('near_expired', BatchState::NearExpired->value);
    }
}
