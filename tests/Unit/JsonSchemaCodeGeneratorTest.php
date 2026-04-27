<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\JsonSchemaCodeGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Chubbyphp\Parsing\JsonSchemaCodeGenerator
 */
final class JsonSchemaCodeGeneratorTest extends TestCase
{
    public function testGeneratesStringSchema(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->string()->minLength(1)->maxLength(10)->pattern('~^[a-z]+$~')",
            $generator->generate([
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 10,
                'pattern' => '^[a-z]+$',
            ])
        );
    }

    public function testGeneratesStringFormatSchema(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->string()->email()',
            $generator->generate(['type' => 'string', 'format' => 'email'])
        );
    }

    public function testGeneratesUuidWithVersionAgnosticPattern(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->string()->pattern('~^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\$~i')",
            $generator->generate(['type' => 'string', 'format' => 'uuid'])
        );
    }

    public function testGeneratesIntegerConstraints(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->int()->minimum(1)->maximum(9)->multipleOf(2)',
            $generator->generate([
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 9,
                'multipleOf' => 2,
            ])
        );
    }

    public function testGeneratesNumberConstraints(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->float()->exclusiveMinimum(0.0)->exclusiveMaximum(10.5)->multipleOf(0.5)',
            $generator->generate([
                'type' => 'number',
                'exclusiveMinimum' => 0,
                'exclusiveMaximum' => 10.5,
                'multipleOf' => 0.5,
            ])
        );
    }

    public function testGeneratesUniformArray(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->array($p->string())->exactItems(2)->uniqueItems()',
            $generator->generate([
                'type' => 'array',
                'items' => ['type' => 'string'],
                'minItems' => 2,
                'maxItems' => 2,
                'uniqueItems' => true,
            ])
        );
    }

    public function testGeneratesExactTuple(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->tuple([$p->string(), $p->int()])',
            $generator->generate([
                'type' => 'array',
                'prefixItems' => [
                    ['type' => 'string'],
                    ['type' => 'integer'],
                ],
                'minItems' => 2,
                'maxItems' => 2,
            ])
        );
    }

    public function testRejectsNonExactTuple(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tuple-like schemas can only be generated when they require exactly the declared items');

        $generator->generate([
            'type' => 'array',
            'prefixItems' => [
                ['type' => 'string'],
                ['type' => 'integer'],
            ],
        ]);
    }

    public function testRejectsArrayWithoutItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array items must be defined to generate exact validation code');

        $generator->generate(['type' => 'array']);
    }

    public function testGeneratesObjectWithoutMakingOptionalFieldsNullable(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->object(['name' => \$p->string(), 'nickname' => \$p->string()])->optional(['nickname'])",
            $generator->generate([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'nickname' => ['type' => 'string'],
                ],
                'required' => ['name'],
            ])
        );
    }

    public function testGeneratesStrictObject(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->object(['name' => \$p->string()])->strict()",
            $generator->generate([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                ],
                'required' => ['name'],
                'additionalProperties' => false,
            ])
        );
    }

    public function testGeneratesRecordWhenOnlyAdditionalPropertiesSchemaExists(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->record($p->int())',
            $generator->generate([
                'type' => 'object',
                'additionalProperties' => ['type' => 'integer'],
            ])
        );
    }

    public function testRejectsTypedAdditionalPropertiesAlongsideFixedProperties(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('additionalProperties schemas cannot be combined with fixed properties');

        $generator->generate([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
            'additionalProperties' => ['type' => 'integer'],
        ]);
    }

    public function testGeneratesConstAndNullConst(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame("\$p->const('active')", $generator->generate(['const' => 'active']));
        self::assertSame('$p->union([])->nullable()', $generator->generate(['const' => null]));
    }

    public function testGeneratesEnumIncludingNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->union([\$p->const('active'), \$p->const('inactive')])->nullable()",
            $generator->generate(['enum' => ['active', 'inactive', null]])
        );
    }

    public function testGeneratesNullOnlySchema(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame('$p->union([])->nullable()', $generator->generate(['type' => 'null']));
    }

    public function testGeneratesNullableOpenApiSchema(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->string()->minLength(1)->nullable()',
            $generator->generate([
                'type' => 'string',
                'minLength' => 1,
                'nullable' => true,
            ])
        );
    }

    public function testGeneratesAnyOfUnion(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->union([$p->string(), $p->int()])',
            $generator->generate([
                'anyOf' => [
                    ['type' => 'string'],
                    ['type' => 'integer'],
                ],
            ])
        );
    }

    public function testGeneratesDiscriminatedOneOfUnion(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->discriminatedUnion([\$p->object(['type' => \$p->const('email'), 'address' => \$p->string()]), \$p->object(['type' => \$p->const('phone'), 'number' => \$p->string()])], 'type')",
            $generator->generate([
                'oneOf' => [
                    [
                        'type' => 'object',
                        'properties' => [
                            'type' => ['const' => 'email'],
                            'address' => ['type' => 'string'],
                        ],
                        'required' => ['type', 'address'],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'type' => ['const' => 'phone'],
                            'number' => ['type' => 'string'],
                        ],
                        'required' => ['type', 'number'],
                    ],
                ],
            ])
        );
    }

    public function testRejectsOverlappingOneOfBranches(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('oneOf can only be generated when branches are provably exclusive');

        $generator->generate([
            'oneOf' => [
                ['type' => 'string'],
                ['const' => 'a'],
            ],
        ]);
    }

    public function testGeneratesAllOfObjectMerge(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            "\$p->object(['id' => \$p->int(), 'name' => \$p->string()])",
            $generator->generate([
                'allOf' => [
                    [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                        ],
                        'required' => ['id'],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                        ],
                        'required' => ['name'],
                    ],
                ],
            ])
        );
    }

    public function testGeneratesSchemaFromJsonString(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame(
            '$p->string()->minLength(1)',
            $generator->generate('{"type":"string","minLength":1}')
        );
    }

    public function testRejectsUnsupportedStringFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported string format: binary');

        $generator->generate(['type' => 'string', 'format' => 'binary']);
    }

    public function testRejectsRef(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$ref is not supported');

        $generator->generate(['$ref' => '#/$defs/address']);
    }
}
