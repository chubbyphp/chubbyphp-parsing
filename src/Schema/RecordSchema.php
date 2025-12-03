<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class RecordSchema extends AbstractSchema implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'record.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public function __construct(private SchemaInterface $fieldSchema) {}

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

            return $this->dispatchPostParses($output);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }
}
