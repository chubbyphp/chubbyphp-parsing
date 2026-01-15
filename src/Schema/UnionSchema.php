<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class UnionSchema extends AbstractSchema implements SchemaInterface
{
    /**
     * @var array<SchemaInterface>
     */
    private array $schemas = [];

    /**
     * @param array<mixed> $schemas
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

            $this->schemas[] = $schema;
        }
    }

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            $output = $this->parseSchemas($input);

            return $this->dispatchPostParses($output);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }

    private function parseSchemas(mixed $input): mixed
    {
        $errors = new Errors();

        foreach ($this->schemas as $schema) {
            try {
                return $schema->parse($input);
            } catch (ErrorsException $e) {
                $errors->add($e->errors);
            }
        }

        throw new ErrorsException($errors);
    }
}
