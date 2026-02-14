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
    public const string ERROR_MINIMUM_TEMPLATE = 'Value should be minimum {{minimum}} {{exclusiveMinimum}}, {{given}} given';

    public const string ERROR_MAXIMUM_CODE = 'int.maximum';
    public const string ERROR_MAXIMUM_TEMPLATE = 'Value should be maximum {{maximum}} {{exclusiveMaximum}}, {{given}} given';

    /** @deprecated: see ERROR_MINIMUM_CODE */
    public const string ERROR_GTE_CODE = 'int.gte';

    /** @deprecated: see ERROR_MINIMUM_TEMPLATE */
    public const string ERROR_GTE_TEMPLATE = 'Value should be greater than or equal {{gte}}, {{given}} given';

    /** @deprecated: see ERROR_MINIMUM_CODE */
    public const string ERROR_GT_CODE = 'int.gt';

    /** @deprecated: see ERROR_MINIMUM_TEMPLATE */
    public const string ERROR_GT_TEMPLATE = 'Value should be greater than {{gt}}, {{given}} given';

    /** @deprecated: see ERROR_MAXIMUM_CODE */
    public const string ERROR_LT_CODE = 'int.lt';

    /** @deprecated: see ERROR_MAXIMUM_TEMPLATE */
    public const string ERROR_LT_TEMPLATE = 'Value should be lesser than {{lt}}, {{given}} given';

    /** @deprecated: see ERROR_MAXIMUM_CODE */
    public const string ERROR_LTE_CODE = 'int.lte';

    /** @deprecated: see ERROR_MAXIMUM_TEMPLATE */
    public const string ERROR_LTE_TEMPLATE = 'Value should be lesser than or equal {{lte}}, {{given}} given';

    public function minimum(int $minimum, bool $exclusiveMinimum = false): static
    {
        return $this->postParse(static function (int $int) use ($minimum, $exclusiveMinimum) {
            if ((!$exclusiveMinimum && $int >= $minimum) || ($exclusiveMinimum && $int > $minimum)) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MINIMUM_CODE,
                    self::ERROR_MINIMUM_TEMPLATE,
                    ['minimum' => $minimum, 'exclusiveMinimum' => $exclusiveMinimum, 'given' => $int]
                )
            );
        });
    }

    public function maximum(int $maximum, bool $exclusiveMaximum = false): static
    {
        return $this->postParse(static function (int $int) use ($maximum, $exclusiveMaximum) {
            if ((!$exclusiveMaximum && $int <= $maximum) || ($exclusiveMaximum && $int < $maximum)) {
                return $int;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAXIMUM_CODE,
                    self::ERROR_MAXIMUM_TEMPLATE,
                    ['maximum' => $maximum, 'exclusiveMaximum' => $exclusiveMaximum, 'given' => $int]
                )
            );
        });
    }

    /**
     * @deprecated Use minimum($gte) instead
     */
    public function gte(int $gte): static
    {
        @trigger_error('Use minimum('.$this->varExport($gte).') instead', E_USER_DEPRECATED);

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
     * @deprecated Use minimum($gt, true) instead
     */
    public function gt(int $gt): static
    {
        @trigger_error('Use minimum('.$this->varExport($gt).', true) instead', E_USER_DEPRECATED);

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
     * @deprecated Use maximum($lt, true) instead
     */
    public function lt(int $lt): static
    {
        @trigger_error('Use maximum('.$this->varExport($lt).', true) instead', E_USER_DEPRECATED);

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
     * @deprecated Use maximum($lte) instead
     */
    public function lte(int $lte): static
    {
        @trigger_error('Use maximum('.$this->varExport($lte).') instead', E_USER_DEPRECATED);

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

    /**
     * @deprecated Use minimum(0) instead
     */
    public function nonNegative(): static
    {
        return $this->gte(0);
    }

    /**
     * @deprecated Use minimum(0, true) instead
     */
    public function positive(): static
    {
        return $this->gt(0);
    }

    /**
     * @deprecated Use maximum(0, true) instead
     */
    public function negative(): static
    {
        return $this->lt(0);
    }

    /**
     * @deprecated Use maximum(0) instead
     */
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
