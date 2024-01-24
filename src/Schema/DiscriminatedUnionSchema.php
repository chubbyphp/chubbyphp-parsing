<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class DiscriminatedUnionSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'discriminatedUnion.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", "{{given}}" given';

    public const ERROR_DISCRIMINATOR_FIELD_CODE = 'discriminatedUnion.discriminatorField';
    public const ERROR_DISCRIMINATOR_FIELD_TEMPLATE
    = 'Input does not contain the discriminator field "{{discriminatorFieldName}}"';

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
                        SchemaInterface::class,
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
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            if (!isset($input[$this->discriminatorFieldName])) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_DISCRIMINATOR_FIELD_CODE,
                        self::ERROR_DISCRIMINATOR_FIELD_TEMPLATE,
                        ['discriminatorFieldName' => $this->discriminatorFieldName]
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

        foreach ($this->objectSchemas as $objectSchema) {
            /** @var SchemaInterface $discriminatorFieldSchema */
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
