<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\AssocSchema;
use Chubbyphp\Parsing\Schema\BackedEnumSchema;
use Chubbyphp\Parsing\Schema\BoolSchema;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\ObjectSchemaInterface;
use Chubbyphp\Parsing\Schema\RecordSchema;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\TupleSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;

/**
 * @method AssocSchema assoc(array<string, SchemaInterface> $fieldNameToSchema)
 */
interface ParserInterface
{
    public function array(SchemaInterface $itemSchema): ArraySchema;

    // /**
    //  * @param array<string, SchemaInterface> $fieldNameToSchema
    //  */
    // public function assoc(array $fieldNameToSchema): AssocSchema;

    /**
     * @param class-string<\BackedEnum> $backedEnumClass
     */
    public function backedEnum(string $backedEnumClass): BackedEnumSchema;

    public function bool(): BoolSchema;

    public function dateTime(): DateTimeSchema;

    /**
     * @param array<ObjectSchemaInterface> $objectSchemas
     */
    public function discriminatedUnion(array $objectSchemas, string $discriminatorFieldName): DiscriminatedUnionSchema;

    public function float(): FloatSchema;

    public function int(): IntSchema;

    /**
     * @param \Closure(): SchemaInterface $lazy
     */
    public function lazy(\Closure $lazy): SchemaInterface;

    public function literal(bool|float|int|string $literal): LiteralSchema;

    /**
     * @param array<string, SchemaInterface> $fieldNameToSchema
     * @param class-string                   $classname
     */
    public function object(array $fieldNameToSchema, string $classname = \stdClass::class): ObjectSchema;

    public function record(SchemaInterface $fieldSchema): RecordSchema;

    public function string(): StringSchema;

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function tuple(array $schemas): TupleSchema;

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function union(array $schemas): UnionSchema;
}
