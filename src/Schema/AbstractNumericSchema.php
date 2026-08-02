<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

abstract class AbstractNumericSchema extends AbstractSchemaInnerParse
{
    /**
     * Exact check based on the decimal representation of the given numbers (json schema
     * spec: "division by this keyword's value results in an integer"), instead of a
     * binary floating point division with an epsilon tolerance, which also accepts near
     * misses like 0.30000000000000004 for 0.1 or 1000000000000000.5 for 1.
     */
    final protected static function isMultipleOf(float|int $value, float|int $multipleOf): bool
    {
        [$valueDigits, $valueExponent] = self::toDigitsAndExponent($value);
        [$multipleOfDigits, $multipleOfExponent] = self::toDigitsAndExponent($multipleOf);

        $minExponent = min($valueExponent, $multipleOfExponent);

        $valueDigits .= str_repeat('0', $valueExponent - $minExponent);
        $multipleOfDigits .= str_repeat('0', $multipleOfExponent - $minExponent);

        return '0' === bcmod($valueDigits, $multipleOfDigits);
    }

    /**
     * Splits the shortest decimal representation of the given number (same as json
     * encoding) into an integer digit string and a base 10 exponent,
     * for example 1.5E-9 => ['15', -10] (15 * 10^-10). Ints are printed exactly by
     * var_export, so they need no dedicated path.
     *
     * @return array{0: numeric-string, 1: int}
     */
    private static function toDigitsAndExponent(float|int $value): array
    {
        $string = var_export($value, true);

        $exponent = 0;

        if (false !== ($ePosition = strpos($string, 'E'))) {
            $exponentString = substr($string, $ePosition + 1);
            // @infection-ignore-all: removing the (int) cast keeps a numeric-string, which behaves identically
            $exponent = (int) $exponentString;
            $string = substr($string, 0, $ePosition);
        }

        if (false !== ($pointPosition = strpos($string, '.'))) {
            // @infection-ignore-all: an off-by-n here shifts both operands equally and cancels out in isMultipleOf
            $exponent -= \strlen($string) - $pointPosition - 1;
            $string = str_replace('.', '', $string);
        }

        /** @var numeric-string $string */
        return [$string, $exponent];
    }
}
