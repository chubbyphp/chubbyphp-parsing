<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class FloatSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'float.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "float", "{{given}}" given';

    public const ERROR_GT_CODE = 'float.gt';
    public const ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const ERROR_GTE_CODE = 'float.gte';
    public const ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const ERROR_LT_CODE = 'float.lt';
    public const ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const ERROR_LTE_CODE = 'float.lte';
    public const ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public const ERROR_INT_CODE = 'float.int';
    public const ERROR_INT_TEMPLATE = 'Cannot convert {{given}} to int';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreMiddlewares($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_float($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchPostMiddlewares($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function gt(float $gt): static
    {
        return $this->postMiddleware(static function (float $float) use ($gt) {
            if ($float <= $gt) {
                throw new ParserErrorException(
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
        return $this->postMiddleware(static function (float $float) use ($gte) {
            if ($float < $gte) {
                throw new ParserErrorException(
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
        return $this->postMiddleware(static function (float $float) use ($lt) {
            if ($float >= $lt) {
                throw new ParserErrorException(
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
        return $this->postMiddleware(static function (float $float) use ($lte) {
            if ($float > $lte) {
                throw new ParserErrorException(
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
        return (new IntSchema())->preMiddleware(function ($input) {
            /** @var float $input */
            $input = $this->parse($input);

            $intInput = (int) $input;

            if ((float) $intInput !== $input) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_INT_CODE,
                        self::ERROR_INT_TEMPLATE,
                        ['given' => $input]
                    )
                );
            }

            return $intInput;
        });
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preMiddleware(function ($input) {
            /** @var float $input */
            $input = $this->parse($input);

            return (string) $input;
        });
    }
}
