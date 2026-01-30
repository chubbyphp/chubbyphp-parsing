<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class BoolSchema extends AbstractSchemaV2 implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'bool.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "bool", {{given}} given';

    public function toFloat(): FloatSchema
    {
        return (new FloatSchema())->preParse(function ($input): ?float {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (float) $input : null;
        })->nullable($this->nullable);
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input): ?int {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (int) $input : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input): ?string {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }

    protected function innerParse(mixed $input): mixed
    {
        if (!\is_bool($input)) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_TYPE_CODE,
                    self::ERROR_TYPE_TEMPLATE,
                    ['given' => $this->getDataType($input)]
                )
            );
        }

        return $input;
    }
}
