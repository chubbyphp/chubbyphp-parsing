<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseError;
use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\ParseErrors;

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
                $objectSchemaType = \is_object($objectSchema) ? $objectSchema::class : \gettype($objectSchema);

                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s ($objectSchemas) must be of type ObjectSchemaInterface, %s given', (string) $i, $objectSchemaType));
            }

            $discriminatorFieldSchema = $objectSchema->getFieldSchema($discriminatorFieldName);

            if (null === $discriminatorFieldSchema) {
                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s #%s ($objectSchemas) must contain LiteralSchemaInterface', (string) $i, $discriminatorFieldName));
            }

            if (!$discriminatorFieldSchema instanceof LiteralSchemaInterface) {
                $discriminatorFieldSchemaType = \is_object($discriminatorFieldSchema) ? $discriminatorFieldSchema::class : \gettype($discriminatorFieldSchema);

                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s #%s ($objectSchemas) must be of type LiteralSchemaInterface, %s given', (string) $i, $discriminatorFieldName, $discriminatorFieldSchemaType));
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
                throw new ParseError(sprintf("Input needs to be array, '%s' given", \gettype($input)));
            }

            $discriminator = $input[$this->discriminatorFieldName] ?? null;

            if (!\is_string($discriminator)) {
                throw new ParseError(sprintf("Discriminator needs to be a string, '%s' given", \gettype($discriminator)));
            }

            /** @var array<ParseErrorInterface> $parseErrors */
            $parseErrors = [];

            $output = $this->parseObjectSchemas($input, $discriminator);

            foreach ($this->transform as $transform) {
                $output = $transform($output, $parseErrors);
            }

            if (\count($parseErrors)) {
                throw new ParseErrors($parseErrors);
            }

            return $output;
        } catch (ParseErrorInterface $parseError) {
            if ($this->catch) {
                return ($this->catch)($input, $parseError);
            }

            throw $parseError;
        }
    }

    private function parseObjectSchemas(mixed $input, string $discriminator): mixed
    {
        /** @var array<ParseErrorInterface> $parseErrors */
        $parseErrors = [];

        foreach ($this->objectSchemas as $i => $objectSchema) {
            $discriminatorFieldSchema = $objectSchema->getFieldSchema($this->discriminatorFieldName);

            try {
                $discriminatorFieldSchema->parse($discriminator);
            } catch (ParseErrorInterface $parseError) {
                $parseErrors[$i] = $parseError;

                continue;
            }

            return $objectSchema->parse($input);
        }

        throw new ParseErrors($parseErrors);
    }
}
