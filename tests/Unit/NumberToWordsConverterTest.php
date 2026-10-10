<?php

namespace Tests\Unit;

use App\Domain\Payroll\Services\NumberToWordsConverter;
use PHPUnit\Framework\TestCase;

class NumberToWordsConverterTest extends TestCase
{
    public function test_it_converts_decimal_strings_without_float_arithmetic(): void
    {
        $this->assertSame(
            'SON PESOS: SEISCIENTOS CATORCE MIL TRESCIENTOS CINCUENTA Y CUATRO CON 39/100',
            NumberToWordsConverter::convert('614354.39')
        );
    }

    public function test_it_rounds_cents_explicitly(): void
    {
        $this->assertSame('SON PESOS: CERO CON 01/100', NumberToWordsConverter::convert('0.005'));
    }

    public function test_it_handles_large_and_negative_exact_values(): void
    {
        $this->assertSame(
            'SON PESOS: UN BILLÓN CON 99/100',
            NumberToWordsConverter::convert('1000000000000.99')
        );
        $this->assertSame('SON PESOS: MENOS UN CON 01/100', NumberToWordsConverter::convert('-1.01'));
    }
}
