<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class IntSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'int.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "int", {{given}} given';

    public const ERROR_GT_CODE = 'int.gt';
    public const ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const ERROR_GTE_CODE = 'int.gte';
    public const ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const ERROR_LT_CODE = 'int.lt';
    public const ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const ERROR_LTE_CODE = 'int.lte';
    public const ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_int($input)) {
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

    public function gt(int $gt): static
    {
        return $this->postParse(static function (int $int) use ($gt) {
            if ($int <= $gt) {
                throw new ErrorsException(
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
        return $this->postParse(static function (int $int) use ($gte) {
            if ($int < $gte) {
                throw new ErrorsException(
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
        return $this->postParse(static function (int $int) use ($lt) {
            if ($int >= $lt) {
                throw new ErrorsException(
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
        return $this->postParse(static function (int $int) use ($lte) {
            if ($int > $lte) {
                throw new ErrorsException(
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

    public function toFloat(): FloatSchema
    {
        return (new FloatSchema())->preParse(function ($input) {
            /** @var null|int $input */
            $input = $this->parse($input);

            return null !== $input ? (float) $input : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input) {
            /** @var null|int $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }

    public function toDateTime(): DateTimeSchema
    {
        return (new DateTimeSchema())->preParse(function ($input) {
            /** @var null|int $input */
            $input = $this->parse($input);

            return null !== $input ? new \DateTimeImmutable('@'.$input) : null;
        })->nullable($this->nullable);
    }
}
