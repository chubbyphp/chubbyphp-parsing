<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

final class DiscriminatedUnionDemo implements \JsonSerializable
{
    public string $field1;
    public string $field2;

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
 * @covers \Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema
 *
 * @internal
 */
final class DiscriminatedUnionSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (\stdClass $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (\stdClass $output, ParserErrorException $e) => $output));
    }

    public function testConstructWithoutObjectSchema(): void
    {
        try {
            new DiscriminatedUnionSchema(['test'], 'field1');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #0 ($objectSchemas) must be of type Chubbyphp\Parsing\Schema\ObjectSchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithoutFieldSchema(): void
    {
        try {
            new DiscriminatedUnionSchema([new ObjectSchema([])], 'field1');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #0 #field1 ($objectSchemas) must contain Chubbyphp\Parsing\Schema\SchemaInterface',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithStdClassInput(): void
    {
        $input = new \stdClass();
        $input->field1 = 'type2';
        $input->field2 = 'test';

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithIteratorInput(): void
    {
        $input = new \ArrayIterator(['field1' => 'type2', 'field2' => 'test']);

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithJsonSerialzableObject(): void
    {
        $input = new DiscriminatedUnionDemo();
        $input->field1 = 'type2';
        $input->field2 = 'test';

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame((array) $input, (array) $output);
    }

    public function testParseSuccessWithArrayDiscriminator(): void
    {
        $input = ['field1' => [5], 'field2' => 'test'];

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new ArraySchema(new StringSchema())]),
            new ObjectSchema(['field1' => new ArraySchema(new IntSchema()), 'field2' => new StringSchema()]),
        ], 'field1');

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = ['field1' => 'type2', 'field2' => 'test1'];
        $input2 = ['field1' => 'type2', 'field2' => 'test2'];

        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->default($input1);

        self::assertSame($input1, (array) $schema->parse(null));
        self::assertSame($input2, (array) $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'discriminatedUnion.type',
                    'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithMissingDiscriminatorField(): void
    {
        $input = ['field2' => 'test'];

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'discriminatedUnion.discriminatorField',
                    'template' => 'Input does not contain the discriminator field {{discriminatorFieldName}}',
                    'variables' => [
                        'discriminatorFieldName' => 'field1',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithNoMatchingDiscriminator(): void
    {
        $input = ['field1' => 'type3', 'field2' => 'test'];

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 'type1',
                        'given' => 'type3',
                    ],
                ],
                1 => [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 'type2',
                        'given' => 'type3',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->preParse(static fn () => $input);

        self::assertSame($input, (array) $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->postParse(static function (\stdClass $output) {
            $output->field3 = 'test';

            return $output;
        });

        self::assertSame([...$input, 'field3' => 'test'], (array) $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'discriminatedUnion.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
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
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        self::assertSame($input, (array) $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1');

        self::assertSame([
            [
                'code' => 'discriminatedUnion.type',
                'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }
}
