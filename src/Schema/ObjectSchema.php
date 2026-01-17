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
    public function __construct(array $fieldToSchema, private string $classname = \stdClass::class)
    {
        parent::__construct($fieldToSchema);
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function parseFields(array $input, Errors $childrenErrors): object
    {
        $object = new $this->classname();

        foreach ($this->getFieldToSchema() as $fieldName => $fieldSchema) {
            try {
                if ($this->skip($input, $fieldName)) {
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
