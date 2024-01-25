<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\ObjectSchema
 *
 * @internal
 */
final class ObjectSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->middleware(static fn (\stdClass $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (\stdClass $output, ParserErrorException $e) => $output));
    }

    public function testConstructWithoutFieldName(): void
    {
        try {
            new ObjectSchema([new StringSchema()]);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 name #0 ($fieldSchemas) must be of type string, integer given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithoutFieldSchema(): void
    {
        try {
            new ObjectSchema(['field1' => new StringSchema(), 'field2' => '', 'field3' => new IntSchema()]);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #field2 ($fieldSchemas) must be of type Chubbyphp\Parsing\Schema\SchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = (new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]))->default($input);

        $output = $schema->parse(null);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'object.type',
                    'template' => 'Type should be "array", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithAdditionalFields(): void
    {
        $input = ['field1' => 'test', 'field2' => 1, 'field3' => 1.5];

        $schema = new ObjectSchema(['field1' => new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                'field2' => [
                    [
                        'code' => 'object.unknownField',
                        'template' => 'Unknown field "{{fieldName}}"',
                        'variables' => [
                            'fieldName' => 'field2',
                        ],
                    ],
                ],
                'field3' => [
                    [
                        'code' => 'object.unknownField',
                        'template' => 'Unknown field "{{fieldName}}"',
                        'variables' => [
                            'fieldName' => 'field3',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithFailedFields(): void
    {
        $input = ['field1' => 'test', 'field2' => 'test', 'field3' => 'test'];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new FloatSchema(), 'field3' => new IntSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                'field2' => [
                    [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", "{{given}}" given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
                'field3' => [
                    [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", "{{given}}" given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithmiddleware(): void
    {
        $input = ['field1' => 'test'];

        $schema = (new ObjectSchema(['field1' => new StringSchema()]))->middleware(static function (\stdClass $output) {
            $output->field2 = 'test';

            return $output;
        });

        self::assertSame([...$input, 'field2' => 'test'], (array) $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new ObjectSchema(['field1' => new StringSchema()]))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'object.type',
                        'template' => 'Type should be "array", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ], $this->errorsToSimpleArray($parserErrorException->getErrors()));

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        self::assertSame($input, (array) $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        self::assertSame([
            [
                'code' => 'object.type',
                'template' => 'Type should be "array", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }

    public function testGetFieldSchemaSuccess(): void
    {
        $field2Schema = new IntSchema();

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => $field2Schema]);

        self::assertSame($field2Schema, $schema->getFieldSchema('field2'));
    }

    public function testGetFieldSchemaFailed(): void
    {
        $schema = new ObjectSchema(['field1' => new StringSchema()]);

        self::assertNull($schema->getFieldSchema('field2'));
    }
}
