<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\UnionSchema
 *
 * @internal
 */
final class UnionSchemaTest extends TestCase
{
    public function testConstructWithWrongArgument(): void
    {
        try {
            new UnionSchema([new StringSchema(), 'string']);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertEquals(
                'Argument #1 value of #1 ($schemas) must be of type SchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithStringAndDefault(): void
    {
        $input = 'test';

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithIntAndDefault(): void
    {
        $input = 1;

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals([
                'Type should be "string" "NULL" given',
                'Type should be "int" "NULL" given',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithStringAndTransform(): void
    {
        $input = '1';

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->transform(static fn (string $input) => (int) $input);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseSuccessWithIntAndTransform(): void
    {
        $input = 1;

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->transform(static fn (int $input) => (string) $input);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    'Type should be "string" "NULL" given',
                    'Type should be "int" "NULL" given',
                ], $parserErrorException->getErrors());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame([
            'Type should be "string" "NULL" given',
            'Type should be "int" "NULL" given',
        ], $schema->safeParse(null)->exception->getErrors());
    }
}
