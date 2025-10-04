<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class TupleSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'tuple.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", {{given}} given';

    public const ERROR_LENGTH_CODE = 'tuple.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const ERROR_MISSING_INDEX_CODE = 'tuple.missingIndex';
    public const ERROR_MISSING_INDEX_TEMPLATE = 'Missing input at index {{index}}';

    public const ERROR_ADDITIONAL_INDEX_CODE = 'tuple.additionalIndex';
    public const ERROR_ADDITIONAL_INDEX_TEMPLATE = 'Additional input at index {{index}}';

    /**
     * @var array<SchemaInterface>
     */
    private array $schemas;

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function __construct(array $schemas)
    {
        foreach ($schemas as $i => $schema) {
            if (!$schema instanceof SchemaInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Argument #1 value of #%s ($schemas) must be of type %s, %s given',
                        $i,
                        SchemaInterface::class,
                        $this->getDataType($schema)
                    )
                );
            }
        }

        $this->schemas = $schemas;
    }

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_array($input)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $childrenErrors = new Errors();

            $output = [];

            foreach ($this->schemas as $i => $schema) {
                if (!isset($input[$i])) {
                    $childrenErrors->add(new Error(
                        self::ERROR_MISSING_INDEX_CODE,
                        self::ERROR_MISSING_INDEX_TEMPLATE,
                        ['index' => $i]
                    ), (string) $i);

                    continue;
                }

                try {
                    $output[$i] = $schema->parse($input[$i]);
                } catch (ErrorsException $e) {
                    $childrenErrors->add($e->errors, (string) $i);
                }
            }

            $inputCount = \count($input);
            $schemaCount = \count($this->schemas);

            for ($i = $schemaCount; $i < $inputCount; ++$i) {
                $childrenErrors->add(new Error(
                    self::ERROR_ADDITIONAL_INDEX_CODE,
                    self::ERROR_ADDITIONAL_INDEX_TEMPLATE,
                    ['index' => $i]
                ), (string) $i);
            }

            if ($childrenErrors->has()) {
                throw new ErrorsException($childrenErrors);
            }

            return $this->dispatchPostParses($output);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }
}
