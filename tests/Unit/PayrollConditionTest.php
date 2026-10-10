<?php

namespace Tests\Unit;

use App\Domain\Payroll\Services\PayrollSettlementService;
use Brick\Math\BigDecimal;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PayrollConditionTest extends TestCase
{
    #[DataProvider('comparisonCases')]
    public function test_conditions_compare_decimal_values_exactly(string $condition, bool $expected): void
    {
        $service = new PayrollSettlementService;

        $this->assertSame($expected, $service->evaluateCondition($condition, [
            'VALUE' => BigDecimal::of('0.50'),
        ]));
    }

    public function test_unknown_variables_fail_closed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Variable desconocida');

        (new PayrollSettlementService)->evaluateCondition('UNKNOWN=0', []);
    }

    public function test_unrecognized_conditions_fail_closed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no reconocida');

        (new PayrollSettlementService)->evaluateCondition('VALUE + 1', ['VALUE' => '0']);
    }

    public static function comparisonCases(): array
    {
        return [
            'equal ignores numeric scale' => ['VALUE=0.5', true],
            'not equal' => ['VALUE!=0.5001', true],
            'less than' => ['VALUE<0.51', true],
            'less than or equal' => ['VALUE<=0.50', true],
            'greater than' => ['VALUE>0.49', true],
            'greater than or equal' => ['VALUE>=0.500', true],
            'false comparison' => ['VALUE<0.5', false],
        ];
    }
}
