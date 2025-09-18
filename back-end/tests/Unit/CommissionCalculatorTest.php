<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CommissionCalculator;
use PHPUnit\Framework\TestCase;

final class CommissionCalculatorTest extends TestCase
{
    public function test_compute_with_bcmath_or_fallback(): void
    {
        $calc = new CommissionCalculator();
        $value = 100.00;
        $percent = 0.18;
        $result = $calc->compute($value, $percent);
        $this->assertSame(18.00, $result);
    }
}
