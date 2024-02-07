<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class ArraySchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'array.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", {{given}} given';

    public const ERROR_LENGTH_CODE = 'array.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const ERROR_MIN_LENGTH_CODE = 'array.minLength';
    public const ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const ERROR_MAX_LENGTH_CODE = 'array.maxLength';
    public const ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const ERROR_INCLUDES_CODE = 'array.includes';
    public const ERROR_INCLUDES_TEMPLATE = '{{given}} does not include {{includes}}';

    public function __construct(private SchemaInterface $itemSchema) {}

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

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

            return $this->dispatchPostParses($array);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function length(int $length): static
    {
        return $this->postParse(static function (array $array) use ($length) {
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
        return $this->postParse(static function (array $array) use ($minLength) {
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
        return $this->postParse(static function (array $array) use ($maxLength) {
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

    public function includes(mixed $includes, bool $strict = true): static
    {
        return $this->postParse(static function (array $array) use ($includes, $strict) {
            if (!\in_array($includes, $array, $strict)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_INCLUDES_CODE,
                        self::ERROR_INCLUDES_TEMPLATE,
                        ['includes' => $includes, 'given' => $array]
                    )
                );
            }

            return $array;
        });
    }

    /**
     * @param \Closure(mixed $value, int $index): mixed $filter
     */
    public function filter(\Closure $filter): static
    {
        return $this->postParse(
            static fn (array $array) => array_values(array_filter($array, $filter, ARRAY_FILTER_USE_BOTH))
        );
    }

    /**
     * @param \Closure(mixed $value): mixed $map
     */
    public function map(\Closure $map): static
    {
        return $this->postParse(static fn (array $array) => array_map($map, $array));
    }

    /**
     * @param null|\Closure(mixed $a, mixed $b): mixed $compare
     */
    public function sort(?\Closure $compare = null): static
    {
        return $this->postParse(static function (array $array) use ($compare) {
            if ($compare) {
                usort($array, $compare);
            } else {
                sort($array);
            }

            return $array;
        });
    }

    /**
     * @param \Closure(mixed $existing, mixed $current): mixed $reduce
     */
    public function reduce(\Closure $reduce, mixed $initial = null): static
    {
        return $this->postParse(static fn (array $array) => array_reduce($array, $reduce, $initial));
    }
}
