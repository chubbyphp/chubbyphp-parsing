<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class JsonSchemaCodeGenerator
{
    /**
     * Generate PHP code from a JSON Schema definition.
     *
     * @param array<string, mixed>|string $jsonSchema JSON Schema as an associative array or JSON string
     *
     * @throws \InvalidArgumentException if the schema is invalid
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
        if (isset($schema['$ref'])) {
            throw new \InvalidArgumentException('$ref is not supported. Please dereference the schema first.');
        }

        if (isset($schema['const'])) {
            return $this->generateConst($schema);
        }

        if (isset($schema['enum'])) {
            return $this->generateEnum($schema);
        }

        // Handle oneOf / anyOf as union
        if (isset($schema['oneOf']) && \is_array($schema['oneOf'])) {
            /** @var array<int, array<string, mixed>> $oneOf */
            $oneOf = $schema['oneOf'];

            return $this->generateUnion($oneOf, $schema);
        }

        if (isset($schema['anyOf']) && \is_array($schema['anyOf'])) {
            /** @var array<int, array<string, mixed>> $anyOf */
            $anyOf = $schema['anyOf'];

            return $this->generateUnion($anyOf, $schema);
        }

        // Handle allOf by merging into a single object schema
        if (isset($schema['allOf']) && \is_array($schema['allOf'])) {
            /** @var array<int, array<string, mixed>> $allOf */
            $allOf = $schema['allOf'];

            return $this->generateAllOf($allOf, $schema);
        }

        $type = $schema['type'] ?? null;

        // Handle type as array (e.g., ["string", "null"])
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
            'null' => $this->generateNull($schema),
            null => $this->generateFallback($schema),
            default => throw new \InvalidArgumentException(\sprintf('Unsupported JSON Schema type: %s', $type)),
        };
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateConst(array $schema): string
    {
        return '$p->const('.$this->exportValue($schema['const']).')';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateEnum(array $schema): string
    {
        $values = $schema['enum'];

        if (!\is_array($values) || 0 === \count($values)) {
            throw new \InvalidArgumentException('Enum must be a non-empty array');
        }

        $hasNull = \in_array(null, $values, true);

        /** @var array<int, mixed> $nonNullValues */
        $nonNullValues = array_values(array_filter($values, static fn (mixed $v): bool => null !== $v));

        if (0 === \count($nonNullValues)) {
            throw new \InvalidArgumentException('Enum must have at least one non null value');
        }

        if (1 === \count($nonNullValues)) {
            $code = '$p->const('.$this->exportValue($nonNullValues[0]).')';

            if ($hasNull) {
                $code .= '->nullable()';
            }

            return $code;
        }

        // Multiple values: union of literals
        $literals = array_map(
            fn (mixed $v): string => '$p->const('.$this->exportValue($v).')',
            $nonNullValues,
        );

        $code = '$p->union(['.implode(', ', $literals).'])';

        if ($hasNull) {
            $code .= '->nullable()';
        }

        return $code;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     * @param array<string, mixed>             $parentSchema
     */
    private function generateUnion(array $schemas, array $parentSchema): string
    {
        // Check if this is a discriminated union (all objects with a common discriminator field)
        $discriminator = $this->detectDiscriminator($schemas);

        if (null !== $discriminator) {
            return $this->generateDiscriminatedUnion($schemas, $discriminator, $parentSchema);
        }

        // Filter out null type schemas
        $hasNull = false;

        /** @var array<int, array<string, mixed>> $nonNullSchemas */
        $nonNullSchemas = [];

        foreach ($schemas as $subSchema) {
            if (isset($subSchema['type']) && 'null' === $subSchema['type']) {
                $hasNull = true;
            } else {
                $nonNullSchemas[] = $subSchema;
            }
        }

        if (1 === \count($nonNullSchemas)) {
            $code = $this->generateSchema($nonNullSchemas[0]);

            if ($hasNull) {
                $code .= '->nullable()';
            }

            return $code;
        }

        $subCodes = array_map(fn (array $s): string => $this->generateSchema($s), $nonNullSchemas);

        $code = '$p->union(['.implode(', ', $subCodes).'])';

        if ($hasNull) {
            $code .= '->nullable()';
        }

        return $code;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     *
     * @return null|string the discriminator field name, or null
     */
    private function detectDiscriminator(array $schemas): ?string
    {
        if (\count($schemas) < 2) {
            return null;
        }

        // All schemas must be objects
        foreach ($schemas as $schema) {
            $type = $schema['type'] ?? null;

            if ('object' !== $type) {
                return null;
            }
        }

        // Find common required fields with const or enum of one value
        /** @var array<string, int> $candidates */
        $candidates = [];

        foreach ($schemas as $schema) {
            /** @var array<string, array<string, mixed>> $properties */
            $properties = isset($schema['properties']) && \is_array($schema['properties'])
                ? $schema['properties']
                : [];

            /** @var array<int, string> $required */
            $required = isset($schema['required']) && \is_array($schema['required'])
                ? $schema['required']
                : [];

            foreach ($required as $fieldName) {
                if (!isset($properties[$fieldName])) {
                    continue;
                }

                /** @var array<string, mixed> $fieldSchema */
                $fieldSchema = $properties[$fieldName];
                $isDiscriminator = false;

                if (\array_key_exists('const', $fieldSchema)) {
                    $isDiscriminator = true;
                } elseif (isset($fieldSchema['enum']) && \is_array($fieldSchema['enum']) && 1 === \count($fieldSchema['enum'])) {
                    $isDiscriminator = true;
                }

                if ($isDiscriminator) {
                    if (!isset($candidates[$fieldName])) {
                        $candidates[$fieldName] = 0;
                    }

                    ++$candidates[$fieldName];
                }
            }
        }

        // The discriminator field must appear in ALL schemas
        foreach ($candidates as $fieldName => $count) {
            if ($count === \count($schemas)) {
                return (string) $fieldName;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     * @param array<string, mixed>             $parentSchema
     */
    private function generateDiscriminatedUnion(array $schemas, string $discriminator, array $parentSchema): string
    {
        $objectCodes = array_map(fn (array $s): string => $this->generateObject($s), $schemas);

        return '$p->discriminatedUnion(['.implode(', ', $objectCodes).'], '
            .$this->exportValue($discriminator).')';
    }

    /**
     * @param array<int, array<string, mixed>> $schemas
     * @param array<string, mixed>             $parentSchema
     */
    private function generateAllOf(array $schemas, array $parentSchema): string
    {
        // Merge all schemas into one object schema
        /** @var array<string, array<string, mixed>> $mergedProperties */
        $mergedProperties = [];

        /** @var array<int, string> $mergedRequired */
        $mergedRequired = [];

        foreach ($schemas as $subSchema) {
            if (isset($subSchema['properties']) && \is_array($subSchema['properties'])) {
                /** @var array<string, array<string, mixed>> $props */
                $props = $subSchema['properties'];
                $mergedProperties = array_merge($mergedProperties, $props);
            }

            if (isset($subSchema['required']) && \is_array($subSchema['required'])) {
                /** @var array<int, string> $req */
                $req = $subSchema['required'];
                $mergedRequired = array_merge($mergedRequired, $req);
            }
        }

        /** @var array<string, mixed> $merged */
        $merged = [
            'type' => 'object',
            'properties' => $mergedProperties,
            'required' => array_values(array_unique($mergedRequired)),
        ];

        // Carry over additionalProperties if set on parent
        if (\array_key_exists('additionalProperties', $parentSchema)) {
            $merged['additionalProperties'] = $parentSchema['additionalProperties'];
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
        $nonNullTypes = array_values(array_filter($types, static fn (string $t): bool => 'null' !== $t));

        if (1 === \count($nonNullTypes)) {
            $singleSchema = $schema;
            $singleSchema['type'] = $nonNullTypes[0];
            $code = $this->generateSchema($singleSchema);

            if ($hasNull) {
                $code .= '->nullable()';
            }

            return $code;
        }

        // Multiple non-null types: union
        $subCodes = [];

        foreach ($nonNullTypes as $t) {
            $subSchema = $schema;
            $subSchema['type'] = $t;
            $subCodes[] = $this->generateSchema($subSchema);
        }

        $code = '$p->union(['.implode(', ', $subCodes).'])';

        if ($hasNull) {
            $code .= '->nullable()';
        }

        return $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateString(array $schema): string
    {
        $code = '$p->string()';

        if (isset($schema['minLength']) && (\is_int($schema['minLength']) || \is_float($schema['minLength']))) {
            $code .= '->minLength('.(int) $schema['minLength'].')';
        }

        if (isset($schema['maxLength']) && (\is_int($schema['maxLength']) || \is_float($schema['maxLength']))) {
            $code .= '->maxLength('.(int) $schema['maxLength'].')';
        }

        if (isset($schema['pattern']) && \is_string($schema['pattern'])) {
            $code .= '->pattern('.$this->exportValue('/'.$schema['pattern'].'/').')';
        }

        if (isset($schema['format']) && \is_string($schema['format'])) {
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
            'uuid' => $code.'->uuid()',
            'hostname' => $code.'->hostname()',
            'date-time' => $code.'->toDateTime()',
            default => $code.' /* unsupported format: '.$format.' */',
        };
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateInteger(array $schema): string
    {
        $code = '$p->int()';

        return $this->applyNumericConstraints($code, $schema, true);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateNumber(array $schema): string
    {
        $code = '$p->float()';

        return $this->applyNumericConstraints($code, $schema, false);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function applyNumericConstraints(string $code, array $schema, bool $isInt): string
    {
        if (isset($schema['minimum']) && (\is_int($schema['minimum']) || \is_float($schema['minimum']))) {
            $val = $isInt ? (int) $schema['minimum'] : (float) $schema['minimum'];
            if (isset($schema['exclusiveMinimum']) && true === $schema['exclusiveMinimum']) {
                $code .= '->exclusiveMinimum('.$this->exportValue($val).')';
            } else {
                $code .= '->minimum('.$this->exportValue($val).')';
            }
        } elseif (isset($schema['exclusiveMinimum']) && (\is_int($schema['exclusiveMinimum']) || \is_float($schema['exclusiveMinimum']))) {
            $val = $isInt ? (int) $schema['exclusiveMinimum'] : (float) $schema['exclusiveMinimum'];
            $code .= '->exclusiveMinimum('.$this->exportValue($val).')';
        }

        if (isset($schema['maximum']) && (\is_int($schema['maximum']) || \is_float($schema['maximum']))) {
            $val = $isInt ? (int) $schema['maximum'] : (float) $schema['maximum'];
            if (isset($schema['exclusiveMaximum']) && true === $schema['exclusiveMaximum']) {
                $code .= '->exclusiveMaximum('.$this->exportValue($val).')';
            } else {
                $code .= '->maximum('.$this->exportValue($val).')';
            }
        } elseif (isset($schema['exclusiveMaximum']) && (\is_int($schema['exclusiveMaximum']) || \is_float($schema['exclusiveMaximum']))) {
            $val = $isInt ? (int) $schema['exclusiveMaximum'] : (float) $schema['exclusiveMaximum'];
            $code .= '->exclusiveMaximum('.$this->exportValue($val).')';
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
        if (isset($schema['prefixItems']) && \is_array($schema['prefixItems'])) {
            return $this->generateTuple($schema);
        }

        if (isset($schema['items']) && \is_array($schema['items']) && [] !== $schema['items'] && array_is_list($schema['items'])) {
            return $this->generateTuple($schema);
        }

        /** @var array<string, mixed> $itemSchema */
        $itemSchema = isset($schema['items']) && \is_array($schema['items']) ? $schema['items'] : [];

        if ([] === $itemSchema) {
            // No items schema defined: default to string
            $itemCode = '$p->string()';
        } else {
            $itemCode = $this->generateSchema($itemSchema);
        }

        $code = '$p->array('.$itemCode.')';

        if (isset($schema['minItems']) && (\is_int($schema['minItems']) || \is_float($schema['minItems']))) {
            $code .= '->minItems('.(int) $schema['minItems'].')';
        }

        if (isset($schema['maxItems']) && (\is_int($schema['maxItems']) || \is_float($schema['maxItems']))) {
            $code .= '->maxItems('.(int) $schema['maxItems'].')';
        }

        return $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateTuple(array $schema): string
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = isset($schema['prefixItems']) && \is_array($schema['prefixItems'])
            ? $schema['prefixItems']
            : (\is_array($schema['items'] ?? null) ? $schema['items'] : []);

        $itemCodes = array_map(fn (array $item): string => $this->generateSchema($item), $items);

        return '$p->tuple(['.implode(', ', $itemCodes).'])';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateObject(array $schema): string
    {
        /** @var array<string, array<string, mixed>> $properties */
        $properties = isset($schema['properties']) && \is_array($schema['properties'])
            ? $schema['properties']
            : [];

        /** @var array<int, string> $required */
        $required = isset($schema['required']) && \is_array($schema['required'])
            ? $schema['required']
            : [];

        $additionalProperties = $schema['additionalProperties'] ?? null;

        // If no properties defined but additionalProperties has a schema, use record
        if ([] === $properties && \is_array($additionalProperties)) {
            /** @var array<string, mixed> $additionalPropertiesSchema */
            $additionalPropertiesSchema = $additionalProperties;

            return '$p->record('.$this->generateSchema($additionalPropertiesSchema).')';
        }

        if ([] === $properties && (true === $additionalProperties || null === $additionalProperties)) {
            return '$p->record($p->string())';
        }

        $fieldCodes = [];

        foreach ($properties as $fieldName => $fieldSchema) {
            $fieldCode = $this->generateSchema($fieldSchema);

            if (!\in_array($fieldName, $required, true)) {
                $fieldCode .= '->nullable()';
            }

            $fieldCodes[] = $this->exportValue($fieldName).' => '.$fieldCode;
        }

        $code = '$p->object(['.implode(', ', $fieldCodes).'])';

        // Build list of optional (non-required) fields
        /** @var array<int, string> $optionalFields */
        $optionalFields = [];

        foreach (array_keys($properties) as $fieldName) {
            if (!\in_array($fieldName, $required, true)) {
                $optionalFields[] = $fieldName;
            }
        }

        if ([] !== $optionalFields) {
            $exportedOptionals = array_map(fn (string $f): string => $this->exportValue($f), $optionalFields);
            $code .= '->optional(['.implode(', ', $exportedOptionals).'])';
        }

        if (false === $additionalProperties) {
            $code .= '->strict()';
        }

        return $code;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function generateNull(array $schema): string
    {
        return '$p->string()->nullable()->default(null)';
    }

    /**
     * Fallback when no type is specified and no composition keywords are found.
     *
     * @param array<string, mixed> $schema
     */
    private function generateFallback(array $schema): string
    {
        // If there are properties, treat as object
        if (isset($schema['properties'])) {
            $schema['type'] = 'object';

            return $this->generateObject($schema);
        }

        // Default: accept any string
        return '$p->string()';
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
            $str = (string) $value;

            // Ensure float representation
            if (!str_contains($str, '.') && !str_contains($str, 'E') && !str_contains($str, 'e')) {
                $str .= '.0';
            }

            return $str;
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
