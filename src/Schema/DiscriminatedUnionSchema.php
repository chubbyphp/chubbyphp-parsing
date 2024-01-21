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
                        'Argument #1 value of #%s ($objectSchemas) must be of type %s, %s given',
                        $i,
                        ObjectSchemaInterface::class,
                        $this->getDataType($objectSchema)
                    )
                );
            }

            $discriminatorFieldSchema = $objectSchema->getFieldSchema($discriminatorFieldName);

            if (null === $discriminatorFieldSchema) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s #%s ($objectSchemas) must contain %s',
                        $i,
                        $discriminatorFieldName,
                        LiteralSchemaInterface::class,
                    )
                );
            }

            if (!$discriminatorFieldSchema instanceof LiteralSchemaInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Argument #1 value of #%s #%s ($objectSchemas) must be of type %s, %s given',
                        $i,
                        $discriminatorFieldName,
                        LiteralSchemaInterface::class,
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

            if (!isset($input[$this->discriminatorFieldName])) {
                throw new ParserErrorException(
                    sprintf(
                        'Missing discriminator value on field "%s"',
                        $this->discriminatorFieldName,
                    )
                );
            }

            $output = $this->parseObjectSchemas($input, $input[$this->discriminatorFieldName]);

            return $this->transformOutput($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    private function parseObjectSchemas(mixed $input, mixed $discriminator): mixed
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
