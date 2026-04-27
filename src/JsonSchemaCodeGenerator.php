<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class JsonSchemaCodeGenerator
{
    /**
     * @param array<string, mixed>|string $jsonSchema
     */
    public function generate(array|string $jsonSchema): string
    {
        if (\is_string($jsonSchema)) {
            $decoded = json_decode($jsonSchema, true);

            if (!\is_array($decoded)) {
                throw new \InvalidArgumentException('Invalid JSON string provided');
            }

            /** @var array<string, mixed> $decoded */
            $jsonSchema = $decoded;
        }

        return $this->generateSchema($jsonSchema);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateSchema(array $schema): string
    {
        $schema = $this->normalizeNullable($schema);

        if (\array_key_exists('$ref', $schema)) {
            throw new \InvalidArgumentException('$ref is not supported. Please dereference the schema first.');
        }

        foreach (['not', 'if', 'then', 'else'] as $unsupportedKeyword) {
            if (\array_key_exists($unsupportedKeyword, $schema)) {
                throw new \InvalidArgumentException(
                    \sprintf('Unsupported JSON Schema keyword: %s', $unsupportedKeyword)
                );
            }
        }

        if (\array_key_exists('const', $schema)) {
            return $this->generateConst($schema);
        }

        if (isset($schema['enum'])) {
            return $this->generateEnum($schema);
        }

        if (isset($schema['allOf']) && \is_array($schema['allOf'])) {
            /** @var array<int, array<string, mixed>> $allOf */
            $allOf = $schema['allOf'];

            return $this->generateAllOf($allOf);
        }

        if (isset($schema['oneOf']) && \is_array($schema['oneOf'])) {
            /** @var array<int, array<string, mixed>> $oneOf */
            $oneOf = $schema['oneOf'];

            return $this->generateOneOf($oneOf);
        }

        if (isset($schema['anyOf']) && \is_array($schema['anyOf'])) {
            /** @var array<int, array<string, mixed>> $anyOf */
            $anyOf = $schema['anyOf'];

            return $this->generateAnyOf($anyOf);
        }

        $type = $schema['type'] ?? null;

        if (\is_array($type)) {
            /** @var array<int, string> $type */
            return $this->generateMultiType($type, $schema);
        }

        if (null !== $type && !\is_string($type)) {
            throw new \InvalidArgumentException('Invalid type value in schema');
        }

        return match ($type) {
            'string' => $this->generateString($schema),
            'integer' => $this->generateInteger($schema),
            'number' => $this->generateNumber($schema),
            'boolean' => $this->generateBoolean($schema),
            'array' => $this->generateArray($schema),
            'object' => $this->generateObject($schema),
            'null' => $this->generateNull(),
            null => $this->generateSchemaWithoutType($schema),
            default => throw new \InvalidArgumentException(\sprintf('Unsupported JSON Schema type: %s', $type)),
        };
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<string, mixed>
     */
    private function normalizeNullable(array $schema): array
    {
        if (true !== ($schema['nullable'] ?? false)) {
            return $schema;
        }

        unset($schema['nullable']);

        if (\array_key_exists('const', $schema)) {
            if (null !== $schema['const']) {
                $schema = ['anyOf' => [$schema, ['type' => 'null']]];
            }

            return $schema;
        }

        if (isset($schema['enum']) && \is_array($schema['enum']) && !\in_array(null, $schema['enum'], true)) {
            $schema['enum'][] = null;

            return $schema;
        }

        $type = $schema['type'] ?? null;

        if (\is_string($type) && 'null' !== $type) {
            $schema['type'] = [$type, 'null'];

            return $schema;
        }

        if (\is_array($type) && !\in_array('null', $type, true)) {
            $type[] = 'null';
            $schema['type'] = $type;

            return $schema;
        }

        return ['anyOf' => [$schema, ['type' => 'null']]];
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateConst(array $schema): string
    {
        $const = $schema['const'];

        if (null === $const) {
            return $this->generateNull();
        }

        if (!\is_bool($const) && !\is_float($const) && !\is_int($const) && !\is_string($const)) {
            throw new \InvalidArgumentException('Only scalar and null const values are supported');
        }

        return '$p->const('.$this->exportValue($const).')';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateEnum(array $schema): string
    {
        $values = $schema['enum'];

        if (!\is_array($values) || [] === $values) {
            throw new \InvalidArgumentException('Enum must be a non-empty array');
        }

        foreach ($values as $value) {
            if (null !== $value && !\is_bool($value) && !\is_float($value) && !\is_int($value) && !\is_string($value)) {
                throw new \InvalidArgumentException('Only scalar and null enum values are supported');
            }
        }

        $hasNull = \in_array(null, $values, true);
        $nonNullValues = array_values(array_filter($values, static fn (mixed $value): bool => null !== $value));

        if ([] === $nonNullValues) {
            return $this->generateNull();
        }

        if (1 === \count($nonNullValues)) {
            $code = '$p->const('.$this->exportValue($nonNullValues[0]).')';

            return $hasNull ? $code.'->nullable()' : $code;
        }

        $literals = array_map(
            fn (mixed $value): string => '$p->const('.$this->exportValue($value).')',
            $nonNullValues,
        );

        $code = '$p->union(['.implode(', ', $literals).'])';

        return $hasNull ? $code.'->nullable()' : $code;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function generateOneOf(array $schemas): string
    {
        $discriminator = $this->detectDiscriminator($schemas);

        if (null !== $discriminator) {
            return $this->generateDiscriminatedUnion($schemas, $discriminator);
        }

        if (!$this->canRepresentOneOfExactly($schemas)) {
            throw new \InvalidArgumentException(
                'oneOf can only be generated when branches are provably exclusive'
            );
        }

        return $this->generateUnion($schemas);
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function generateAnyOf(array $schemas): string
    {
        return $this->generateUnion($schemas);
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function generateUnion(array $schemas): string
    {
        $hasNull = false;
        $nonNullSchemas = [];

        foreach ($schemas as $schema) {
            if ('null' === ($schema['type'] ?? null)) {
                $hasNull = true;

                continue;
            }

            $nonNullSchemas[] = $schema;
        }

        if ([] === $nonNullSchemas) {
            return $this->generateNull();
        }

        if (1 === \count($nonNullSchemas)) {
            $code = $this->generateSchema($nonNullSchemas[0]);

            return $hasNull ? $code.'->nullable()' : $code;
        }

        $subCodes = array_map(fn (array $subSchema): string => $this->generateSchema($subSchema), $nonNullSchemas);
        $code = '$p->union(['.implode(', ', $subCodes).'])';

        return $hasNull ? $code.'->nullable()' : $code;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function canRepresentOneOfExactly(array $schemas): bool
    {
        if ([] === $schemas) {
            throw new \InvalidArgumentException('oneOf must contain at least one schema');
        }

        if ($this->allLiteralSchemas($schemas)) {
            $values = array_map(fn (array $schema): string => $this->literalSignature($schema), $schemas);

            return \count($values) === \count(array_unique($values));
        }

        $types = [];

        foreach ($schemas as $schema) {
            $type = $this->exclusiveBaseType($schema);

            if (null === $type || \in_array($type, $types, true)) {
                return false;
            }

            $types[] = $type;
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function allLiteralSchemas(array $schemas): bool
    {
        foreach ($schemas as $schema) {
            if (!\array_key_exists('const', $schema)) {
                if (!isset($schema['enum']) || !\is_array($schema['enum']) || 1 !== \count($schema['enum'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function literalSignature(array $schema): string
    {
        if (\array_key_exists('const', $schema)) {
            return json_encode($schema['const'], JSON_THROW_ON_ERROR);
        }

        /** @var array<int, mixed> $enum */
        $enum = $schema['enum'];

        return json_encode($enum[0], JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function exclusiveBaseType(array $schema): ?string
    {
        if (\array_key_exists('const', $schema)) {
            return match (true) {
                null === $schema['const'] => 'null',
                \is_string($schema['const']) => 'string',
                \is_int($schema['const']) => 'integer',
                \is_float($schema['const']) => 'number',
                \is_bool($schema['const']) => 'boolean',
                default => null,
            };
        }

        if (isset($schema['enum']) && \is_array($schema['enum']) && 1 === \count($schema['enum'])) {
            return $this->exclusiveBaseType(['const' => $schema['enum'][0]]);
        }

        $type = $schema['type'] ?? null;

        if (!\is_string($type)) {
            return null;
        }

        return \in_array($type, ['string', 'integer', 'number', 'boolean', 'array', 'object', 'null'], true)
            ? $type
            : null;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function detectDiscriminator(array $schemas): ?string
    {
        if (\count($schemas) < 2) {
            return null;
        }

        $candidateCounts = [];

        foreach ($schemas as $schema) {
            if ('object' !== ($schema['type'] ?? 'object') || !isset($schema['properties']) || !\is_array($schema['properties'])) {
                return null;
            }

            /** @var array<string, array<string, mixed>> $properties */
            $properties = $schema['properties'];
            $required = isset($schema['required']) && \is_array($schema['required']) ? $schema['required'] : [];

            foreach ($required as $fieldName) {
                if (!\is_string($fieldName) || !isset($properties[$fieldName])) {
                    continue;
                }

                $fieldSchema = $properties[$fieldName];
                $isDiscriminator = \array_key_exists('const', $fieldSchema)
                    || (isset($fieldSchema['enum']) && \is_array($fieldSchema['enum']) && 1 === \count($fieldSchema['enum']));

                if (!$isDiscriminator) {
                    continue;
                }

                $candidateCounts[$fieldName] = ($candidateCounts[$fieldName] ?? 0) + 1;
            }
        }

        foreach ($candidateCounts as $fieldName => $count) {
            if ($count === \count($schemas)) {
                return (string) $fieldName;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function generateDiscriminatedUnion(array $schemas, string $discriminator): string
    {
        $objectCodes = array_map(fn (array $schema): string => $this->generateObject($schema), $schemas);

        return '$p->discriminatedUnion(['.implode(', ', $objectCodes).'], '.$this->exportValue($discriminator).')';
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     */
    private function generateAllOf(array $schemas): string
    {
        if ([] === $schemas) {
            throw new \InvalidArgumentException('allOf must contain at least one schema');
        }

        $mergedProperties = [];
        $mergedRequired = [];
        $additionalProperties = null;

        foreach ($schemas as $schema) {
            if (isset($schema['type']) && 'object' !== $schema['type']) {
                throw new \InvalidArgumentException('allOf is only supported for object schemas');
            }

            if (isset($schema['properties']) && !\is_array($schema['properties'])) {
                throw new \InvalidArgumentException('Object properties must be an associative array');
            }

            /** @var array<string, array<string, mixed>> $properties */
            $properties = $schema['properties'] ?? [];
            $mergedProperties = array_merge($mergedProperties, $properties);

            if (isset($schema['required'])) {
                if (!\is_array($schema['required'])) {
                    throw new \InvalidArgumentException('Object required must be an array of field names');
                }

                foreach ($schema['required'] as $fieldName) {
                    if (!\is_string($fieldName)) {
                        throw new \InvalidArgumentException('Object required must be an array of field names');
                    }

                    $mergedRequired[] = $fieldName;
                }
            }

            if (\array_key_exists('additionalProperties', $schema)) {
                if (null !== $additionalProperties && $additionalProperties !== $schema['additionalProperties']) {
                    throw new \InvalidArgumentException('Conflicting allOf additionalProperties definitions are not supported');
                }

                $additionalProperties = $schema['additionalProperties'];
            }
        }

        $merged = [
            'type' => 'object',
            'properties' => $mergedProperties,
            'required' => array_values(array_unique($mergedRequired)),
        ];

        if (null !== $additionalProperties) {
            $merged['additionalProperties'] = $additionalProperties;
        }

        return $this->generateObject($merged);
    }

    /**
     * @param array<int, string>   $types
     * @param array<string, mixed> $schema
     */
    private function generateMultiType(array $types, array $schema): string
    {
        $hasNull = \in_array('null', $types, true);
        $nonNullTypes = array_values(array_filter($types, static fn (string $type): bool => 'null' !== $type));

        if ([] === $nonNullTypes) {
            return $this->generateNull();
        }

        if (1 === \count($nonNullTypes)) {
            $singleSchema = $schema;
            $singleSchema['type'] = $nonNullTypes[0];

            $code = $this->generateSchema($singleSchema);

            return $hasNull ? $code.'->nullable()' : $code;
        }

        $subCodes = [];

        foreach ($nonNullTypes as $type) {
            $subSchema = $schema;
            $subSchema['type'] = $type;
            $subCodes[] = $this->generateSchema($subSchema);
        }

        $code = '$p->union(['.implode(', ', $subCodes).'])';

        return $hasNull ? $code.'->nullable()' : $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateString(array $schema): string
    {
        $code = '$p->string()';

        if (isset($schema['minLength'])) {
            $code .= '->minLength('.$this->exportInt($schema['minLength'], 'minLength').')';
        }

        if (isset($schema['maxLength'])) {
            $code .= '->maxLength('.$this->exportInt($schema['maxLength'], 'maxLength').')';
        }

        if (isset($schema['pattern'])) {
            if (!\is_string($schema['pattern'])) {
                throw new \InvalidArgumentException('String pattern must be a string');
            }

            $code .= '->pattern('.$this->exportValue($this->toPregPattern($schema['pattern'])).')';
        }

        if (isset($schema['format'])) {
            if (!\is_string($schema['format'])) {
                throw new \InvalidArgumentException('String format must be a string');
            }

            $code = $this->applyStringFormat($code, $schema['format']);
        }

        return $code;
    }

    private function applyStringFormat(string $code, string $format): string
    {
        return match ($format) {
            'email' => $code.'->email()',
            'uri', 'url' => $code.'->uri()',
            'ipv4' => $code.'->ipV4()',
            'ipv6' => $code.'->ipV6()',
            'hostname' => $code.'->hostname()',
            'date-time' => $code.'->toDateTime()',
            'uuid' => $code.'->pattern('.$this->exportValue('~^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i').')',
            default => throw new \InvalidArgumentException(\sprintf('Unsupported string format: %s', $format)),
        };
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateInteger(array $schema): string
    {
        return $this->applyNumericConstraints('$p->int()', $schema, true);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateNumber(array $schema): string
    {
        return $this->applyNumericConstraints('$p->float()', $schema, false);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function applyNumericConstraints(string $code, array $schema, bool $isInt): string
    {
        if (isset($schema['minimum'])) {
            $code .= $isInt
                ? '->minimum('.$this->exportInt($schema['minimum'], 'minimum').')'
                : '->minimum('.$this->exportFloat($schema['minimum'], 'minimum').')';
        }

        if (isset($schema['maximum'])) {
            $code .= $isInt
                ? '->maximum('.$this->exportInt($schema['maximum'], 'maximum').')'
                : '->maximum('.$this->exportFloat($schema['maximum'], 'maximum').')';
        }

        if (isset($schema['exclusiveMinimum'])) {
            $code .= $isInt
                ? '->exclusiveMinimum('.$this->exportInt($schema['exclusiveMinimum'], 'exclusiveMinimum').')'
                : '->exclusiveMinimum('.$this->exportFloat($schema['exclusiveMinimum'], 'exclusiveMinimum').')';
        }

        if (isset($schema['exclusiveMaximum'])) {
            $code .= $isInt
                ? '->exclusiveMaximum('.$this->exportInt($schema['exclusiveMaximum'], 'exclusiveMaximum').')'
                : '->exclusiveMaximum('.$this->exportFloat($schema['exclusiveMaximum'], 'exclusiveMaximum').')';
        }

        if (isset($schema['multipleOf'])) {
            $code .= $isInt
                ? '->multipleOf('.$this->exportInt($schema['multipleOf'], 'multipleOf').')'
                : '->multipleOf('.$this->exportFloat($schema['multipleOf'], 'multipleOf').')';
        }

        return $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateBoolean(array $schema): string
    {
        return '$p->bool()';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateArray(array $schema): string
    {
        if (isset($schema['contains']) || isset($schema['minContains']) || isset($schema['maxContains'])) {
            throw new \InvalidArgumentException('Array contains constraints are not supported');
        }

        if (isset($schema['prefixItems']) || (isset($schema['items']) && \is_array($schema['items']) && array_is_list($schema['items']))) {
            return $this->generateTuple($schema);
        }

        if (false === ($schema['items'] ?? true)) {
            $minItems = isset($schema['minItems']) ? $this->intValue($schema['minItems'], 'minItems') : 0;
            $maxItems = isset($schema['maxItems']) ? $this->intValue($schema['maxItems'], 'maxItems') : 0;

            if (0 !== $minItems || 0 !== $maxItems) {
                throw new \InvalidArgumentException('items=false can only be generated for an empty array schema');
            }

            return '$p->tuple([])';
        }

        if (!isset($schema['items']) || true === $schema['items']) {
            throw new \InvalidArgumentException('Array items must be defined to generate exact validation code');
        }

        if (!\is_array($schema['items']) || [] === $schema['items']) {
            throw new \InvalidArgumentException('Array items must be a schema object');
        }

        /** @var array<string, mixed> $itemSchema */
        $itemSchema = $schema['items'];
        $code = '$p->array('.$this->generateSchema($itemSchema).')';

        $minItems = isset($schema['minItems']) ? $this->intValue($schema['minItems'], 'minItems') : null;
        $maxItems = isset($schema['maxItems']) ? $this->intValue($schema['maxItems'], 'maxItems') : null;

        if (null !== $minItems && null !== $maxItems && $minItems === $maxItems) {
            $code .= '->exactItems('.$this->exportInt($minItems, 'minItems').')';
        } else {
            if (null !== $minItems) {
                $code .= '->minItems('.$this->exportInt($minItems, 'minItems').')';
            }

            if (null !== $maxItems) {
                $code .= '->maxItems('.$this->exportInt($maxItems, 'maxItems').')';
            }
        }

        if (true === ($schema['uniqueItems'] ?? false)) {
            $code .= '->uniqueItems()';
        }

        return $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateTuple(array $schema): string
    {
        $items = [];

        if (isset($schema['prefixItems'])) {
            if (!\is_array($schema['prefixItems'])) {
                throw new \InvalidArgumentException('prefixItems must be an array');
            }

            /** @var array<int, array<string, mixed>> $items */
            $items = $schema['prefixItems'];
        } elseif (isset($schema['items']) && \is_array($schema['items']) && array_is_list($schema['items'])) {
            /** @var array<int, array<string, mixed>> $items */
            $items = $schema['items'];
        }

        $itemCount = \count($items);
        $minItems = isset($schema['minItems']) ? $this->exportInt($schema['minItems'], 'minItems') : null;
        $maxItems = isset($schema['maxItems']) ? $this->exportInt($schema['maxItems'], 'maxItems') : null;
        $additionalItemsAllowed = !isset($schema['items']) || false !== $schema['items'];

        $isExactTuple = $itemCount === (int) $minItems
            && ((null !== $maxItems && $itemCount === (int) $maxItems) || !$additionalItemsAllowed);

        if (!$isExactTuple) {
            throw new \InvalidArgumentException(
                'Tuple-like schemas can only be generated when they require exactly the declared items'
            );
        }

        $itemCodes = array_map(fn (array $item): string => $this->generateSchema($item), $items);

        return '$p->tuple(['.implode(', ', $itemCodes).'])';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateObject(array $schema): string
    {
        if (isset($schema['patternProperties']) || isset($schema['propertyNames'])) {
            throw new \InvalidArgumentException('patternProperties and propertyNames are not supported');
        }

        if (isset($schema['properties']) && !\is_array($schema['properties'])) {
            throw new \InvalidArgumentException('Object properties must be an associative array');
        }

        /** @var array<string, array<string, mixed>> $properties */
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        if (!\is_array($required)) {
            throw new \InvalidArgumentException('Object required must be an array of field names');
        }

        foreach ($required as $fieldName) {
            if (!\is_string($fieldName)) {
                throw new \InvalidArgumentException('Object required must be an array of field names');
            }

            if (!isset($properties[$fieldName])) {
                throw new \InvalidArgumentException(
                    \sprintf('Required field "%s" must be defined in properties', $fieldName)
                );
            }
        }

        $additionalProperties = $schema['additionalProperties'] ?? true;

        if (\is_array($additionalProperties) && [] !== $additionalProperties && [] !== $properties) {
            throw new \InvalidArgumentException(
                'additionalProperties schemas cannot be combined with fixed properties'
            );
        }

        if ([] === $properties && \is_array($additionalProperties) && [] !== $additionalProperties) {
            /** @var array<string, mixed> $additionalProperties */
            return '$p->record('.$this->generateSchema($additionalProperties).')';
        }

        $fieldCodes = [];
        $optionalFields = [];

        foreach ($properties as $fieldName => $fieldSchema) {
            $fieldCodes[] = $this->exportValue($fieldName).' => '.$this->generateSchema($fieldSchema);

            if (!\in_array($fieldName, $required, true)) {
                $optionalFields[] = $fieldName;
            }
        }

        $code = '$p->object(['.implode(', ', $fieldCodes).'])';

        if ([] !== $optionalFields) {
            $code .= '->optional(['.implode(', ', array_map($this->exportValue(...), $optionalFields)).'])';
        }

        if (false === $additionalProperties) {
            $code .= '->strict()';
        }

        return $code;
    }

    private function generateNull(): string
    {
        return '$p->union([])->nullable()';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateSchemaWithoutType(array $schema): string
    {
        if (isset($schema['properties']) || isset($schema['required']) || \array_key_exists('additionalProperties', $schema)) {
            return $this->generateObject($schema);
        }

        throw new \InvalidArgumentException('Schemas without a type are only supported for object-like schemas');
    }

    private function toPregPattern(string $pattern): string
    {
        return '~'.str_replace('~', '\~', $pattern).'~';
    }

    private function exportInt(mixed $value, string $key): string
    {
        return (string) $this->intValue($value, $key);
    }

    private function intValue(mixed $value, string $key): int
    {
        if (!\is_int($value) && !\is_float($value)) {
            throw new \InvalidArgumentException(\sprintf('%s must be numeric', $key));
        }

        return (int) $value;
    }

    private function exportFloat(mixed $value, string $key): string
    {
        if (!\is_int($value) && !\is_float($value)) {
            throw new \InvalidArgumentException(\sprintf('%s must be numeric', $key));
        }

        return $this->exportValue((float) $value);
    }

    private function exportValue(mixed $value): string
    {
        if (\is_string($value)) {
            return "'".addcslashes($value, "'\\")."'";
        }

        if (\is_int($value)) {
            return (string) $value;
        }

        if (\is_float($value)) {
            $string = (string) $value;

            if (!str_contains($string, '.') && !str_contains($string, 'E') && !str_contains($string, 'e')) {
                $string .= '.0';
            }

            return $string;
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (null === $value) {
            return 'null';
        }

        return var_export($value, true);
    }
}
