<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class IntSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'int.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "int", "{{given}}" given';

    public const ERROR_GT_CODE = 'int.gt';
    public const ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const ERROR_GTE_CODE = 'int.gte';
    public const ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const ERROR_LT_CODE = 'int.lt';
    public const ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const ERROR_LTE_CODE = 'int.lte';
    public const ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public const ERROR_MULTIPLEOF_CODE = 'int.multipleOf';
    public const ERROR_MULTIPLEOF_TEMPLATE = 'Value should be multiple of {{multipleOf}}, {{given}} given';

    public const ERROR_DIVISOROF_CODE = 'int.divisorOf';
    public const ERROR_DIVISOROF_TEMPLATE = 'Value should be divisor of {{divisorOf}}, {{given}} given';

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_int($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchMiddlewares($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function gt(int $gt): static
    {
        return $this->middleware(static function (int $int) use ($gt) {
            if ($int <= $gt) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_GT_CODE,
                        self::ERROR_GT_TEMPLATE,
                        ['gt' => $gt, 'given' => $int]
                    )
                );
            }

            return $int;
        });
    }

    public function gte(int $gte): static
    {
        return $this->middleware(static function (int $int) use ($gte) {
            if ($int < $gte) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_GTE_CODE,
                        self::ERROR_GTE_TEMPLATE,
                        ['gte' => $gte, 'given' => $int]
                    )
                );
            }

            return $int;
        });
    }

    public function lt(int $lt): static
    {
        return $this->middleware(static function (int $int) use ($lt) {
            if ($int >= $lt) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_LT_CODE,
                        self::ERROR_LT_TEMPLATE,
                        ['lt' => $lt, 'given' => $int]
                    )
                );
            }

            return $int;
        });
    }

    public function lte(int $lte): static
    {
        return $this->middleware(static function (int $int) use ($lte) {
            if ($int > $lte) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_LTE_CODE,
                        self::ERROR_LTE_TEMPLATE,
                        ['lte' => $lte, 'given' => $int]
                    )
                );
            }

            return $int;
        });
    }

    public function positive(): static
    {
        return $this->gt(0);
    }

    public function nonNegative(): static
    {
        return $this->gte(0);
    }

    public function negative(): static
    {
        return $this->lt(0);
    }

    public function nonPositive(): static
    {
        return $this->lte(0);
    }

    public function toFloat(): static
    {
        return $this->middleware(static fn (int $int) => (float) $int);
    }

    public function toString(): static
    {
        return $this->middleware(static fn (int $int) => (string) $int);
    }
}
