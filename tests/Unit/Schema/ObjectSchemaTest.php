<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\BoolSchema;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

final class ObjectDemo implements \JsonSerializable
{
    public string $field1;
    public int $field2;

    public function jsonSerialize(): array
    {
        return [
            'field1' => $this->field1,
            'field2' => $this->field2,
        ];
    }
}

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\ObjectSchema
 *
 * @internal
 */
final class ObjectSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (\stdClass $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (\stdClass $output, ErrorsException $e) => $output));

        self::assertNotSame($schema, $schema->strict());
        self::assertNotSame($schema, $schema->optional([]));
    }

    public function testConstructWithoutFieldName(): void
    {
        try {
            new ObjectSchema([new StringSchema()]);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 name #0 ($fieldToSchema) must be of type string, integer given',
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
                'Argument #1 value of #field2 ($fieldToSchema) must be of type Chubbyphp\Parsing\Schema\SchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        $output = $schema->parse([...$input, 'field3' => 1.5]);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithClass(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()], ObjectDemo::class);

        $output = $schema->parse($input);

        self::assertInstanceOf(ObjectDemo::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithStdClassInput(): void
    {
        $input = new \stdClass();
        $input->field1 = 'test';
        $input->field2 = 1;

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithIteratorInput(): void
    {
        $input = new \ArrayIterator(['field1' => 'test', 'field2' => 1]);

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithJsonSerialzableObject(): void
    {
        $input = new ObjectDemo();
        $input->field1 = 'test';
        $input->field2 = 1;

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithOptional(): void
    {
        $input = ['field2' => 'test', 'field3' => null];

        $schema = (new ObjectSchema([
            'field1' => new StringSchema(),
            'field2' => new StringSchema(),
            'field3' => (new StringSchema())->nullable(),
        ]))->optional(['field1', 'field3']);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithStrictIgnore(): void
    {
        $input = ['field1' => 'test', 'field2' => 1];

        $schema = (new ObjectSchema(['field1' => new StringSchema()]))->strict(['field2']);

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame(['field1' => 'test'], (array) $output);
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = ['field1' => 'test', 'field2' => 1];
        $input2 = ['field1' => 'test', 'field2' => 2];

        $schema = (new ObjectSchema(['field1' => new StringSchema(), 'field2' => new IntSchema()]))->default($input1);

        self::assertSame($input1, (array) $schema->parse(null));
        self::assertSame($input2, (array) $schema->parse($input2));
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
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'object.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithAdditionalFields(): void
    {
        $input = ['field1' => 'test', 'field2' => 1, 'field3' => 1.5];

        $schema = (new ObjectSchema(['field1' => new StringSchema()]))->strict();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => 'field2',
                    'error' => [
                        'code' => 'object.unknownField',
                        'template' => 'Unknown field {{fieldName}}',
                        'variables' => [
                            'fieldName' => 'field2',
                        ],
                    ],
                ],
                [
                    'path' => 'field3',
                    'error' => [
                        'code' => 'object.unknownField',
                        'template' => 'Unknown field {{fieldName}}',
                        'variables' => [
                            'fieldName' => 'field3',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithFailedFields(): void
    {
        $input = ['field1' => 'test', 'field2' => 'test', 'field3' => 'test'];

        $schema = new ObjectSchema(['field1' => new StringSchema(), 'field2' => new FloatSchema(), 'field3' => new IntSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => 'field2',
                    'error' => [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", {{given}} given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
                [
                    'path' => 'field3',
                    'error' => [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithOptionalButStillNotProvidedEnoughFields(): void
    {
        try {
            $schema = (new ObjectSchema([
                'field1' => new StringSchema(),
                'field2' => new StringSchema(),
                'field3' => new StringSchema(),
            ]))->optional(['field1']);

            $schema->parse(['field2' => 'test']);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                'field3: Type should be "string", "NULL" given',
                $errorsException->getMessage()
            );
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = ['field1' => 'test'];

        $schema = (new ObjectSchema(['field1' => new StringSchema()]))->preParse(static fn () => $input);

        self::assertSame($input, (array) $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = ['field1' => 'test'];

        $schema = (new ObjectSchema(['field1' => new StringSchema()]))->postParse(static function (\stdClass $output) {
            $output->field2 = 'test';

            return $output;
        });

        self::assertSame([...$input, 'field2' => 'test'], (array) $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new ObjectSchema(['field1' => new StringSchema()]))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'object.type',
                            'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                ], $errorsException->errors->jsonSerialize());

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
                'path' => '',
                'error' => [
                    'code' => 'object.type',
                    'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testGetFieldToSchema(): void
    {
        $fieldToSchema = ['field1' => new StringSchema(), 'field2' => new IntSchema()];

        $schema = new ObjectSchema($fieldToSchema);

        self::assertSame($fieldToSchema, $schema->getFieldToSchema());

        $fieldToSchema2 = [...$schema->getFieldToSchema(), 'field3' => new BoolSchema()];

        $schema2 = new ObjectSchema($fieldToSchema2);

        self::assertSame($fieldToSchema2, $schema2->getFieldToSchema());
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
