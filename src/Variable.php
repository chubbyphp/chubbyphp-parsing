<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class Variable
{
    public static function toCode(mixed $variable, int $level = 1): string
    {
        if ($variable instanceof \DateTimeInterface) {
            return 'new \\'.$variable::class.'(\''.$variable->format('c').'\')';
        }

        if ($variable instanceof \BackedEnum) {
            return '\\'.$variable::class.'::from('.self::toCode($variable->value).')';
        }

        if ($variable instanceof \UnitEnum) {
            return '\\'.$variable::class.'::'.$variable->name;
        }

        if ($variable instanceof \stdClass) {
            return '(object) '.self::toCode((array) $variable, $level);
        }

        if (\is_object($variable)) {
            return '<\\'.$variable::class.'>';
        }

        if (\is_resource($variable)) {
            return '<resource>';
        }

        if (\is_array($variable)) {
            return '['.PHP_EOL.implode('', array_map(
                static fn (int|string $key, mixed $value) => str_repeat(' ', $level * 4).self::toCode($key).' => '.self::toCode($value, $level + 1).','.PHP_EOL,
                array_keys($variable),
                $variable
            )).str_repeat(' ', ($level - 1) * 4).']';
        }

        if (null === $variable) {
            return 'null';
        }

        return var_export($variable, true);
    }
}
