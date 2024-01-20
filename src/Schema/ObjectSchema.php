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
    public function __construct(array $fieldSchemas, private string $classname)
    {
        foreach ($fieldSchemas as $name => $fieldSchema) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 name #%s ($fieldSchemas) must be of type string, %s given',
                        (string) $name,
                        $this->getDataType($name)
                    )
                );
            }

            if (!$fieldSchema instanceof SchemaInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s ($fieldSchemas) must be of type SchemaInterface, %s given',
                        (string) $name,
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
                throw new ParserErrorException(sprintf('Type should be "array" "%s" given', $this->getDataType($input)));
            }

            $output = new $this->classname();

            $childrenParserErrorException = new ParserErrorException();

            foreach (array_keys($input) as $fieldName) {
                if (!isset($this->fieldSchemas[$fieldName])) {
                    $childrenParserErrorException->addError(sprintf("Additional property '%s'", $fieldName), $fieldName);
                }
            }

            foreach ($this->fieldSchemas as $fieldName => $fieldSchema) {
                try {
                    $output->{$fieldName} = $fieldSchema->parse($input[$fieldName] ?? null);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $fieldName);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
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
