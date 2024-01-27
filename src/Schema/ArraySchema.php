<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class ArraySchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'array.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", "{{given}}" given';

    public const ERROR_LENGTH_CODE = 'array.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const ERROR_MIN_LENGTH_CODE = 'array.minLength';
    public const ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const ERROR_MAX_LENGTH_CODE = 'array.maxLength';
    public const ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const ERROR_CONTAINS_CODE = 'array.contains';
    public const ERROR_CONTAINS_TEMPLATE = '"{{given}}" does not contain "{{contains}}"';

    public function __construct(private SchemaInterface $itemSchema) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_array($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $array = [];

            $childrenParserErrorException = new ParserErrorException();

            foreach ($input as $i => $item) {
                try {
                    $array[$i] = $this->itemSchema->parse($item);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $i);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
            }

            return $this->dispatchMiddlewares($array);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function length(int $length): static
    {
        return $this->middleware(static function (array $array) use ($length) {
            $arrayLength = \count($array);

            if ($arrayLength !== $length) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_LENGTH_CODE,
                        self::ERROR_LENGTH_TEMPLATE,
                        ['length' => $length, 'given' => $arrayLength]
                    )
                );
            }

            return $array;
        });
    }

    public function minLength(int $minLength): static
    {
        return $this->middleware(static function (array $array) use ($minLength) {
            $arrayLength = \count($array);

            if ($arrayLength < $minLength) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MIN_LENGTH_CODE,
                        self::ERROR_MIN_LENGTH_TEMPLATE,
                        ['minLength' => $minLength, 'given' => $arrayLength]
                    )
                );
            }

            return $array;
        });
    }

    public function maxLength(int $maxLength): static
    {
        return $this->middleware(static function (array $array) use ($maxLength) {
            $arrayLength = \count($array);

            if ($arrayLength > $maxLength) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MAX_LENGTH_CODE,
                        self::ERROR_MAX_LENGTH_TEMPLATE,
                        ['maxLength' => $maxLength, 'given' => $arrayLength]
                    )
                );
            }

            return $array;
        });
    }

    public function contains(mixed $contains, bool $strict = true): static
    {
        return $this->middleware(static function (array $array) use ($contains, $strict) {
            if (!\in_array($contains, $array, $strict)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_CONTAINS_CODE,
                        self::ERROR_CONTAINS_TEMPLATE,
                        ['contains' => $contains, 'given' => $array]
                    )
                );
            }

            return $array;
        });
    }
}
