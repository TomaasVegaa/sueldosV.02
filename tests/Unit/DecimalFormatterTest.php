<?php

namespace Tests\Unit;

use App\Support\DecimalFormatter;
use PHPUnit\Framework\TestCase;

class DecimalFormatterTest extends TestCase
{
    public function test_it_formats_and_sums_exact_decimal_strings(): void
    {
        $total = DecimalFormatter::sum(['9999999999999.99', '0.01']);

        $this->assertSame('10.000.000.000.000,00', DecimalFormatter::format($total));
        $this->assertSame('-1.234,57', DecimalFormatter::format('-1234.565'));
    }

    public function test_positive_check_does_not_coerce_to_float(): void
    {
        $this->assertTrue(DecimalFormatter::isPositive('0.00000001'));
        $this->assertFalse(DecimalFormatter::isPositive('0.00'));
        $this->assertFalse(DecimalFormatter::isPositive(null));
    }
}
