<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ArraySchema extends AbstractSchemaV2 implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'array.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array", {{given}} given';

    public const string ERROR_LENGTH_CODE = 'array.length';
    public const string ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const string ERROR_MIN_LENGTH_CODE = 'array.minLength';
    public const string ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const string ERROR_MAX_LENGTH_CODE = 'array.maxLength';
    public const string ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const string ERROR_INCLUDES_CODE = 'array.includes';
    public const string ERROR_INCLUDES_TEMPLATE = '{{given}} does not include {{includes}}';

    public function __construct(private SchemaInterface $itemSchema) {}

    public function length(int $length): static
    {
        return $this->postParse(static function (array $array) use ($length) {
            $arrayLength = \count($array);

            if ($arrayLength !== $length) {
                throw new ErrorsException(
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
                throw new ErrorsException(
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
                throw new ErrorsException(
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
                throw new ErrorsException(
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
     * @param \Closure(mixed $value, int $index): bool $filter
     */
    public function filter(\Closure $filter): static
    {
        return $this->postParse(
            static fn (array $array): array => array_values(array_filter($array, $filter, ARRAY_FILTER_USE_BOTH))
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
     * @param null|\Closure(mixed $a, mixed $b): int $compare
     */
    public function sort(?\Closure $compare = null): static
    {
        return $this->postParse(static function (array $array) use ($compare): array {
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

    protected function innerParse(mixed $input): mixed
    {
        if (!\is_array($input)) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_TYPE_CODE,
                    self::ERROR_TYPE_TEMPLATE,
                    ['given' => $this->getDataType($input)]
                )
            );
        }

        $output = [];

        $childrenErrors = new Errors();

        foreach ($input as $i => $item) {
            try {
                $output[$i] = $this->itemSchema->parse($item);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, (string) $i);
            }
        }

        if ($childrenErrors->has()) {
            throw new ErrorsException($childrenErrors);
        }

        return $output;
    }
}
