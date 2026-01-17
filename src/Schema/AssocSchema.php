<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class AssocSchema extends AbstractObjectSchema implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'assoc.type';
    public const string ERROR_UNKNOWN_FIELD_CODE = 'assoc.unknownField';

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    protected function parseFields(array $input, Errors $childrenErrors): array
    {
        $output = [];

        foreach ($this->getFieldToSchema() as $fieldName => $fieldSchema) {
            try {
                if ($this->skip($input, $fieldName)) {
                    continue;
                }

                $output[$fieldName] = $fieldSchema->parse($input[$fieldName] ?? null);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        return $output;
    }
}
