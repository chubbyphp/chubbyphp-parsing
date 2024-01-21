<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class ObjectSchema extends AbstractSchema implements ObjectSchemaInterface
{
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
            if (!\is_array($input)) {
                throw new ParserErrorException(
                    sprintf('Type should be "array" "%s" given', $this->getDataType($input))
                );
            }

            $output = new $this->classname();

            $parserErrorException = new ParserErrorException();

            foreach (array_keys($input) as $fieldName) {
                if (!isset($this->fieldSchemas[$fieldName])) {
                    $parserErrorException->addError('Unknown field', $fieldName);
                }
            }

            foreach ($this->fieldSchemas as $fieldName => $fieldSchema) {
                try {
                    $output->{$fieldName} = $fieldSchema->parse($input[$fieldName] ?? null);
                } catch (ParserErrorException $childParserErrorException) {
                    $parserErrorException->addParserErrorException($childParserErrorException, $fieldName);
                }
            }

            if ($parserErrorException->hasError()) {
                throw $parserErrorException;
            }

            return $this->transformOutput($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function getFieldSchema(string $fieldName): null|SchemaInterface
    {
        return $this->fieldSchemas[$fieldName] ?? null;
    }
}
