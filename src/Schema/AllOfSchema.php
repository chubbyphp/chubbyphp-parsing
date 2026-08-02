<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class AllOfSchema extends AbstractSchemaInnerParse
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

    /*
     * Every schema validates the original input (json schema spec: allOf): like not() it
     * is pure validation - the schemas' coercions/transformations never leak, the input
     * is returned unchanged. Unlike union(), which stops at the first success, every
     * failing schema contributes its errors, so all violations get reported at once, at
     * their unchanged instance paths. An empty schema list is vacuously valid.
     */
    protected function innerParse(mixed $input): mixed
    {
        $errors = new Errors();

        foreach ($this->schemas as $schema) {
            try {
                $schema->parse($input);
            } catch (ErrorsException $e) {
                $errors->add($e->errors);
            }
        }

        if ($errors->has()) {
            throw new ErrorsException($errors);
        }

        return $input;
    }
}
