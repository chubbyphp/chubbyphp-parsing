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
    public const string ERROR_MINIMUM_TEMPLATE = 'Value should be greater than or equal {{minimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MINIMUM_CODE = 'float.exclusiveMinimum';
    public const string ERROR_EXCLUSIVE_MINIMUM_TEMPLATE = 'Value should be greater than {{exclusiveMinimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MAXIMUM_CODE = 'float.exclusiveMaximum';
    public const string ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE = 'Value should be lesser than {{exclusiveMaximum}}, {{given}} given';

    public const string ERROR_MAXIMUM_CODE = 'float.maximum';
    public const string ERROR_MAXIMUM_TEMPLATE = 'Value should be lesser than or equal {{maximum}}, {{given}} given';

    public const string ERROR_GTE_CODE = 'float.gte';
    public const string ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const string ERROR_GT_CODE = 'float.gt';
    public const string ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const string ERROR_LT_CODE = 'float.lt';
    public const string ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const string ERROR_LTE_CODE = 'float.lte';
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

    /**
     * @deprecated use minimum
     */
    public function gte(float $gte): static
    {
        @trigger_error('Use minimum instead', E_USER_DEPRECATED);

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
     * @deprecated use exclusiveMinimum
     */
    public function gt(float $gt): static
    {
        @trigger_error('Use exclusiveMinimum instead', E_USER_DEPRECATED);

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
     * @deprecated use exclusiveMaximum
     */
    public function lt(float $lt): static
    {
        @trigger_error('Use exclusiveMaximum instead', E_USER_DEPRECATED);

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
     * @deprecated use maximum
     */
    public function lte(float $lte): static
    {
        @trigger_error('Use maximum instead', E_USER_DEPRECATED);

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

    public function positive(): static
    {
        return $this->gt(0.0);
    }

    public function nonNegative(): static
    {
        return $this->gte(0.0);
    }

    public function negative(): static
    {
        return $this->lt(0.0);
    }

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

        throw new ErrorsException(
            new Error(
                self::ERROR_TYPE_CODE,
                self::ERROR_TYPE_TEMPLATE,
                ['given' => $this->getDataType($input)]
            )
        );
    }
}
