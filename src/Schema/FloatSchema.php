<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class FloatSchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'float.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "float", {{given}} given';

    public const string ERROR_MINIMUM_CODE = 'float.minimum';
    public const string ERROR_MINIMUM_TEMPLATE = 'Value should be minimum {{minimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MINIMUM_CODE = 'float.exclusiveMinimum';
    public const string ERROR_EXCLUSIVE_MINIMUM_TEMPLATE = 'Value should be greater than {{exclusiveMinimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MAXIMUM_CODE = 'float.exclusiveMaximum';
    public const string ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE = 'Value should be lesser than {{exclusiveMaximum}}, {{given}} given';

    public const string ERROR_MAXIMUM_CODE = 'float.maximum';
    public const string ERROR_MAXIMUM_TEMPLATE = 'Value should be maximum {{maximum}}, {{given}} given';

    public const string ERROR_MULTIPLE_OF_CODE = 'float.multipleOf';
    public const string ERROR_MULTIPLE_OF_TEMPLATE = 'Value should be multiple of {{multipleOf}}, {{given}} given';

    /** @deprecated: see ERROR_MINIMUM_CODE */
    public const string ERROR_GTE_CODE = 'float.gte';

    /** @deprecated: see ERROR_MINIMUM_TEMPLATE */
    public const string ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    /** @deprecated: see ERROR_EXCLUSIVE_MINIMUM_CODE */
    public const string ERROR_GT_CODE = 'float.gt';

    /** @deprecated: see ERROR_EXCLUSIVE_MINIMUM_TEMPLATE */
    public const string ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    /** @deprecated: see ERROR_EXCLUSIVE_MAXIMUM_CODE */
    public const string ERROR_LT_CODE = 'float.lt';

    /** @deprecated: see ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE */
    public const string ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    /** @deprecated: see ERROR_MAXIMUM_CODE */
    public const string ERROR_LTE_CODE = 'float.lte';

    /** @deprecated: see ERROR_MAXIMUM_TEMPLATE */
    public const string ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public const string ERROR_INT_CODE = 'float.int';
    public const string ERROR_INT_TEMPLATE = 'Cannot convert {{given}} to int';

    public function minimum(float $minimum): static
    {
        return $this->postParse(static function (float $float) use ($minimum) {
            if ($float >= $minimum) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MINIMUM_CODE,
                    self::ERROR_MINIMUM_TEMPLATE,
                    ['minimum' => $minimum, 'given' => $float]
                )
            );
        });
    }

    public function exclusiveMinimum(float $exclusiveMinimum): static
    {
        return $this->postParse(static function (float $float) use ($exclusiveMinimum) {
            if ($float > $exclusiveMinimum) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EXCLUSIVE_MINIMUM_CODE,
                    self::ERROR_EXCLUSIVE_MINIMUM_TEMPLATE,
                    ['exclusiveMinimum' => $exclusiveMinimum, 'given' => $float]
                )
            );
        });
    }

    public function exclusiveMaximum(float $exclusiveMaximum): static
    {
        return $this->postParse(static function (float $float) use ($exclusiveMaximum) {
            if ($float < $exclusiveMaximum) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EXCLUSIVE_MAXIMUM_CODE,
                    self::ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE,
                    ['exclusiveMaximum' => $exclusiveMaximum, 'given' => $float]
                )
            );
        });
    }

    public function maximum(float $maximum): static
    {
        return $this->postParse(static function (float $float) use ($maximum) {
            if ($float <= $maximum) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAXIMUM_CODE,
                    self::ERROR_MAXIMUM_TEMPLATE,
                    ['maximum' => $maximum, 'given' => $float]
                )
            );
        });
    }

    public function multipleOf(float $multipleOf): static
    {
        if (!is_finite($multipleOf) || $multipleOf <= 0.0) {
            throw new \InvalidArgumentException(
                \sprintf('Argument #1 ($multipleOf) must be finite and greater than 0, %s given', $multipleOf)
            );
        }

        return $this->postParse(static function (float $float) use ($multipleOf) {
            if (is_finite($float) && self::isMultipleOf($float, $multipleOf)) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MULTIPLE_OF_CODE,
                    self::ERROR_MULTIPLE_OF_TEMPLATE,
                    ['multipleOf' => $multipleOf, 'given' => $float]
                )
            );
        });
    }

    /**
     * @deprecated Use minimum($minimum) instead
     */
    public function gte(float $gte): static
    {
        @trigger_error('Use minimum($minimum) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (float $float) use ($gte) {
            if ($float >= $gte) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_GTE_CODE,
                    self::ERROR_GTE_TEMPLATE,
                    ['gte' => $gte, 'given' => $float]
                )
            );
        });
    }

    /**
     * @deprecated Use exclusiveMinimum($exclusiveMinimum) instead
     */
    public function gt(float $gt): static
    {
        @trigger_error('Use exclusiveMinimum($exclusiveMinimum) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (float $float) use ($gt) {
            if ($float > $gt) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_GT_CODE,
                    self::ERROR_GT_TEMPLATE,
                    ['gt' => $gt, 'given' => $float]
                )
            );
        });
    }

    /**
     * @deprecated Use exclusiveMaximum($exclusiveMaximum) instead
     */
    public function lt(float $lt): static
    {
        @trigger_error('Use exclusiveMaximum($exclusiveMaximum) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (float $float) use ($lt) {
            if ($float < $lt) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LT_CODE,
                    self::ERROR_LT_TEMPLATE,
                    ['lt' => $lt, 'given' => $float]
                )
            );
        });
    }

    /**
     * @deprecated Use maximum($maximum) instead
     */
    public function lte(float $lte): static
    {
        @trigger_error('Use maximum($maximum) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (float $float) use ($lte) {
            if ($float <= $lte) {
                return $float;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LTE_CODE,
                    self::ERROR_LTE_TEMPLATE,
                    ['lte' => $lte, 'given' => $float]
                )
            );
        });
    }

    /**
     * @deprecated Use minimum(0.0) instead
     */
    public function nonNegative(): static
    {
        return $this->gte(0.0);
    }

    /**
     * @deprecated Use minimum(0.0, true) instead
     */
    public function positive(): static
    {
        return $this->gt(0.0);
    }

    /**
     * @deprecated Use maximum(0.0, true) instead
     */
    public function negative(): static
    {
        return $this->lt(0.0);
    }

    /**
     * @deprecated Use maximum(0.0) instead
     */
    public function nonPositive(): static
    {
        return $this->lte(0.0);
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input): ?int {
            /** @var null|float $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            $intInput = (int) $input;

            if ((float) $intInput === $input) {
                return $intInput;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_INT_CODE,
                    self::ERROR_INT_TEMPLATE,
                    ['given' => $input]
                )
            );
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input): ?string {
            /** @var null|float $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }

    protected function innerParse(mixed $input): mixed
    {
        if (\is_float($input)) {
            return $input;
        }

        if (\is_int($input)) {
            return (float) $input;
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_TYPE_CODE,
                self::ERROR_TYPE_TEMPLATE,
                ['given' => $this->getDataType($input)]
            )
        );
    }

    /**
     * Exact check based on the decimal representation of the floats (json schema spec:
     * "division by this keyword's value results in an integer"), instead of a binary
     * floating point division with an epsilon tolerance, which also accepts near misses
     * like 0.30000000000000004 for 0.1 or 1000000000000000.5 for 1.
     */
    private static function isMultipleOf(float $float, float $multipleOf): bool
    {
        [$floatDigits, $floatExponent] = self::toDigitsAndExponent($float);
        [$multipleOfDigits, $multipleOfExponent] = self::toDigitsAndExponent($multipleOf);

        $minExponent = min($floatExponent, $multipleOfExponent);

        $floatDigits .= str_repeat('0', $floatExponent - $minExponent);
        $multipleOfDigits .= str_repeat('0', $multipleOfExponent - $minExponent);

        return '0' === bcmod($floatDigits, $multipleOfDigits);
    }

    /**
     * Splits the shortest decimal representation of the given float (same as json
     * encoding) into an integer digit string and a base 10 exponent,
     * for example 1.5E-9 => ['15', -10] (15 * 10^-10).
     *
     * @return array{0: numeric-string, 1: int}
     */
    private static function toDigitsAndExponent(float $float): array
    {
        $string = var_export($float, true);

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
