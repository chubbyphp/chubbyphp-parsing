<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ObjectSchema extends AbstractObjectSchema implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'object.type';
    public const string ERROR_UNKNOWN_FIELD_CODE = 'object.unknownField';

    /**
     * @param array<mixed, mixed> $fieldToSchema
     * @param class-string        $classname
     */
    public function __construct(array $fieldToSchema, private string $classname = \stdClass::class, private bool $construct = false)
    {
        parent::__construct($fieldToSchema);
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function parseFields(array $input, Errors $childrenErrors): object
    {
        $fields = [];
        foreach ($this->getFieldToSchema() as $fieldName => $fieldSchema) {
            try {
                if ($this->skip($input, $fieldName)) {
                    continue;
                }

                $fields[$fieldName] = $fieldSchema->parse($input[$fieldName] ?? null);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
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
}
