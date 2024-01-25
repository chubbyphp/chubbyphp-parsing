<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class RecordSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'record.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", "{{given}}" given';

    public function __construct(private SchemaInterface $fieldSchema) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_array($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $object = new \stdClass();

            $childrenParserErrorException = new ParserErrorException();

            foreach ($input as $fieldName => $fieldValue) {
                try {
                    $object->{$fieldName} = $this->fieldSchema->parse($fieldValue);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $fieldName);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
            }

            return $this->dispatchMiddlewares($object);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }
}
