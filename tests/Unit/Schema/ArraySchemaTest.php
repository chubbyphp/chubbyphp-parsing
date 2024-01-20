<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\ArraySchema
 *
 * @internal
 */
final class ArraySchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = ['test'];

        $schema = new ArraySchema(new StringSchema());

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = ['test'];

        $schema = (new ArraySchema(new StringSchema()))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new ArraySchema(new StringSchema()))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new ArraySchema(new StringSchema());

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals(['Type should be "array" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseFailedWithoutStringInArray(): void
    {
        $input = ['test', 1, true];

        $schema = new ArraySchema(new StringSchema());

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals([
                1 => ['Type should be "string" "integer" given'],
                2 => ['Type should be "string" "boolean" given'],
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = ['test1'];

        $schema = (new ArraySchema(new StringSchema()))->transform(static fn (array $input) => array_merge($input, ['test2']));

        self::assertEquals(array_merge($input, ['test2']), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new ArraySchema(new StringSchema()))
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
        $input = ['test'];

        $schema = new ArraySchema(new StringSchema());

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new ArraySchema(new StringSchema());

        self::assertSame(['Type should be "array" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}
