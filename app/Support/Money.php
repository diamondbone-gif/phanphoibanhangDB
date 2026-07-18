<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function cents(int|float|string|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $value = is_float($amount) ? number_format($amount, 2, '.', '') : (string) $amount;
        $value = trim(str_replace(',', '', $value));

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException("Invalid money value: {$value}");
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');
        [$whole, $decimal] = array_pad(explode('.', $value, 2), 2, '');
        $decimal = str_pad(substr($decimal, 0, 3), 3, '0');
        $cents = ((int) $whole * 100) + (int) substr($decimal, 0, 2);

        if ((int) $decimal[2] >= 5) {
            $cents++;
        }

        return $negative ? -$cents : $cents;
    }

    public static function decimal(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function percentBasisPoints(int|float|string|null $percent): int
    {
        return max(0, min(10_000, self::cents($percent)));
    }

    public static function percentage(int $amountCents, int $basisPoints): int
    {
        $sign = $amountCents < 0 ? -1 : 1;
        $absolute = abs($amountCents);

        return $sign * intdiv(($absolute * $basisPoints) + 5_000, 10_000);
    }
}
