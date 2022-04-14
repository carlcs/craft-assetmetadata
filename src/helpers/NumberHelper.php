<?php

namespace carlcs\assetmetadata\helpers;

use Craft;

class NumberHelper
{
    /**
     * Converts a decimal number to a representation of that number in another numeral system.
     */
    public static function numeralSystem(int $int, string $system, int $zero = -1): int|string
    {
        $int = match ($zero) {
            -1 => ($int < 1) ? $int - 1 : $int,
            1 => ($int > -1) ? $int + 1 : $int,
        };

        if ($int == 0) {
            return $zero;
        }

        if ($int < 0) {
            $int = abs($int);
            $prefix = '-';
        } else {
            $prefix = '';
        }

        return match ($system) {
            'roman', 'upperRoman' => $prefix . self::_roman($int),
            'lowerRoman' => $prefix . self::_roman($int, 'lower'),
            'alpha', 'upperAlpha' => $prefix . self::_alpha($int),
            'lowerAlpha' => $prefix . self::_alpha($int, 'lower'),
            default => $int,
        };
    }

    /**
     * Formats a number with unit prefixes.
     */
    public static function unitPrefix(float $float, array|string $system = 'decimal', int $decimals = 1, bool $trailingZeros = false, string $decPoint = '.', string $thousandsSep = '', string $unitSep = ' '): string
    {
        if (is_string($system)) {
            $system = self::_getUnitPrefixSettings($system);
        }

        if (!array_key_exists('map', $system)) {
            return $float;
        }

        $base = array_key_exists('base', $system) ? $system['base'] : 10;

        /** @var array $map */
        $map = $system['map'];

        foreach ($map as $exp => $prefix) {
            if ($float >= ($base ** $exp)) {
                $float /= ($base ** $exp);

                $float = number_format($float, $decimals, $decPoint, $thousandsSep);

                if (!$trailingZeros) {
                    $float = self::trimTrailingZeroes($float, $decPoint);
                }

                return $float.$unitSep.Craft::t('site', $prefix);
            }
        }

        return $float;
    }

    /**
     * Converts a fraction to a decimal number.
     */
    public static function fractionToFloat(string $str, int $precision = 4): float
    {
        if (self::isFloat($str)) {
            return $str;
        }

        if (self::isFraction($str)) {
            list($numerator, $denominator) = explode('/', $str);

            $float = $numerator / ($denominator ?: 1);

            return round($float, $precision);
        }

        return 0;
    }

    /**
     * Converts a decimal number to a fraction.
     */
    public static function floatToFraction(float $float, float $tolerance = 0.001): string
    {
        if (!self::isFloat($float)) {
            return 0;
        }

        $h1 = 1;
        $h2 = 0;
        $k1 = 0;
        $k2 = 1;
        $b = 1 / $float;

        do {
            $b = 1 / $b;
            $a = floor($b);
            $aux = $h1;
            $h1 = $a * $h1 + $h2;
            $h2 = $aux;
            $aux = $k1;
            $k1 = $a * $k1 + $k2;
            $k2 = $aux;
            $b -= $a;
        } while (abs($float - $h1 / $k1) > $float * $tolerance);

        if ($h1 == $k1) {
            return $h1;
        }

        return $h1.'/'.$k1;
    }

    /**
     * Returns whether a number is a fraction.
     */
    public static function isFraction(string $str): bool
    {
        return preg_match('/^[-+]?\d*\.?\d+[ ]?\/[ ]?[-+]?\d*\.?\d+$/', $str);
    }

    /**
     * Returns whether a number is a rational number.
     */
    public static function isFloat(float $float): bool
    {
        return preg_match('/^[-+]?\d*\.?\d+$/', $float);
    }

    /**
     * Trims trailing zeroes.
     */
    public static function trimTrailingZeroes(int $int, string $decPoint = '.'): string
    {
        return str_contains($int, $decPoint) ? rtrim(rtrim($int, '0'), $decPoint) : $int;
    }

    // Private Methods
    // =========================================================================

    /**
     * Converts a decimal number to its roman numberal equivalent.
     */
    private static function _roman(int $int, string $case = 'upper'): string
    {
        $map = [1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'];
        $roman = '';

        foreach ($map as $d => $r) {
            $roman .= str_repeat($r, (int)($int / $d));
            $int %= $d;
        }

        return ($case == 'lower') ? strtolower($roman) : $roman;
    }

    /**
     * Converts a decimal number to its alphabetic equivalent.
     */
    private static function _alpha(int $int, string $case = 'upper'): string
    {
        $counter = 1;
        for ($alpha = 'A'; $alpha <= 'ZZ'; $alpha++) {
            if ($counter == $int) {
                return ($case == 'lower') ? strtolower($alpha) : $alpha;
            }
            $counter++;
        }

        return '';
    }

    /**
     * Returns configuration settings for unit prefixes.
     */
    private static function _getUnitPrefixSettings(string $preset): array
    {
        $settings = [];

        switch ($preset) {
            case 'names':
                $settings['map'] = [12 => 'trillion', 9 => 'billion', 6 => 'million', 3 => 'thousand', 2 => 'hundred', 0 => ''];
                break;
            case 'decimal':
            case 'decimalSymbol':
                $settings['map'] = [15 => 'P', 12 => 'T', 9 => 'G', 6 => 'M', 3 => 'k', 0 => '', -2 => 'c', -3 => 'm', -6 => 'µ', -9 => 'n'];
                break;
            case 'decimalNames':
                $settings['map'] = [15 => 'peta', 12 => 'tera', 9 => 'giga', 6 => 'mega', 3 => 'kilo', 0 => '', -2 => 'centi', -3 => 'milli', -6 => 'micro', -9 => 'nano'];
                break;
            case 'binary':
            case 'binarySymbol':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'Pi', 40 => 'Ti', 30 => 'Gi', 20 => 'Mi', 10 => 'Ki', 0 => ''];
                break;
            case 'binaryNames':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'pebi', 40 => 'tebi', 30 => 'gibi', 20 => 'mebi', 10 => 'kibi', 0 => ''];
                break;
        }

        return $settings;
    }
}
