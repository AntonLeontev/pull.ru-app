<?php

namespace Src\Domain\Synchronizer\Services;

use DomainException;

class BarcodeGenerator
{
    public static function ean13(string $start = '30'): string
    {
        if (strlen($start) > 2) {
            $start = substr($start, 0, 2);
        }

        $code = $start.time();

        $control = self::ean13Control($code);

        return $code.$control;
    }

    public static function isEan13(string $code): bool
    {
        if (strlen($code) !== 13) {
            return false;
        }

        try {
            $control = static::ean13Control(substr($code, 0, 12));
        } catch (\Throwable $th) {
            return false;
        }

        return $control === substr($code, 12, 1);
    }

    private static function ean13Control(string $code): string
    {
        if (strlen($code) !== 12) {
            throw new DomainException('Основа кода должна быть из 12 цифр');
        }

        $digits = array_reverse(str_split($code.'C'));

        $step1 = ($digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11]) * 3;
        $step2 = $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10] + $digits[12];
        $stepsSum = $step1 + $step2;

        foreach (range(0, 9) as $control) {
            $sum = $stepsSum + $control;

            if ($sum % 10 === 0) {
                return (string) $control;
            }
        }
    }
}
