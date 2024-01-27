<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class ObjectSchema extends AbstractSchema implements ObjectSchemaInterface
{
    public const ERROR_TYPE_CODE = 'object.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", "{{given}}" given';

    public const ERROR_UNKNOWN_FIELD_CODE = 'object.unknownField';
    public const ERROR_UNKNOWN_FIELD_TEMPLATE = 'Unknown field "{{fieldName}}"';

    /**
     * @var array<string, SchemaInterface>
     */
    private array $fieldSchemas;

    /**
     * @param array<string, SchemaInterface> $fieldSchemas
     * @param class-string                   $classname
     */
    public function __construct(array $fieldSchemas, private string $classname = \stdClass::class)
    {
        foreach ($fieldSchemas as $fieldName => $fieldSchema) {
            if (!\is_string($fieldName)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 name #%s ($fieldSchemas) must be of type string, %s given',
                        $fieldName,
                        $this->getDataType($fieldName)
                    )
                );
            }

            if (!$fieldSchema instanceof SchemaInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s ($fieldSchemas) must be of type %s, %s given',
                        $fieldName,
                        SchemaInterface::class,
                        $this->getDataType($fieldSchema)
                    )
                );
            }
        }

        $this->fieldSchemas = $fieldSchemas;
    }

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if ($input instanceof \stdClass || $input instanceof \Traversable) {
                $input = (array) $input;
            }

            if (!\is_array($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $object = new $this->classname();

            $childrenParserErrorException = new ParserErrorException();

            foreach (array_keys($input) as $fieldName) {
                if (!isset($this->fieldSchemas[$fieldName])) {
                    $childrenParserErrorException->addError(new Error(
                        self::ERROR_UNKNOWN_FIELD_CODE,
                        self::ERROR_UNKNOWN_FIELD_TEMPLATE,
                        ['fieldName' => $fieldName]
                    ), $fieldName);
                }
            }

            foreach ($this->fieldSchemas as $fieldName => $fieldSchema) {
                try {
                    $object->{$fieldName} = $fieldSchema->parse($input[$fieldName] ?? null);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $fieldName);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
            }

            return $this->dispatchMiddlewares($object);
        } catch (ParserErrorException $childrenParserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $childrenParserErrorException);
            }

            throw $childrenParserErrorException;
        }
    }

    public function getFieldSchema(string $fieldName): null|SchemaInterface
    {
        return $this->fieldSchemas[$fieldName] ?? null;
    }
}
