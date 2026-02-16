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
    public function testStringBasic(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string']);

        self::assertSame('$p->string()', $code);
    }

    public function testStringWithMinLength(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'minLength' => 3]);

        self::assertSame('$p->string()->minLength(3)', $code);
    }

    public function testStringWithMaxLength(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'maxLength' => 100]);

        self::assertSame('$p->string()->maxLength(100)', $code);
    }

    public function testStringWithMinAndMaxLength(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'minLength' => 1, 'maxLength' => 255]);

        self::assertSame('$p->string()->minLength(1)->maxLength(255)', $code);
    }

    public function testStringWithPattern(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'pattern' => '^[a-z]+$']);

        self::assertSame("\$p->string()->pattern('/^[a-z]+$/')", $code);
    }

    public function testStringWithEmailFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'email']);

        self::assertSame('$p->string()->email()', $code);
    }

    public function testStringWithUriFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'uri']);

        self::assertSame('$p->string()->uri()', $code);
    }

    public function testStringWithIpv4Format(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'ipv4']);

        self::assertSame('$p->string()->ipV4()', $code);
    }

    public function testStringWithIpv6Format(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'ipv6']);

        self::assertSame('$p->string()->ipV6()', $code);
    }

    public function testStringWithUuidFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'uuid']);

        self::assertSame('$p->string()->uuid()', $code);
    }

    public function testStringWithHostnameFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'hostname']);

        self::assertSame('$p->string()->hostname()', $code);
    }

    public function testStringWithDateTimeFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'date-time']);

        self::assertSame('$p->string()->toDateTime()', $code);
    }

    public function testStringWithUnsupportedFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'binary']);

        self::assertSame('$p->string() /* unsupported format: binary */', $code);
    }

    public function testInteger(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer']);

        self::assertSame('$p->int()', $code);
    }

    public function testIntegerWithMinimum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'minimum' => 0]);

        self::assertSame('$p->int()->minimum(0)', $code);
    }

    public function testIntegerWithMaximum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'maximum' => 100]);

        self::assertSame('$p->int()->maximum(100)', $code);
    }

    public function testIntegerWithExclusiveMinimum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'exclusiveMinimum' => 0]);

        self::assertSame('$p->int()->exclusiveMinimum(0)', $code);
    }

    public function testIntegerWithExclusiveMaximum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'exclusiveMaximum' => 100]);

        self::assertSame('$p->int()->exclusiveMaximum(100)', $code);
    }

    public function testIntegerWithAllConstraints(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'integer',
            'minimum' => 1,
            'maximum' => 99,
        ]);

        self::assertSame('$p->int()->minimum(1)->maximum(99)', $code);
    }

    public function testIntegerWithMinimumAndExclusiveMinimumTrue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'minimum' => 0, 'exclusiveMinimum' => true]);

        self::assertSame('$p->int()->exclusiveMinimum(0)', $code);
    }

    public function testIntegerWithMinimumAndExclusiveMinimumFalse(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'minimum' => 0, 'exclusiveMinimum' => false]);

        self::assertSame('$p->int()->minimum(0)', $code);
    }

    public function testIntegerWithMaximumAndExclusiveMaximumTrue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'maximum' => 100, 'exclusiveMaximum' => true]);

        self::assertSame('$p->int()->exclusiveMaximum(100)', $code);
    }

    public function testIntegerWithMaximumAndExclusiveMaximumFalse(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'integer', 'maximum' => 100, 'exclusiveMaximum' => false]);

        self::assertSame('$p->int()->maximum(100)', $code);
    }

    public function testNumber(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number']);

        self::assertSame('$p->float()', $code);
    }

    public function testNumberWithMinimum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'minimum' => 0.5]);

        self::assertSame('$p->float()->minimum(0.5)', $code);
    }

    public function testNumberWithMaximum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'maximum' => 99.9]);

        self::assertSame('$p->float()->maximum(99.9)', $code);
    }

    public function testNumberWithExclusiveMinimum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'exclusiveMinimum' => 0.0]);

        self::assertSame('$p->float()->exclusiveMinimum(0.0)', $code);
    }

    public function testNumberWithExclusiveMaximum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'exclusiveMaximum' => 100.0]);

        self::assertSame('$p->float()->exclusiveMaximum(100.0)', $code);
    }

    public function testNumberWithMinimumAndExclusiveMinimumTrue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'minimum' => 0.5, 'exclusiveMinimum' => true]);

        self::assertSame('$p->float()->exclusiveMinimum(0.5)', $code);
    }

    public function testNumberWithMinimumAndExclusiveMinimumFalse(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'minimum' => 0.5, 'exclusiveMinimum' => false]);

        self::assertSame('$p->float()->minimum(0.5)', $code);
    }

    public function testNumberWithMaximumAndExclusiveMaximumTrue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'maximum' => 99.9, 'exclusiveMaximum' => true]);

        self::assertSame('$p->float()->exclusiveMaximum(99.9)', $code);
    }

    public function testNumberWithMaximumAndExclusiveMaximumFalse(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'maximum' => 99.9, 'exclusiveMaximum' => false]);

        self::assertSame('$p->float()->maximum(99.9)', $code);
    }

    public function testBoolean(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'boolean']);

        self::assertSame('$p->bool()', $code);
    }

    public function testNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'null']);

        self::assertSame('$p->string()->nullable()->default(null)', $code);
    }

    public function testArrayBasic(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => ['type' => 'string'],
        ]);

        self::assertSame('$p->array($p->string())', $code);
    }

    public function testArrayOfIntegers(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]);

        self::assertSame('$p->array($p->int())', $code);
    }

    public function testArrayWithMinItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => ['type' => 'string'],
            'minItems' => 1,
        ]);

        self::assertSame('$p->array($p->string())->minItems(1)', $code);
    }

    public function testArrayWithMaxItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => ['type' => 'string'],
            'maxItems' => 10,
        ]);

        self::assertSame('$p->array($p->string())->maxItems(10)', $code);
    }

    public function testArrayWithMinAndMaxItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => ['type' => 'string'],
            'minItems' => 1,
            'maxItems' => 10,
        ]);

        self::assertSame('$p->array($p->string())->minItems(1)->maxItems(10)', $code);
    }

    public function testArrayWithNoItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'array']);

        self::assertSame('$p->array($p->string())', $code);
    }

    public function testArrayOfObjects(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                ],
                'required' => ['name'],
            ],
        ]);

        self::assertSame("\$p->array(\$p->object(['name' => \$p->string()]))", $code);
    }

    public function testTupleWithPrefixItems(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'prefixItems' => [
                ['type' => 'number'],
                ['type' => 'number'],
            ],
        ]);

        self::assertSame('$p->tuple([$p->float(), $p->float()])', $code);
    }

    public function testObjectBasic(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name', 'age'],
        ]);

        self::assertSame("\$p->object(['name' => \$p->string(), 'age' => \$p->int()])", $code);
    }

    public function testObjectWithOptionalFields(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'nickname' => ['type' => 'string'],
            ],
            'required' => ['name'],
        ]);

        self::assertSame(
            "\$p->object(['name' => \$p->string(), 'nickname' => \$p->string()->nullable()])->optional(['nickname'])",
            $code,
        );
    }

    public function testObjectStrict(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
            'required' => ['name'],
            'additionalProperties' => false,
        ]);

        self::assertSame("\$p->object(['name' => \$p->string()])->strict()", $code);
    }

    public function testObjectAsRecord(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'additionalProperties' => ['type' => 'string'],
        ]);

        self::assertSame('$p->record($p->string())', $code);
    }

    public function testObjectAsRecordWithIntValues(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'additionalProperties' => ['type' => 'integer'],
        ]);

        self::assertSame('$p->record($p->int())', $code);
    }

    public function testObjectNested(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'address' => [
                    'type' => 'object',
                    'properties' => [
                        'street' => ['type' => 'string'],
                        'city' => ['type' => 'string'],
                    ],
                    'required' => ['street', 'city'],
                ],
            ],
            'required' => ['address'],
        ]);

        self::assertSame(
            "\$p->object(['address' => \$p->object(['street' => \$p->string(), 'city' => \$p->string()])])",
            $code,
        );
    }

    public function testConst(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        self::assertSame("\$p->const('active')", $generator->generate(['const' => 'active']));
        self::assertSame('$p->const(42)', $generator->generate(['const' => 42]));
        self::assertSame('$p->const(true)', $generator->generate(['const' => true]));
    }

    public function testEnumStrings(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['enum' => ['active', 'inactive', 'pending']]);

        self::assertSame(
            "\$p->union([\$p->const('active'), \$p->const('inactive'), \$p->const('pending')])",
            $code,
        );
    }

    public function testEnumSingleValue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['enum' => ['only']]);

        self::assertSame("\$p->const('only')", $code);
    }

    public function testEnumWithNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['enum' => ['active', 'inactive', null]]);

        self::assertSame(
            "\$p->union([\$p->const('active'), \$p->const('inactive')])->nullable()",
            $code,
        );
    }

    public function testEnumIntegers(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['enum' => [1, 2, 3]]);

        self::assertSame('$p->union([$p->const(1), $p->const(2), $p->const(3)])', $code);
    }

    public function testOneOfSimple(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'oneOf' => [
                ['type' => 'string'],
                ['type' => 'integer'],
            ],
        ]);

        self::assertSame('$p->union([$p->string(), $p->int()])', $code);
    }

    public function testOneOfWithNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'oneOf' => [
                ['type' => 'string'],
                ['type' => 'null'],
            ],
        ]);

        self::assertSame('$p->string()->nullable()', $code);
    }

    public function testAnyOfSimple(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'anyOf' => [
                ['type' => 'string'],
                ['type' => 'integer'],
            ],
        ]);

        self::assertSame('$p->union([$p->string(), $p->int()])', $code);
    }

    public function testNullableTypeArray(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => ['string', 'null']]);

        self::assertSame('$p->string()->nullable()', $code);
    }

    public function testMultiType(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => ['string', 'integer']]);

        self::assertSame('$p->union([$p->string(), $p->int()])', $code);
    }

    public function testMultiTypeWithNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => ['string', 'integer', 'null']]);

        self::assertSame('$p->union([$p->string(), $p->int()])->nullable()', $code);
    }

    public function testAllOfMerge(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'allOf' => [
                [
                    'type' => 'object',
                    'properties' => ['id' => ['type' => 'integer']],
                    'required' => ['id'],
                ],
                [
                    'type' => 'object',
                    'properties' => ['name' => ['type' => 'string']],
                    'required' => ['name'],
                ],
            ],
        ]);

        self::assertSame("\$p->object(['id' => \$p->int(), 'name' => \$p->string()])", $code);
    }

    public function testDiscriminatedUnion(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
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
        ]);

        self::assertSame(
            "\$p->discriminatedUnion([\$p->object(['type' => \$p->const('email'), 'address' => \$p->string()]), "
            ."\$p->object(['type' => \$p->const('phone'), 'number' => \$p->string()])], 'type')",
            $code,
        );
    }

    public function testFromJsonString(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $json = '{"type": "string", "minLength": 1}';
        $code = $generator->generate($json);

        self::assertSame('$p->string()->minLength(1)', $code);
    }

    public function testInvalidJsonString(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON string provided');

        $generator->generate('not valid json');
    }

    public function testUnsupportedType(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported JSON Schema type: foobar');

        $generator->generate(['type' => 'foobar']);
    }

    public function testRefThrowsException(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$ref is not supported');

        $generator->generate(['$ref' => '#/definitions/Foo']);
    }

    public function testEmptyEnumThrowsException(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Enum must be a non-empty array');

        $generator->generate(['enum' => []]);
    }

    public function testComplexSchema(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'minimum' => 1],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
                'email' => ['type' => 'string', 'format' => 'email'],
                'age' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 150],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 1,
                ],
                'metadata' => [
                    'type' => 'object',
                    'additionalProperties' => ['type' => 'string'],
                ],
            ],
            'required' => ['id', 'name', 'email'],
            'additionalProperties' => false,
        ]);

        self::assertSame(
            "\$p->object(['id' => \$p->int()->minimum(1), 'name' => \$p->string()->minLength(1)->maxLength(100), "
            ."'email' => \$p->string()->email(), 'age' => \$p->int()->minimum(0)->maximum(150)->nullable(), "
            ."'tags' => \$p->array(\$p->string())->minItems(1)->nullable(), 'metadata' => \$p->record(\$p->string())->nullable()])"
            ."->optional(['age', 'tags', 'metadata'])->strict()",
            $code,
        );
    }

    public function testSchemaWithNoType(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([]);

        self::assertSame('$p->string()', $code);
    }

    public function testSchemaWithNoTypeButProperties(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'properties' => [
                'name' => ['type' => 'string'],
            ],
            'required' => ['name'],
        ]);

        self::assertSame("\$p->object(['name' => \$p->string()])", $code);
    }

    public function testObjectEmptyPropertiesWithAdditionalPropertiesTrue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'additionalProperties' => true,
        ]);

        self::assertSame('$p->record($p->string())', $code);
    }

    public function testStringWithMultipleConstraints(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'string',
            'minLength' => 3,
            'maxLength' => 50,
            'format' => 'email',
        ]);

        self::assertSame('$p->string()->minLength(3)->maxLength(50)->email()', $code);
    }

    public function testNestedArraysOfObjects(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'array',
            'items' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
            ],
        ]);

        self::assertSame('$p->array($p->array($p->int()))', $code);
    }

    public function testDiscriminatedUnionWithEnum(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'oneOf' => [
                [
                    'type' => 'object',
                    'properties' => [
                        'kind' => ['enum' => ['circle']],
                        'radius' => ['type' => 'number'],
                    ],
                    'required' => ['kind', 'radius'],
                ],
                [
                    'type' => 'object',
                    'properties' => [
                        'kind' => ['enum' => ['square']],
                        'side' => ['type' => 'number'],
                    ],
                    'required' => ['kind', 'side'],
                ],
            ],
        ]);

        self::assertSame(
            "\$p->discriminatedUnion([\$p->object(['kind' => \$p->const('circle'), 'radius' => \$p->float()]), "
            ."\$p->object(['kind' => \$p->const('square'), 'side' => \$p->float()])], 'kind')",
            $code,
        );
    }

    public function testNullableTypeArrayWithConstraints(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => ['string', 'null'],
            'minLength' => 1,
        ]);

        self::assertSame('$p->string()->minLength(1)->nullable()', $code);
    }

    public function testStringWithUrlFormat(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'string', 'format' => 'url']);

        self::assertSame('$p->string()->uri()', $code);
    }

    public function testAllOfWithOptionalFields(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'allOf' => [
                [
                    'type' => 'object',
                    'properties' => ['id' => ['type' => 'integer']],
                    'required' => ['id'],
                ],
                [
                    'type' => 'object',
                    'properties' => ['nickname' => ['type' => 'string']],
                ],
            ],
        ]);

        self::assertSame(
            "\$p->object(['id' => \$p->int(), 'nickname' => \$p->string()->nullable()])->optional(['nickname'])",
            $code,
        );
    }

    public function testObjectAllFieldsOptional(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate([
            'type' => 'object',
            'properties' => [
                'a' => ['type' => 'string'],
                'b' => ['type' => 'integer'],
            ],
        ]);

        self::assertSame(
            "\$p->object(['a' => \$p->string()->nullable(), 'b' => \$p->int()->nullable()])->optional(['a', 'b'])",
            $code,
        );
    }

    public function testRefInsideNestedSchemaThrows(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$ref is not supported');

        $generator->generate([
            'type' => 'object',
            'properties' => [
                'child' => ['$ref' => '#/definitions/Child'],
            ],
            'required' => ['child'],
        ]);
    }

    public function testEnumOnlyNull(): void
    {
        $generator = new JsonSchemaCodeGenerator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Enum must have at leas one non null value');

        $generator->generate(['enum' => [null]]);
    }

    public function testObjectWithNoProperties(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'object']);

        self::assertSame('$p->record($p->string())', $code);
    }

    public function testNumberIntegerWholeValue(): void
    {
        $generator = new JsonSchemaCodeGenerator();
        $code = $generator->generate(['type' => 'number', 'minimum' => 0]);

        self::assertSame('$p->float()->minimum(0.0)', $code);
    }
}
