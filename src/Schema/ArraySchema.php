<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ArraySchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'array.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array", {{given}} given';

    public const string ERROR_EXACT_ITEMS_CODE = 'array.exactItems';
    public const string ERROR_EXACT_ITEMS_TEMPLATE = 'Items count {{exactItems}}, {{given}} given';

    public const string ERROR_MIN_ITEMS_CODE = 'array.minItems';
    public const string ERROR_MIN_ITEMS_TEMPLATE = 'Min items {{minItems}}, {{given}} given';

    public const string ERROR_MAX_ITEMS_CODE = 'array.maxItems';
    public const string ERROR_MAX_ITEMS_TEMPLATE = 'Max items {{maxItems}}, {{given}} given';

    /** @deprecated: see ERROR_EXACT_ITEMS_CODE */
    public const string ERROR_LENGTH_CODE = 'array.length';

    /** @deprecated: see ERROR_EXACT_ITEMS_TEMPLATE */
    public const string ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    /** @deprecated: see ERROR_MIN_ITEMS_CODE */
    public const string ERROR_MIN_LENGTH_CODE = 'array.minLength';

    /** @deprecated: see ERROR_MIN_ITEMS_TEMPLATE */
    public const string ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    /** @deprecated: see ERROR_MAX_ITEMS_CODE */
    public const string ERROR_MAX_LENGTH_CODE = 'array.maxLength';

    /** @deprecated: see ERROR_MAX_ITEMS_TEMPLATE */
    public const string ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const string ERROR_CONTAINS_CODE = 'array.contains';
    public const string ERROR_CONTAINS_TEMPLATE = '{{given}} does not contain {{contains}}';

    /** @deprecated: see ERROR_CONTAINS_CODE */
    public const string ERROR_INCLUDES_CODE = 'array.includes';

    /** @deprecated: see ERROR_CONTAINS_TEMPLATE */
    public const string ERROR_INCLUDES_TEMPLATE = '{{given}} does not include {{includes}}';

    public const string ERROR_UNIQUE_ITEMS_CODE = 'array.uniqueItems';
    public const string ERROR_UNIQUE_ITEMS_TEMPLATE = 'Duplicate keys {{duplicateKeys}}, {{given}} given';

    public function __construct(private SchemaInterface $itemSchema) {}

    public function exactItems(int $exactItems): static
    {
        return $this->postParse(static function (array $array) use ($exactItems) {
            $arrayLength = \count($array);

            if ($arrayLength === $exactItems) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EXACT_ITEMS_CODE,
                    self::ERROR_EXACT_ITEMS_TEMPLATE,
                    ['exactItems' => $exactItems, 'given' => $arrayLength]
                )
            );
        });
    }

    public function minItems(int $minItems): static
    {
        return $this->postParse(static function (array $array) use ($minItems) {
            $arrayLength = \count($array);

            if ($arrayLength >= $minItems) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MIN_ITEMS_CODE,
                    self::ERROR_MIN_ITEMS_TEMPLATE,
                    ['minItems' => $minItems, 'given' => $arrayLength]
                )
            );
        });
    }

    public function maxItems(int $maxItems): static
    {
        return $this->postParse(static function (array $array) use ($maxItems) {
            $arrayLength = \count($array);

            if ($arrayLength <= $maxItems) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAX_ITEMS_CODE,
                    self::ERROR_MAX_ITEMS_TEMPLATE,
                    ['maxItems' => $maxItems, 'given' => $arrayLength]
                )
            );
        });
    }

    /**
     * @deprecated Use exactItems($exactItems) instead
     */
    public function length(int $length): static
    {
        @trigger_error('Use exactItems($exactItems) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (array $array) use ($length) {
            $arrayLength = \count($array);

            if ($arrayLength === $length) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LENGTH_CODE,
                    self::ERROR_LENGTH_TEMPLATE,
                    ['length' => $length, 'given' => $arrayLength]
                )
            );
        });
    }

    /**
     * @deprecated Use minItems($minItems) instead
     */
    public function minLength(int $minLength): static
    {
        @trigger_error('Use minItems($minItems) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (array $array) use ($minLength) {
            $arrayLength = \count($array);

            if ($arrayLength >= $minLength) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MIN_LENGTH_CODE,
                    self::ERROR_MIN_LENGTH_TEMPLATE,
                    ['minLength' => $minLength, 'given' => $arrayLength]
                )
            );
        });
    }

    /**
     * @deprecated Use maxItems($maxItems) instead
     */
    public function maxLength(int $maxLength): static
    {
        @trigger_error('Use maxItems($maxItems) instead', E_USER_DEPRECATED);

        return $this->postParse(static function (array $array) use ($maxLength) {
            $arrayLength = \count($array);

            if ($arrayLength <= $maxLength) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAX_LENGTH_CODE,
                    self::ERROR_MAX_LENGTH_TEMPLATE,
                    ['maxLength' => $maxLength, 'given' => $arrayLength]
                )
            );
        });
    }

    public function contains(mixed $contains, bool $strict = true): static
    {
        return $this->postParse(static function (array $array) use ($contains, $strict) {
            if (!\in_array($contains, $array, $strict)) {
                throw new ErrorsException(
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

    /**
     * @deprecated use contains($contains, $strict)
     */
    public function includes(mixed $includes, bool $strict = true): static
    {
        @trigger_error('Use contains($contains, $strict) instead', E_USER_DEPRECATED);

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

    public function uniqueItems(): static
    {
        return $this->postParse(static function (array $array) {
            $uniqueArray = array_unique($array);

            if (\count($uniqueArray) === \count($array)) {
                return $array;
            }

            $duplicateKeys = array_values(
                array_diff(
                    array_keys($array),
                    array_keys($uniqueArray)
                )
            );

            throw new ErrorsException(
                new Error(
                    self::ERROR_UNIQUE_ITEMS_CODE,
                    self::ERROR_UNIQUE_ITEMS_TEMPLATE,
                    ['duplicateKeys' => $duplicateKeys, 'given' => $array]
                )
            );
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
