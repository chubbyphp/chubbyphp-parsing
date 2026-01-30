<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class DiscriminatedUnionSchema extends AbstractSchemaV2 implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'discriminatedUnion.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public const string ERROR_DISCRIMINATOR_FIELD_CODE = 'discriminatedUnion.discriminatorField';
    public const string ERROR_DISCRIMINATOR_FIELD_TEMPLATE
        = 'Input does not contain the discriminator field {{discriminatorFieldName}}';

    /**
     * @var array<ObjectSchemaInterface>
     */
    private array $objectSchemas = [];

    /**
     * @param array<mixed> $objectSchemas
     */
    public function __construct(array $objectSchemas, private string $discriminatorFieldName)
    {
        foreach ($objectSchemas as $i => $objectSchema) {
            if (!$objectSchema instanceof ObjectSchemaInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
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
                    \sprintf(
                        'Argument #1 value of #%s #%s ($objectSchemas) must contain %s',
                        $i,
                        $discriminatorFieldName,
                        SchemaInterface::class,
                    )
                );
            }

            $this->objectSchemas[] = $objectSchema;
        }

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

        if (!isset($input[$this->discriminatorFieldName])) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_DISCRIMINATOR_FIELD_CODE,
                    self::ERROR_DISCRIMINATOR_FIELD_TEMPLATE,
                    ['discriminatorFieldName' => $this->discriminatorFieldName]
                )
            );
        }

        $errors = new Errors();

        foreach ($this->objectSchemas as $objectSchema) {
            /** @var SchemaInterface $discriminatorFieldSchema */
            $discriminatorFieldSchema = $objectSchema->getFieldSchema($this->discriminatorFieldName);

            try {
                $discriminatorFieldSchema->parse($input[$this->discriminatorFieldName]);
            } catch (ErrorsException $e) {
                $errors->add($e->errors);

                continue;
            }

            return $objectSchema->parse($input);
        }

        throw new ErrorsException($errors);
    }
}
