<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\IntegerSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;

final class Parser
{
    public function array(SchemaInterface $itemSchema): ArraySchema
    {
        return new ArraySchema($itemSchema);
    }

    public function integer(): IntegerSchema
    {
        return new IntegerSchema();
    }

    public function literal(string $literal): LiteralSchema
    {
        return new LiteralSchema($literal);
    }

    /**
     * @param array<string, SchemaInterface> $objectSchema
     * @param class-string                   $classname
     */
    public function object(array $objectSchema, string $classname = \stdClass::class): ObjectSchema
    {
        return new ObjectSchema($objectSchema, $classname);
    }

    public function string(): StringSchema
    {
        return new StringSchema();
    }

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function union(array $schemas): UnionSchema
    {
        return new UnionSchema($schemas);
    }
}
