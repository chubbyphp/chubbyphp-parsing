<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class IntSchema extends AbstractNumericSchema implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'int.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "int", {{given}} given';

    public const string ERROR_MINIMUM_CODE = 'int.minimum';
    public const string ERROR_MINIMUM_TEMPLATE = 'Value should be minimum {{minimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MINIMUM_CODE = 'int.exclusiveMinimum';
    public const string ERROR_EXCLUSIVE_MINIMUM_TEMPLATE = 'Value should be greater than {{exclusiveMinimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MAXIMUM_CODE = 'int.exclusiveMaximum';
    public const string ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE = 'Value should be lesser than {{exclusiveMaximum}}, {{given}} given';

    public const string ERROR_MAXIMUM_CODE = 'int.maximum';
    public const string ERROR_MAXIMUM_TEMPLATE = 'Value should be maximum {{maximum}}, {{given}} given';

    public const string ERROR_MULTIPLE_OF_CODE = 'int.multipleOf';
    public const string ERROR_MULTIPLE_OF_TEMPLATE = 'Value should be multiple of {{multipleOf}}, {{given}} given';

    public function minimum(int $minimum): static
    {
        return $this->postParse(static function (int $int) use ($minimum) {
            if ($int >= $minimum) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MINIMUM_CODE,
                    self::ERROR_MINIMUM_TEMPLATE,
                    ['minimum' => $minimum, 'given' => $int]
                )
            );
        });
    }

    public function exclusiveMinimum(int $exclusiveMinimum): static
    {
        return $this->postParse(static function (int $int) use ($exclusiveMinimum) {
            if ($int > $exclusiveMinimum) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EXCLUSIVE_MINIMUM_CODE,
                    self::ERROR_EXCLUSIVE_MINIMUM_TEMPLATE,
                    ['exclusiveMinimum' => $exclusiveMinimum, 'given' => $int]
                )
            );
        });
    }

    public function exclusiveMaximum(int $exclusiveMaximum): static
    {
        return $this->postParse(static function (int $int) use ($exclusiveMaximum) {
            if ($int < $exclusiveMaximum) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EXCLUSIVE_MAXIMUM_CODE,
                    self::ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE,
                    ['exclusiveMaximum' => $exclusiveMaximum, 'given' => $int]
                )
            );
        });
    }

    public function maximum(int $maximum): static
    {
        return $this->postParse(static function (int $int) use ($maximum) {
            if ($int <= $maximum) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAXIMUM_CODE,
                    self::ERROR_MAXIMUM_TEMPLATE,
                    ['maximum' => $maximum, 'given' => $int]
                )
            );
        });
    }

    /**
     * A float $multipleOf covers the json schema spec, which allows a non-integer
     * multipleOf on integers ({"type": "integer", "multipleOf": 0.5}): the check uses
     * the same exact decimal math as FloatSchema::multipleOf().
     */
    public function multipleOf(float|int $multipleOf): static
    {
        if (!is_finite($multipleOf) || $multipleOf <= 0) {
            throw new \InvalidArgumentException(
                \sprintf('Argument #1 ($multipleOf) must be finite and greater than 0, %s given', $multipleOf)
            );
        }

        return $this->postParse(static function (int $int) use ($multipleOf) {
            if (self::isMultipleOf($int, $multipleOf)) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MULTIPLE_OF_CODE,
                    self::ERROR_MULTIPLE_OF_TEMPLATE,
                    ['multipleOf' => $multipleOf, 'given' => $int]
                )
            );
        });
    }

    public function toFloat(): FloatSchema
    {
        return (new FloatSchema())->preParse(function ($input): ?float {
            /** @var null|int $input */
            $input = $this->parse($input);

            /** @infection-ignore-all */
            return null !== $input ? (float) $input : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input): ?string {
            /** @var null|int $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }

    public function toDateTime(): DateTimeSchema
    {
        return (new DateTimeSchema())->preParse(function ($input): ?\DateTimeImmutable {
            /** @var null|int $input */
            $input = $this->parse($input);

            return null !== $input ? new \DateTimeImmutable('@'.$input) : null;
        })->nullable($this->nullable);
    }

    protected function innerParse(mixed $input): mixed
    {
        if (\is_int($input)) {
            return $input;
        }

        if (\is_float($input) && $this->isConvertibleToInt($input)) {
            return (int) $input;
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_TYPE_CODE,
                self::ERROR_TYPE_TEMPLATE,
                ['given' => $this->getDataType($input)]
            )
        );
    }

    private function isConvertibleToInt(float $input): bool
    {
        return $input >= PHP_INT_MIN
            && $input < PHP_INT_MAX
            && 0.0 === fmod($input, 1.0);
    }
}
