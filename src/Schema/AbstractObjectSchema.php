<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

abstract class AbstractObjectSchema extends AbstractSchemaInnerParse implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'abstract_object.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public const string ERROR_UNKNOWN_FIELD_CODE = 'abstract_object.unknownField';
    public const string ERROR_UNKNOWN_FIELD_TEMPLATE = 'Unknown field {{fieldName}}';

    /**
     * @var array<string, SchemaInterface>
     */
    private readonly array $fieldToSchema;

    /**
     * @var null|array<string>
     */
    private ?array $strict = null;

    /**
     * @var null|array<string>
     */
    private ?array $optional = null;

    /**
     * @param array<mixed, mixed> $fieldToSchema
     */
    public function __construct(array $fieldToSchema)
    {
        $typeCheckedFieldToSchema = [];

        foreach ($fieldToSchema as $fieldName => $fieldSchema) {
            if (!\is_string($fieldName)) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Argument #1 name #%s ($fieldToSchema) must be of type string, %s given',
                        $fieldName,
                        $this->getDataType($fieldName)
                    )
                );
            }

            if (!$fieldSchema instanceof SchemaInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Argument #1 value of #%s ($fieldToSchema) must be of type %s, %s given',
                        $fieldName,
                        SchemaInterface::class,
                        $this->getDataType($fieldSchema)
                    )
                );
            }

            $typeCheckedFieldToSchema[$fieldName] = $fieldSchema;
        }

        $this->fieldToSchema = $typeCheckedFieldToSchema;

        $this->preParses[] = static function (mixed $input) {
            if ($input instanceof \stdClass || $input instanceof \Traversable) {
                return (array) $input;
            }

            if ($input instanceof \JsonSerializable) {
                return $input->jsonSerialize();
            }

            return $input;
        };
    }

    /**
     * @return array<string, SchemaInterface>
     */
    final public function getFieldToSchema(): array
    {
        return $this->fieldToSchema;
    }

    final public function getFieldSchema(string $field): ?SchemaInterface
    {
        return $this->fieldToSchema[$field] ?? null;
    }

    final public function strict(array $strict = []): static
    {
        $clone = clone $this;
        $clone->strict = $strict;

        return $clone;
    }

    /**
     * @param array<string> $optional
     */
    final public function optional(array $optional = []): static
    {
        $clone = clone $this;
        $clone->optional = $optional;

        return $clone;
    }

    protected function innerParse(mixed $input): mixed
    {
        if (!\is_array($input)) {
            throw new ErrorsException(
                new Error(
                    static::ERROR_TYPE_CODE,
                    static::ERROR_TYPE_TEMPLATE,
                    ['given' => $this->getDataType($input)]
                )
            );
        }

        /** @var array<string, mixed> $input */
        $childrenErrors = new Errors();

        $this->unknownFields($input, $childrenErrors);

        $output = $this->parseFields($input, $childrenErrors);

        if ($childrenErrors->has()) {
            throw new ErrorsException($childrenErrors);
        }

        return $output;
    }

    /**
     * @param array<string, mixed> $input
     */
    abstract protected function parseFields(array $input, Errors $childrenErrors): mixed;

    /**
     * @param array<string, mixed> $input
     */
    final protected function skip(array $input, string $fieldName): bool
    {
        return !\array_key_exists($fieldName, $input)
            && \is_array($this->optional)
            && \in_array($fieldName, $this->optional, true);
    }

    /**
     * @param array<string, mixed> $input
     */
    private function unknownFields(array $input, Errors $childrenErrors): void
    {
        if (null === $this->strict) {
            return;
        }

        foreach (array_keys($input) as $fieldName) {
            if (!\in_array($fieldName, $this->strict, true) && !isset($this->fieldToSchema[$fieldName])) {
                $childrenErrors->add(new Error(
                    static::ERROR_UNKNOWN_FIELD_CODE,
                    static::ERROR_UNKNOWN_FIELD_TEMPLATE,
                    ['fieldName' => $fieldName]
                ), $fieldName);
            }
        }
    }
}
