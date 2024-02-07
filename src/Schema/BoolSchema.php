<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class BoolSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'bool.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "bool", {{given}} given';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_bool($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchPostParses($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function toFloat(): FloatSchema
    {
        return (new FloatSchema())->preParse(function ($input) {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (float) $input : null;
        })->nullable($this->nullable);
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input) {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (int) $input : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input) {
            /** @var null|bool $input */
            $input = $this->parse($input);

            return null !== $input ? (string) $input : null;
        })->nullable($this->nullable);
    }
}
