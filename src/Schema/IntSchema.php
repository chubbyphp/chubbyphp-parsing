<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class IntSchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'int.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "int", {{given}} given';

    public const string ERROR_MINIMUM_CODE = 'int.minimum';
    public const string ERROR_MINIMUM_TEMPLATE = 'Value should be greater than or equal {{minimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MINIMUM_CODE = 'int.exclusiveMinimum';
    public const string ERROR_EXCLUSIVE_MINIMUM_TEMPLATE = 'Value should be greater than {{exclusiveMinimum}}, {{given}} given';

    public const string ERROR_EXCLUSIVE_MAXIMUM_CODE = 'int.exclusiveMaximum';
    public const string ERROR_EXCLUSIVE_MAXIMUM_TEMPLATE = 'Value should be lesser than {{exclusiveMaximum}}, {{given}} given';

    public const string ERROR_MAXIMUM_CODE = 'int.maximum';
    public const string ERROR_MAXIMUM_TEMPLATE = 'Value should be lesser than or equal {{maximum}}, {{given}} given';

    public const string ERROR_GTE_CODE = 'int.gte';
    public const string ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    public const string ERROR_GT_CODE = 'int.gt';
    public const string ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    public const string ERROR_LT_CODE = 'int.lt';
    public const string ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    public const string ERROR_LTE_CODE = 'int.lte';
    public const string ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

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
     * @deprecated use minimum
     */
    public function gte(int $gte): static
    {
        @trigger_error('Use minimum instead', E_USER_DEPRECATED);

        return $this->postParse(static function (int $int) use ($gte) {
            if ($int >= $gte) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_GTE_CODE,
                    self::ERROR_GTE_TEMPLATE,
                    ['gte' => $gte, 'given' => $int]
                )
            );
        });
    }

    /**
     * @deprecated use exclusiveMinimum
     */
    public function gt(int $gt): static
    {
        @trigger_error('Use exclusiveMinimum instead', E_USER_DEPRECATED);

        return $this->postParse(static function (int $int) use ($gt) {
            if ($int > $gt) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_GT_CODE,
                    self::ERROR_GT_TEMPLATE,
                    ['gt' => $gt, 'given' => $int]
                )
            );
        });
    }

    /**
     * @deprecated use exclusiveMaximum
     */
    public function lt(int $lt): static
    {
        @trigger_error('Use exclusiveMaximum instead', E_USER_DEPRECATED);

        return $this->postParse(static function (int $int) use ($lt) {
            if ($int < $lt) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LT_CODE,
                    self::ERROR_LT_TEMPLATE,
                    ['lt' => $lt, 'given' => $int]
                )
            );
        });
    }

    /**
     * @deprecated use maximum
     */
    public function lte(int $lte): static
    {
        @trigger_error('Use maximum instead', E_USER_DEPRECATED);

        return $this->postParse(static function (int $int) use ($lte) {
            if ($int <= $lte) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LTE_CODE,
                    self::ERROR_LTE_TEMPLATE,
                    ['lte' => $lte, 'given' => $int]
                )
            );
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

        throw new ErrorsException(
            new Error(
                self::ERROR_TYPE_CODE,
                self::ERROR_TYPE_TEMPLATE,
                ['given' => $this->getDataType($input)]
            )
        );
    }
}
