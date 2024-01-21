<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema
 *
 * @internal
 */
final class DiscriminatedUnionSchemaTest extends TestCase
{
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
                'Argument #1 value of #0 #field1 ($objectSchemas) must contain Chubbyphp\Parsing\Schema\LiteralSchemaInterface',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithFieldSchemaIsNotLiteral(): void
    {
        try {
            new DiscriminatedUnionSchema([new ObjectSchema(['field1' => new StringSchema()])], 'field1');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #0 #field1 ($objectSchemas) must be of type Chubbyphp\Parsing\Schema\LiteralSchemaInterface, Chubbyphp\Parsing\Schema\StringSchema given',
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

    public function testParseSuccessWithDefault(): void
    {
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->default($input);

        $output = $schema->parse(null);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
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
            self::assertSame(['Type should be "array" "NULL" given'], $parserErrorException->getErrors());
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
            self::assertSame(['Missing discriminator value on field "field1"'], $parserErrorException->getErrors());
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
                'Input should be "type1" "type3" given',
                'Input should be "type2" "type3" given',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = ['field1' => 'type2', 'field2' => 'test'];

        $schema = (new DiscriminatedUnionSchema([
            new ObjectSchema(['field1' => new LiteralSchema('type1')]),
            new ObjectSchema(['field1' => new LiteralSchema('type2'), 'field2' => new StringSchema()]),
        ], 'field1'))->transform(static function (\stdClass $output) {
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
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame(['Type should be "array" "NULL" given'], $parserErrorException->getErrors());

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

        self::assertSame(['Type should be "array" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}
