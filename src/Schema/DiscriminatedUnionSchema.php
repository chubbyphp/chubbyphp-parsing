<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class DiscriminatedUnionSchema extends AbstractSchema implements SchemaInterface
{
    /**
     * @var array<ObjectSchemaInterface>
     */
    private array $objectSchemas;

    /**
     * @param array<ObjectSchemaInterface> $objectSchemas
     */
    public function __construct(array $objectSchemas, private string $discriminatorFieldName)
    {
        foreach ($objectSchemas as $i => $objectSchema) {
            if (!$objectSchema instanceof ObjectSchemaInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s ($objectSchemas) must be of type ObjectSchemaInterface, %s given',
                        (string) $i,
                        $this->getDataType($objectSchema)
                    )
                );
            }

            $discriminatorFieldSchema = $objectSchema->getFieldSchema($discriminatorFieldName);

            if (null === $discriminatorFieldSchema) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s #%s ($objectSchemas) must contain LiteralSchemaInterface',
                        (string) $i,
                        $discriminatorFieldName
                    )
                );
            }

            if (!$discriminatorFieldSchema instanceof LiteralSchemaInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s #%s ($objectSchemas) must be of type LiteralSchemaInterface, %s given',
                        (string) $i,
                        $discriminatorFieldName,
                        $this->getDataType($discriminatorFieldSchema)
                    )
                );
            }
        }

        $this->objectSchemas = $objectSchemas;
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

            $discriminator = $input[$this->discriminatorFieldName] ?? null;

            if (!\is_string($discriminator)) {
                throw new ParserErrorException(
                    sprintf("Discriminator needs to be a string, '%s' given", \gettype($discriminator))
                );
            }

            $output = $this->parseObjectSchemas($input, $discriminator);

            return $this->transformOutput($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    private function parseObjectSchemas(mixed $input, string $discriminator): mixed
    {
        $parserErrorException = new ParserErrorException();

        foreach ($this->objectSchemas as $i => $objectSchema) {
            $discriminatorFieldSchema = $objectSchema->getFieldSchema($this->discriminatorFieldName);

            try {
                $discriminatorFieldSchema->parse($discriminator);
            } catch (ParserErrorException $childParserErrorException) {
                $parserErrorException->addParserErrorException($childParserErrorException);

                continue;
            }

            return $objectSchema->parse($input);
        }

        throw $parserErrorException;
    }
}
