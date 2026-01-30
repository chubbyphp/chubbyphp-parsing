<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class RecordSchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'record.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public function __construct(private SchemaInterface $fieldSchema)
    {
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

        foreach ($input as $fieldName => $fieldValue) {
            try {
                $output[$fieldName] = $this->fieldSchema->parse($fieldValue);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        if ($childrenErrors->has()) {
            throw new ErrorsException($childrenErrors);
        }

        return $output;
    }
}
