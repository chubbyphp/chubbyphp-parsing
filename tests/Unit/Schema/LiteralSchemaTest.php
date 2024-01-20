<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\LiteralSchema
 *
 * @internal
 */
final class LiteralSchemaTest extends TestCase
{
    public function testParseSuccessWithBool(): void
    {
        $input = true;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithFloat(): void
    {
        $input = 1.5;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new LiteralSchema($input))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new LiteralSchema('test'))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new LiteralSchema('test');

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals(['Type should be "bool|float|int|string" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseFailedWithDifferentLiteralValue(): void
    {
        $schema = new LiteralSchema('tes1');

        try {
            $schema->parse('test2');

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals(['Input should be "tes1" "test2" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = 'test1';

        $schema = (new LiteralSchema($input))->transform(static fn (string $input) => $input.'1');

        self::assertSame('test11', $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new LiteralSchema('test'))
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame(['Type should be "bool|float|int|string" "NULL" given'], $parserErrorException->getErrors());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new LiteralSchema('test');

        self::assertSame(['Type should be "bool|float|int|string" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}
