<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ObjectSchema extends AbstractObjectSchema implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'object.type';
    public const string ERROR_UNKNOWN_FIELD_CODE = 'object.unknownField';
    public const string ERROR_MISSING_FIELD_CODE = 'object.missingField';

    /**
     * @param array<mixed, mixed> $fieldToSchema
     * @param class-string        $classname
     */
    public function __construct(
        array $fieldToSchema,
        private string $classname = \stdClass::class,
        private bool $construct = false
    ) {
        parent::__construct($fieldToSchema);
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function parseFields(array $input, Errors $childrenErrors): ?object
    {
        $fields = [];
        foreach ($this->getFieldToSchema() as $fieldName => $fieldSchema) {
            try {
                if ($this->skip($input, $fieldName)) {
                    continue;
                }

                $fields[$fieldName] = $this->parseField($input, $fieldName, $fieldSchema);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        $fields = $this->parseAdditionalFields($input, $fields, $childrenErrors);

        if ($childrenErrors->has()) {
            return null;
        }

        if (!$this->construct) {
            $object = new ($this->classname);

            foreach ($fields as $fieldName => $fieldValue) {
                $object->{$fieldName} = $fieldValue;
            }

            return $object;
        }

        return new ($this->classname)(...$fields);
    }

    /**
     * The extra fields kept by additionalProperties() become dynamic properties, so the
     * classname must accept them (\stdClass or __set()) and construct must be false.
     */
    protected function assertAdditionalPropertiesSupport(): void
    {
        if ($this->construct) {
            throw new \InvalidArgumentException(
                'additionalProperties() is not supported with construct: true, an unknown named argument would be fatal'
            );
        }

        if (!self::allowsDynamicProperties($this->classname)) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'additionalProperties() needs a classname which accepts dynamic properties (\stdClass or __set()), %s given',
                    $this->classname
                )
            );
        }
    }

    /**
     * @param class-string $classname
     */
    private static function allowsDynamicProperties(string $classname): bool
    {
        return is_a($classname, \stdClass::class, true)
            || (new \ReflectionClass($classname))->hasMethod('__set');
    }
}
