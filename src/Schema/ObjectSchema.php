<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ObjectSchema extends AbstractSchema implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'object.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public const string ERROR_UNKNOWN_FIELD_CODE = 'object.unknownField';
    public const string ERROR_UNKNOWN_FIELD_TEMPLATE = 'Unknown field {{fieldName}}';

    /**
     * @var array<string, SchemaInterface>
     */
    private array $fieldToSchema = [];

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
     * @param class-string        $classname
     */
    public function __construct(array $fieldToSchema, private string $classname = \stdClass::class)
    {
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

            $this->fieldToSchema[$fieldName] = $fieldSchema;
        }
    }

    public function parse(mixed $input): mixed
    {
        if ($input instanceof \stdClass || $input instanceof \Traversable) {
            $input = (array) $input;
        }

        if ($input instanceof \JsonSerializable) {
            $input = $input->jsonSerialize();
        }

        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_array($input)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
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

            return $this->dispatchPostParses($output);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }

    /**
     * @return array<string, SchemaInterface>
     */
    public function getFieldToSchema(): array
    {
        return $this->fieldToSchema;
    }

    public function getFieldSchema(string $field): ?SchemaInterface
    {
        return $this->fieldToSchema[$field] ?? null;
    }

    /**
     * @param array<string> $optional
     */
    public function optional(array $optional = []): static
    {
        $clone = clone $this;
        $clone->optional = $optional;

        return $clone;
    }

    /**
     * @param array<string> $strict
     */
    public function strict(array $strict = []): static
    {
        $clone = clone $this;
        $clone->strict = $strict;

        return $clone;
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
                    self::ERROR_UNKNOWN_FIELD_CODE,
                    self::ERROR_UNKNOWN_FIELD_TEMPLATE,
                    ['fieldName' => $fieldName]
                ), $fieldName);
            }
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function parseFields(array $input, Errors $childrenErrors): object
    {
        $object = new $this->classname();

        foreach ($this->fieldToSchema as $fieldName => $fieldSchema) {
            try {
                if (
                    !\array_key_exists($fieldName, $input)
                    && \is_array($this->optional)
                    && \in_array($fieldName, $this->optional, true)
                ) {
                    continue;
                }

                $object->{$fieldName} = $fieldSchema->parse($input[$fieldName] ?? null);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        return $object;
    }
}
