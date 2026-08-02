<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class OneOfSchema extends AbstractSchemaInnerParse
{
    public const string ERROR_MULTIPLE_MATCH_CODE = 'oneOf.multipleMatch';
    public const string ERROR_MULTIPLE_MATCH_TEMPLATE = 'Input should match exactly one schema, schemas at indexes {{indexes}} matched, {{given}} given';

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

    /*
     * Every schema is evaluated against the original input (json schema spec: oneOf).
     * With exactly one match that schema's output is returned - unlike allOf(),
     * exactly-one means the applied coercion is unambiguous, so the output is kept.
     * With no match every schema's errors are reported (like union()). With two or more
     * matches the single oneOf.multipleMatch error reports the matching indexes - the
     * schemas succeeded, so there are no inner errors to report.
     */
    protected function innerParse(mixed $input): mixed
    {
        $errors = new Errors();

        $matchedIndexes = [];
        $output = null;

        foreach ($this->schemas as $i => $schema) {
            $result = $schema->safeParse($input);

            if (null !== $result->exception) {
                $errors->add($result->exception->errors);

                continue;
            }

            $matchedIndexes[] = $i;
            $output = $result->data;
        }

        if (1 === \count($matchedIndexes)) {
            return $output;
        }

        if ([] === $matchedIndexes) {
            throw new ErrorsException($errors);
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_MULTIPLE_MATCH_CODE,
                self::ERROR_MULTIPLE_MATCH_TEMPLATE,
                ['indexes' => $matchedIndexes, 'given' => $input]
            )
        );
    }
}
