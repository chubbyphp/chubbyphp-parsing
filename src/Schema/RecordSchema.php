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

    private ?int $minProperties = null;

    private ?int $maxProperties = null;

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

    public function minProperties(int $minProperties): static
    {
        $clone = clone $this;
        $clone->minProperties = $minProperties;

        return $clone;
    }

    public function maxProperties(int $maxProperties): static
    {
        $clone = clone $this;
        $clone->maxProperties = $maxProperties;

        return $clone;
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

        $this->propertiesCount($input);

        $output = [];

        $childrenErrors = new Errors();

        foreach ($input as $fieldName => $fieldValue) {
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

    /**
     * @param array<mixed> $input
     */
    private function propertiesCount(array $input): void
    {
        $propertiesCount = \count($input);

        if (null !== $this->minProperties && $propertiesCount < $this->minProperties) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_MIN_PROPERTIES_CODE,
                    self::ERROR_MIN_PROPERTIES_TEMPLATE,
                    ['minProperties' => $this->minProperties, 'given' => $propertiesCount]
                )
            );
        }

        if (null !== $this->maxProperties && $propertiesCount > $this->maxProperties) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_MAX_PROPERTIES_CODE,
                    self::ERROR_MAX_PROPERTIES_TEMPLATE,
                    ['maxProperties' => $this->maxProperties, 'given' => $propertiesCount]
                )
            );
        }
    }
}
