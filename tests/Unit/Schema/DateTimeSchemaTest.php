<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\DateTimeSchema
 *
 * @internal
 */
final class DateTimeSchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00Z');

        $schema = new DateTimeSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00Z');

        $schema = (new DateTimeSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new DateTimeSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new DateTimeSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Type should be "DateTimeInterface" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00Z');

        $schema = (new DateTimeSchema())->transform(static fn (\DateTimeInterface $output) => $output->format('c'));

        self::assertSame($input->format('c'), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new DateTimeSchema())
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame(['Type should be "DateTimeInterface" "NULL" given'], $parserErrorException->getErrors());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00Z');

        $schema = new DateTimeSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new DateTimeSchema();

        self::assertSame(['Type should be "DateTimeInterface" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}
