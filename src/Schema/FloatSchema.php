<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class FloatSchema extends AbstractSchema implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'float.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "float", {{given}} given';

    public const string ERROR_GT_CODE = 'float.gt';
    public const string ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const string ERROR_GTE_CODE = 'float.gte';
    public const string ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const string ERROR_LT_CODE = 'float.lt';
    public const string ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const string ERROR_LTE_CODE = 'float.lte';
    public const string ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public const string ERROR_INT_CODE = 'float.int';
    public const string ERROR_INT_TEMPLATE = 'Cannot convert {{given}} to int';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_float($input)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchPostParses($input);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }

    public function gt(float $gt): static
    {
        return $this->postParse(static function (float $float) use ($gt) {
            if ($float <= $gt) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_GT_CODE,
                        self::ERROR_GT_TEMPLATE,
                        ['gt' => $gt, 'given' => $float]
                    )
                );
            }

            return $float;
        });
    }

    public function gte(float $gte): static
    {
        return $this->postParse(static function (float $float) use ($gte) {
            if ($float < $gte) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_GTE_CODE,
                        self::ERROR_GTE_TEMPLATE,
                        ['gte' => $gte, 'given' => $float]
                    )
                );
            }

            return $float;
        });
    }

    public function lt(float $lt): static
    {
        return $this->postParse(static function (float $float) use ($lt) {
            if ($float >= $lt) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_LT_CODE,
                        self::ERROR_LT_TEMPLATE,
                        ['lt' => $lt, 'given' => $float]
                    )
                );
            }

            return $float;
        });
    }

    public function lte(float $lte): static
    {
        return $this->postParse(static function (float $float) use ($lte) {
            if ($float > $lte) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_LTE_CODE,
                        self::ERROR_LTE_TEMPLATE,
                        ['lte' => $lte, 'given' => $float]
                    )
                );
            }

            return $float;
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
        return (new IntSchema())->preParse(function ($input) {
            /** @var null|float $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            $intInput = (int) $input;

            if ((float) $intInput !== $input) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_INT_CODE,
                        self::ERROR_INT_TEMPLATE,
                        ['given' => $input]
                    )
                );
            }

            return $intInput;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input) {
            /** @var null|float $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }
}
