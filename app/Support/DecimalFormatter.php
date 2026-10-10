<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

final class DecimalFormatter
{
    public static function format(BigDecimal|string|int $value, int $scale = 2): string
    {
        if ($scale < 0 || $scale > 8) {
            throw new InvalidArgumentException('La escala decimal debe estar entre 0 y 8.');
        }

        $decimal = ($value instanceof BigDecimal ? $value : BigDecimal::of((string) $value))
            ->toScale($scale, RoundingMode::HALF_UP);
        $plain = (string) $decimal;
        $negative = str_starts_with($plain, '-');
        $unsigned = $negative ? substr($plain, 1) : $plain;
        [$integer, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer);

        return ($negative ? '-' : '')
            .$grouped
            .($scale > 0 ? ','.str_pad($fraction, $scale, '0') : '');
    }

    /** @param iterable<BigDecimal|string|int> $values */
    public static function sum(iterable $values): BigDecimal
    {
        $total = BigDecimal::zero();

        foreach ($values as $value) {
            $total = $total->plus($value instanceof BigDecimal ? $value : BigDecimal::of((string) $value));
        }

        return $total;
    }

    public static function isPositive(BigDecimal|string|int|null $value): bool
    {
        return $value !== null
            && ($value instanceof BigDecimal ? $value : BigDecimal::of((string) $value))->isPositive();
    }
}
