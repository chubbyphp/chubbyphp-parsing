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

    public const string ERROR_MIN_CONTAINS_CODE = 'array.minContains';
    public const string ERROR_MIN_CONTAINS_TEMPLATE = '{{given}} contains {{contains}} {{containsCount}} times, min {{minContains}} required';

    public const string ERROR_MAX_CONTAINS_CODE = 'array.maxContains';
    public const string ERROR_MAX_CONTAINS_TEMPLATE = '{{given}} contains {{contains}} {{containsCount}} times, max {{maxContains}} allowed';

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

    /**
     * The given $contains can be a literal value or a schema. If it is a schema (json schema
     * spec), at least one item has to be valid against it, otherwise at least one item has to
     * be equal to the literal value ($strict defines whether the comparison is strict or not).
     */
    public function contains(mixed $contains, bool $strict = true): static
    {
        return $this->postParse(static function (array $array) use ($contains, $strict) {
            if (self::containsCount($array, $contains, $strict) > 0) {
                return $array;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_CONTAINS_CODE,
                    self::ERROR_CONTAINS_TEMPLATE,
                    ['contains' => self::containsVariable($contains), 'given' => $array]
                )
            );
        });
    }

    /**
     * The given $contains can be a literal value or a schema. If it is a schema (json schema
     * spec), at least $minContains items have to be valid against it, otherwise at least
     * $minContains items have to be equal to the literal value ($strict defines whether the
     * comparison is strict or not).
     */
    public function minContains(mixed $contains, int $minContains, bool $strict = true): static
    {
        return $this->postParse(static function (array $array) use ($contains, $minContains, $strict) {
            $containsCount = self::containsCount($array, $contains, $strict);

            if ($containsCount < $minContains) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_MIN_CONTAINS_CODE,
                        self::ERROR_MIN_CONTAINS_TEMPLATE,
                        [
                            'contains' => self::containsVariable($contains),
                            'containsCount' => $containsCount,
                            'given' => $array,
                            'minContains' => $minContains,
                        ]
                    )
                );
            }

            return $array;
        });
    }

    /**
     * The given $contains can be a literal value or a schema. If it is a schema (json schema
     * spec), at most $maxContains items have to be valid against it, otherwise at most
     * $maxContains items have to be equal to the literal value ($strict defines whether the
     * comparison is strict or not).
     */
    public function maxContains(mixed $contains, int $maxContains, bool $strict = true): static
    {
        return $this->postParse(static function (array $array) use ($contains, $maxContains, $strict) {
            $containsCount = self::containsCount($array, $contains, $strict);

            if ($containsCount > $maxContains) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_MAX_CONTAINS_CODE,
                        self::ERROR_MAX_CONTAINS_TEMPLATE,
                        [
                            'contains' => self::containsVariable($contains),
                            'containsCount' => $containsCount,
                            'given' => $array,
                            'maxContains' => $maxContains,
                        ]
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

    /**
     * Uniqueness is based on json (schema spec) equality: numbers with the same mathematical
     * value (1 and 1.0) are equal, while 1, "1" and true are not. Lists are equal if the items
     * at each position are equal, objects (associative arrays) if they have the same property
     * names with equal values, independent of the property order.
     */
    public function uniqueItems(): static
    {
        return $this->postParse(static function (array $array) {
            $duplicateKeys = [];

            $seenHashes = [];

            foreach ($array as $key => $item) {
                $hash = self::jsonHash($item);

                if (isset($seenHashes[$hash])) {
                    $duplicateKeys[] = $key;

                    continue;
                }

                $seenHashes[$hash] = $hash;
            }

            if ([] === $duplicateKeys) {
                return $array;
            }

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

    /**
     * If $contains is a schema, the items valid against it are counted (json schema spec),
     * otherwise the items equal to the literal value.
     *
     * @param array<mixed> $array
     */
    private static function containsCount(array $array, mixed $contains, bool $strict): int
    {
        $containsCount = 0;

        foreach ($array as $item) {
            if (self::itemMatches($item, $contains, $strict)) {
                ++$containsCount;
            }
        }

        return $containsCount;
    }

    private static function itemMatches(mixed $item, mixed $contains, bool $strict): bool
    {
        if ($contains instanceof SchemaInterface) {
            return $contains->safeParse($item)->success;
        }

        return $strict ? $item === $contains : $item == $contains;
    }

    private static function containsVariable(mixed $contains): mixed
    {
        return $contains instanceof SchemaInterface ? $contains::class : $contains;
    }

    /**
     * Creates a canonical hash based on json (schema spec) equality.
     */
    private static function jsonHash(mixed $value): string
    {
        return serialize(self::normalizeJson($value));
    }

    /**
     * Normalizes a value so that json (schema spec) equal values share the same
     * representation: integral floats within the json safe integer range (2 ** 53, same as
     * Number.MAX_SAFE_INTEGER) are equal to their integer counterpart (1.0 equals 1),
     * objects (associative arrays) are sorted by their property names, as the property
     * order does not matter.
     */
    private static function normalizeJson(mixed $value): mixed
    {
        if (\is_float($value) && abs($value) <= 2 ** 53 && 0.0 === fmod($value, 1.0)) {
            return (int) $value;
        }

        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (\is_array($value)) {
            ksort($value);

            return array_map(self::normalizeJson(...), $value);
        }

        return $value;
    }
}
