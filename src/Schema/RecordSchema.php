<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class RecordSchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'record.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|\stdClass|\Traversable", {{given}} given';

    public const string ERROR_MIN_PROPERTIES_CODE = 'record.minProperties';
    public const string ERROR_MIN_PROPERTIES_TEMPLATE = 'Properties should be minimum {{minProperties}}, {{given}} given';

    public const string ERROR_MAX_PROPERTIES_CODE = 'record.maxProperties';
    public const string ERROR_MAX_PROPERTIES_TEMPLATE = 'Properties should be maximum {{maxProperties}}, {{given}} given';

    private ?SchemaInterface $propertyNameSchema = null;

    public function __construct(private SchemaInterface $fieldSchema)
    {
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

    /**
     * Each property name (key) has to be valid against the given schema (json schema spec
     * propertyNames). Keys are validated as strings, failures get reported at the key's
     * error path, next to a possible value error.
     */
    public function propertyNames(SchemaInterface $propertyNameSchema): static
    {
        $clone = clone $this;
        $clone->propertyNameSchema = $propertyNameSchema;

        return $clone;
    }

    public function minProperties(int $minProperties): static
    {
        return $this->postParse(static function (array $record) use ($minProperties) {
            $propertiesCount = \count($record);

            if ($propertiesCount >= $minProperties) {
                return $record;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MIN_PROPERTIES_CODE,
                    self::ERROR_MIN_PROPERTIES_TEMPLATE,
                    ['minProperties' => $minProperties, 'given' => $propertiesCount]
                )
            );
        });
    }

    public function maxProperties(int $maxProperties): static
    {
        return $this->postParse(static function (array $record) use ($maxProperties) {
            $propertiesCount = \count($record);

            if ($propertiesCount <= $maxProperties) {
                return $record;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAX_PROPERTIES_CODE,
                    self::ERROR_MAX_PROPERTIES_TEMPLATE,
                    ['maxProperties' => $maxProperties, 'given' => $propertiesCount]
                )
            );
        });
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

        $output = [];

        $childrenErrors = new Errors();

        foreach ($input as $fieldName => $fieldValue) {
            $fieldName = (string) $fieldName;

            if (null !== $this->propertyNameSchema) {
                try {
                    $this->propertyNameSchema->parse($fieldName);
                } catch (ErrorsException $e) {
                    $childrenErrors->add($e->errors, $fieldName);
                }
            }

            try {
                $output[$fieldName] = $this->fieldSchema->parse($fieldValue);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        if ($childrenErrors->has()) {
            throw new ErrorsException($childrenErrors);
        }

        return $output;
    }
}
