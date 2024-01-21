<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\StringSchema
 *
 * @internal
 */
final class StringSchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new StringSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new StringSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Type should be "string" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = '1';

        $schema = (new StringSchema())->transform(static fn (string $output) => (int) $output);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new StringSchema())
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame(['Type should be "string" "NULL" given'], $parserErrorException->getErrors());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new StringSchema();

        self::assertSame(['Type should be "string" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}
