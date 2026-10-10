<?php

namespace App\Domain\Payroll\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class NumberToWordsConverter
{
    private static array $unidades = [
        0 => '', 1 => 'UN', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO',
        5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
        15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
        20 => 'VEINTE', 21 => 'VEINTIÚN', 22 => 'VEINTIDÓS', 23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO',
        25 => 'VEINTICINCO', 26 => 'VEINTISÉIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE',
    ];

    private static array $decenas = [
        30 => 'TREINTA', 40 => 'CUARENTA', 50 => 'CINCUENTA',
        60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA', 90 => 'NOVENTA',
    ];

    private static array $centenas = [
        100 => 'CIENTO', 200 => 'DOSCIENTOS', 300 => 'TRESCIENTOS',
        400 => 'CUATROCIENTOS', 500 => 'QUINIENTOS', 600 => 'SEISCIENTOS',
        700 => 'SETECIENTOS', 800 => 'OCHOCIENTOS', 900 => 'NOVECIENTOS',
    ];

    public static function convert(string|int $amount): string
    {
        $decimal = BigDecimal::of((string) $amount)->toScale(2, RoundingMode::HALF_UP);
        $negative = $decimal->isNegative();
        $absolute = $decimal->abs();
        [$pesosPart, $centavosPart] = explode('.', (string) $absolute, 2);
        $pesos = (int) $pesosPart;
        $centavos = (int) $centavosPart;

        if ($pesos === 0) {
            $words = 'CERO';
        } else {
            $words = self::convertNumber($pesos);
        }

        if ($negative) {
            $words = 'MENOS '.$words;
        }

        return sprintf('SON PESOS: %s CON %02d/100', $words, $centavos);
    }

    private static function convertNumber(int $n): string
    {
        if ($n === 100) {
            return 'CIEN';
        }

        if ($n < 30) {
            return self::$unidades[$n];
        }

        if ($n < 100) {
            $d = intdiv($n, 10) * 10;
            $u = $n % 10;

            return self::$decenas[$d].($u > 0 ? ' Y '.self::$unidades[$u] : '');
        }

        if ($n < 1000) {
            $c = intdiv($n, 100) * 100;
            $resto = $n % 100;

            return self::$centenas[$c].($resto > 0 ? ' '.self::convertNumber($resto) : '');
        }

        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $milesText = $miles === 1 ? 'MIL' : self::convertNumber($miles).' MIL';

            return $milesText.($resto > 0 ? ' '.self::convertNumber($resto) : '');
        }

        if ($n < 1000000000) {
            $millones = intdiv($n, 1000000);
            $resto = $n % 1000000;
            $millText = $millones === 1 ? 'UN MILLÓN' : self::convertNumber($millones).' MILLONES';

            return $millText.($resto > 0 ? ' '.self::convertNumber($resto) : '');
        }

        if ($n < 1000000000000) {
            $milesDeMillones = intdiv($n, 1000000000);
            $resto = $n % 1000000000;
            $text = $milesDeMillones === 1
                ? 'MIL MILLONES'
                : self::convertNumber($milesDeMillones).' MIL MILLONES';

            return $text.($resto > 0 ? ' '.self::convertNumber($resto) : '');
        }

        if ($n < 1000000000000000) {
            $billones = intdiv($n, 1000000000000);
            $resto = $n % 1000000000000;
            $text = $billones === 1 ? 'UN BILLÓN' : self::convertNumber($billones).' BILLONES';

            return $text.($resto > 0 ? ' '.self::convertNumber($resto) : '');
        }

        return (string) $n;
    }
}
